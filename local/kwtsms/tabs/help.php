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
 * Help tab controller for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

use local_kwtsms\template_manager;

global $OUTPUT;

$events = [
    'user_enrolment_created',
    'user_enrolment_deleted',
    'user_graded',
    'course_completed',
    'attempt_submitted',
    'assessable_uploaded',
    'user_created',
];

$placeholdersctx = [];
foreach ($events as $event) {
    $placeholders = template_manager::get_placeholders_for_event($event);
    $rendered = array_map(function ($p) {
        return '<code>{' . s($p) . '}</code>';
    }, $placeholders);
    $placeholdersctx[] = [
        'eventlabel'      => get_string('event_' . $event, 'local_kwtsms'),
        'placeholderlist' => implode(', ', $rendered),
    ];
}

echo $OUTPUT->render_from_template('local_kwtsms/help', ['placeholders' => $placeholdersctx]);
