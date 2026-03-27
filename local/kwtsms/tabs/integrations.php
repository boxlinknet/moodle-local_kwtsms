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
 * Integrations tab content for local_kwtsms admin UI.
 *
 * Shows toggles for each event type, grouped into Student Notifications
 * and Admin Alerts.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

// Student notification events.
$studentevents = [
    'user_enrolment_created',
    'user_enrolment_deleted',
    'user_graded',
    'course_completed',
    'attempt_submitted',
    'assessable_uploaded',
];

// Admin alert events.
$adminevents = [
    'user_created',
];

// All events combined for form processing.
$allevents = array_merge($studentevents, $adminevents);

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    foreach ($allevents as $event) {
        set_config('event_' . $event, optional_param('event_' . $event, 0, PARAM_INT), 'local_kwtsms');
    }
    echo $OUTPUT->notification(get_string('settings_saved', 'local_kwtsms'), 'success');
}

$formaction = $baseurl->out(false, ['tab' => 'integrations']);
?>

<p class="text-muted mb-4"><?php echo get_string('integrations_desc', 'local_kwtsms'); ?></p>

<form method="post" action="<?php echo s($formaction); ?>" class="mform">
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">

    <h4><?php echo get_string('student_notifications', 'local_kwtsms'); ?></h4>

    <?php foreach ($studentevents as $event) :
        $configval = (int) get_config('local_kwtsms', 'event_' . $event);
    ?>
    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_event_<?php echo s($event); ?>">
                <?php echo get_string('event_' . $event, 'local_kwtsms'); ?>
            </label>
        </div>
        <div class="col-sm-9">
            <input type="hidden" name="event_<?php echo s($event); ?>" value="0">
            <input type="checkbox" name="event_<?php echo s($event); ?>"
                id="id_event_<?php echo s($event); ?>" value="1"
                <?php echo $configval ? 'checked' : ''; ?>>
            <small class="form-text text-muted">
                <?php echo get_string('event_' . $event . '_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>
    <?php endforeach; ?>

    <h4 class="mt-4"><?php echo get_string('admin_alerts', 'local_kwtsms'); ?></h4>

    <?php foreach ($adminevents as $event) :
        $configval = (int) get_config('local_kwtsms', 'event_' . $event);
    ?>
    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_event_<?php echo s($event); ?>">
                <?php echo get_string('event_' . $event, 'local_kwtsms'); ?>
            </label>
        </div>
        <div class="col-sm-9">
            <input type="hidden" name="event_<?php echo s($event); ?>" value="0">
            <input type="checkbox" name="event_<?php echo s($event); ?>"
                id="id_event_<?php echo s($event); ?>" value="1"
                <?php echo $configval ? 'checked' : ''; ?>>
            <small class="form-text text-muted">
                <?php echo get_string('event_' . $event . '_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="form-group row mb-3">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary"><?php echo get_string('savechanges'); ?></button>
        </div>
    </div>
</form>
