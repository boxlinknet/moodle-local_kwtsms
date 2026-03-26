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
 * Gateway tab client-side logic for local_kwtsms.
 *
 * Handles login, logout, and reload actions via AJAX.
 *
 * @module     local_kwtsms/gateway
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {

    /**
     * Show a feedback message in the gateway feedback area.
     *
     * @param {string} message The message text.
     * @param {string} type Alert type: 'success', 'danger', 'info', 'warning'.
     */
    function showFeedback(message, type) {
        var alertClass = 'alert alert-' + type;
        $('#kwtsms-gateway-feedback').html(
            '<div class="' + alertClass + '" role="alert">' + message + '</div>'
        );
    }

    /**
     * Clear the feedback area.
     */
    function clearFeedback() {
        $('#kwtsms-gateway-feedback').empty();
    }

    /**
     * Disable a button and show a spinner.
     *
     * @param {jQuery} $btn The button element.
     */
    function disableButton($btn) {
        $btn.prop('disabled', true);
        $btn.data('original-text', $btn.text());
        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    }

    /**
     * Re-enable a button and restore its text.
     *
     * @param {jQuery} $btn The button element.
     */
    function enableButton($btn) {
        $btn.prop('disabled', false);
        $btn.text($btn.data('original-text'));
    }

    return {
        /**
         * Initialize gateway tab event handlers.
         */
        init: function() {
            // Login form submission.
            $('#kwtsms-login-form').on('submit', function(e) {
                e.preventDefault();
                clearFeedback();

                var $btn = $('#kwtsms-login-btn');
                var username = $('#kwtsms-username').val().trim();
                var password = $('#kwtsms-password').val().trim();

                if (!username || !password) {
                    return;
                }

                disableButton($btn);

                $.post(
                    M.cfg.wwwroot + '/local/kwtsms/ajax/gateway_login.php',
                    {
                        username: username,
                        password: password,
                        sesskey: M.cfg.sesskey
                    },
                    function(result) {
                        if (result.success) {
                            location.reload();
                        } else {
                            showFeedback(result.error || 'Login failed.', 'danger');
                            enableButton($btn);
                        }
                    },
                    'json'
                ).fail(function() {
                    showFeedback('Request failed. Please try again.', 'danger');
                    enableButton($btn);
                });
            });

            // Logout button.
            $('#kwtsms-logout-btn').on('click', function() {
                clearFeedback();
                var $btn = $(this);
                disableButton($btn);

                $.post(
                    M.cfg.wwwroot + '/local/kwtsms/ajax/gateway_logout.php',
                    {
                        sesskey: M.cfg.sesskey
                    },
                    function(result) {
                        if (result.success) {
                            location.reload();
                        } else {
                            showFeedback(result.error || 'Logout failed.', 'danger');
                            enableButton($btn);
                        }
                    },
                    'json'
                ).fail(function() {
                    showFeedback('Request failed. Please try again.', 'danger');
                    enableButton($btn);
                });
            });

            // Reload button.
            $('#kwtsms-reload-btn').on('click', function() {
                clearFeedback();
                var $btn = $(this);
                disableButton($btn);

                $.post(
                    M.cfg.wwwroot + '/local/kwtsms/ajax/gateway_reload.php',
                    {
                        sesskey: M.cfg.sesskey
                    },
                    function(result) {
                        if (result.success) {
                            location.reload();
                        } else {
                            showFeedback(result.error || 'Reload failed.', 'danger');
                            enableButton($btn);
                        }
                    },
                    'json'
                ).fail(function() {
                    showFeedback('Request failed. Please try again.', 'danger');
                    enableButton($btn);
                });
            });
        }
    };
});
