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
 * Logs tab client-side logic for local_kwtsms.
 *
 * Handles clear logs (External Services), view details toggle, and CSV export navigation.
 *
 * @module     local_kwtsms/logs
 * @package
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/str', 'core/notification'], function($, Ajax, Str, Notification) {

    /**
     * Show a feedback message in the logs feedback area.
     *
     * @param {string} message The message text.
     * @param {string} type Alert type: 'success', 'danger', 'info', 'warning'.
     */
    function showFeedback(message, type) {
        var alertClass = 'alert alert-' + type;
        var $alert = $('<div>').addClass(alertClass).attr('role', 'alert').text(message);
        $('#kwtsms-logs-feedback').empty().append($alert);
    }

    /**
     * Collect current filter params from the filter form.
     *
     * Property names use snake_case to match the PHP endpoint parameter
     * names expected by logs_export.php.
     *
     * @return {Object} The filter parameters as key-value pairs.
     */
    /* eslint-disable camelcase */
    function getFilterParams() {
        var params = {};
        var status = $('#filter_status').val();
        var event = $('#filter_event').val();
        var search = $('#filter_search').val();
        var dateFrom = $('#filter_date_from').val();
        var dateTo = $('#filter_date_to').val();

        if (status) {
            params.filter_status = status;
        }
        if (event) {
            params.filter_event = event;
        }
        if (search) {
            params.filter_search = search;
        }
        if (dateFrom) {
            params.filter_date_from = dateFrom;
        }
        if (dateTo) {
            params.filter_date_to = dateTo;
        }
        return params;
    }
    /* eslint-enable camelcase */

    /**
     * Build a query string from an object.
     *
     * @param {Object} obj Key-value pairs.
     * @return {string} The query string without leading ?.
     */
    function buildQueryString(obj) {
        var parts = [];
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]));
            }
        }
        return parts.join('&');
    }

    return {
        /**
         * Initialize logs tab event handlers.
         */
        init: function() {
            $(document).on('click', '.kwtsms-log-details-btn', function() {
                var logId = $(this).data('log-id');
                var $detailRow = $('#kwtsms-detail-' + logId);
                $detailRow.toggleClass('d-none');
            });

            $('#kwtsms-clear-logs-btn').on('click', function() {
                var dateFrom = $('#filter_date_from').val();
                var dateTo = $('#filter_date_to').val();
                var $btn = $(this);

                Notification.saveCancel(
                    Str.get_string('log_clear', 'local_kwtsms'),
                    Str.get_string('log_clear_confirm', 'local_kwtsms'),
                    Str.get_string('yes'),
                    function() {
                        $btn.prop('disabled', true);

                        var args = {
                            datefrom: 0,
                            dateto: 0
                        };
                        if (dateFrom) {
                            args.datefrom = Math.floor(new Date(dateFrom + 'T00:00:00').getTime() / 1000);
                        }
                        if (dateTo) {
                            args.dateto = Math.floor(new Date(dateTo + 'T23:59:59').getTime() / 1000);
                        }

                        var request = Ajax.call([{
                            methodname: 'local_kwtsms_logs_clear',
                            args: args
                        }])[0];

                        request.done(function(result) {
                            if (result.success) {
                                window.location.reload();
                            } else {
                                $btn.prop('disabled', false);
                            }
                        }).fail(function() {
                            Str.get_string('error_request_failed', 'local_kwtsms').done(function(errmsg) {
                                showFeedback(errmsg, 'danger');
                            }).fail(Notification.exception);
                            $btn.prop('disabled', false);
                        });
                    }
                );
            });

            $('#kwtsms-export-logs-btn').on('click', function() {
                var params = getFilterParams();
                params.sesskey = M.cfg.sesskey;
                var qs = buildQueryString(params);
                window.location.href = M.cfg.wwwroot + '/local/kwtsms/logs_export.php?' + qs;
            });
        }
    };
});
