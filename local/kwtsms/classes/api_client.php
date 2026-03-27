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
 * API client wrapper for kwtSMS gateway.
 *
 * Wraps the bundled kwtsms-php library with Moodle config integration
 * and cache management via the local_kwtsms_cache table.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms;

// @codingStandardsIgnoreStart
require_once(__DIR__ . '/../lib/kwtsms/KwtSMS.php');
require_once(__DIR__ . '/../lib/kwtsms/PhoneUtils.php');
require_once(__DIR__ . '/../lib/kwtsms/MessageUtils.php');
require_once(__DIR__ . '/../lib/kwtsms/ApiErrors.php');
// @codingStandardsIgnoreEnd

/**
 * Moodle-aware wrapper around the kwtsms-php library.
 *
 * Provides singleton client access, credential management via Moodle config,
 * and gateway data caching (balance, sender IDs, coverage) in the
 * local_kwtsms_cache table.
 */
class api_client {
    /** @var \KwtSMS\KwtSMS|null Cached client singleton. */
    private static $client = null;

    /**
     * Get or create the cached KwtSMS client instance.
     *
     * Reads api_username, api_password, sender_id, and test_mode from
     * plugin config. Returns null if credentials are not configured.
     *
     * @return \KwtSMS\KwtSMS|null
     */
    public static function get_client(): ?\KwtSMS\KwtSMS {
        if (self::$client !== null) {
            return self::$client;
        }

        if (!self::is_configured()) {
            return null;
        }

        $username = get_config('local_kwtsms', 'api_username');
        $password = get_config('local_kwtsms', 'api_password');
        $senderid = get_config('local_kwtsms', 'sender_id');
        $testmode = get_config('local_kwtsms', 'test_mode');

        self::$client = new \KwtSMS\KwtSMS(
            (string) $username,
            (string) $password,
            $senderid ? (string) $senderid : 'KWT-SMS',
            !empty($testmode),
            '' // Disable file logging; Moodle uses its own logging.
        );

        return self::$client;
    }

    /**
     * Reset the cached client instance.
     *
     * Forces a fresh client to be created on the next get_client() call.
     *
     * @return void
     */
    public static function reset_client(): void {
        self::$client = null;
    }

    /**
     * Check whether API credentials are configured.
     *
     * @return bool True if both api_username and api_password are set.
     */
    public static function is_configured(): bool {
        $username = get_config('local_kwtsms', 'api_username');
        $password = get_config('local_kwtsms', 'api_password');
        return !empty($username) && !empty($password);
    }

    /**
     * Check whether the SMS gateway is enabled.
     *
     * @return bool True if gateway_enabled config is truthy.
     */
    public static function is_enabled(): bool {
        return !empty(get_config('local_kwtsms', 'gateway_enabled'));
    }

    /**
     * Authenticate with the kwtSMS API and cache gateway data.
     *
     * Creates a temporary client with the given credentials, calls balance()
     * to verify them, and on success saves the credentials to Moodle config,
     * then fetches and caches balance, sender IDs, and coverage data.
     *
     * @param string $username API username.
     * @param string $password API password.
     * @return array{success: bool, balance: int|null, error: string|null}
     */
    public static function login(string $username, string $password): array {
        // Create a temporary client to verify credentials via the balance endpoint.
        $tempclient = new \KwtSMS\KwtSMS($username, $password, 'KWT-SMS', false, '');

        // The verify() method calls the balance endpoint to validate credentials.
        [$ok, $balance, $error] = $tempclient->verify();
        if (!$ok) {
            return [
                'success' => false,
                'balance' => null,
                'error' => $error,
            ];
        }

        // Credentials are valid. Save them.
        set_config('api_username', $username, 'local_kwtsms');
        set_config('api_password', $password, 'local_kwtsms');

        // Reset cached client so it picks up new credentials.
        self::reset_client();

        // Cache the balance from the verify call (avoids a redundant API call).
        $cachedata = [
            'balance' => $balance,
            'purchased' => $tempclient->purchased(),
            'timestamp' => time(),
        ];
        self::set_cache('balance', json_encode($cachedata));

        // Fetch and cache sender IDs and coverage (these need separate API calls).
        $client = self::get_client();
        if ($client !== null) {
            self::fetch_and_cache_senderids($client);
            self::fetch_and_cache_coverage($client);
        }

        return [
            'success' => true,
            'balance' => (int) $balance,
            'error' => null,
        ];
    }

    /**
     * Clear all credentials, config, and cached data.
     *
     * Removes api_username, api_password, sender_id, and default_country_code
     * from config, resets the client singleton, and deletes all cache records.
     *
     * @return void
     */
    public static function logout(): void {
        global $DB;

        // Clear credentials and related config.
        set_config('api_username', '', 'local_kwtsms');
        set_config('api_password', '', 'local_kwtsms');
        set_config('sender_id', '', 'local_kwtsms');
        set_config('default_country_code', '', 'local_kwtsms');

        // Reset the client singleton.
        self::reset_client();

        // Delete all cache records.
        $DB->delete_records('local_kwtsms_cache');
    }

