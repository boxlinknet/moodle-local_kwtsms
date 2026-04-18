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
 * Unit tests for phone_utils.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Tests for the phone_utils utility class.
 *
 * @covers \local_kwtsms\phone_utils
 */
final class phone_utils_test extends \advanced_testcase {
    /**
     * Test that normalize strips +, 00, spaces, dashes, and parentheses.
     */
    public function test_normalize_international(): void {
        // Plus prefix stripped.
        $this->assertEquals('96598765432', phone_utils::normalize('+96598765432'));

        // Double-zero international prefix stripped.
        $this->assertEquals('96598765432', phone_utils::normalize('0096598765432'));

        // Spaces stripped.
        $this->assertEquals('96598765432', phone_utils::normalize('965 9876 5432'));

        // Dashes stripped.
        $this->assertEquals('96598765432', phone_utils::normalize('965-9876-5432'));

        // Parentheses stripped.
        $this->assertEquals('96598765432', phone_utils::normalize('(965) 98765432'));

        // Combination of all.
        $this->assertEquals('96598765432', phone_utils::normalize('+965 (9876) 5432'));
    }

    /**
     * Test that normalize prepends country code when phone starts with 0.
     */
    public function test_normalize_local_with_country_code(): void {
        // Kuwait local number with leading 0.
        $this->assertEquals('96598765432', phone_utils::normalize('098765432', '965'));

        // Saudi local number with leading 0.
        $this->assertEquals('9665698741236', phone_utils::normalize('05698741236', '966'));
    }

    /**
     * Test that normalize leaves the number unchanged when no country code is given.
     */
    public function test_normalize_local_without_country_code(): void {
        $this->assertEquals('098765432', phone_utils::normalize('098765432'));
    }

    /**
     * Test that Arabic-Indic and Extended Arabic-Indic digits are converted to Latin.
     */
    public function test_normalize_arabic_digits(): void {
        // Arabic-Indic digits (U+0660..U+0669).
        $this->assertEquals('96598765432', phone_utils::normalize('٩٦٥٩٨٧٦٥٤٣٢'));

        // Extended Arabic-Indic digits (U+06F0..U+06F9).
        $this->assertEquals('96598765432', phone_utils::normalize('۹۶۵۹۸۷۶۵۴۳۲'));

        // Mixed Arabic-Indic and Latin.
        $this->assertEquals('96598765432', phone_utils::normalize('٩٦٥98765432'));
    }

    /**
     * Test validate accepts valid numbers and rejects invalid ones.
     */
    public function test_validate(): void {
        // Valid: 8+ digits.
        $this->assertTrue(phone_utils::validate('96598765432'));
        $this->assertTrue(phone_utils::validate('12345678'));

        // Invalid: empty string.
        $this->assertFalse(phone_utils::validate(''));

        // Invalid: too short (less than 8 digits).
        $this->assertFalse(phone_utils::validate('1234567'));
        $this->assertFalse(phone_utils::validate('123'));

        // Invalid: contains non-digit characters.
        $this->assertFalse(phone_utils::validate('9659876abc'));
        $this->assertFalse(phone_utils::validate('+96598765432'));
    }

    /**
     * Test get_user_phone prefers phone2, falls back to phone1, returns empty when both empty.
     */
    public function test_get_user_phone(): void {
        // Prefers phone2 when both are set.
        $user = (object) ['phone1' => '11111111', 'phone2' => '96598765432'];
        $this->assertEquals('96598765432', phone_utils::get_user_phone($user));

        // Falls back to phone1 when phone2 is empty.
        $user = (object) ['phone1' => '11111111', 'phone2' => ''];
        $this->assertEquals('11111111', phone_utils::get_user_phone($user));

        // Returns empty when both are empty.
        $user = (object) ['phone1' => '', 'phone2' => ''];
        $this->assertEquals('', phone_utils::get_user_phone($user));

        // Handles missing properties gracefully.
        $user = (object) [];
        $this->assertEquals('', phone_utils::get_user_phone($user));
    }

    /**
     * Test deduplicate removes duplicate phone numbers while preserving order.
     */
    public function test_deduplicate(): void {
        $input = ['96598765432', '96512345678', '96598765432', '96512345678', '96511112222'];
        $expected = ['96598765432', '96512345678', '96511112222'];
        $this->assertEquals($expected, phone_utils::deduplicate($input));

        // Empty array stays empty.
        $this->assertEquals([], phone_utils::deduplicate([]));

        // Single element stays unchanged.
        $this->assertEquals(['96598765432'], phone_utils::deduplicate(['96598765432']));
    }

    /**
     * Test filter_by_coverage splits phones into covered and uncovered arrays.
     */
    public function test_filter_by_coverage(): void {
        $phones = ['96598765432', '97312345678', '4412345678901', '96512345678'];
        $coverage = ['965', '973'];

        $result = phone_utils::filter_by_coverage($phones, $coverage);

        $this->assertArrayHasKey('covered', $result);
        $this->assertArrayHasKey('uncovered', $result);
        $this->assertEquals(['96598765432', '97312345678', '96512345678'], $result['covered']);
        $this->assertEquals(['4412345678901'], $result['uncovered']);

        // Empty coverage means all uncovered.
        $result = phone_utils::filter_by_coverage($phones, []);
        $this->assertEquals([], $result['covered']);
        $this->assertEquals($phones, $result['uncovered']);

        // Empty phones means empty result.
        $result = phone_utils::filter_by_coverage([], $coverage);
        $this->assertEquals([], $result['covered']);
        $this->assertEquals([], $result['uncovered']);
    }
}
