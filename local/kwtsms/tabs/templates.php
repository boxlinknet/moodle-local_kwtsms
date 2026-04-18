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
 * Templates tab controller for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

use local_kwtsms\template_manager;

global $OUTPUT, $PAGE;

$templates = template_manager::get_all_templates();

$recipientlabels = [
    'student' => get_string('recipient_student', 'local_kwtsms'),
    'admin'   => get_string('recipient_admin', 'local_kwtsms'),
    'both'    => get_string('recipient_both', 'local_kwtsms'),
];

$templatesctx = [];
foreach ($templates as $template) {
    $eventtype = $template->event_type;
    $placeholders = template_manager::get_placeholders_for_event($eventtype);
    $recipientlabel = $recipientlabels[$template->recipient_type] ?? $template->recipient_type;

    $maxdisplay = 80;
    $messageen = $template->message_en;
    $messagear = $template->message_ar;
    $messageenshort = (mb_strlen($messageen) > $maxdisplay)
        ? mb_substr($messageen, 0, $maxdisplay) . '...'
        : $messageen;
    $messagearshort = (mb_strlen($messagear) > $maxdisplay)
        ? mb_substr($messagear, 0, $maxdisplay) . '...'
        : $messagear;

    $placeholdersctx = array_map(function ($ph) {
        return ['name' => $ph];
    }, $placeholders);

    $templatesctx[] = [
        'id'              => (int) $template->id,
        'name'            => $template->name,
        'eventtype'       => $eventtype,
        'recipientlabel'  => $recipientlabel,
        'messageenshort'  => $messageenshort,
        'messagearshort'  => $messagearshort,
        'messageen'       => $messageen,
        'messagear'       => $messagear,
        'placeholders'    => $placeholdersctx,
    ];
}

$PAGE->requires->js_call_amd('local_kwtsms/templates', 'init');

echo $OUTPUT->render_from_template('local_kwtsms/templates', ['templates' => $templatesctx]);
