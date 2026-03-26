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
 * Unit tests for template_manager.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the template_manager class.
 *
 * @covers \local_kwtsms\template_manager
 */
class template_manager_test extends \advanced_testcase {

    /**
     * Test that replace_placeholders substitutes all matching keys.
     */
    public function test_replace_placeholders(): void {
        $template = 'Hello {firstname}, welcome to {coursename}.';
        $placeholders = [
            'firstname' => 'Ahmed',
            'coursename' => 'Math 101',
        ];

        $result = template_manager::replace_placeholders($template, $placeholders);

        $this->assertEquals('Hello Ahmed, welcome to Math 101.', $result);
    }

    /**
     * Test that replace_placeholders leaves missing placeholders as-is.
     */
    public function test_replace_placeholders_missing(): void {
        $template = 'Hello {firstname}, your grade is {grade}.';
        $placeholders = [
            'firstname' => 'Ahmed',
        ];

        $result = template_manager::replace_placeholders($template, $placeholders);

        $this->assertEquals('Hello Ahmed, your grade is {grade}.', $result);
    }

    /**
     * Test that pick_language returns 'ar' when user lang starts with 'ar'.
     */
    public function test_pick_language_user_arabic(): void {
        $result = template_manager::pick_language('ar', 'en');
        $this->assertEquals('ar', $result);

        // Also works with locale variants like 'ar_sa'.
        $result = template_manager::pick_language('ar_sa', 'en');
        $this->assertEquals('ar', $result);
    }

    /**
     * Test that pick_language returns 'en' when user lang starts with 'en'.
     */
    public function test_pick_language_user_english(): void {
        $result = template_manager::pick_language('en', 'ar');
        $this->assertEquals('en', $result);

        // Also works with locale variants like 'en_us'.
        $result = template_manager::pick_language('en_us', 'ar');
        $this->assertEquals('en', $result);
    }

    /**
     * Test that pick_language falls back to default when user lang is empty.
     */
    public function test_pick_language_fallback(): void {
        $result = template_manager::pick_language('', 'ar');
        $this->assertEquals('ar', $result);

        $result = template_manager::pick_language('', 'en');
        $this->assertEquals('en', $result);
    }

    /**
     * Test that pick_language falls back to default for unsupported languages.
     */
    public function test_pick_language_other_lang_falls_back(): void {
        $result = template_manager::pick_language('fr', 'en');
        $this->assertEquals('en', $result);

        $result = template_manager::pick_language('fr', 'ar');
        $this->assertEquals('ar', $result);

        // Default itself falls back to 'en' if it is neither 'ar' nor 'en'.
        $result = template_manager::pick_language('fr', 'de');
        $this->assertEquals('en', $result);
    }

    /**
     * Test that get_placeholders_for_event returns common + event-specific placeholders.
     */
    public function test_get_placeholders_for_event(): void {
        // Enrollment event should include common placeholders plus course-specific ones.
        $enrolment = template_manager::get_placeholders_for_event('user_enrolment_created');
        $this->assertContains('firstname', $enrolment);
        $this->assertContains('coursename', $enrolment);
        $this->assertContains('sitename', $enrolment);

        // Graded event should include grade-specific placeholders.
        $graded = template_manager::get_placeholders_for_event('user_graded');
        $this->assertContains('grade', $graded);
        $this->assertContains('gradeitem', $graded);
        $this->assertContains('firstname', $graded);
        $this->assertContains('coursename', $graded);
    }
}
