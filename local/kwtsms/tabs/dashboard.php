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
 * Dashboard tab controller for local_kwtsms admin UI.
 *
 * Gathers gateway status, balance, sender ID, send statistics, and
 * recent activity data for the dashboard Mustache template.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

use local_kwtsms\api_client;

global $DB, $OUTPUT;

$isconfigured = api_client::is_configured();
$isenabled = api_client::is_enabled();
$testmode = (bool) get_config('local_kwtsms', 'test_mode');

$balance = (int) api_client::get_cached_balance();
$balancerecord = $DB->get_record('local_kwtsms_cache', ['cache_key' => 'balance']);
$lastsynced = $balancerecord ? userdate($balancerecord->timemodified) : get_string('never_synced', 'local_kwtsms');

$senderid = get_config('local_kwtsms', 'sender_id');

$todaystart = mktime(0, 0, 0);
$weekstart = strtotime('monday this week');
$monthstart = mktime(0, 0, 0, date('n'), 1);

$senttoday = $DB->count_records_select(
    'local_kwtsms_log',
    "status = :status AND timecreated >= :timestart",
    ['status' => 'sent', 'timestart' => $todaystart]
);
$sentweek = $DB->count_records_select(
    'local_kwtsms_log',
    "status = :status AND timecreated >= :timestart",
    ['status' => 'sent', 'timestart' => $weekstart]
);
$sentmonth = $DB->count_records_select(
    'local_kwtsms_log',
    "status = :status AND timecreated >= :timestart",
    ['status' => 'sent', 'timestart' => $monthstart]
);
$failedmonth = $DB->count_records_select(
    'local_kwtsms_log',
    "status = :status AND timecreated >= :timestart",
    ['status' => 'failed', 'timestart' => $monthstart]
);

$recentlogs = $DB->get_records_sql(
    "SELECT id, timecreated, recipient_phone, event_type, status
       FROM {local_kwtsms_log}
      ORDER BY timecreated DESC",
    [],
    0,
    10
);

$recentlogsctx = [];
foreach ($recentlogs as $log) {
    // Mask phone, showing only last 4 digits.
    $phone = $log->recipient_phone;
    $phonemasked = (strlen($phone) > 4)
        ? str_repeat('*', strlen($phone) - 4) . substr($phone, -4)
        : $phone;

    $eventkey = 'event_' . $log->event_type;
    $eventlabel = get_string_manager()->string_exists($eventkey, 'local_kwtsms')
        ? get_string($eventkey, 'local_kwtsms')
        : $log->event_type;

    $statuskey = 'status_' . $log->status;
    $statuslabel = get_string_manager()->string_exists($statuskey, 'local_kwtsms')
        ? get_string($statuskey, 'local_kwtsms')
        : $log->status;

    $recentlogsctx[] = [
        'timecreatedstr' => userdate($log->timecreated),
        'phonemasked'    => $phonemasked,
        'eventlabel'     => $eventlabel,
        'statuslabel'    => $statuslabel,
    ];
}

$templatecontext = [
    'isconfigured'  => $isconfigured,
    'isenabled'     => $isenabled,
    'testmode'      => $testmode,
    'balance'       => $balance,
    'lastsynced'    => $lastsynced,
    'senderid'      => $senderid,
    'hassenderid'   => !empty($senderid),
    'senttoday'     => (int) $senttoday,
    'sentweek'      => (int) $sentweek,
    'sentmonth'     => (int) $sentmonth,
    'failedmonth'   => (int) $failedmonth,
    'hasrecentlogs' => !empty($recentlogsctx),
    'recentlogs'    => $recentlogsctx,
];

echo $OUTPUT->render_from_template('local_kwtsms/dashboard', $templatecontext);
