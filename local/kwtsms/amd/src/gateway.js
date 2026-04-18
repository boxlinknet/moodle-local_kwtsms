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
 * Handles login, logout, and reload actions via the External Services API.
 *
 * @module     local_kwtsms/gateway
 * @package
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/str', 'core/notification'], function($, Ajax, Str, Notification) {

    /**
     * Show a feedback message in the gateway feedback area.
     *
     * @param {string} message The message text.
     * @param {string} type Alert type: 'success', 'danger', 'info', 'warning'.
     */
    function showFeedback(message, type) {
        var alertClass = 'alert alert-' + type;
        var $alert = $('<div>').addClass(alertClass).attr('role', 'alert').text(message);
        $('#kwtsms-gateway-feedback').empty().append($alert);
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

    /**
     * Show the generic request failed message in the feedback area and re-enable the button.
     *
     * @param {jQuery} $btn The button element.
     */
    function showRequestFailed($btn) {
        Str.get_string('error_request_failed', 'local_kwtsms').done(function(msg) {
            showFeedback(msg, 'danger');
            enableButton($btn);
        }).fail(Notification.exception);
    }

    return {
        /**
         * Initialize gateway tab event handlers.
         */
        init: function() {
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

                var request = Ajax.call([{
                    methodname: 'local_kwtsms_gateway_login',
                    args: {username: username, password: password}
                }])[0];

                request.done(function(result) {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        Str.get_string('login_failed', 'local_kwtsms').done(function(fallback) {
                            showFeedback(result.error || fallback, 'danger');
                            enableButton($btn);
                        }).fail(Notification.exception);
                    }
                }).fail(function() {
                    showRequestFailed($btn);
                });
            });

            $('#kwtsms-logout-btn').on('click', function() {
                clearFeedback();
                var $btn = $(this);
                disableButton($btn);

                var request = Ajax.call([{
                    methodname: 'local_kwtsms_gateway_logout',
                    args: {}
                }])[0];

                request.done(function(result) {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        enableButton($btn);
                    }
                }).fail(function() {
                    showRequestFailed($btn);
                });
            });

            $('#kwtsms-reload-btn').on('click', function() {
                clearFeedback();
                var $btn = $(this);
                disableButton($btn);

                var request = Ajax.call([{
                    methodname: 'local_kwtsms_gateway_reload',
                    args: {}
                }])[0];

                request.done(function(result) {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        Str.get_string('error_reload_failed', 'local_kwtsms').done(function(fallback) {
                            showFeedback(result.error || fallback, 'danger');
                            enableButton($btn);
                        }).fail(Notification.exception);
                    }
                }).fail(function() {
                    showRequestFailed($btn);
                });
            });
        }
    };
});
