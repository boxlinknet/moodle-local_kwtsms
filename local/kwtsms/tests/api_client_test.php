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
 * Integration tests for the API client.
 *
 * Tests config-based methods and cache operations that do not require
 * network access. Real API calls are not possible in a unit test context.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

/**
 * Tests for the api_client wrapper class.
 *
 * @covers \local_kwtsms\api_client
 */
final class api_client_test extends \advanced_testcase {
    /**
     * Set up each test with a fresh Moodle state.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Test that is_configured returns false when no credentials are set.
     */
    public function test_is_configured_false_by_default(): void {
        $this->assertFalse(api_client::is_configured());
    }

    /**
     * Test that is_configured returns true when both username and password are set.
     */
    public function test_is_configured_true_with_credentials(): void {
        set_config('api_username', 'testuser', 'local_kwtsms');
        set_config('api_password', 'testpass', 'local_kwtsms');
        $this->assertTrue(api_client::is_configured());
    }

    /**
     * Test that is_enabled returns false when gateway_enabled is not set.
     */
    public function test_is_enabled_false_by_default(): void {
        $this->assertFalse(api_client::is_enabled());
    }

    /**
     * Test that is_enabled returns true when gateway_enabled is set.
     */
    public function test_is_enabled_true_when_set(): void {
        set_config('gateway_enabled', 1, 'local_kwtsms');
        $this->assertTrue(api_client::is_enabled());
    }

    /**
     * Test that get_client returns null when credentials are not configured.
     */
    public function test_get_client_returns_null_when_not_configured(): void {
        api_client::reset_client();
        $this->assertNull(api_client::get_client());
    }

    /**
     * Test that sanitize_response strips username and password keys.
     */
    public function test_sanitize_response_strips_credentials(): void {
        $response = [
            'result' => 'OK',
            'username' => 'secret',
            'password' => 'secret',
            'balance-after' => 100,
        ];
        $json = api_client::sanitize_response($response);
        $decoded = json_decode($json, true);
        $this->assertArrayNotHasKey('username', $decoded);
        $this->assertArrayNotHasKey('password', $decoded);
        $this->assertEquals('OK', $decoded['result']);
        $this->assertEquals(100, $decoded['balance-after']);
    }

    /**
     * Test set_cache and get_cache for insert, update, and missing key.
     */
    public function test_cache_operations(): void {
        // Test set and get cache.
        api_client::set_cache('test_key', '{"value": 42}');
        $result = api_client::get_cache('test_key');
        $this->assertEquals('{"value": 42}', $result);

        // Test update existing cache.
        api_client::set_cache('test_key', '{"value": 99}');
        $result = api_client::get_cache('test_key');
        $this->assertEquals('{"value": 99}', $result);

        // Test non-existent key.
        $this->assertNull(api_client::get_cache('nonexistent'));
    }

    /**
     * Test that get_cached_balance returns 0 when no cache exists.
     */
    public function test_get_cached_balance_defaults_to_zero(): void {
        $this->assertEquals(0, api_client::get_cached_balance());
    }

    /**
     * Test that get_cached_balance reads the balance from cached JSON.
     */
    public function test_get_cached_balance_from_cache(): void {
        api_client::set_cache('balance', json_encode(['balance' => 150, 'purchased' => 1000]));
        $this->assertEquals(150, api_client::get_cached_balance());
    }

    /**
     * Test that get_cached_senderids returns empty array when no cache exists.
     */
    public function test_get_cached_senderids_defaults_to_empty(): void {
        $this->assertEquals([], api_client::get_cached_senderids());
    }

    /**
     * Test that logout clears credentials, config, and cache.
     */
    public function test_logout_clears_everything(): void {
        set_config('api_username', 'testuser', 'local_kwtsms');
        set_config('api_password', 'testpass', 'local_kwtsms');
        set_config('sender_id', 'TEST', 'local_kwtsms');
        api_client::set_cache('balance', '{"balance": 100}');

        api_client::logout();

        $this->assertEmpty(get_config('local_kwtsms', 'api_username'));
        $this->assertEmpty(get_config('local_kwtsms', 'api_password'));
        $this->assertEmpty(get_config('local_kwtsms', 'sender_id'));
        $this->assertFalse(api_client::is_configured());
        $this->assertEquals(0, api_client::get_cached_balance());
    }
}
