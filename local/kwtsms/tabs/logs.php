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
 * Logs tab controller for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

global $DB, $OUTPUT, $PAGE;

$status = optional_param('filter_status', '', PARAM_ALPHA);
$eventtype = optional_param('filter_event', '', PARAM_ALPHANUMEXT);
$search = optional_param('filter_search', '', PARAM_TEXT);
$datefrom = optional_param('filter_date_from', '', PARAM_TEXT);
$dateto = optional_param('filter_date_to', '', PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 50;

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
$total = $DB->count_records_select('local_kwtsms_log', $where, $params);
$records = $DB->get_records_select(
    'local_kwtsms_log',
    $where,
    $params,
    'timecreated DESC',
    '*',
    $page * $perpage,
    $perpage
);

$eventtyperecords = $DB->get_records_sql(
    "SELECT DISTINCT event_type FROM {local_kwtsms_templates} ORDER BY event_type ASC"
);

$statuses = ['sent', 'failed', 'skipped', 'queued'];
$statusclasses = [
    'sent'    => 'badge-success',
    'failed'  => 'badge-danger',
    'skipped' => 'badge-warning',
    'queued'  => 'badge-info',
];

$statusctx = [];
foreach ($statuses as $s) {
    $statusctx[] = [
        'key'      => $s,
        'label'    => get_string('status_' . $s, 'local_kwtsms'),
        'selected' => $status === $s,
    ];
}

$eventtypesctx = [];
foreach ($eventtyperecords as $et) {
    $ekey = $et->event_type;
    $elabel = get_string_manager()->string_exists('event_' . $ekey, 'local_kwtsms')
        ? get_string('event_' . $ekey, 'local_kwtsms')
        : $ekey;
    $eventtypesctx[] = [
        'key'      => $ekey,
        'label'    => $elabel,
        'selected' => $eventtype === $ekey,
    ];
}

$templatenames = $DB->get_records_menu('local_kwtsms_templates', null, '', 'id, name');

$recordsctx = [];
foreach ($records as $record) {
    $badgeclass = $statusclasses[$record->status] ?? 'badge-secondary';
    $statuslabel = get_string_manager()->string_exists('status_' . $record->status, 'local_kwtsms')
        ? get_string('status_' . $record->status, 'local_kwtsms')
        : $record->status;
    $eventlabel = get_string_manager()->string_exists('event_' . $record->event_type, 'local_kwtsms')
        ? get_string('event_' . $record->event_type, 'local_kwtsms')
        : $record->event_type;

    $templatename = '';
    if (!empty($record->template_id) && isset($templatenames[$record->template_id])) {
        $templatename = $templatenames[$record->template_id];
    }

    $skipreason = '';
    if (!empty($record->skip_reason)) {
        $skipreason = get_string_manager()->string_exists('skip_' . $record->skip_reason, 'local_kwtsms')
            ? get_string('skip_' . $record->skip_reason, 'local_kwtsms')
            : $record->skip_reason;
    }

    $apiresponse = '';
    if (!empty($record->api_response)) {
        $decoded = json_decode($record->api_response);
        $apiresponse = $decoded !== null
            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : $record->api_response;
    }

    $recordsctx[] = [
        'id'             => (int) $record->id,
        'timecreatedstr' => userdate($record->timecreated),
        'phone'          => $record->recipient_phone,
        'eventlabel'     => $eventlabel,
        'templatename'   => $templatename,
        'statuslabel'    => $statuslabel,
        'badgeclass'     => $badgeclass,
        'skipreason'     => $skipreason,
        'credits'        => (int) $record->credits_used,
        'testmode'       => (bool) $record->test_mode,
        'message'        => $record->message,
        'hasapiresponse' => !empty($record->api_response),
        'apiresponse'    => $apiresponse,
        'haserrorcode'   => !empty($record->error_code),
        'errorcode'      => $record->error_code ?? '',
    ];
}

$pagingparams = ['tab' => 'logs'];
if (!empty($status)) {
    $pagingparams['filter_status'] = $status;
}
if (!empty($eventtype)) {
    $pagingparams['filter_event'] = $eventtype;
}
if (!empty($search)) {
    $pagingparams['filter_search'] = $search;
}
if (!empty($datefrom)) {
    $pagingparams['filter_date_from'] = $datefrom;
}
if (!empty($dateto)) {
    $pagingparams['filter_date_to'] = $dateto;
}
$pagingurl = new moodle_url('/local/kwtsms/view.php', $pagingparams);
$pagingbar = $OUTPUT->paging_bar($total, $page, $perpage, $pagingurl);

$templatecontext = [
    'filterurl'  => $baseurl->out(false, ['tab' => 'logs']),
    'search'     => $search,
    'datefrom'   => $datefrom,
    'dateto'     => $dateto,
    'statuses'   => $statusctx,
    'eventtypes' => $eventtypesctx,
    'hasrecords' => !empty($recordsctx),
    'records'    => $recordsctx,
    'pagingbar'  => $pagingbar,
    'canmanage'  => has_capability('local/kwtsms:manage', $context),
];

$PAGE->requires->string_for_js('log_clear_confirm', 'local_kwtsms');
$PAGE->requires->js_call_amd('local_kwtsms/logs', 'init');

echo $OUTPUT->render_from_template('local_kwtsms/logs', $templatecontext);
