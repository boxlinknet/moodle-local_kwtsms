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
 * English language strings for local_kwtsms.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin name.
$string['pluginname'] = 'kwtSMS Notifications';

// Capabilities.
$string['kwtsms:manage'] = 'Manage kwtSMS settings';
$string['kwtsms:viewlogs'] = 'View kwtSMS logs';

// Tabs.
$string['tab_dashboard'] = 'Dashboard';
$string['tab_settings'] = 'Settings';
$string['tab_gateway'] = 'Gateway';
$string['tab_templates'] = 'Templates';
$string['tab_integrations'] = 'Integrations';
$string['tab_logs'] = 'Logs';
$string['tab_help'] = 'Help';

// Dashboard.
$string['gateway_status'] = 'Gateway Status';
$string['connected'] = 'Connected';
$string['disconnected'] = 'Disconnected';
$string['enabled'] = 'Enabled';
$string['disabled'] = 'Disabled';
$string['test_mode_on'] = 'Test Mode ON';
$string['test_mode_off'] = 'Test Mode OFF';
$string['balance'] = 'Balance';
$string['credits'] = 'credits';
$string['last_synced'] = 'Last synced';
$string['sync_now'] = 'Sync Now';
$string['active_sender_id'] = 'Active Sender ID';
$string['sent_today'] = 'Sent Today';
$string['sent_week'] = 'Sent This Week';
$string['sent_month'] = 'Sent This Month';
$string['failed_count'] = 'Failed';
$string['recent_activity'] = 'Recent Activity';
$string['send_statistics'] = 'Send Statistics';
$string['never_synced'] = 'Never';
$string['not_configured'] = 'Not configured';
$string['no_recent_activity'] = 'No recent SMS activity.';

// Settings.
$string['settings_general'] = 'General Settings';
$string['gateway_enabled'] = 'Enable Gateway';
$string['gateway_enabled_desc'] = 'Global on/off switch for SMS sending.';
$string['test_mode'] = 'Test Mode';
$string['test_mode_desc'] = 'When enabled, SMS messages are sent to the API with test=1. Messages are not delivered to handsets. Credits can be recovered from the kwtSMS queue.';
$string['debug_logging'] = 'Debug Logging';
$string['debug_logging_desc'] = 'Log detailed debug information (normalization steps, placeholder replacement, API timing). Disable in production.';
$string['default_language'] = 'Default SMS Language';
$string['default_language_desc'] = 'Fallback language for SMS when the user has no language set in their profile.';
$string['admin_phones'] = 'Admin Phone Numbers';
$string['admin_phones_desc'] = 'Phone numbers for admin SMS alerts. Comma-separated, international format (e.g. 96598765432).';
$string['low_balance_threshold'] = 'Low Balance Threshold';
$string['low_balance_threshold_desc'] = 'Send admin alert when balance drops below this number. Set to 0 to disable.';

// Gateway.
$string['gateway_settings'] = 'Gateway Connection';
$string['api_username'] = 'API Username';
$string['api_password'] = 'API Password';
$string['login'] = 'Login';
$string['logout'] = 'Logout';
$string['reload'] = 'Reload';
$string['connected_as'] = 'Connected as: {$a}';
$string['sender_id'] = 'Sender ID';
$string['default_country_code'] = 'Default Country Code';
$string['default_country_code_desc'] = 'Country code to prepend to local numbers starting with 0.';
$string['active_coverage'] = 'Active Coverage';
$string['login_success'] = 'Successfully connected to kwtSMS gateway.';
$string['login_failed'] = 'Invalid credentials. Please check your API username and password.';
$string['logout_success'] = 'Disconnected from kwtSMS gateway.';
$string['reload_success'] = 'Gateway data refreshed successfully.';
$string['no_senderids'] = 'No sender IDs available on this account.';

// Templates.
$string['template_event'] = 'Event';
$string['template_recipient'] = 'Recipient';
$string['template_message_en'] = 'English Message';
$string['template_message_ar'] = 'Arabic Message';
$string['template_actions'] = 'Actions';
$string['template_edit'] = 'Edit';
$string['template_reset'] = 'Reset to Default';
$string['template_reset_confirm'] = 'Reset this template to its default content? Your customizations will be lost.';
$string['template_saved'] = 'Template saved successfully.';
$string['template_reset_success'] = 'Template reset to default.';
$string['template_placeholders'] = 'Available Placeholders';
$string['template_char_count'] = 'Characters: {$a->chars} ({$a->pages} SMS page(s))';
$string['template_preview'] = 'Preview';
$string['recipient_student'] = 'Student';
$string['recipient_admin'] = 'Admin';
$string['recipient_both'] = 'Both';

