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
 * Client-side JS for the Templates tab.
 *
 * Handles inline editing, AJAX save/reset, and character counting.
 *
 * @module     local_kwtsms/templates
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /**
     * SMS page limits.
     * Arabic: 70 chars single, 67 per page multipart.
     * English: 160 chars single, 153 per page multipart.
     */
    var SMS_LIMITS = {
        ar: {single: 70, multi: 67},
        en: {single: 160, multi: 153}
    };

    /**
     * Check whether a string contains Arabic characters.
     *
     * @param {string} text
     * @return {boolean}
     */
    function isArabic(text) {
        return /[\u0600-\u06FF]/.test(text);
    }

    /**
     * Calculate the number of SMS pages for a given message.
     *
     * @param {string} message
     * @return {object} With keys: chars, pages.
     */
    function countSms(message) {
        var len = message.length;
        if (len === 0) {
            return {chars: 0, pages: 0};
        }

        var limits = isArabic(message) ? SMS_LIMITS.ar : SMS_LIMITS.en;

        var pages;
        if (len <= limits.single) {
            pages = 1;
        } else {
            pages = Math.ceil(len / limits.multi);
        }

        return {chars: len, pages: pages};
    }

    /**
     * Update the character/page counter display for a textarea.
     *
     * @param {jQuery} $textarea
     * @param {jQuery} $counter
     */
    function updateCounter($textarea, $counter) {
        var result = countSms($textarea.val());
        $counter.text('Characters: ' + result.chars + ' (' + result.pages + ' SMS page(s))');
    }

    /**
     * Show the edit row for a given template id.
     *
     * @param {number} id
     */
    function showEditRow(id) {
        $('tr[data-edit-for="' + id + '"]').show();
        var $editRow = $('tr[data-edit-for="' + id + '"]');

        // Update counters on show.
        var $enTextarea = $editRow.find('.kwtsms-textarea-en');
        var $arTextarea = $editRow.find('.kwtsms-textarea-ar');
        updateCounter($enTextarea, $editRow.find('.kwtsms-char-count-en'));
        updateCounter($arTextarea, $editRow.find('.kwtsms-char-count-ar'));
    }

    /**
     * Hide the edit row for a given template id.
     *
     * @param {number} id
     */
    function hideEditRow(id) {
        $('tr[data-edit-for="' + id + '"]').hide();
    }

    /**
     * Save a template via AJAX.
     *
     * @param {number} id
     */
    function saveTemplate(id) {
        var $editRow = $('tr[data-edit-for="' + id + '"]');
        var messageEn = $editRow.find('.kwtsms-textarea-en').val();
        var messageAr = $editRow.find('.kwtsms-textarea-ar').val();

        var $saveBtn = $editRow.find('.kwtsms-save-btn');
        $saveBtn.prop('disabled', true);

        $.ajax({
            url: M.cfg.wwwroot + '/local/kwtsms/ajax/template_save.php',
            method: 'POST',
            data: {
                id: id,
                message_en: messageEn,
                message_ar: messageAr,
                sesskey: M.cfg.sesskey
            },
            dataType: 'json'
        }).done(function(response) {
            if (response.success) {
                // Update the display cells in the main row.
                var $mainRow = $('tr[data-template-id="' + id + '"]');
                var maxDisplay = 80;
                var enShort = messageEn.length > maxDisplay
                    ? messageEn.substring(0, maxDisplay) + '...'
                    : messageEn;
                var arShort = messageAr.length > maxDisplay
                    ? messageAr.substring(0, maxDisplay) + '...'
                    : messageAr;
                $mainRow.find('.kwtsms-msg-en-display').text(enShort);
                $mainRow.find('.kwtsms-msg-ar-display').text(arShort);

                hideEditRow(id);

                // Brief success feedback.
                $saveBtn.removeClass('btn-primary').addClass('btn-success').text('Saved!');
                setTimeout(function() {
                    $saveBtn.removeClass('btn-success').addClass('btn-primary').text('Save changes');
                    $saveBtn.prop('disabled', false);
                }, 1500);
            } else {
                $saveBtn.prop('disabled', false);
                window.alert('Failed to save template. Please try again.');
            }
        }).fail(function() {
            $saveBtn.prop('disabled', false);
            window.alert('Error saving template. Please check your connection and try again.');
        });
    }

    /**
     * Reset a template via AJAX after confirmation.
     *
     * @param {number} id
     */
    function resetTemplate(id) {
        var confirmed = window.confirm(
            'Reset this template to its default content? Your customizations will be lost.'
        );
        if (!confirmed) {
            return;
        }

        $.ajax({
            url: M.cfg.wwwroot + '/local/kwtsms/ajax/template_reset.php',
            method: 'POST',
            data: {
                id: id,
                sesskey: M.cfg.sesskey
            },
            dataType: 'json'
        }).done(function(response) {
            if (response.success) {
                // Reload the page to reflect the reset content.
                window.location.reload();
            } else {
                window.alert('Failed to reset template. Please try again.');
            }
        }).fail(function() {
            window.alert('Error resetting template. Please check your connection and try again.');
        });
    }

    return {
        /**
         * Initialize the templates tab event handlers.
         */
        init: function() {
            // Edit button: toggle edit row.
            $(document).on('click', '.kwtsms-edit-btn', function() {
                var id = $(this).data('id');
                showEditRow(id);
            });

            // Cancel button: hide edit row.
            $(document).on('click', '.kwtsms-cancel-btn', function() {
                var id = $(this).data('id');
                hideEditRow(id);
            });

            // Save button: AJAX save.
            $(document).on('click', '.kwtsms-save-btn', function() {
                var id = $(this).data('id');
                saveTemplate(id);
            });

            // Reset button: confirm and AJAX reset.
            $(document).on('click', '.kwtsms-reset-btn', function() {
                var id = $(this).data('id');
                resetTemplate(id);
            });

            // Live character/page counters on textareas.
            $(document).on('input', '.kwtsms-textarea-en', function() {
                var $editRow = $(this).closest('tr');
                updateCounter($(this), $editRow.find('.kwtsms-char-count-en'));
            });

            $(document).on('input', '.kwtsms-textarea-ar', function() {
                var $editRow = $(this).closest('tr');
                updateCounter($(this), $editRow.find('.kwtsms-char-count-ar'));
            });
        }
    };
});
