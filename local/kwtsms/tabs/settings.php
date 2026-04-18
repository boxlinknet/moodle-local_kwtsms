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
 * Settings tab controller for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

global $OUTPUT;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    set_config('gateway_enabled', optional_param('gateway_enabled', 0, PARAM_INT), 'local_kwtsms');
    set_config('test_mode', optional_param('test_mode', 0, PARAM_INT), 'local_kwtsms');
    set_config('debug_logging', optional_param('debug_logging', 0, PARAM_INT), 'local_kwtsms');
    set_config('default_language', optional_param('default_language', 'en', PARAM_ALPHA), 'local_kwtsms');
    set_config('admin_phones', optional_param('admin_phones', '', PARAM_TEXT), 'local_kwtsms');
    set_config('low_balance_threshold', max(0, optional_param('low_balance_threshold', 0, PARAM_INT)), 'local_kwtsms');

    echo $OUTPUT->notification(get_string('settings_saved', 'local_kwtsms'), 'success');
}

$defaultlanguage = get_config('local_kwtsms', 'default_language') ?: 'en';

$templatecontext = [
    'formaction'          => $baseurl->out(false, ['tab' => 'settings']),
    'sesskey'             => sesskey(),
    'gatewayenabled'      => (bool) get_config('local_kwtsms', 'gateway_enabled'),
    'testmode'            => (bool) get_config('local_kwtsms', 'test_mode'),
    'debuglogging'        => (bool) get_config('local_kwtsms', 'debug_logging'),
    'langen'              => $defaultlanguage === 'en',
    'langar'              => $defaultlanguage === 'ar',
    'adminphones'         => (string) (get_config('local_kwtsms', 'admin_phones') ?: ''),
    'lowbalancethreshold' => (int) get_config('local_kwtsms', 'low_balance_threshold'),
];

echo $OUTPUT->render_from_template('local_kwtsms/settings', $templatecontext);
