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
 * Help tab content for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

use local_kwtsms\template_manager;

?>

<!-- 1. Getting Started -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0"><?php echo get_string('help_getting_started', 'local_kwtsms'); ?></h4>
    </div>
    <div class="card-body">
        <p><?php echo get_string('help_getting_started_text', 'local_kwtsms'); ?></p>
        <ol>
            <li>Create an account at <a href="https://www.kwtsms.com" target="_blank">kwtsms.com</a>.</li>
            <li>Once registered, navigate to your account dashboard to find your API username and password.</li>
            <li>Use these credentials in the Gateway tab to connect this plugin to the kwtSMS service.</li>
        </ol>
    </div>
</div>

<!-- 2. Setup Guide -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0"><?php echo get_string('help_setup_guide', 'local_kwtsms'); ?></h4>
    </div>
    <div class="card-body">
        <ol>
            <li>
                <strong><?php echo get_string('tab_gateway', 'local_kwtsms'); ?>:</strong>
                Enter your kwtSMS API username and password, then click Login. Once connected,
                select your Sender ID and default country code.
            </li>
            <li>
                <strong><?php echo get_string('tab_settings', 'local_kwtsms'); ?>:</strong>
                Enable the gateway, choose your default SMS language, configure admin phone numbers,
                and set the low balance threshold.
            </li>
            <li>
                <strong><?php echo get_string('tab_integrations', 'local_kwtsms'); ?>:</strong>
                Enable or disable SMS notifications for each event type (enrolment, grading, course completion, etc.).
            </li>
            <li>
                <strong><?php echo get_string('tab_templates', 'local_kwtsms'); ?>:</strong>
                Customize the English and Arabic message templates for each event. Use placeholders to personalize messages.
            </li>
        </ol>
        <div class="alert alert-info">
            It is recommended to enable <strong>Test Mode</strong> in Settings during initial setup.
            Test mode sends messages to the API with a test flag so they are not delivered
            to handsets and credits can be recovered from the kwtSMS queue.
        </div>
    </div>
</div>

<!-- 3. Sender ID -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0"><?php echo get_string('help_senderid', 'local_kwtsms'); ?></h4>
    </div>
    <div class="card-body">
        <p><?php echo get_string('help_senderid_text', 'local_kwtsms'); ?></p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Use Case</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>KWT-SMS</code></td>
                    <td>Shared testing sender ID available on all accounts.</td>
                    <td>Development and testing only.</td>
                </tr>
                <tr>
                    <td>Transactional</td>
                    <td>Private sender ID registered for transactional messages. Bypasses DND (Do Not Disturb) filtering.</td>
                    <td>OTP codes, account alerts, enrollment confirmations.</td>
                </tr>
                <tr>
                    <td>Promotional</td>
                    <td>Private sender ID for marketing messages. Subject to DND filtering.</td>
                    <td>Course announcements, marketing campaigns.</td>
                </tr>
            </tbody>
        </table>
        <p>To register a private sender ID, contact kwtSMS support or use the sender ID management panel in your kwtSMS account.</p>
    </div>
</div>

