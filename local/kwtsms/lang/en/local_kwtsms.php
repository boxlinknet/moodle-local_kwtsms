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

$string['active_coverage'] = 'Active Coverage';
$string['active_sender_id'] = 'Active Sender ID';
$string['admin_alerts'] = 'Admin Alerts';
$string['admin_phones'] = 'Admin Phone Numbers';
$string['admin_phones_desc'] = 'Phone numbers for admin SMS alerts. Comma-separated, international format (e.g. 96598765432).';
$string['api_password'] = 'API Password';
$string['api_username'] = 'API Username';
$string['balance'] = 'Balance';
$string['connected'] = 'Connected';
$string['connected_as'] = 'Connected as: {$a}';
$string['credits'] = 'credits';
$string['debug_logging'] = 'Debug Logging';
$string['debug_logging_desc'] = 'Log detailed debug information (normalization steps, placeholder replacement, API timing). Disable in production.';
$string['default_country_code'] = 'Default Country Code';
$string['default_country_code_desc'] = 'Country code to prepend to local numbers starting with 0.';
$string['default_language'] = 'Default SMS Language';
$string['default_language_desc'] = 'Fallback language for SMS when the user has no language set in their profile.';
$string['disabled'] = 'Disabled';
$string['disconnected'] = 'Disconnected';
$string['enabled'] = 'Enabled';
$string['error_invalid_sesskey'] = 'Invalid session key. Please reload the page and try again.';
$string['error_no_capability'] = 'You do not have permission to access this page.';
$string['error_reload_failed'] = 'Reload failed.';
$string['error_request_failed'] = 'Request failed. Please try again.';
$string['error_tab_not_found'] = 'Tab not found.';
$string['event_assessable_uploaded'] = 'Assignment Submitted';
$string['event_assessable_uploaded_desc'] = 'Send SMS when an assignment file is submitted.';
$string['event_attempt_submitted'] = 'Quiz Submitted';
$string['event_attempt_submitted_desc'] = 'Send SMS when a quiz attempt is submitted.';
$string['event_course_completed'] = 'Course Completed';
$string['event_course_completed_desc'] = 'Send SMS when a user completes a course.';
$string['event_low_balance_alert'] = 'Low Balance Alert';
$string['event_user_created'] = 'New User Registered';
$string['event_user_created_desc'] = 'Send SMS to admin when a new user registers.';
$string['event_user_enrolment_created'] = 'User Enrolled';
$string['event_user_enrolment_created_desc'] = 'Send SMS when a user is enrolled in a course.';
$string['event_user_enrolment_deleted'] = 'User Unenrolled';
$string['event_user_enrolment_deleted_desc'] = 'Send SMS when a user is unenrolled from a course.';
$string['event_user_graded'] = 'Grade Posted';
$string['event_user_graded_desc'] = 'Send SMS when a grade is posted for a user.';
$string['failed_count'] = 'Failed';
$string['gateway_enabled'] = 'Enable Gateway';
$string['gateway_enabled_desc'] = 'Global on/off switch for SMS sending.';
$string['gateway_settings'] = 'Gateway Connection';
$string['gateway_status'] = 'Gateway Status';
$string['help_getting_started'] = 'Getting Started';
$string['help_getting_started_step1'] = 'Create an account at <a href="https://www.kwtsms.com" target="_blank">kwtsms.com</a>.';
$string['help_getting_started_step2'] = 'Once registered, navigate to your account dashboard to find your API username and password.';
$string['help_getting_started_step3'] = 'Use these credentials in the Gateway tab to connect this plugin to the kwtSMS service.';
$string['help_getting_started_text'] = 'To use this plugin, you need a kwtSMS account. Sign up at <a href="https://www.kwtsms.com" target="_blank">kwtsms.com</a> to get your API credentials.';
$string['help_phone_format'] = 'Phone Number Format';
$string['help_phone_format_arabic'] = '<strong>Arabic digit conversion:</strong> Arabic-Indic digits (<code>٠١٢٣٤٥٦٧٨٩</code>) are automatically converted to Western digits (<code>0123456789</code>) before processing.';
$string['help_phone_format_international'] = '<strong>International format:</strong> Use the full number including country code, e.g. <code>96598765432</code> for a Kuwait mobile number.';
$string['help_phone_format_local'] = '<strong>Local number conversion:</strong> If a user\'s phone number starts with <code>0</code>, the plugin will automatically strip the leading zero and prepend the default country code configured in the Gateway tab.';
$string['help_phone_format_note'] = 'Make sure the user\'s phone number field in their Moodle profile is filled in correctly. Users without a phone number will be skipped (logged as a "No phone number" reason).';
$string['help_phone_format_text'] = 'Phone numbers should be in international format (e.g. 96598765432). Local numbers starting with 0 will have the default country code prepended automatically.';
$string['help_placeholders'] = 'Template Placeholders';
$string['help_placeholders_intro'] = 'Each event type supports a set of placeholders that are replaced with actual values when the SMS is sent. Wrap placeholder names in curly braces in your templates, e.g. <code>{firstname}</code>.';
$string['help_senderid'] = 'Sender ID';
$string['help_senderid_register'] = 'To register a private sender ID, contact kwtSMS support or use the sender ID management panel in your kwtSMS account.';
$string['help_senderid_table_description'] = 'Description';
$string['help_senderid_table_kwt_description'] = 'Shared testing sender ID available on all accounts.';
$string['help_senderid_table_kwt_usecase'] = 'Development and testing only.';
$string['help_senderid_table_promo_description'] = 'Private sender ID for marketing messages. Subject to DND filtering.';
$string['help_senderid_table_promo_type'] = 'Promotional';
$string['help_senderid_table_promo_usecase'] = 'Course announcements, marketing campaigns.';
$string['help_senderid_table_trans_description'] = 'Private sender ID registered for transactional messages. Bypasses DND (Do Not Disturb) filtering.';
$string['help_senderid_table_trans_type'] = 'Transactional';
$string['help_senderid_table_trans_usecase'] = 'OTP codes, account alerts, enrollment confirmations.';
$string['help_senderid_table_type'] = 'Type';
$string['help_senderid_table_usecase'] = 'Use Case';
$string['help_senderid_text'] = 'KWT-SMS is a shared testing sender ID. For production use, register a private sender ID through your kwtSMS account. Transactional sender IDs are required for OTP messages to bypass DND filtering.';
$string['help_setup_guide'] = 'Setup Guide';
$string['help_setup_step_gateway'] = 'Enter your kwtSMS API username and password, then click Login. Once connected, select your Sender ID and default country code.';
$string['help_setup_step_integrations'] = 'Enable or disable SMS notifications for each event type (enrolment, grading, course completion, etc.).';
$string['help_setup_step_settings'] = 'Enable the gateway, choose your default SMS language, configure admin phone numbers, and set the low balance threshold.';
$string['help_setup_step_templates'] = 'Customize the English and Arabic message templates for each event. Use placeholders to personalize messages.';
$string['help_setup_testmode_note'] = 'It is recommended to enable <strong>Test Mode</strong> in Settings during initial setup. Test mode sends messages to the API with a test flag so they are not delivered to handsets and credits can be recovered from the kwtSMS queue.';
$string['help_support'] = 'Support';
$string['help_support_plugin'] = '<strong>Plugin Issues:</strong> <a href="https://github.com/boxlinknet/moodle-local_kwtsms/issues" target="_blank">GitHub Issues</a> (bug reports, feature requests, plugin configuration)';
$string['help_support_provider'] = '<strong>kwtSMS Account Support:</strong> <a href="https://www.kwtsms.com/support.html" target="_blank">kwtsms.com/support</a> (billing, sender IDs, coverage, API issues)';
$string['help_support_text'] = 'For kwtSMS account issues, contact <a href="https://www.kwtsms.com/support.html" target="_blank">kwtSMS support</a>. For plugin issues, use the GitHub Issues tracker.';
$string['help_troubleshooting'] = 'Troubleshooting';
$string['help_troubleshooting_balance_heading'] = 'Balance issues';
$string['help_troubleshooting_balance_item1'] = 'If messages fail with a balance error, check your credit balance in the Gateway tab or your kwtSMS account.';
$string['help_troubleshooting_balance_item2'] = 'Set a low balance threshold in Settings to receive admin alerts before credits run out.';
$string['help_troubleshooting_balance_item3'] = 'Credits used in test mode can be recovered from the kwtSMS queue.';
$string['help_troubleshooting_country_heading'] = 'Country not covered';
$string['help_troubleshooting_country_item1'] = 'If a user\'s phone number belongs to a country not in your kwtSMS coverage list, the message will be skipped.';
$string['help_troubleshooting_country_item2'] = 'Check your active coverage in the Gateway tab. Contact kwtSMS support to enable additional countries.';
$string['help_troubleshooting_nosms_heading'] = 'SMS not arriving';
$string['help_troubleshooting_nosms_item1'] = '<strong>Check the Logs tab:</strong> Look for the message status. If it shows "Failed" or "Skipped", the reason column will explain why.';
$string['help_troubleshooting_nosms_item2'] = '<strong>Test Mode enabled:</strong> When test mode is on, messages are sent to the API but not delivered to handsets. Disable test mode in Settings for production use.';
$string['help_troubleshooting_nosms_item3'] = '<strong>Check the kwtSMS queue:</strong> Log in to your kwtSMS account and check the message archive/queue for delivery status.';
$string['help_troubleshooting_nosms_item4'] = '<strong>Gateway disabled:</strong> Verify that the gateway is enabled in the Settings tab.';
$string['help_troubleshooting_senderid_heading'] = 'Wrong Sender ID';
$string['help_troubleshooting_senderid_item1'] = 'The sender ID displayed on the recipient\'s phone is controlled by the Sender ID selected in the Gateway tab.';
$string['help_troubleshooting_senderid_item2'] = '<code>KWT-SMS</code> is the shared test sender ID. For production, register a private sender ID through your kwtSMS account.';
$string['integrations_desc'] = 'Enable or disable SMS notifications for each event.';
$string['kwtsms:manage'] = 'Manage kwtSMS settings';
$string['kwtsms:viewlogs'] = 'View kwtSMS logs';
$string['language_ar'] = 'العربية';
$string['language_en'] = 'English';
$string['last_synced'] = 'Last synced';
$string['log_api_response'] = 'API Response';
$string['log_clear'] = 'Clear Logs';
$string['log_clear_confirm'] = 'Delete all logs in the selected date range? This action cannot be undone.';
$string['log_cleared'] = 'Logs cleared successfully.';
$string['log_credits'] = 'Credits';
$string['log_date'] = 'Date/Time';
$string['log_details'] = 'View Details';
$string['log_error_code'] = 'Error Code';
$string['log_event'] = 'Event';
$string['log_export'] = 'Export CSV';
$string['log_filter_all'] = 'All';
$string['log_filter_btn'] = 'Filter';
$string['log_filter_event'] = 'Event Type';
$string['log_filter_from'] = 'From';
$string['log_filter_search'] = 'Search by phone';
$string['log_filter_status'] = 'Status';
$string['log_filter_to'] = 'To';
$string['log_message'] = 'Message';
$string['log_no_records'] = 'No SMS log entries found.';
$string['log_phone'] = 'Recipient Phone';
$string['log_reason'] = 'Reason';
$string['log_status'] = 'Status';
$string['log_template'] = 'Template';
$string['log_test_mode'] = 'Test';
$string['login'] = 'Login';
$string['login_failed'] = 'Invalid credentials. Please check your API username and password.';
$string['login_success'] = 'Successfully connected to kwtSMS gateway.';
$string['logout'] = 'Logout';
$string['logout_success'] = 'Disconnected from kwtSMS gateway.';
$string['low_balance_threshold'] = 'Low Balance Threshold';
$string['low_balance_threshold_desc'] = 'Send admin alert when balance drops below this number. Set to 0 to disable.';
$string['never_synced'] = 'Never';
$string['no_recent_activity'] = 'No recent SMS activity.';
$string['no_senderids'] = 'No sender IDs available on this account.';
$string['not_configured'] = 'Not configured';
$string['pluginname'] = 'kwtSMS Notifications';
$string['privacy:metadata:external'] = 'Phone numbers and message content are sent to the kwtSMS gateway for delivery.';
$string['privacy:metadata:external:message'] = 'The SMS message content sent to the gateway.';
$string['privacy:metadata:external:phone'] = 'The recipient phone number sent to the gateway.';
$string['privacy:metadata:local_kwtsms_log'] = 'Log of SMS messages sent via kwtSMS gateway.';
$string['privacy:metadata:local_kwtsms_log:message'] = 'The content of the SMS message.';
$string['privacy:metadata:local_kwtsms_log:recipient_phone'] = 'The phone number the SMS was sent to.';
$string['privacy:metadata:local_kwtsms_log:timecreated'] = 'The time the SMS was sent.';
$string['privacy:metadata:local_kwtsms_log:userid'] = 'The ID of the user the SMS was sent to.';
$string['recent_activity'] = 'Recent Activity';
$string['recipient_admin'] = 'Admin';
$string['recipient_both'] = 'Both';
$string['recipient_student'] = 'Student';
$string['reload'] = 'Reload';
$string['reload_success'] = 'Gateway data refreshed successfully.';
$string['send_statistics'] = 'Send Statistics';
$string['sender_id'] = 'Sender ID';
$string['sent_month'] = 'Sent This Month';
$string['sent_today'] = 'Sent Today';
$string['sent_week'] = 'Sent This Week';
$string['settings_general'] = 'General Settings';
$string['settings_saved'] = 'Settings saved successfully.';
$string['skip_country_not_covered'] = 'Country not covered';
$string['skip_empty_message'] = 'Empty message after cleaning';
$string['skip_gateway_disabled'] = 'Gateway disabled';
$string['skip_gateway_not_configured'] = 'Gateway not configured';
$string['skip_no_phone_number'] = 'No phone number';
$string['skip_zero_balance'] = 'Zero balance';
$string['status_failed'] = 'Failed';
$string['status_queued'] = 'Queued';
$string['status_sent'] = 'Sent';
$string['status_skipped'] = 'Skipped';
$string['student_notifications'] = 'Student Notifications';
$string['sync_now'] = 'Sync Now';
$string['tab_dashboard'] = 'Dashboard';
$string['tab_gateway'] = 'Gateway';
$string['tab_help'] = 'Help';
$string['tab_integrations'] = 'Integrations';
$string['tab_logs'] = 'Logs';
$string['tab_settings'] = 'Settings';
$string['tab_templates'] = 'Templates';
$string['task_sync_gateway_data'] = 'Sync kwtSMS gateway data (balance, sender IDs, coverage)';
$string['template_actions'] = 'Actions';
$string['template_char_count'] = 'Characters: {$a->chars} ({$a->pages} SMS page(s))';
$string['template_edit'] = 'Edit';
$string['template_event'] = 'Event';
$string['template_message_ar'] = 'Arabic Message';
$string['template_message_en'] = 'English Message';
$string['template_placeholders'] = 'Available Placeholders';
$string['template_preview'] = 'Preview';
$string['template_recipient'] = 'Recipient';
$string['template_reset'] = 'Reset to Default';
$string['template_reset_confirm'] = 'Reset this template to its default content? Your customizations will be lost.';
$string['template_reset_failed'] = 'Failed to reset template. Please try again.';
$string['template_reset_success'] = 'Template reset to default.';
$string['template_save_failed'] = 'Failed to save template. Please try again.';
$string['template_saved'] = 'Template saved successfully.';
$string['test_mode'] = 'Test Mode';
$string['test_mode_desc'] = 'When enabled, SMS messages are sent to the API with test=1. Messages are not delivered to handsets. Credits can be recovered from the kwtSMS queue.';
$string['test_mode_off'] = 'Test Mode OFF';
$string['test_mode_on'] = 'Test Mode ON';
