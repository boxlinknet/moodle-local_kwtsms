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
 * External service registrations for local_kwtsms.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_kwtsms_gateway_login' => [
        'classname'   => 'local_kwtsms\external\gateway_login',
        'description' => 'Verify kwtSMS API credentials and save them on success.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/kwtsms:manage',
    ],
    'local_kwtsms_gateway_logout' => [
        'classname'   => 'local_kwtsms\external\gateway_logout',
        'description' => 'Clear kwtSMS credentials and cached gateway data.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/kwtsms:manage',
    ],
    'local_kwtsms_gateway_reload' => [
        'classname'   => 'local_kwtsms\external\gateway_reload',
        'description' => 'Refresh balance, sender IDs, and coverage from the kwtSMS API.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/kwtsms:manage',
    ],
    'local_kwtsms_logs_clear' => [
        'classname'   => 'local_kwtsms\external\logs_clear',
        'description' => 'Delete SMS log entries, optionally restricted to a date range.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/kwtsms:manage',
    ],
    'local_kwtsms_template_save' => [
        'classname'   => 'local_kwtsms\external\template_save',
        'description' => 'Update an SMS template\'s English and Arabic message bodies.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/kwtsms:manage',
    ],
    'local_kwtsms_template_reset' => [
        'classname'   => 'local_kwtsms\external\template_reset',
        'description' => 'Reset an SMS template to its default content.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/kwtsms:manage',
    ],
];
