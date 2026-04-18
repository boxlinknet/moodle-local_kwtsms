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
 * Tab router entry point for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
$tab = optional_param('tab', 'dashboard', PARAM_ALPHA);

// Check capability based on tab.
if ($tab === 'logs') {
    require_capability('local/kwtsms:viewlogs', $context);
} else {
    require_capability('local/kwtsms:manage', $context);
}

$baseurl = new moodle_url('/local/kwtsms/view.php');
$PAGE->set_context($context);
$PAGE->set_url($baseurl, ['tab' => $tab]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_kwtsms'));
$PAGE->set_heading(get_string('pluginname', 'local_kwtsms'));

// Define tabs.
$validtabs = ['dashboard', 'settings', 'gateway', 'templates', 'integrations', 'logs', 'help'];
if (!in_array($tab, $validtabs)) {
    $tab = 'dashboard';
}

$tabs = [];
foreach ($validtabs as $t) {
    $tabs[] = new tabobject(
        $t,
        new moodle_url('/local/kwtsms/view.php', ['tab' => $t]),
        get_string('tab_' . $t, 'local_kwtsms')
    );
}

echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, $tab);

// Load the tab content.
$tabfile = __DIR__ . '/tabs/' . $tab . '.php';
if (file_exists($tabfile)) {
    require_once($tabfile);
} else {
    echo $OUTPUT->notification(get_string('error_tab_not_found', 'local_kwtsms'), 'error');
}

echo $OUTPUT->footer();
