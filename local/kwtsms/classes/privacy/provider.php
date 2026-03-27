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
 * Privacy API provider for local_kwtsms.
 *
 * Declares metadata stored in the local_kwtsms_log table and data sent
 * to the external kwtSMS gateway. Implements user data export and deletion
 * for GDPR compliance.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for the kwtSMS plugin.
 *
 * Handles metadata declaration, data export, and data deletion for GDPR.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the type of personal data stored by this plugin.
     *
     * @param collection $collection The privacy metadata collection.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        // Database table: local_kwtsms_log.
        $collection->add_database_table(
            'local_kwtsms_log',
            [
                'userid'          => 'privacy:metadata:local_kwtsms_log:userid',
                'recipient_phone' => 'privacy:metadata:local_kwtsms_log:recipient_phone',
                'message'         => 'privacy:metadata:local_kwtsms_log:message',
                'timecreated'     => 'privacy:metadata:local_kwtsms_log:timecreated',
            ],
            'privacy:metadata:local_kwtsms_log'
        );

        // External system: kwtSMS API.
        $collection->add_external_location_link(
            'kwtsms_api',
            [
                'phone'   => 'privacy:metadata:external:phone',
                'message' => 'privacy:metadata:external:message',
            ],
            'privacy:metadata:external'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user data for the given user.
     *
     * SMS log records are stored at the system context level.
     *
     * @param int $userid The user ID.
     * @return contextlist The list of contexts containing user data.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {local_kwtsms_log} l
                  JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = 0
                 WHERE l.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_SYSTEM,
            'userid'       => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist to populate.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        $sql = "SELECT DISTINCT userid FROM {local_kwtsms_log}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Export all user data for the given approved context list.
     *
     * @param approved_contextlist $contextlist The approved contexts for this user.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $context = \context_system::instance();

        // Check that system context is in the approved list.
        $found = false;
        foreach ($contextlist->get_contexts() as $ctx) {
            if ($ctx->id == $context->id) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return;
        }

        $records = $DB->get_records('local_kwtsms_log', ['userid' => $userid], 'timecreated ASC');
        if (empty($records)) {
            return;
        }

        $exportdata = [];
        foreach ($records as $record) {
            $exportdata[] = (object) [
                'recipient_phone' => $record->recipient_phone,
                'message'         => $record->message,
                'event_type'      => $record->event_type,
                'status'          => $record->status,
                'timecreated'     => transform::datetime($record->timecreated),
            ];
        }

        writer::with_context($context)->export_data(
            [get_string('pluginname', 'local_kwtsms')],
            (object) ['sms_log' => $exportdata]
        );
    }

    /**
     * Delete all user data in the given context.
     *
     * Only acts on the system context.
     *
     * @param \context $context The context to delete data for.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_system) {
            return;
        }

        $DB->delete_records('local_kwtsms_log');
    }

    /**
     * Delete all user data for the given user in the approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts for deletion.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_system) {
                $DB->delete_records('local_kwtsms_log', ['userid' => $userid]);
                break;
            }
        }
    }

    /**
     * Delete data for multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved userlist for deletion.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_system) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('local_kwtsms_log', "userid {$insql}", $inparams);
    }
}
