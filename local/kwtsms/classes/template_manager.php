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
 * Template manager for SMS message templates.
 *
 * Handles template retrieval, placeholder replacement, and language selection
 * for SMS messages sent by the kwtSMS plugin.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Manages SMS templates, placeholder substitution, and bilingual message rendering.
 */
class template_manager {
    /**
     * Maps event types to their available placeholder names.
     * The '_common' key holds placeholders shared across all events.
     */
    private const PLACEHOLDERS = [
        '_common' => ['firstname', 'lastname', 'fullname', 'sitename', 'date', 'time'],
        'user_enrolment_created' => ['coursename', 'courseshortname'],
        'user_enrolment_deleted' => ['coursename', 'courseshortname'],
        'user_graded' => ['coursename', 'courseshortname', 'grade', 'gradeitem'],
        'course_completed' => ['coursename', 'courseshortname'],
        'attempt_submitted' => ['coursename', 'courseshortname'],
        'assessable_uploaded' => ['coursename', 'courseshortname'],
        'user_created' => [],
    ];

    /**
     * Retrieve a single template record by event type.
     *
     * @param string $eventtype The event type identifier.
     * @return object|null The template record, or null if not found.
     */
    public static function get_template(string $eventtype): ?object {
        global $DB;

        $record = $DB->get_record('local_kwtsms_templates', ['event_type' => $eventtype]);
        return $record ?: null;
    }

    /**
     * Retrieve all template records ordered by id ascending.
     *
     * @return array Array of template record objects.
     */
    public static function get_all_templates(): array {
        global $DB;

        return array_values($DB->get_records('local_kwtsms_templates', null, 'id ASC'));
    }

    /**
     * Update the editable message fields of a template.
     *
     * @param int    $id        The template record id.
     * @param string $messageen The English message body.
     * @param string $messagear The Arabic message body.
     * @return bool True on success, false on failure.
     */
    public static function update_template(int $id, string $messageen, string $messagear): bool {
        global $DB;

        $record = new \stdClass();
        $record->id = $id;
        $record->message_en = $messageen;
        $record->message_ar = $messagear;
        $record->timemodified = time();

        return $DB->update_record('local_kwtsms_templates', $record);
    }

    /**
     * Reset a template's messages back to the factory defaults.
     *
     * Copies default_en and default_ar into message_en and message_ar.
     *
     * @param int $id The template record id.
     * @return bool True on success, false on failure.
     */
    public static function reset_template(int $id): bool {
        global $DB;

        $template = $DB->get_record('local_kwtsms_templates', ['id' => $id]);
        if (!$template) {
            return false;
        }

        $record = new \stdClass();
        $record->id = $id;
        $record->message_en = $template->default_en;
        $record->message_ar = $template->default_ar;
        $record->timemodified = time();

        return $DB->update_record('local_kwtsms_templates', $record);
    }

    /**
     * Render a template for a given event, substituting placeholders and selecting language.
     *
     * @param string $eventtype    The event type identifier.
     * @param array  $placeholders Key-value pairs for placeholder substitution.
     * @param string $userlang     The user's preferred language code (e.g. 'ar', 'en_us').
     * @return array With keys 'message' (rendered string) and 'template_id' (int).
     *               Returns empty message and template_id 0 if no template is found.
     */
    public static function render_template(string $eventtype, array $placeholders, string $userlang = ''): array {
        $template = self::get_template($eventtype);
        if (!$template) {
            return ['message' => '', 'template_id' => 0];
        }

        $defaultlang = get_config('local_kwtsms', 'default_language');
        if (empty($defaultlang)) {
            $defaultlang = 'en';
        }

        $lang = self::pick_language($userlang, $defaultlang);
        $field = ($lang === 'ar') ? 'message_ar' : 'message_en';
        $message = $template->$field ?? '';

        $message = self::replace_placeholders($message, $placeholders);

        return [
            'message' => $message,
            'template_id' => (int) $template->id,
        ];
    }

    /**
     * Replace {key} tokens in a template string with provided values.
     *
     * Placeholders not present in the $placeholders array are left unchanged.
     *
     * @param string $template     The template string containing {key} tokens.
     * @param array  $placeholders Key-value pairs where key matches token name.
     * @return string The template with matched placeholders replaced.
     */
    public static function replace_placeholders(string $template, array $placeholders): string {
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Determine which language to use for a message.
     *
     * If the user's language starts with 'ar', returns 'ar'.
     * If it starts with 'en', returns 'en'.
     * Otherwise, falls back to the provided default (which itself defaults to 'en'
     * if it is neither 'ar' nor 'en').
     *
     * @param string $userlang    The user's language preference.
     * @param string $defaultlang The site/plugin default language.
     * @return string Either 'ar' or 'en'.
     */
    public static function pick_language(string $userlang, string $defaultlang): string {
        if ($userlang !== '' && strpos($userlang, 'ar') === 0) {
            return 'ar';
        }
        if ($userlang !== '' && strpos($userlang, 'en') === 0) {
            return 'en';
        }

        // Fall back to default language.
        if (strpos($defaultlang, 'ar') === 0) {
            return 'ar';
        }
        if (strpos($defaultlang, 'en') === 0) {
            return 'en';
        }

        // Ultimate fallback.
        return 'en';
    }

    /**
     * Get the list of available placeholder names for a given event type.
     *
     * Merges the common placeholders with any event-specific ones.
     *
     * @param string $eventtype The event type identifier.
     * @return array List of placeholder name strings.
     */
    public static function get_placeholders_for_event(string $eventtype): array {
        $common = self::PLACEHOLDERS['_common'];
        $specific = self::PLACEHOLDERS[$eventtype] ?? [];
        return array_merge($common, $specific);
    }
}
