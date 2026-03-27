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
 * Dashboard tab content for local_kwtsms admin UI.
 *
 * Shows key metrics at a glance: gateway status, balance, sender ID,
 * send statistics, and recent activity.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

global $DB;

use local_kwtsms\api_client;

// Gateway status values.
$isconfigured = api_client::is_configured();
$isenabled = api_client::is_enabled();
$testmode = (int) get_config('local_kwtsms', 'test_mode');

// Balance.
$balance = api_client::get_cached_balance();
$balancerecord = $DB->get_record('local_kwtsms_cache', ['cache_key' => 'balance']);
$lastsynced = $balancerecord ? userdate($balancerecord->timemodified) : get_string('never_synced', 'local_kwtsms');

// Sender ID.
$senderid = get_config('local_kwtsms', 'sender_id');

// Time boundaries for statistics.
$todaystart = mktime(0, 0, 0);
$weekstart = strtotime('monday this week');
$monthstart = mktime(0, 0, 0, date('n'), 1);

// Send statistics.
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

// Recent activity (last 10 log entries).
$recentlogs = $DB->get_records_sql(
    "SELECT id, timecreated, recipient_phone, event_type, status
       FROM {local_kwtsms_log}
      ORDER BY timecreated DESC",
    [],
    0,
    10
);
?>

<div class="row mb-4">
    <!-- Gateway Status Card -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo get_string('gateway_status', 'local_kwtsms'); ?></h5>
                <p class="mb-1">
                    <?php if ($isconfigured) : ?>
                        <span class="badge badge-success bg-success">
                            <?php echo get_string('connected', 'local_kwtsms'); ?>
                        </span>
                    <?php else : ?>
                        <span class="badge badge-danger bg-danger">
                            <?php echo get_string('disconnected', 'local_kwtsms'); ?>
                        </span>
                    <?php endif; ?>
                </p>
                <p class="mb-1">
                    <?php if ($isenabled) : ?>
                        <span class="badge badge-success bg-success">
                            <?php echo get_string('enabled', 'local_kwtsms'); ?>
                        </span>
                    <?php else : ?>
                        <span class="badge badge-secondary bg-secondary">
                            <?php echo get_string('disabled', 'local_kwtsms'); ?>
                        </span>
                    <?php endif; ?>
                </p>
                <p class="mb-0">
                    <?php if ($testmode) : ?>
                        <span class="badge badge-warning bg-warning text-dark">
                            <?php echo get_string('test_mode_on', 'local_kwtsms'); ?>
                        </span>
                    <?php else : ?>
                        <span class="badge badge-info bg-info text-dark">
                            <?php echo get_string('test_mode_off', 'local_kwtsms'); ?>
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Balance Card -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo get_string('balance', 'local_kwtsms'); ?></h5>
                <p class="h3 mb-1">
                    <?php echo (int) $balance; ?>
                    <small class="text-muted"><?php echo get_string('credits', 'local_kwtsms'); ?></small>
                </p>
                <p class="text-muted mb-0">
                    <small><?php echo get_string('last_synced', 'local_kwtsms'); ?>: <?php echo s($lastsynced); ?></small>
                </p>
            </div>
        </div>
    </div>

    <!-- Active Sender ID Card -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo get_string('active_sender_id', 'local_kwtsms'); ?></h5>
                <p class="h4 mb-0">
                    <?php echo $senderid ? s($senderid) : get_string('not_configured', 'local_kwtsms'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Send Statistics Card -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo get_string('send_statistics', 'local_kwtsms'); ?></h5>
                <ul class="list-unstyled mb-0">
                    <li>
                        <?php echo get_string('sent_today', 'local_kwtsms'); ?>:
                        <strong><?php echo (int) $senttoday; ?></strong>
                    </li>
                    <li>
                        <?php echo get_string('sent_week', 'local_kwtsms'); ?>:
                        <strong><?php echo (int) $sentweek; ?></strong>
                    </li>
                    <li>
                        <?php echo get_string('sent_month', 'local_kwtsms'); ?>:
                        <strong><?php echo (int) $sentmonth; ?></strong>
                    </li>
                    <li>
                        <?php echo get_string('failed_count', 'local_kwtsms'); ?>:
                        <strong class="text-danger"><?php echo (int) $failedmonth; ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?php echo get_string('recent_activity', 'local_kwtsms'); ?></h5>
        <?php if (empty($recentlogs)) : ?>
            <p class="text-muted"><?php echo get_string('no_recent_activity', 'local_kwtsms'); ?></p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th><?php echo get_string('log_date', 'local_kwtsms'); ?></th>
                            <th><?php echo get_string('log_phone', 'local_kwtsms'); ?></th>
                            <th><?php echo get_string('log_event', 'local_kwtsms'); ?></th>
                            <th><?php echo get_string('log_status', 'local_kwtsms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentlogs as $log) : ?>
                            <tr>
                                <td><?php echo userdate($log->timecreated); ?></td>
                                <td>
                                    <?php
                                    // Mask phone number, showing only last 4 digits.
                                    $phone = $log->recipient_phone;
                                    if (strlen($phone) > 4) {
                                        echo s(str_repeat('*', strlen($phone) - 4) . substr($phone, -4));
                                    } else {
                                        echo s($phone);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $eventkey = 'event_' . $log->event_type;
                                    if (get_string_manager()->string_exists($eventkey, 'local_kwtsms')) {
                                        echo get_string($eventkey, 'local_kwtsms');
                                    } else {
                                        echo s($log->event_type);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $statuskey = 'status_' . $log->status;
                                    if (get_string_manager()->string_exists($statuskey, 'local_kwtsms')) {
                                        echo get_string($statuskey, 'local_kwtsms');
                                    } else {
                                        echo s($log->status);
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
