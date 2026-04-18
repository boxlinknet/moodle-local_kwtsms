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
 * Unit tests for message_utils.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Tests for {@see message_utils}.
 *
 * @package    local_kwtsms
 * @covers     \local_kwtsms\message_utils
 */
final class message_utils_test extends \advanced_testcase {
    /**
     * Plain text passes through the clean method unchanged.
     */
    public function test_clean_normal_message(): void {
        $this->assertEquals(
            'Hello World',
            message_utils::clean('Hello World')
        );
    }

    /**
     * HTML tags are stripped, leaving only the text content.
     */
    public function test_clean_strips_html(): void {
        $this->assertEquals(
            'Hello John',
            message_utils::clean('<p>Hello <b>John</b></p>')
        );
    }

    /**
     * Emoji characters are removed from the message.
     */
    public function test_clean_strips_emojis(): void {
        $this->assertEquals(
            'Hello  World',
            message_utils::clean("Hello \xF0\x9F\x98\x80 World \xF0\x9F\x8E\x89")
        );
    }

    /**
     * Hidden Unicode control characters are stripped.
     */
    public function test_clean_strips_hidden_chars(): void {
        // Zero-width space (U+200B).
        $this->assertEquals('ab', message_utils::clean("a\xE2\x80\x8Bb"));
        // BOM (U+FEFF).
        $this->assertEquals('ab', message_utils::clean("a\xEF\xBB\xBFb"));
        // Soft hyphen (U+00AD).
        $this->assertEquals('ab', message_utils::clean("a\xC2\xADb"));
    }

    /**
     * Arabic-Indic digits are converted to Latin digits.
     */
    public function test_clean_converts_arabic_numerals(): void {
        $this->assertEquals(
            'Your code is 123456',
            message_utils::clean("Your code is \xD9\xA1\xD9\xA2\xD9\xA3\xD9\xA4\xD9\xA5\xD9\xA6")
        );
    }

    /**
     * English SMS page counting: 160 for first page, 153 per subsequent page.
     */
    public function test_page_count_english(): void {
        // Empty message = 0 pages.
        $this->assertEquals(0, message_utils::page_count(''));

        // Exactly 160 chars = 1 page.
        $this->assertEquals(1, message_utils::page_count(str_repeat('A', 160)));

        // 161 chars = 2 pages.
        $this->assertEquals(2, message_utils::page_count(str_repeat('A', 161)));

        // 306 chars = 2 pages (153 + 153 = 306).
        $this->assertEquals(2, message_utils::page_count(str_repeat('A', 306)));

        // 307 chars = 3 pages.
        $this->assertEquals(3, message_utils::page_count(str_repeat('A', 307)));
    }

    /**
     * Arabic SMS page counting: 70 for first page, 67 per subsequent page.
     */
    public function test_page_count_arabic(): void {
        // Build a string of Arabic characters.
        // U+0645 = م (meem), each is 2 bytes in UTF-8.
        $arabic1 = str_repeat("\xD9\x85", 70);
        $this->assertEquals(1, message_utils::page_count($arabic1));

        $arabic2 = str_repeat("\xD9\x85", 71);
        $this->assertEquals(2, message_utils::page_count($arabic2));
    }

    /**
     * Arabic character detection in mixed content.
     */
    public function test_is_arabic(): void {
        // Pure English.
        $this->assertFalse(message_utils::is_arabic('Hello World'));

        // Pure Arabic.
        $this->assertTrue(message_utils::is_arabic("\xD9\x85\xD8\xB1\xD8\xAD\xD8\xA8\xD8\xA7"));

        // Mixed content with at least one Arabic character.
        $this->assertTrue(message_utils::is_arabic("Hello \xD9\x85 World"));

        // Numbers only.
        $this->assertFalse(message_utils::is_arabic('12345'));
    }
}
