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
 * CSV export endpoint for SMS log entries.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();
require_capability('local/kwtsms:viewlogs', context_system::instance());

$status = optional_param('filter_status', '', PARAM_ALPHA);
$eventtype = optional_param('filter_event', '', PARAM_ALPHANUMEXT);
$search = optional_param('filter_search', '', PARAM_TEXT);
$datefrom = optional_param('filter_date_from', '', PARAM_TEXT);
$dateto = optional_param('filter_date_to', '', PARAM_TEXT);

// Build WHERE conditions.
$conditions = [];
$params = [];
if (!empty($status)) {
    $conditions[] = 'status = :status';
    $params['status'] = $status;
}
if (!empty($eventtype)) {
    $conditions[] = 'event_type = :eventtype';
    $params['eventtype'] = $eventtype;
}
if (!empty($search)) {
    $conditions[] = $DB->sql_like('recipient_phone', ':phone');
    $params['phone'] = '%' . $DB->sql_like_escape($search) . '%';
}
if (!empty($datefrom)) {
    $fromts = strtotime($datefrom);
    if ($fromts !== false) {
        $conditions[] = 'timecreated >= :datefrom';
        $params['datefrom'] = $fromts;
    }
}
if (!empty($dateto)) {
    $tots = strtotime($dateto . ' 23:59:59');
    if ($tots !== false) {
        $conditions[] = 'timecreated <= :dateto';
        $params['dateto'] = $tots;
    }
}

$where = !empty($conditions) ? implode(' AND ', $conditions) : '1=1';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="kwtsms_logs_' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
// UTF-8 BOM for Excel compatibility.
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($out, [
    'Date',
    'Phone',
    'Event',
    'Status',
    'Skip Reason',
    'Credits',
    'Test Mode',
    'Message',
    'Error Code',
]);

$records = $DB->get_records_select('local_kwtsms_log', $where, $params, 'timecreated DESC');
foreach ($records as $record) {
    fputcsv($out, [
        userdate($record->timecreated),
        $record->recipient_phone,
        $record->event_type,
        $record->status,
        $record->skip_reason ?? '',
        $record->credits_used,
        $record->test_mode ? 'Yes' : 'No',
        $record->message,
        $record->error_code ?? '',
    ]);
}

fclose($out);
exit;
