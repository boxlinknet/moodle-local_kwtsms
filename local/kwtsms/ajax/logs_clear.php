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
 * AJAX endpoint to clear SMS log entries.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();
require_sesskey();
require_capability('local/kwtsms:manage', context_system::instance());

header('Content-Type: application/json; charset=utf-8');

$from = optional_param('date_from', 0, PARAM_INT);
$to = optional_param('date_to', 0, PARAM_INT);

$conditions = [];
$params = [];
if ($from > 0) {
    $conditions[] = 'timecreated >= :fromts';
    $params['fromts'] = $from;
}
if ($to > 0) {
    $conditions[] = 'timecreated <= :tots';
    $params['tots'] = $to;
}

$where = !empty($conditions) ? implode(' AND ', $conditions) : '1=1';
$DB->delete_records_select('local_kwtsms_log', $where, $params);

echo json_encode(['success' => true]);
