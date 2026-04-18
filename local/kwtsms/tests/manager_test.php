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
 * Integration tests for the SMS manager.
 *
 * Tests the send orchestrator's skip logic and logging behaviour.
 * No real API calls are made because gateway checks fail before the
 * API is reached.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Tests for the manager send orchestrator.
 *
 * @covers \local_kwtsms\manager
 */
final class manager_test extends \advanced_testcase {
    /**
     * Set up each test with a fresh Moodle state.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Test that send logs a skip when the gateway is disabled.
     */
    public function test_send_logs_skip_when_gateway_disabled(): void {
        // Gateway is disabled by default.
        manager::send(['96598765432'], 'Test message', 'test_event');

        global $DB;
        $logs = $DB->get_records('local_kwtsms_log');
        $this->assertCount(1, $logs);
        $log = reset($logs);
        $this->assertEquals('skipped', $log->status);
        $this->assertEquals('gateway_disabled', $log->skip_reason);
    }

    /**
     * Test that send logs a skip when credentials are not configured.
     */
    public function test_send_logs_skip_when_not_configured(): void {
        set_config('gateway_enabled', 1, 'local_kwtsms');
        // No credentials configured.
        manager::send(['96598765432'], 'Test message', 'test_event');

        global $DB;
        $logs = $DB->get_records('local_kwtsms_log');
        $this->assertCount(1, $logs);
        $log = reset($logs);
        $this->assertEquals('skipped', $log->status);
        $this->assertEquals('gateway_not_configured', $log->skip_reason);
    }

    /**
     * Test that send logs a skip when cached balance is zero.
     */
    public function test_send_logs_skip_when_zero_balance(): void {
        set_config('gateway_enabled', 1, 'local_kwtsms');
        set_config('api_username', 'testuser', 'local_kwtsms');
        set_config('api_password', 'testpass', 'local_kwtsms');
        // Balance is 0 by default (no cache).
        manager::send(['96598765432'], 'Test message', 'test_event');

        global $DB;
        $logs = $DB->get_records('local_kwtsms_log');
        $this->assertCount(1, $logs);
        $log = reset($logs);
        $this->assertEquals('skipped', $log->status);
        $this->assertEquals('zero_balance', $log->skip_reason);
    }

    /**
     * Test that send_notification returns silently when event is disabled.
     */
    public function test_send_notification_skips_when_event_disabled(): void {
        $user = (object) [
            'id' => 1,
            'firstname' => 'Test',
            'lastname' => 'User',
            'phone1' => '',
            'phone2' => '96598765432',
            'lang' => 'en',
        ];
        // Event not enabled, should return silently without logging.
        manager::send_notification('user_enrolment_created', $user, ['coursename' => 'Test Course']);

        global $DB;
        $count = $DB->count_records('local_kwtsms_log');
        $this->assertEquals(0, $count);
    }

    /**
     * Test that send_notification logs a skip when user has no phone number.
     */
    public function test_send_notification_logs_skip_no_phone(): void {
        set_config('event_user_enrolment_created', 1, 'local_kwtsms');
        $user = (object) [
            'id' => 1,
            'firstname' => 'Test',
            'lastname' => 'User',
            'phone1' => '',
            'phone2' => '',
            'lang' => 'en',
        ];

        manager::send_notification('user_enrolment_created', $user, ['coursename' => 'Test Course']);

        global $DB;
        $logs = $DB->get_records('local_kwtsms_log');
        $this->assertCount(1, $logs);
        $log = reset($logs);
        $this->assertEquals('skipped', $log->status);
        $this->assertEquals('no_phone_number', $log->skip_reason);
    }

    /**
     * Test that send deduplicates phone numbers before processing.
     */
    public function test_send_deduplicates_phones(): void {
        set_config('gateway_enabled', 1, 'local_kwtsms');
        set_config('api_username', 'testuser', 'local_kwtsms');
        set_config('api_password', 'testpass', 'local_kwtsms');
        // Balance 0, so will be skipped after dedup, but should only log unique phones.
        manager::send(['96598765432', '96598765432', '96598765432'], 'Test', 'test_event');

        global $DB;
        $logs = $DB->get_records('local_kwtsms_log');
        // After dedup: 1 unique phone, skipped for zero_balance.
        $this->assertCount(1, $logs);
    }
}
