<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Central SMS send orchestrator.
 *
 * All outbound SMS goes through this class. It handles event gating,
 * phone resolution, template rendering, and the full validation pipeline
 * before dispatching messages via the kwtSMS API.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Central SMS send orchestrator.
 *
 * Provides two entry points: send_notification() for event-driven messages
 * with template rendering, and send() for raw message dispatch with a
 * 12-step validation pipeline.
 */
class manager {
    /**
     * Send a notification for a specific event type to a user or admin phones.
     *
     * Checks whether the event is enabled, resolves the recipient phone(s),
     * builds placeholders, renders the template, and dispatches via send().
     *
     * @param string $eventtype     The event type identifier (e.g. 'user_enrolment_created').
     * @param object $user          The Moodle user object related to the event.
     * @param array  $placeholders  Event-specific placeholder key-value pairs.
     * @param string $recipienttype Either 'student' or 'admin'.
     * @return void
     */
    public static function send_notification(
        string $eventtype,
        object $user,
        array $placeholders,
        string $recipienttype = 'student'
    ): void {
        // 1. Check if event is enabled. Return silently if disabled.
        if (!get_config('local_kwtsms', 'event_' . $eventtype)) {
            return;
        }

        // 2/3. Resolve recipient phones.
        $phones = [];
        if ($recipienttype === 'admin') {
            $adminphones = get_config('local_kwtsms', 'admin_phones');
            if (empty($adminphones)) {
                return;
            }
            $phones = array_map('trim', explode(',', $adminphones));
            $phones = array_filter($phones, function ($p) {
                return $p !== '';
            });
            $phones = array_values($phones);
            if (empty($phones)) {
                return;
            }
        } else {
            $phone = phone_utils::get_user_phone($user);
            if ($phone === '') {
                self::log_skip($user->id ?? 0, '', $eventtype, 'no_phone_number');
                return;
            }
            $phones = [$phone];
        }

        // 4. Build common placeholders.
        $site = get_site();
        $commonplaceholders = [
            'firstname' => $user->firstname ?? '',
            'lastname'  => $user->lastname ?? '',
            'fullname'  => fullname($user),
            'sitename'  => format_string($site->fullname),
            'date'      => userdate(time(), get_string('strftimedate', 'langconfig')),
            'time'      => userdate(time(), get_string('strftimetime', 'langconfig')),
        ];
        $placeholders = array_merge($commonplaceholders, $placeholders);

        // Determine user language for template rendering.
        $userlang = $user->lang ?? '';

        // 5. Render template.
        $rendered = template_manager::render_template($eventtype, $placeholders, $userlang);

        // 6. If empty message, return.
        if ($rendered['message'] === '') {
            return;
        }

        // 7. Dispatch.
        self::send($phones, $rendered['message'], $eventtype, $rendered['template_id'], $user->id ?? 0);
    }

