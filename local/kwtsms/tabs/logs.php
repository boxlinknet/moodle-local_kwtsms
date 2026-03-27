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
 * Logs tab content for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

$status = optional_param('filter_status', '', PARAM_ALPHA);
$eventtype = optional_param('filter_event', '', PARAM_ALPHANUMEXT);
$search = optional_param('filter_search', '', PARAM_TEXT);
$datefrom = optional_param('filter_date_from', '', PARAM_TEXT);
$dateto = optional_param('filter_date_to', '', PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 50;

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

// Get distinct event types for the filter dropdown.
$eventtypes = $DB->get_records_sql(
    "SELECT DISTINCT event_type FROM {local_kwtsms_templates} ORDER BY event_type ASC"
);

// Build the filter form URL.
$filterurl = $baseurl->out(false, ['tab' => 'logs']);

// Status options.
$statuses = ['sent', 'failed', 'skipped', 'queued'];

// Status badge CSS classes.
$statusclasses = [
    'sent' => 'badge-success',
    'failed' => 'badge-danger',
    'skipped' => 'badge-warning',
    'queued' => 'badge-info',
];
?>

<div id="kwtsms-logs-feedback"></div>

<form method="get" action="<?php echo s($filterurl); ?>" class="mb-3">
    <input type="hidden" name="tab" value="logs">
    <div class="form-row align-items-end">
        <div class="form-group col-md-2 mb-2">
            <label for="filter_status"><?php echo get_string('log_filter_status', 'local_kwtsms'); ?></label>
            <select name="filter_status" id="filter_status" class="form-control custom-select">
                <option value=""><?php echo get_string('log_filter_all', 'local_kwtsms'); ?></option>
                <?php foreach ($statuses as $s) : ?>
                    <option value="<?php echo s($s); ?>"
                        <?php echo ($status === $s) ? 'selected' : ''; ?>>
                        <?php echo get_string('status_' . $s, 'local_kwtsms'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group col-md-2 mb-2">
            <label for="filter_event"><?php echo get_string('log_filter_event', 'local_kwtsms'); ?></label>
            <select name="filter_event" id="filter_event" class="form-control custom-select">
                <option value=""><?php echo get_string('log_filter_all', 'local_kwtsms'); ?></option>
                <?php foreach ($eventtypes as $et) : ?>
                    <?php
                    $ekey = $et->event_type;
                    $elabel = get_string_manager()->string_exists('event_' . $ekey, 'local_kwtsms')
                        ? get_string('event_' . $ekey, 'local_kwtsms')
                        : $ekey;
                    ?>
                    <option value="<?php echo s($ekey); ?>"
                        <?php echo ($eventtype === $ekey) ? 'selected' : ''; ?>>
                        <?php echo s($elabel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group col-md-2 mb-2">
            <label for="filter_search"><?php echo get_string('log_filter_search', 'local_kwtsms'); ?></label>
            <input type="text" name="filter_search" id="filter_search" class="form-control"
                   value="<?php echo s($search); ?>"
                   placeholder="<?php echo s(get_string('log_filter_search', 'local_kwtsms')); ?>">
        </div>

        <div class="form-group col-md-2 mb-2">
            <label for="filter_date_from"><?php echo get_string('log_filter_from', 'local_kwtsms'); ?></label>
            <input type="date" name="filter_date_from" id="filter_date_from" class="form-control"
                   value="<?php echo s($datefrom); ?>">
        </div>

        <div class="form-group col-md-2 mb-2">
            <label for="filter_date_to"><?php echo get_string('log_filter_to', 'local_kwtsms'); ?></label>
            <input type="date" name="filter_date_to" id="filter_date_to" class="form-control"
                   value="<?php echo s($dateto); ?>">
        </div>

        <div class="form-group col-md-2 mb-2">
            <button type="submit" class="btn btn-primary btn-block">
                <?php echo get_string('log_filter_btn', 'local_kwtsms'); ?>
            </button>
        </div>
    </div>
</form>

<?php
// Pre-load all template names to avoid N+1 queries in the loop.
$templatenames = $DB->get_records_menu('local_kwtsms_templates', null, '', 'id, name');
?>
<?php if (empty($records)) : ?>
    <div class="alert alert-info"><?php echo get_string('log_no_records', 'local_kwtsms'); ?></div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover" id="kwtsms-logs-table">
            <thead>
                <tr>
                    <th><?php echo get_string('log_date', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_phone', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_event', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_template', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_status', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_reason', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_credits', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_test_mode', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('log_details', 'local_kwtsms'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record) : ?>
                    <?php
                    $badgeclass = isset($statusclasses[$record->status]) ? $statusclasses[$record->status] : 'badge-secondary';
                    $statuslabel = get_string_manager()->string_exists('status_' . $record->status, 'local_kwtsms')
                        ? get_string('status_' . $record->status, 'local_kwtsms')
                        : s($record->status);
                    $eventlabel = get_string_manager()->string_exists('event_' . $record->event_type, 'local_kwtsms')
                        ? get_string('event_' . $record->event_type, 'local_kwtsms')
                        : s($record->event_type);

                    // Look up template name from pre-loaded map.
                    $templatename = '';
                    if (!empty($record->template_id) && isset($templatenames[$record->template_id])) {
                        $templatename = $templatenames[$record->template_id];
                    }

                    $skipreason = '';
                    if (!empty($record->skip_reason)) {
                        $skipreason = get_string_manager()->string_exists('skip_' . $record->skip_reason, 'local_kwtsms')
                            ? get_string('skip_' . $record->skip_reason, 'local_kwtsms')
                            : s($record->skip_reason);
                    }
                    ?>
                    <tr>
                        <td><?php echo userdate($record->timecreated); ?></td>
                        <td><?php echo s($record->recipient_phone); ?></td>
                        <td><?php echo s($eventlabel); ?></td>
                        <td><?php echo s($templatename); ?></td>
                        <td><span class="badge <?php echo $badgeclass; ?>"><?php echo s($statuslabel); ?></span></td>
                        <td><?php echo s($skipreason); ?></td>
                        <td><?php echo (int) $record->credits_used; ?></td>
                        <td>
                            <?php if ($record->test_mode) : ?>
                                <span class="badge badge-warning"><?php echo get_string('yes'); ?></span>
                            <?php else : ?>
                                <span class="badge badge-light"><?php echo get_string('no'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-secondary kwtsms-log-details-btn"
                                    data-log-id="<?php echo (int) $record->id; ?>">
                                <?php echo get_string('log_details', 'local_kwtsms'); ?>
                            </button>
                        </td>
                    </tr>
                    <tr class="kwtsms-log-detail-row d-none" id="kwtsms-detail-<?php echo (int) $record->id; ?>">
                        <td colspan="9">
                            <div class="p-3 bg-light border rounded">
                                <h6><?php echo get_string('log_message', 'local_kwtsms'); ?></h6>
                                <pre class="mb-3"><?php echo s($record->message); ?></pre>

                                <?php if (!empty($record->api_response)) : ?>
                                    <h6><?php echo get_string('log_api_response', 'local_kwtsms'); ?></h6>
                                    <pre class="mb-3"><?php
                                        $decoded = json_decode($record->api_response);
                                    if ($decoded !== null) {
                                        echo s(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                    } else {
                                        echo s($record->api_response);
                                    }
                                    ?></pre>
                                <?php endif; ?>

                                <?php if (!empty($record->error_code)) : ?>
                                    <h6><?php echo get_string('log_error_code', 'local_kwtsms'); ?></h6>
                                    <p><code><?php echo s($record->error_code); ?></code></p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Build pagination URL with current filters preserved.
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
    echo $OUTPUT->paging_bar($total, $page, $perpage, $pagingurl);
    ?>
<?php endif; ?>

<div class="mt-3">
    <?php if (has_capability('local/kwtsms:manage', $context)) : ?>
        <button type="button" id="kwtsms-clear-logs-btn" class="btn btn-outline-danger mr-2">
            <?php echo get_string('log_clear', 'local_kwtsms'); ?>
        </button>
    <?php endif; ?>
    <button type="button" id="kwtsms-export-logs-btn" class="btn btn-outline-primary">
        <?php echo get_string('log_export', 'local_kwtsms'); ?>
    </button>
</div>

<?php
$PAGE->requires->string_for_js('log_clear_confirm', 'local_kwtsms');
$PAGE->requires->js_call_amd('local_kwtsms/logs', 'init');
