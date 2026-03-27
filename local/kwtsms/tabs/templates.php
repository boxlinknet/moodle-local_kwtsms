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
 * Templates tab content for local_kwtsms admin UI.
 *
 * Displays all SMS templates with inline editing and reset functionality.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

use local_kwtsms\template_manager;

$templates = template_manager::get_all_templates();

$recipientlabels = [
    'student' => get_string('recipient_student', 'local_kwtsms'),
    'admin'   => get_string('recipient_admin', 'local_kwtsms'),
    'both'    => get_string('recipient_both', 'local_kwtsms'),
];
?>

<div class="kwtsms-templates-tab">
    <table class="generaltable table table-striped" id="kwtsms-templates-table">
        <thead>
            <tr>
                <th><?php echo get_string('template_event', 'local_kwtsms'); ?></th>
                <th><?php echo get_string('template_recipient', 'local_kwtsms'); ?></th>
                <th><?php echo get_string('template_message_en', 'local_kwtsms'); ?></th>
                <th><?php echo get_string('template_message_ar', 'local_kwtsms'); ?></th>
                <th><?php echo get_string('template_placeholders', 'local_kwtsms'); ?></th>
                <th><?php echo get_string('template_actions', 'local_kwtsms'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $template) :
                $eventtype = $template->event_type;
                $placeholders = template_manager::get_placeholders_for_event($eventtype);
                $recipientlabel = $recipientlabels[$template->recipient_type] ?? s($template->recipient_type);

                // Truncate long messages for display.
                $maxdisplay = 80;
                $messageen = $template->message_en;
                $messagear = $template->message_ar;
                $messageenshort = (mb_strlen($messageen) > $maxdisplay)
                    ? mb_substr($messageen, 0, $maxdisplay) . '...'
                    : $messageen;
                $messagearshort = (mb_strlen($messagear) > $maxdisplay)
                    ? mb_substr($messagear, 0, $maxdisplay) . '...'
                    : $messagear;
            ?>
            <tr data-template-id="<?php echo $template->id; ?>">
                <td>
                    <strong><?php echo s($template->name); ?></strong>
                    <br>
                    <small class="text-muted"><?php echo s($eventtype); ?></small>
                </td>
                <td>
                    <span class="badge badge-secondary"><?php echo $recipientlabel; ?></span>
                </td>
                <td class="kwtsms-msg-en-display">
                    <?php echo s($messageenshort); ?>
                </td>
                <td class="kwtsms-msg-ar-display" dir="rtl">
                    <?php echo s($messagearshort); ?>
                </td>
                <td>
                    <?php foreach ($placeholders as $ph) : ?>
                        <span class="badge badge-info">{<?php echo s($ph); ?>}</span>
                    <?php endforeach; ?>
                </td>
                <td class="kwtsms-template-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary kwtsms-edit-btn"
                            data-id="<?php echo $template->id; ?>"
                            title="<?php echo get_string('template_edit', 'local_kwtsms'); ?>">
                        <i class="fa fa-pencil"></i>
                        <?php echo get_string('template_edit', 'local_kwtsms'); ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning kwtsms-reset-btn"
                            data-id="<?php echo $template->id; ?>"
                            title="<?php echo get_string('template_reset', 'local_kwtsms'); ?>">
                        <i class="fa fa-undo"></i>
                        <?php echo get_string('template_reset', 'local_kwtsms'); ?>
                    </button>
                </td>
            </tr>
            <tr class="kwtsms-edit-row" data-edit-for="<?php echo $template->id; ?>" style="display: none;">
                <td colspan="6">
                    <div class="kwtsms-edit-form p-3 border rounded bg-light">
                        <div class="form-group mb-3">
                            <label for="kwtsms-en-<?php echo $template->id; ?>">
                                <strong><?php echo get_string('template_message_en', 'local_kwtsms'); ?></strong>
                            </label>
                            <textarea id="kwtsms-en-<?php echo $template->id; ?>"
                                      class="form-control kwtsms-textarea-en"
                                      rows="3"><?php echo s($messageen); ?></textarea>
                            <small class="form-text text-muted kwtsms-char-count-en"></small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="kwtsms-ar-<?php echo $template->id; ?>">
                                <strong><?php echo get_string('template_message_ar', 'local_kwtsms'); ?></strong>
                            </label>
                            <textarea id="kwtsms-ar-<?php echo $template->id; ?>"
                                      class="form-control kwtsms-textarea-ar"
                                      dir="rtl"
                                      rows="3"><?php echo s($messagear); ?></textarea>
                            <small class="form-text text-muted kwtsms-char-count-ar"></small>
                        </div>
                        <div class="mb-2">
                            <strong><?php echo get_string('template_placeholders', 'local_kwtsms'); ?>:</strong>
                            <?php foreach ($placeholders as $ph) : ?>
                                <span class="badge badge-info">{<?php echo s($ph); ?>}</span>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary kwtsms-save-btn"
                                    data-id="<?php echo $template->id; ?>">
                                <?php echo get_string('savechanges'); ?>
                            </button>
                            <button type="button" class="btn btn-secondary kwtsms-cancel-btn"
                                    data-id="<?php echo $template->id; ?>">
                                <?php echo get_string('cancel'); ?>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$PAGE->requires->js_call_amd('local_kwtsms/templates', 'init');
