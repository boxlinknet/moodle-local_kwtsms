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
 * Settings tab content for local_kwtsms admin UI.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:disable moodle.Commenting.MissingDocblock.File
// phpcs:disable moodle.Commenting.FileExpectedTags

defined('MOODLE_INTERNAL') || die();

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    set_config('gateway_enabled', optional_param('gateway_enabled', 0, PARAM_INT), 'local_kwtsms');
    set_config('test_mode', optional_param('test_mode', 0, PARAM_INT), 'local_kwtsms');
    set_config('debug_logging', optional_param('debug_logging', 0, PARAM_INT), 'local_kwtsms');
    set_config('default_language', optional_param('default_language', 'en', PARAM_ALPHA), 'local_kwtsms');
    set_config('admin_phones', optional_param('admin_phones', '', PARAM_TEXT), 'local_kwtsms');
    set_config('low_balance_threshold', max(0, optional_param('low_balance_threshold', 0, PARAM_INT)), 'local_kwtsms');

    echo $OUTPUT->notification(get_string('settings_saved', 'local_kwtsms'), 'success');
}

// Load current values.
$gatewayenabled = (int) get_config('local_kwtsms', 'gateway_enabled');
$testmode = (int) get_config('local_kwtsms', 'test_mode');
$debuglogging = (int) get_config('local_kwtsms', 'debug_logging');
$defaultlanguage = get_config('local_kwtsms', 'default_language') ?: 'en';
$adminphones = get_config('local_kwtsms', 'admin_phones') ?: '';
$lowbalancethreshold = (int) get_config('local_kwtsms', 'low_balance_threshold');

$formaction = $baseurl->out(false, ['tab' => 'settings']);
?>

<form method="post" action="<?php echo s($formaction); ?>" class="mform">
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">

    <h3><?php echo get_string('settings_general', 'local_kwtsms'); ?></h3>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_gateway_enabled"><?php echo get_string('gateway_enabled', 'local_kwtsms'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="hidden" name="gateway_enabled" value="0">
            <input type="checkbox" name="gateway_enabled" id="id_gateway_enabled" value="1"
                <?php echo $gatewayenabled ? 'checked' : ''; ?>>
            <small class="form-text text-muted">
                <?php echo get_string('gateway_enabled_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_test_mode"><?php echo get_string('test_mode', 'local_kwtsms'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="hidden" name="test_mode" value="0">
            <input type="checkbox" name="test_mode" id="id_test_mode" value="1"
                <?php echo $testmode ? 'checked' : ''; ?>>
            <small class="form-text text-muted">
                <?php echo get_string('test_mode_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_debug_logging"><?php echo get_string('debug_logging', 'local_kwtsms'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="hidden" name="debug_logging" value="0">
            <input type="checkbox" name="debug_logging" id="id_debug_logging" value="1"
                <?php echo $debuglogging ? 'checked' : ''; ?>>
            <small class="form-text text-muted">
                <?php echo get_string('debug_logging_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_default_language"><?php echo get_string('default_language', 'local_kwtsms'); ?></label>
        </div>
        <div class="col-sm-9">
            <select name="default_language" id="id_default_language" class="form-control custom-select">
                <option value="en" <?php echo $defaultlanguage === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="ar" <?php echo $defaultlanguage === 'ar' ? 'selected' : ''; ?>>العربية</option>
            </select>
            <small class="form-text text-muted">
                <?php echo get_string('default_language_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_admin_phones"><?php echo get_string('admin_phones', 'local_kwtsms'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" name="admin_phones" id="id_admin_phones" class="form-control"
                value="<?php echo s($adminphones); ?>">
            <small class="form-text text-muted">
                <?php echo get_string('admin_phones_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>

    <div class="form-group row mb-3">
        <div class="col-sm-3">
            <label for="id_low_balance_threshold"><?php echo get_string('low_balance_threshold', 'local_kwtsms'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="number" name="low_balance_threshold" id="id_low_balance_threshold" class="form-control"
                value="<?php echo $lowbalancethreshold; ?>" min="0">
            <small class="form-text text-muted">
                <?php echo get_string('low_balance_threshold_desc', 'local_kwtsms'); ?>
            </small>
        </div>
    </div>

    <div class="form-group row mb-3">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary"><?php echo get_string('savechanges'); ?></button>
        </div>
    </div>
</form>
