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
 * Message cleaning and SMS page counting utilities.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Utility class for cleaning SMS message text and calculating page counts.
 *
 * @package    local_kwtsms
 */
class message_utils {
    /**
     * Arabic-Indic and Extended Arabic-Indic digit mapping to Latin digits.
     */
    private const ARABIC_DIGITS = [
        "\xD9\xA0" => '0', // Arabic-Indic digit zero (U+0660).
        "\xD9\xA1" => '1', // Arabic-Indic digit one (U+0661).
        "\xD9\xA2" => '2', // Arabic-Indic digit two (U+0662).
        "\xD9\xA3" => '3', // Arabic-Indic digit three (U+0663).
        "\xD9\xA4" => '4', // Arabic-Indic digit four (U+0664).
        "\xD9\xA5" => '5', // Arabic-Indic digit five (U+0665).
        "\xD9\xA6" => '6', // Arabic-Indic digit six (U+0666).
        "\xD9\xA7" => '7', // Arabic-Indic digit seven (U+0667).
        "\xD9\xA8" => '8', // Arabic-Indic digit eight (U+0668).
        "\xD9\xA9" => '9', // Arabic-Indic digit nine (U+0669).
        "\xDB\xB0" => '0', // Extended Arabic-Indic digit zero (U+06F0).
        "\xDB\xB1" => '1', // Extended Arabic-Indic digit one (U+06F1).
        "\xDB\xB2" => '2', // Extended Arabic-Indic digit two (U+06F2).
        "\xDB\xB3" => '3', // Extended Arabic-Indic digit three (U+06F3).
        "\xDB\xB4" => '4', // Extended Arabic-Indic digit four (U+06F4).
        "\xDB\xB5" => '5', // Extended Arabic-Indic digit five (U+06F5).
        "\xDB\xB6" => '6', // Extended Arabic-Indic digit six (U+06F6).
        "\xDB\xB7" => '7', // Extended Arabic-Indic digit seven (U+06F7).
        "\xDB\xB8" => '8', // Extended Arabic-Indic digit eight (U+06F8).
        "\xDB\xB9" => '9', // Extended Arabic-Indic digit nine (U+06F9).
    ];

    /**
     * Hidden Unicode characters to strip from messages.
     *
     * U+200B  Zero-width space
     * U+200C  Zero-width non-joiner
     * U+200D  Zero-width joiner
     * U+200E  Left-to-right mark
     * U+200F  Right-to-left mark
     * U+FEFF  Byte order mark
     * U+00AD  Soft hyphen
     * U+2060  Word joiner
     * U+2061  Function application
     * U+2062  Invisible times
     * U+2063  Invisible separator
     * U+2064  Invisible plus
     */
    private const HIDDEN_CHARS = [
        "\xE2\x80\x8B" => '', // Zero-width space (U+200B).
        "\xE2\x80\x8C" => '', // Zero-width non-joiner (U+200C).
        "\xE2\x80\x8D" => '', // Zero-width joiner (U+200D).
        "\xE2\x80\x8E" => '', // Left-to-right mark (U+200E).
        "\xE2\x80\x8F" => '', // Right-to-left mark (U+200F).
        "\xEF\xBB\xBF" => '', // Byte order mark (U+FEFF).
        "\xC2\xAD"     => '', // Soft hyphen (U+00AD).
        "\xE2\x81\xA0" => '', // Word joiner (U+2060).
        "\xE2\x81\xA1" => '', // Function application (U+2061).
        "\xE2\x81\xA2" => '', // Invisible times (U+2062).
        "\xE2\x81\xA3" => '', // Invisible separator (U+2063).
        "\xE2\x81\xA4" => '', // Invisible plus (U+2064).
    ];

    /**
     * Clean a message for SMS delivery.
     *
     * Processing order:
     * 1. Strip HTML tags
     * 2. Strip emoji characters
     * 3. Strip hidden Unicode control characters
     * 4. Convert Arabic-Indic digits to Latin
     * 5. Trim whitespace
     *
     * @param string $message The raw message text.
     * @return string The cleaned message.
     */
    public static function clean(string $message): string {
        // 1. Strip HTML tags.
        $message = strip_tags($message);

        // 2. Strip emojis across all major emoji Unicode blocks.
        $message = preg_replace(
            '/['
            . '\x{1F600}-\x{1F64F}'  // Emoticons.
            . '\x{1F300}-\x{1F5FF}'  // Miscellaneous symbols and pictographs.
            . '\x{1F680}-\x{1F6FF}'  // Transport and map symbols.
            . '\x{1F1E0}-\x{1F1FF}'  // Regional indicator symbols (flags).
            . '\x{2600}-\x{26FF}'    // Miscellaneous symbols.
            . '\x{2700}-\x{27BF}'    // Dingbats.
            . '\x{FE00}-\x{FE0F}'    // Variation selectors.
            . '\x{1F900}-\x{1F9FF}'  // Supplemental symbols and pictographs.
            . '\x{200D}'             // Zero-width joiner (used in emoji sequences).
            . ']/u',
            '',
            $message
        ) ?? $message;

        // 3. Strip hidden Unicode control characters.
        $message = strtr($message, self::HIDDEN_CHARS);

        // 4. Convert Arabic-Indic digits to Latin.
        $message = strtr($message, self::ARABIC_DIGITS);

        // 5. Trim whitespace.
        return trim($message);
    }

    /**
     * Detect whether a string contains Arabic characters.
     *
     * Checks for characters in the Arabic Unicode block (U+0600 to U+06FF).
     *
     * @param string $message The text to check.
     * @return bool True if the message contains at least one Arabic character.
     */
    public static function is_arabic(string $message): bool {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $message);
    }

    /**
     * Calculate the number of SMS pages required for a message.
     *
     * SMS page limits:
     *   Arabic:  70 characters for a single page, 67 per page when multipart.
     *   English: 160 characters for a single page, 153 per page when multipart.
     *
     * @param string $message The message text (should be pre-cleaned).
     * @return int Number of SMS pages. Returns 0 for empty messages.
     */
    public static function page_count(string $message): int {
        $length = mb_strlen($message, 'UTF-8');

        if ($length === 0) {
            return 0;
        }

        if (self::is_arabic($message)) {
            $singlelimit = 70;
            $multilimit = 67;
        } else {
            $singlelimit = 160;
            $multilimit = 153;
        }

        if ($length <= $singlelimit) {
            return 1;
        }

        return (int) ceil($length / $multilimit);
    }
}
