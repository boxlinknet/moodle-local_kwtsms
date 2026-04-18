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
 * Gateway tab controller for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

use local_kwtsms\api_client;

global $OUTPUT, $PAGE;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $action = optional_param('gateway_action', '', PARAM_ALPHA);
    if ($action === 'savesenderid') {
        $senderid = required_param('sender_id', PARAM_TEXT);
        set_config('sender_id', $senderid, 'local_kwtsms');
        echo $OUTPUT->notification(get_string('settings_saved', 'local_kwtsms'), 'success');
    } else if ($action === 'savecountrycode') {
        $countrycode = required_param('default_country_code', PARAM_TEXT);
        set_config('default_country_code', $countrycode, 'local_kwtsms');
        echo $OUTPUT->notification(get_string('settings_saved', 'local_kwtsms'), 'success');
    }
}

$isconnected = api_client::is_configured();

$templatecontext = [
    'isconnected' => $isconnected,
    'formaction'  => $baseurl->out(false, ['tab' => 'gateway']),
    'sesskey'     => sesskey(),
];

if ($isconnected) {
    $username = (string) (get_config('local_kwtsms', 'api_username') ?: '');
    $balance = (int) api_client::get_cached_balance();
    $senderids = api_client::get_cached_senderids() ?: [];
    $currentsenderid = (string) (get_config('local_kwtsms', 'sender_id') ?: '');
    $coverage = api_client::get_cached_coverage() ?: [];
    $currentcc = (string) (get_config('local_kwtsms', 'default_country_code') ?: '');

    $senderidsctx = array_map(function ($sid) use ($currentsenderid) {
        return ['value' => $sid, 'selected' => $sid === $currentsenderid];
    }, $senderids);

    $coveragectx = array_map(function ($cc) use ($currentcc) {
        return ['value' => $cc, 'selected' => $cc === $currentcc];
    }, $coverage);

    $coveragebadges = array_map(function ($cc) {
        return ['value' => $cc];
    }, $coverage);

    $templatecontext += [
        'username'       => $username,
        'balance'        => $balance,
        'hassenderids'   => !empty($senderids),
        'senderids'      => $senderidsctx,
        'hascoverage'    => !empty($coverage),
        'coverage'       => $coveragectx,
        'coveragebadges' => $coveragebadges,
    ];
}

$PAGE->requires->js_call_amd('local_kwtsms/gateway', 'init');

echo $OUTPUT->render_from_template('local_kwtsms/gateway', $templatecontext);