    /**
     * Send an SMS message through the 12-step validation pipeline.
     *
     * Validates gateway state, normalizes and filters phones, checks balance,
     * cleans the message, sends via API, and logs the result for each phone.
     *
     * @param array  $phones     Raw phone number strings.
     * @param string $message    The message text to send.
     * @param string $eventtype  The event type identifier (for logging).
     * @param int    $templateid The template record id (for logging).
     * @param int    $userid     The Moodle user id (for logging).
     * @return void
     */
    public static function send(
        array $phones,
        string $message,
        string $eventtype = '',
        int $templateid = 0,
        int $userid = 0
    ): void {
        // Step 1. Check gateway enabled.
        if (!api_client::is_enabled()) {
            foreach ($phones as $phone) {
                self::log_skip($userid, $phone, $eventtype, 'gateway_disabled', $templateid);
            }
            return;
        }

        // Step 2. Check configured.
        if (!api_client::is_configured()) {
            foreach ($phones as $phone) {
                self::log_skip($userid, $phone, $eventtype, 'gateway_not_configured', $templateid);
            }
            return;
        }

        // Step 3. Normalize phones and validate each.
        $countrycode = get_config('local_kwtsms', 'default_country_code');
        $countrycode = !empty($countrycode) ? (string) $countrycode : '';

        $normalized = [];
        foreach ($phones as $phone) {
            $norm = phone_utils::normalize($phone, $countrycode);
            if (!phone_utils::validate($norm)) {
                self::log_skip($userid, $phone, $eventtype, 'no_phone_number', $templateid);
                continue;
            }
            $normalized[] = $norm;
        }

        if (empty($normalized)) {
            return;
        }

        // Step 4. Deduplicate.
        $normalized = phone_utils::deduplicate($normalized);

        // Step 5. Filter by coverage.
        $coverage = api_client::get_cached_coverage();
        if (!empty($coverage)) {
            $filtered = phone_utils::filter_by_coverage($normalized, $coverage);
            foreach ($filtered['uncovered'] as $uncoveredphone) {
                self::log_skip($userid, $uncoveredphone, $eventtype, 'country_not_covered', $templateid);
            }
            $normalized = $filtered['covered'];
            if (empty($normalized)) {
                return;
            }
        }

        // Step 6. Check cached balance.
        $balance = api_client::get_cached_balance();
        if ($balance <= 0) {
            foreach ($normalized as $phone) {
                self::log_skip($userid, $phone, $eventtype, 'zero_balance', $templateid);
            }
            return;
        }

        // Step 7. Clean message.
        $cleanedmessage = message_utils::clean($message);
        if ($cleanedmessage === '') {
            foreach ($normalized as $phone) {
                self::log_skip($userid, $phone, $eventtype, 'empty_message', $templateid);
            }
            return;
        }

        // Step 8. Get client.
        $client = api_client::get_client();
        if ($client === null) {
            foreach ($normalized as $phone) {
                self::log_skip($userid, $phone, $eventtype, 'gateway_not_configured', $templateid);
            }
            return;
        }

        // Step 9. Get sender_id and test_mode from config.
        $senderid = get_config('local_kwtsms', 'sender_id');
        $senderid = !empty($senderid) ? (string) $senderid : 'KWT-SMS';
        $testmode = !empty(get_config('local_kwtsms', 'test_mode'));

        // Step 10. Send via API.
        $response = $client->send(implode(',', $normalized), $cleanedmessage);

        // Step 11. Update cached balance from response if present.
        if (isset($response['balance-after'])) {
            $cachedata = [
                'balance'   => (int) $response['balance-after'],
                'timestamp' => time(),
            ];
            api_client::set_cache('balance', json_encode($cachedata));
        }

        // Step 12. Log result for each phone.
        $ok = isset($response['result']) && $response['result'] === 'OK';
        $status = $ok ? 'sent' : 'failed';
        $errorcode = $response['code'] ?? null;
        $apiresponse = api_client::sanitize_response($response);
        $credits = (int) ($response['points-charged'] ?? 0);

        // Calculate per-phone credits (distribute evenly).
        $phonecount = count($normalized);
        $creditsperphone = ($phonecount > 0) ? (int) floor($credits / $phonecount) : 0;

        foreach ($normalized as $phone) {
            self::log_send(
                $userid,
                $phone,
                $cleanedmessage,
                $eventtype,
                $senderid,
                $status,
                $apiresponse,
                $errorcode,
                $creditsperphone,
                $testmode,
                $templateid
            );
        }
    }

    /**
     * Log a skipped SMS send attempt.
     *
     * @param int    $userid     The Moodle user id.
     * @param string $phone      The phone number (may be empty).
     * @param string $eventtype  The event type identifier.
     * @param string $reason     The skip reason code.
     * @param int    $templateid The template record id.
     * @return void
     */
    private static function log_skip(
        int $userid,
        string $phone,
        string $eventtype,
        string $reason,
        int $templateid = 0
    ): void {
        global $DB;

        $record = new \stdClass();
        $record->userid = $userid;
        $record->recipient_phone = $phone;
        $record->message = '';
        $record->template_id = $templateid;
        $record->event_type = $eventtype;
        $record->sender_id = '';
        $record->status = 'skipped';
        $record->skip_reason = $reason;
        $record->api_response = null;
        $record->error_code = null;
        $record->credits_used = 0;
        $record->test_mode = 0;
        $record->timecreated = time();

        $DB->insert_record('local_kwtsms_log', $record);
    }

    /**
     * Log a completed SMS send attempt (successful or failed).
     *
     * @param int         $userid      The Moodle user id.
     * @param string      $phone       The recipient phone number.
     * @param string      $message     The sent message text.
     * @param string      $eventtype   The event type identifier.
     * @param string      $senderid    The sender ID used.
     * @param string      $status      Either 'sent' or 'failed'.
     * @param string      $apiresponse JSON-encoded API response (credentials stripped).
     * @param string|null $errorcode   The API error code, or null on success.
     * @param int         $credits     Credits used for this phone.
     * @param bool        $testmode    Whether test mode was enabled.
     * @param int         $templateid  The template record id.
     * @return void
     */
    private static function log_send(
        int $userid,
        string $phone,
        string $message,
        string $eventtype,
        string $senderid,
        string $status,
        string $apiresponse,
        ?string $errorcode,
        int $credits,
        bool $testmode,
        int $templateid
    ): void {
        global $DB;

        $record = new \stdClass();
        $record->userid = $userid;
        $record->recipient_phone = $phone;
        $record->message = $message;
        $record->template_id = $templateid;
        $record->event_type = $eventtype;
        $record->sender_id = $senderid;
        $record->status = $status;
        $record->skip_reason = null;
        $record->api_response = $apiresponse;
        $record->error_code = $errorcode;
        $record->credits_used = $credits;
        $record->test_mode = $testmode ? 1 : 0;
        $record->timecreated = time();

        $DB->insert_record('local_kwtsms_log', $record);
    }
}
