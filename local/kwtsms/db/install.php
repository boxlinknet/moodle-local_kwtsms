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
 * Post-install script to seed default SMS templates.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Seed default SMS templates on install.
 *
 * @return bool
 */
function xmldb_local_kwtsms_install() {
    global $DB;

    $now = time();

    $templates = [
        [
            'event_type' => 'user_enrolment_created',
            'name' => 'User Enrolled',
            'message_en' => 'Hello {firstname}, you have been enrolled in {coursename}. Welcome! - {sitename}',
            'message_ar' => 'مرحبا {firstname}، تم تسجيلك في {coursename}. أهلا وسهلا! - {sitename}',
            'recipient_type' => 'student',
        ],
        [
            'event_type' => 'user_enrolment_deleted',
            'name' => 'User Unenrolled',
            'message_en' => 'Hello {firstname}, you have been unenrolled from {coursename}. - {sitename}',
            'message_ar' => 'مرحبا {firstname}، تم إلغاء تسجيلك من {coursename}. - {sitename}',
            'recipient_type' => 'student',
        ],
        [
            'event_type' => 'user_graded',
            'name' => 'Grade Posted',
            'message_en' => 'Hello {firstname}, your grade for {gradeitem} in {coursename} has been posted: {grade}. - {sitename}',
            'message_ar' => 'مرحبا {firstname}، تم نشر درجتك في {gradeitem} لمادة {coursename}: {grade}. - {sitename}',
            'recipient_type' => 'student',
        ],
        [
            'event_type' => 'course_completed',
            'name' => 'Course Completed',
            'message_en' => 'Congratulations {firstname}! You have completed {coursename}. - {sitename}',
            'message_ar' => 'تهانينا {firstname}! لقد أكملت {coursename}. - {sitename}',
            'recipient_type' => 'student',
        ],
        [
            'event_type' => 'attempt_submitted',
            'name' => 'Quiz Submitted',
            'message_en' => 'Hello {firstname}, your quiz attempt in {coursename} has been submitted. - {sitename}',
            'message_ar' => 'مرحبا {firstname}، تم تقديم إجابتك للاختبار في {coursename}. - {sitename}',
            'recipient_type' => 'student',
        ],
        [
            'event_type' => 'assessable_uploaded',
            'name' => 'Assignment Submitted',
            'message_en' => 'Hello {firstname}, your assignment in {coursename} has been submitted successfully. - {sitename}',
            'message_ar' => 'مرحبا {firstname}، تم تقديم واجبك في {coursename} بنجاح. - {sitename}',
            'recipient_type' => 'student',
        ],
        [
            'event_type' => 'user_created',
            'name' => 'New User Registered',
            'message_en' => 'New user registered: {fullname} ({date} {time}). - {sitename}',
            'message_ar' => 'مستخدم جديد مسجل: {fullname} ({date} {time}). - {sitename}',
            'recipient_type' => 'admin',
        ],
    ];

    foreach ($templates as $tpl) {
        $record = (object) [
            'event_type'     => $tpl['event_type'],
            'name'           => $tpl['name'],
            'message_en'     => $tpl['message_en'],
            'message_ar'     => $tpl['message_ar'],
            'default_en'     => $tpl['message_en'],
            'default_ar'     => $tpl['message_ar'],
            'recipient_type' => $tpl['recipient_type'],
            'timecreated'    => $now,
            'timemodified'   => $now,
        ];
        $DB->insert_record('local_kwtsms_templates', $record);
    }

    return true;
}