    /**
     * Refresh all cached gateway data from the API.
     *
     * Fetches and caches balance, sender IDs, and coverage data.
     *
     * @return array{success: bool, balance: int|null, error: string|null}
     */
    public static function reload(): array {
        $client = self::get_client();
        if ($client === null) {
            return [
                'success' => false,
                'balance' => null,
                'error' => 'API client is not configured.',
            ];
        }

        // Fetch and cache balance (also verifies credentials).
        $balance = self::fetch_and_cache_balance($client);
        if ($balance === 0 && self::get_cache('balance') === null) {
            return [
                'success' => false,
                'balance' => null,
                'error' => 'Failed to verify credentials.',
            ];
        }

        // Fetch and cache sender IDs.
        self::fetch_and_cache_senderids($client);

        // Fetch and cache coverage.
        self::fetch_and_cache_coverage($client);

        return [
            'success' => true,
            'balance' => (int) $balance,
            'error' => null,
        ];
    }

    /**
     * Read a value from the local_kwtsms_cache table.
     *
     * @param string $key The cache key to look up.
     * @return string|null The cached value, or null if not found.
     */
    public static function get_cache(string $key): ?string {
        global $DB;

        $record = $DB->get_record('local_kwtsms_cache', ['cache_key' => $key]);
        if ($record) {
            return $record->cache_value;
        }

        return null;
    }

    /**
     * Insert or update a value in the local_kwtsms_cache table.
     *
     * Performs an upsert: updates the existing record if the key exists,
     * otherwise inserts a new one.
     *
     * @param string $key The cache key.
     * @param string $value The value to cache.
     * @return void
     */
    public static function set_cache(string $key, string $value): void {
        global $DB;

        $existing = $DB->get_record('local_kwtsms_cache', ['cache_key' => $key]);
        if ($existing) {
            $existing->cache_value = $value;
            $existing->timemodified = time();
            $DB->update_record('local_kwtsms_cache', $existing);
        } else {
            $record = new \stdClass();
            $record->cache_key = $key;
            $record->cache_value = $value;
            $record->timemodified = time();
            try {
                $DB->insert_record('local_kwtsms_cache', $record);
            } catch (\dml_write_exception $e) {
                // Race condition: another process inserted first. Update instead.
                $existing = $DB->get_record('local_kwtsms_cache', ['cache_key' => $key]);
                if ($existing) {
                    $existing->cache_value = $value;
                    $existing->timemodified = time();
                    $DB->update_record('local_kwtsms_cache', $existing);
                }
            }
        }
    }

    /**
     * Get the cached SMS balance.
     *
     * Parses the cached balance JSON and returns the available balance,
     * or 0 if no cached data exists.
     *
     * @return int The available balance, or 0.
     */
    public static function get_cached_balance(): int {
        $json = self::get_cache('balance');
        if ($json === null) {
            return 0;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return 0;
        }

        return (int) ($data['balance'] ?? 0);
    }

    /**
     * Get the cached sender ID list.
     *
     * Parses the cached senderids JSON and returns the array of sender IDs,
     * or an empty array if no cached data exists.
     *
     * @return array The list of sender IDs.
     */
    public static function get_cached_senderids(): array {
        $json = self::get_cache('senderids');
        if ($json === null) {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        return $data['senderids'] ?? [];
    }

    /**
     * Get the cached coverage prefix list.
     *
     * Parses the cached coverage JSON and extracts the list of supported
     * country code prefixes. The coverage API returns data keyed by country
     * codes under a top-level "result" key.
     *
     * @return array The list of supported country code prefixes.
     */
    public static function get_cached_coverage(): array {
        $json = self::get_cache('coverage');
        if ($json === null) {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        // The coverage response has 'result' => 'OK' plus country code keys.
        // Extract all keys that are numeric (country code prefixes).
        $prefixes = [];
        foreach ($data as $key => $value) {
            if ($key === 'result' || $key === 'code' || $key === 'description' || $key === 'action') {
                continue;
            }
            if (is_numeric($key)) {
                $prefixes[] = (string) $key;
            }
        }

        return $prefixes;
    }

    /**
     * Sanitize an API response for storage by removing credentials.
     *
     * Strips the username and password keys from the response array
     * before JSON encoding it.
     *
     * @param array $response The raw API response array.
     * @return string JSON-encoded response with credentials removed.
     */
    public static function sanitize_response(array $response): string {
        unset($response['username']);
        unset($response['password']);

        return json_encode($response);
    }

    /**
     * Fetch balance from the API and cache the response.
     *
     * @param \KwtSMS\KwtSMS $client The API client instance.
     * @return int The available balance.
     */
    private static function fetch_and_cache_balance(\KwtSMS\KwtSMS $client): int {
        [$ok, $balance, $error] = $client->verify();
        if ($ok) {
            $cachedata = [
                'balance' => $balance,
                'purchased' => $client->purchased(),
                'timestamp' => time(),
            ];
            self::set_cache('balance', json_encode($cachedata));
            return (int) $balance;
        }

        return 0;
    }

    /**
     * Fetch sender IDs from the API and cache the response.
     *
     * @param \KwtSMS\KwtSMS $client The API client instance.
     * @return void
     */
    private static function fetch_and_cache_senderids(\KwtSMS\KwtSMS $client): void {
        $response = $client->senderids();
        if (isset($response['result']) && $response['result'] === 'OK') {
            self::set_cache('senderids', self::sanitize_response($response));
        }
    }

    /**
     * Fetch coverage data from the API and cache the response.
     *
     * @param \KwtSMS\KwtSMS $client The API client instance.
     * @return void
     */
    private static function fetch_and_cache_coverage(\KwtSMS\KwtSMS $client): void {
        $response = $client->coverage();
        if (isset($response['result']) && $response['result'] === 'OK') {
            self::set_cache('coverage', self::sanitize_response($response));
        }
    }
}