// Integrations.
$string['integrations_desc'] = 'Enable or disable SMS notifications for each event.';
$string['student_notifications'] = 'Student Notifications';
$string['admin_alerts'] = 'Admin Alerts';
$string['event_user_enrolment_created'] = 'User Enrolled';
$string['event_user_enrolment_created_desc'] = 'Send SMS when a user is enrolled in a course.';
$string['event_user_enrolment_deleted'] = 'User Unenrolled';
$string['event_user_enrolment_deleted_desc'] = 'Send SMS when a user is unenrolled from a course.';
$string['event_user_graded'] = 'Grade Posted';
$string['event_user_graded_desc'] = 'Send SMS when a grade is posted for a user.';
$string['event_course_completed'] = 'Course Completed';
$string['event_course_completed_desc'] = 'Send SMS when a user completes a course.';
$string['event_attempt_submitted'] = 'Quiz Submitted';
$string['event_attempt_submitted_desc'] = 'Send SMS when a quiz attempt is submitted.';
$string['event_assessable_uploaded'] = 'Assignment Submitted';
$string['event_assessable_uploaded_desc'] = 'Send SMS when an assignment file is submitted.';
$string['event_user_created'] = 'New User Registered';
$string['event_user_created_desc'] = 'Send SMS to admin when a new user registers.';
$string['settings_saved'] = 'Settings saved successfully.';

// Logs.
$string['log_date'] = 'Date/Time';
$string['log_phone'] = 'Recipient Phone';
$string['log_event'] = 'Event';
$string['log_template'] = 'Template';
$string['log_status'] = 'Status';
$string['log_reason'] = 'Reason';
$string['log_credits'] = 'Credits';
$string['log_test_mode'] = 'Test';
$string['log_details'] = 'View Details';
$string['log_message'] = 'Message';
$string['log_api_response'] = 'API Response';
$string['log_clear'] = 'Clear Logs';
$string['log_clear_confirm'] = 'Delete all logs in the selected date range? This action cannot be undone.';
$string['log_cleared'] = 'Logs cleared successfully.';
$string['log_export'] = 'Export CSV';
$string['log_no_records'] = 'No SMS log entries found.';
$string['log_filter_status'] = 'Status';
$string['log_filter_event'] = 'Event Type';
$string['log_filter_all'] = 'All';
$string['log_filter_search'] = 'Search by phone';
$string['log_filter_from'] = 'From';
$string['log_filter_to'] = 'To';
$string['log_filter_btn'] = 'Filter';
$string['log_error_code'] = 'Error Code';
$string['status_sent'] = 'Sent';
$string['status_failed'] = 'Failed';
$string['status_skipped'] = 'Skipped';
$string['status_queued'] = 'Queued';

// Skip reasons.
$string['skip_gateway_disabled'] = 'Gateway disabled';
$string['skip_gateway_not_configured'] = 'Gateway not configured';
$string['skip_no_phone_number'] = 'No phone number';
$string['skip_country_not_covered'] = 'Country not covered';
$string['skip_zero_balance'] = 'Zero balance';
$string['skip_empty_message'] = 'Empty message after cleaning';

// Scheduled task.
$string['task_sync_gateway_data'] = 'Sync kwtSMS gateway data (balance, sender IDs, coverage)';

// Privacy.
$string['privacy:metadata:local_kwtsms_log'] = 'Log of SMS messages sent via kwtSMS gateway.';
$string['privacy:metadata:local_kwtsms_log:userid'] = 'The ID of the user the SMS was sent to.';
$string['privacy:metadata:local_kwtsms_log:recipient_phone'] = 'The phone number the SMS was sent to.';
$string['privacy:metadata:local_kwtsms_log:message'] = 'The content of the SMS message.';
$string['privacy:metadata:local_kwtsms_log:timecreated'] = 'The time the SMS was sent.';
$string['privacy:metadata:external'] = 'Phone numbers and message content are sent to the kwtSMS gateway for delivery.';
$string['privacy:metadata:external:phone'] = 'The recipient phone number sent to the gateway.';
$string['privacy:metadata:external:message'] = 'The SMS message content sent to the gateway.';

// Help page.
$string['help_getting_started'] = 'Getting Started';
$string['help_getting_started_text'] = 'To use this plugin, you need a kwtSMS account. Sign up at <a href="https://www.kwtsms.com" target="_blank">kwtsms.com</a> to get your API credentials.';
$string['help_setup_guide'] = 'Setup Guide';
$string['help_senderid'] = 'Sender ID';
$string['help_senderid_text'] = 'KWT-SMS is a shared testing sender ID. For production use, register a private sender ID through your kwtSMS account. Transactional sender IDs are required for OTP messages to bypass DND filtering.';
$string['help_placeholders'] = 'Template Placeholders';
$string['help_phone_format'] = 'Phone Number Format';
$string['help_phone_format_text'] = 'Phone numbers should be in international format (e.g. 96598765432). Local numbers starting with 0 will have the default country code prepended automatically.';
$string['help_troubleshooting'] = 'Troubleshooting';
$string['help_support'] = 'Support';
$string['help_support_text'] = 'For kwtSMS account issues, contact <a href="https://www.kwtsms.com/support.html" target="_blank">kwtSMS support</a>. For plugin issues, use the GitHub Issues tracker.';

// Errors.
$string['error_no_capability'] = 'You do not have permission to access this page.';
$string['error_invalid_sesskey'] = 'Invalid session key. Please reload the page and try again.';
