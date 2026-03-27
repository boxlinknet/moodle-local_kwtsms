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
 * Phone number normalization and validation utilities.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Utility class for phone number normalization, validation, and filtering.
 */
class phone_utils {
    /**
     * Map of Arabic-Indic and Extended Arabic-Indic digits to Latin digits.
     */
    private const ARABIC_DIGIT_MAP = [
        // Arabic-Indic (U+0660..U+0669).
        "\xD9\xA0" => '0', "\xD9\xA1" => '1', "\xD9\xA2" => '2', "\xD9\xA3" => '3',
        "\xD9\xA4" => '4', "\xD9\xA5" => '5', "\xD9\xA6" => '6', "\xD9\xA7" => '7',
        "\xD9\xA8" => '8', "\xD9\xA9" => '9',
        // Extended Arabic-Indic (U+06F0..U+06F9).
        "\xDB\xB0" => '0', "\xDB\xB1" => '1', "\xDB\xB2" => '2', "\xDB\xB3" => '3',
        "\xDB\xB4" => '4', "\xDB\xB5" => '5', "\xDB\xB6" => '6', "\xDB\xB7" => '7',
        "\xDB\xB8" => '8', "\xDB\xB9" => '9',
    ];

    /**
     * Normalize a phone number for storage and comparison.
     *
     * Steps:
     * 1. Convert Arabic-Indic and Extended Arabic-Indic digits to Latin.
     * 2. Strip all non-digit characters (spaces, dashes, parentheses, plus sign).
     * 3. Strip leading "00" international prefix.
     * 4. If a country code is provided and the number starts with "0", strip the
     *    leading zero and prepend the country code.
     *
     * @param string $phone The raw phone number input.
     * @param string $countrycode Optional country code to prepend for local numbers.
     * @return string The normalized phone number (digits only).
     */
    public static function normalize(string $phone, string $countrycode = ''): string {
        // Step 1: convert Arabic-Indic digits to Latin.
        $phone = strtr($phone, self::ARABIC_DIGIT_MAP);

        // Step 2: strip all non-digit characters.
        $phone = preg_replace('/\D/', '', $phone);

        // Step 3: strip leading "00" international prefix.
        if (strpos($phone, '00') === 0) {
            $phone = substr($phone, 2);
        }

        // Step 4: if country code given and number starts with "0", replace leading 0 with country code.
        if ($countrycode !== '' && isset($phone[0]) && $phone[0] === '0') {
            $phone = $countrycode . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Validate that a phone number contains only digits and is at least 8 characters long.
     *
     * This is a basic structural check. It does not verify country-specific rules.
     *
     * @param string $phone The phone number to validate (should be already normalized).
     * @return bool True if valid, false otherwise.
     */
    public static function validate(string $phone): bool {
        if ($phone === '') {
            return false;
        }

        if (!ctype_digit($phone)) {
            return false;
        }

        if (strlen($phone) < 8) {
            return false;
        }

        return true;
    }

    /**
     * Get the preferred phone number from a Moodle user object.
     *
     * Prefers phone2 (mobile) over phone1 (home/office). Returns an empty string
     * if neither field contains a value.
     *
     * @param object $user A Moodle user object (or stdClass with phone1/phone2 properties).
     * @return string The phone number, or empty string if none available.
     */
    public static function get_user_phone(object $user): string {
        $phone2 = trim($user->phone2 ?? '');
        if ($phone2 !== '') {
            return $phone2;
        }

        $phone1 = trim($user->phone1 ?? '');
        if ($phone1 !== '') {
            return $phone1;
        }

        return '';
    }

    /**
     * Remove duplicate phone numbers from an array, preserving order.
     *
     * @param array $phones List of phone number strings.
     * @return array De-duplicated list with re-indexed keys.
     */
    public static function deduplicate(array $phones): array {
        return array_values(array_unique($phones));
    }

    /**
     * Split phone numbers into covered and uncovered groups based on prefix matching.
     *
     * A phone number is "covered" if it starts with any of the given coverage prefixes
     * (typically country codes).
     *
     * @param array $phones List of phone number strings.
     * @param array $coverage List of prefix strings to match against.
     * @return array Associative array with 'covered' and 'uncovered' sub-arrays.
     */
    public static function filter_by_coverage(array $phones, array $coverage): array {
        $covered = [];
        $uncovered = [];

        foreach ($phones as $phone) {
            $matched = false;
            foreach ($coverage as $prefix) {
                if (strpos($phone, $prefix) === 0) {
                    $matched = true;
                    break;
                }
            }
            if ($matched) {
                $covered[] = $phone;
            } else {
                $uncovered[] = $phone;
            }
        }

        return [
            'covered' => $covered,
            'uncovered' => $uncovered,
        ];
    }
}
