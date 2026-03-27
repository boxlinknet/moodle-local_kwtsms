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
 * Gateway tab content for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

use local_kwtsms\api_client;

// Handle sender_id or country_code save via POST.
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

?>

<div id="kwtsms-gateway-feedback"></div>

<?php if ($isconnected) : ?>
    <?php
    $username = get_config('local_kwtsms', 'api_username');
    $balance = api_client::get_cached_balance();
    $senderids = api_client::get_cached_senderids();
    $currentsenderid = get_config('local_kwtsms', 'sender_id') ?: '';
    $coverage = api_client::get_cached_coverage();
    $currentcc = get_config('local_kwtsms', 'default_country_code') ?: '';
    $formaction = $baseurl->out(false, ['tab' => 'gateway']);
    ?>

    <h3><?php echo get_string('gateway_settings', 'local_kwtsms'); ?></h3>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <strong><?php echo get_string('gateway_status', 'local_kwtsms'); ?></strong>
        </div>
        <div class="col-sm-9">
            <span class="badge badge-success"><?php echo get_string('connected', 'local_kwtsms'); ?></span>
            <?php echo s(get_string('connected_as', 'local_kwtsms', $username)); ?>
        </div>
    </div>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <strong><?php echo get_string('balance', 'local_kwtsms'); ?></strong>
        </div>
        <div class="col-sm-9">
            <?php echo (int) $balance; ?> <?php echo get_string('credits', 'local_kwtsms'); ?>
        </div>
    </div>

    <form method="post" action="<?php echo s($formaction); ?>" class="mform">
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
        <input type="hidden" name="gateway_action" value="savesenderid">

        <div class="form-group row mb-3">
            <div class="col-sm-3">
                <label for="id_sender_id"><?php echo get_string('sender_id', 'local_kwtsms'); ?></label>
            </div>
            <div class="col-sm-9">
                <?php if (!empty($senderids)) : ?>
                    <select name="sender_id" id="id_sender_id" class="form-control custom-select"
                            onchange="this.form.submit();">
                        <option value=""><?php echo get_string('no_senderids', 'local_kwtsms'); ?></option>
                        <?php foreach ($senderids as $sid) : ?>
                            <option value="<?php echo s($sid); ?>"
                                <?php echo ($sid === $currentsenderid) ? 'selected' : ''; ?>>
                                <?php echo s($sid); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <p class="form-control-static text-muted">
                        <?php echo get_string('no_senderids', 'local_kwtsms'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <form method="post" action="<?php echo s($formaction); ?>" class="mform">
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
        <input type="hidden" name="gateway_action" value="savecountrycode">

        <div class="form-group row mb-3">
            <div class="col-sm-3">
                <label for="id_default_country_code">
                    <?php echo get_string('default_country_code', 'local_kwtsms'); ?>
                </label>
            </div>
            <div class="col-sm-9">
                <?php if (!empty($coverage)) : ?>
                    <select name="default_country_code" id="id_default_country_code"
                            class="form-control custom-select" onchange="this.form.submit();">
                        <option value="">--</option>
                        <?php foreach ($coverage as $cc) : ?>
                            <option value="<?php echo s($cc); ?>"
                                <?php echo ($cc === $currentcc) ? 'selected' : ''; ?>>
                                +<?php echo s($cc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">
                        <?php echo get_string('default_country_code_desc', 'local_kwtsms'); ?>
                    </small>
                <?php else : ?>
                    <p class="form-control-static text-muted">--</p>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if (!empty($coverage)) : ?>
        <div class="form-group row mb-3">
            <div class="col-sm-3">
                <strong><?php echo get_string('active_coverage', 'local_kwtsms'); ?></strong>
            </div>
            <div class="col-sm-9">
                <?php
                $coveragebadges = [];
                foreach ($coverage as $cc) {
                    $coveragebadges[] = '<span class="badge badge-light mr-1 mb-1">+' . s($cc) . '</span>';
                }
                echo implode(' ', $coveragebadges);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group row mb-3">
        <div class="col-sm-9 offset-sm-3">
            <button type="button" id="kwtsms-reload-btn" class="btn btn-outline-primary mr-2">
                <?php echo get_string('reload', 'local_kwtsms'); ?>
            </button>
            <button type="button" id="kwtsms-logout-btn" class="btn btn-outline-danger">
                <?php echo get_string('logout', 'local_kwtsms'); ?>
            </button>
        </div>
    </div>

<?php else : ?>
    <h3><?php echo get_string('gateway_settings', 'local_kwtsms'); ?></h3>

    <form id="kwtsms-login-form" class="mform">
        <div class="form-group row mb-3">
            <div class="col-sm-3">
                <label for="kwtsms-username"><?php echo get_string('api_username', 'local_kwtsms'); ?></label>
            </div>
            <div class="col-sm-9">
                <input type="text" id="kwtsms-username" class="form-control"
                       autocomplete="username" required>
            </div>
        </div>

        <div class="form-group row mb-3">
            <div class="col-sm-3">
                <label for="kwtsms-password"><?php echo get_string('api_password', 'local_kwtsms'); ?></label>
            </div>
            <div class="col-sm-9">
                <input type="password" id="kwtsms-password" class="form-control"
                       autocomplete="current-password" required>
            </div>
        </div>

        <div class="form-group row mb-3">
            <div class="col-sm-9 offset-sm-3">
                <button type="submit" id="kwtsms-login-btn" class="btn btn-primary">
                    <?php echo get_string('login', 'local_kwtsms'); ?>
                </button>
            </div>
        </div>
    </form>

<?php endif; ?>

<?php
$PAGE->requires->js_call_amd('local_kwtsms/gateway', 'init');
