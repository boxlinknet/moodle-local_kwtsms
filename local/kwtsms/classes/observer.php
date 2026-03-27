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
 * Event observers for local_kwtsms.
 *
 * Listens to Moodle events and triggers SMS notifications via the manager.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Observer class that handles Moodle events and dispatches SMS notifications.
 */
class observer {
    /**
     * Handle user enrolment created event.
     *
     * @param \core\event\user_enrolment_created $event
     * @return void
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event): void {
        global $DB;

        $user = $DB->get_record('user', ['id' => $event->relateduserid], 'id, firstname, lastname, phone1, phone2, lang');
        if (!$user) {
            return;
        }

        $course = $DB->get_record('course', ['id' => $event->courseid], 'id, fullname, shortname');
        if (!$course) {
            return;
        }

        $placeholders = [
            'coursename'      => $course->fullname,
            'courseshortname' => $course->shortname,
        ];

        manager::send_notification('user_enrolment_created', $user, $placeholders, 'student');
    }

    /**
     * Handle user enrolment deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return void
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event): void {
        global $DB;

        $user = $DB->get_record('user', ['id' => $event->relateduserid], 'id, firstname, lastname, phone1, phone2, lang');
        if (!$user) {
            return;
        }

        $course = $DB->get_record('course', ['id' => $event->courseid], 'id, fullname, shortname');
        if (!$course) {
            return;
        }

        $placeholders = [
            'coursename'      => $course->fullname,
            'courseshortname' => $course->shortname,
        ];

        manager::send_notification('user_enrolment_deleted', $user, $placeholders, 'student');
    }

    /**
     * Handle user graded event.
     *
     * @param \core\event\user_graded $event
     * @return void
     */
    public static function user_graded(\core\event\user_graded $event): void {
        global $DB;

        $user = $DB->get_record('user', ['id' => $event->relateduserid], 'id, firstname, lastname, phone1, phone2, lang');
        if (!$user) {
            return;
        }

        $course = $DB->get_record('course', ['id' => $event->courseid], 'id, fullname, shortname');
        if (!$course) {
            return;
        }

        $grade = $event->get_grade();
        $gradeitemname = '';
        if ($grade && !empty($grade->itemid)) {
            $gradeitem = $DB->get_record('grade_items', ['id' => $grade->itemid], 'itemname');
            if ($gradeitem) {
                $gradeitemname = $gradeitem->itemname ?? '';
            }
        }

        $finalgrade = ($grade && isset($grade->finalgrade)) ? round($grade->finalgrade, 2) : '';

        $placeholders = [
            'coursename'      => $course->fullname,
            'courseshortname' => $course->shortname,
            'grade'           => (string) $finalgrade,
            'gradeitem'       => $gradeitemname,
        ];

        manager::send_notification('user_graded', $user, $placeholders, 'student');
    }

    /**
     * Handle course completed event.
     *
     * @param \core\event\course_completed $event
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event): void {
        global $DB;

        $user = $DB->get_record('user', ['id' => $event->relateduserid], 'id, firstname, lastname, phone1, phone2, lang');
        if (!$user) {
            return;
        }

        $course = $DB->get_record('course', ['id' => $event->courseid], 'id, fullname, shortname');
        if (!$course) {
            return;
        }

        $placeholders = [
            'coursename'      => $course->fullname,
            'courseshortname' => $course->shortname,
        ];

        manager::send_notification('course_completed', $user, $placeholders, 'student');
    }

    /**
     * Handle quiz attempt submitted event.
     *
     * @param \mod_quiz\event\attempt_submitted $event
     * @return void
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event): void {
        global $DB;

        $user = $DB->get_record('user', ['id' => $event->userid], 'id, firstname, lastname, phone1, phone2, lang');
        if (!$user) {
            return;
        }

        $course = $DB->get_record('course', ['id' => $event->courseid], 'id, fullname, shortname');
        if (!$course) {
            return;
        }

        $placeholders = [
            'coursename'      => $course->fullname,
            'courseshortname' => $course->shortname,
        ];

        manager::send_notification('attempt_submitted', $user, $placeholders, 'student');
    }

    /**
     * Handle assignment file upload event.
     *
     * @param \assignsubmission_file\event\assessable_uploaded $event
     * @return void
     */
    public static function assessable_uploaded(\assignsubmission_file\event\assessable_uploaded $event): void {
        global $DB;

        $user = $DB->get_record('user', ['id' => $event->userid], 'id, firstname, lastname, phone1, phone2, lang');
        if (!$user) {
            return;
        }

        $course = $DB->get_record('course', ['id' => $event->courseid], 'id, fullname, shortname');
        if (!$course) {
            return;
        }

        $placeholders = [
            'coursename'      => $course->fullname,
            'courseshortname' => $course->shortname,
        ];

        manager::send_notification('assessable_uploaded', $user, $placeholders, 'student');
    }

    /**
     * Handle user created event.
     *
     * @param \core\event\user_created $event
     * @return void
     */
    public static function user_created(\core\event\user_created $event): void {
        global $DB;

        $user = $DB->get_record('user', ['id' => $event->objectid], 'id, firstname, lastname, phone1, phone2, lang');
        if (!$user) {
            return;
        }

        $placeholders = [];

        manager::send_notification('user_created', $user, $placeholders, 'admin');
    }
}
