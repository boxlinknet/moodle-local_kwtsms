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
 * Integrations tab controller for local_kwtsms admin UI.
 *
 * Gathers event toggle data for the integrations Mustache template.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

global $OUTPUT;

$studentevents = [
    'user_enrolment_created',
    'user_enrolment_deleted',
    'user_graded',
    'course_completed',
    'attempt_submitted',
    'assessable_uploaded',
];

$adminevents = [
    'user_created',
];

$allevents = array_merge($studentevents, $adminevents);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    foreach ($allevents as $event) {
        set_config('event_' . $event, optional_param('event_' . $event, 0, PARAM_INT), 'local_kwtsms');
    }
    echo $OUTPUT->notification(get_string('settings_saved', 'local_kwtsms'), 'success');
}

$buildevent = function ($event) {
    return [
        'key'     => $event,
        'label'   => get_string('event_' . $event, 'local_kwtsms'),
        'desc'    => get_string('event_' . $event . '_desc', 'local_kwtsms'),
        'enabled' => (bool) get_config('local_kwtsms', 'event_' . $event),
    ];
};

$templatecontext = [
    'formaction'     => $baseurl->out(false, ['tab' => 'integrations']),
    'sesskey'        => sesskey(),
    'studentevents'  => array_map($buildevent, $studentevents),
    'adminevents'    => array_map($buildevent, $adminevents),
];

echo $OUTPUT->render_from_template('local_kwtsms/integrations', $templatecontext);