<!-- 4. Template Placeholders -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0"><?php echo get_string('help_placeholders', 'local_kwtsms'); ?></h4>
    </div>
    <div class="card-body">
        <p>Each event type supports a set of placeholders that are replaced with actual values
        when the SMS is sent. Wrap placeholder names in curly braces in your templates,
        e.g. <code>{firstname}</code>.</p>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><?php echo get_string('template_event', 'local_kwtsms'); ?></th>
                    <th><?php echo get_string('template_placeholders', 'local_kwtsms'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $events = [
                    'user_enrolment_created',
                    'user_enrolment_deleted',
                    'user_graded',
                    'course_completed',
                    'attempt_submitted',
                    'assessable_uploaded',
                    'user_created',
                ];
                foreach ($events as $event) {
                    $placeholders = template_manager::get_placeholders_for_event($event);
                    $placeholderlist = implode(', ', array_map(function ($p) {
                        return '<code>{' . s($p) . '}</code>';
                    }, $placeholders));
                    $eventlabel = get_string('event_' . $event, 'local_kwtsms');
                    ?>
                    <tr>
                        <td><?php echo $eventlabel; ?></td>
                        <td><?php echo $placeholderlist; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 5. Phone Number Format -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0"><?php echo get_string('help_phone_format', 'local_kwtsms'); ?></h4>
    </div>
    <div class="card-body">
        <p><?php echo get_string('help_phone_format_text', 'local_kwtsms'); ?></p>
        <ul>
            <li>
                <strong>International format:</strong> Use the full number including country code,
                e.g. <code>96598765432</code> for a Kuwait mobile number.
            </li>
            <li><strong>Local number conversion:</strong> If a user's phone number starts with
            <code>0</code>, the plugin will automatically strip the leading zero and prepend the
            default country code configured in the Gateway tab.</li>
            <li><strong>Arabic digit conversion:</strong> Arabic-Indic digits
            (<code>٠١٢٣٤٥٦٧٨٩</code>) are automatically converted to Western digits
            (<code>0123456789</code>) before processing.</li>
        </ul>
        <div class="alert alert-warning">
            Make sure the user's phone number field in their Moodle profile is filled in correctly.
            Users without a phone number will be skipped (logged as "No phone number").
        </div>
    </div>
</div>

<!-- 6. Troubleshooting -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0"><?php echo get_string('help_troubleshooting', 'local_kwtsms'); ?></h4>
    </div>
    <div class="card-body">
        <h5>SMS not arriving</h5>
        <ul>
            <li><strong>Check the Logs tab:</strong> Look for the message status.
            If it shows "Failed" or "Skipped", the reason column will explain why.</li>
            <li><strong>Test Mode enabled:</strong> When test mode is on, messages are sent to
            the API but not delivered to handsets. Disable test mode in Settings for production use.</li>
            <li>
                <strong>Check the kwtSMS queue:</strong> Log in to your kwtSMS account and check
                the message archive/queue for delivery status.
            </li>
            <li><strong>Gateway disabled:</strong> Verify that the gateway is enabled in the Settings tab.</li>
        </ul>

        <h5>Balance issues</h5>
        <ul>
            <li>If messages fail with a balance error, check your credit balance in the Gateway tab or your kwtSMS account.</li>
            <li>Set a low balance threshold in Settings to receive admin alerts before credits run out.</li>
            <li>Credits used in test mode can be recovered from the kwtSMS queue.</li>
        </ul>

        <h5>Wrong Sender ID</h5>
        <ul>
            <li>The sender ID displayed on the recipient's phone is controlled by the Sender ID selected in the Gateway tab.</li>
            <li>
                <code>KWT-SMS</code> is the shared test sender ID. For production, register a
                private sender ID through your kwtSMS account.
            </li>
        </ul>

        <h5>Country not covered</h5>
        <ul>
            <li>If a user's phone number belongs to a country not in your kwtSMS coverage list, the message will be skipped.</li>
            <li>Check your active coverage in the Gateway tab. Contact kwtSMS support to enable additional countries.</li>
        </ul>
    </div>
</div>

<!-- 7. Support -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0"><?php echo get_string('help_support', 'local_kwtsms'); ?></h4>
    </div>
    <div class="card-body">
        <p><?php echo get_string('help_support_text', 'local_kwtsms'); ?></p>
        <ul>
            <li>
                <strong>kwtSMS Account Support:</strong>
                <a href="https://www.kwtsms.com/support.html" target="_blank">kwtsms.com/support</a>
                (billing, sender IDs, coverage, API issues)
            </li>
            <li>
                <strong>Plugin Issues:</strong>
                <a href="https://github.com/kwtsms/kwtsms-moodle/issues" target="_blank">GitHub Issues</a>
                (bug reports, feature requests, plugin configuration)
            </li>
        </ul>
    </div>
</div>
