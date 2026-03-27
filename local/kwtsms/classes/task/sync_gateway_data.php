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
 * Scheduled task to sync gateway data and send low balance alerts.
 *
 * Refreshes cached balance, sender IDs, and coverage from the kwtSMS API.
 * If the balance falls below the configured threshold, sends an SMS alert
 * to admin phone numbers.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms\task;

use local_kwtsms\api_client;
use local_kwtsms\manager;

/**
 * Scheduled task that syncs gateway data and sends low balance alerts.
 */
class sync_gateway_data extends \core\task\scheduled_task {
    /**
     * Return the task name as shown in admin screens.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_sync_gateway_data', 'local_kwtsms');
    }

    /**
     * Execute the scheduled task.
     *
     * 1. Checks that the gateway is enabled and configured.
     * 2. Reloads balance, sender IDs, and coverage from the API.
     * 3. If balance is below the low_balance_threshold, sends an SMS alert
     *    to the configured admin phone numbers.
     *
     * @return void
     */
    public function execute(): void {
        if (!api_client::is_enabled()) {
            mtrace('kwtSMS: gateway disabled, skipping sync.');
            return;
        }

        if (!api_client::is_configured()) {
            mtrace('kwtSMS: gateway not configured, skipping sync.');
            return;
        }

        $result = api_client::reload();

        if (!$result['success']) {
            mtrace('kwtSMS: sync failed: ' . ($result['error'] ?? 'unknown error'));
            return;
        }

        $balance = $result['balance'];
        mtrace('kwtSMS: sync complete, balance: ' . $balance . ' credits.');

        // Check low balance threshold.
        $threshold = (int) get_config('local_kwtsms', 'low_balance_threshold');
        if ($threshold > 0 && $balance < $threshold) {
            $adminphones = get_config('local_kwtsms', 'admin_phones');
            if (empty($adminphones)) {
                mtrace('kwtSMS: low balance detected but no admin phones configured.');
                return;
            }

            $phones = array_map('trim', explode(',', $adminphones));
            $phones = array_filter($phones);
            if (empty($phones)) {
                mtrace('kwtSMS: low balance detected but no valid admin phones.');
                return;
            }

            $sitename = format_string(get_site()->shortname);
            $message = "Low SMS balance alert: {$balance} credits remaining (threshold: {$threshold}). - {$sitename}";

            manager::send($phones, $message, 'low_balance_alert', 0, 0);
            mtrace('kwtSMS: low balance alert sent to ' . count($phones) . ' admin phone(s).');
        }
    }
}
