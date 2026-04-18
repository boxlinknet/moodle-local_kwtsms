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
 * External service: verify kwtSMS API credentials and save them.
 *
 * @package    local_kwtsms
 * @copyright  2026 kwtSMS <support@kwtsms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kwtsms\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Verify kwtSMS API credentials and persist them on success.
 */
class gateway_login extends external_api {
    /**
     * Describe the input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'username' => new external_value(PARAM_TEXT, 'kwtSMS API username'),
            'password' => new external_value(PARAM_RAW, 'kwtSMS API password'),
        ]);
    }

    /**
     * Verify credentials and save them if valid.
     *
     * @param string $username API username.
     * @param string $password API password.
     * @return array Login result with success flag, balance, and error message.
     */
    public static function execute(string $username, string $password): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'username' => $username,
            'password' => $password,
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/kwtsms:manage', $context);

        $result = \local_kwtsms\api_client::login($params['username'], $params['password']);

        return [
            'success' => (bool) $result['success'],
            'balance' => isset($result['balance']) ? (int) $result['balance'] : 0,
            'error'   => $result['error'] ?? '',
        ];
    }

    /**
     * Describe the return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the login succeeded'),
            'balance' => new external_value(PARAM_INT, 'Account balance after successful login', VALUE_DEFAULT, 0),
            'error'   => new external_value(PARAM_TEXT, 'Error message on failure', VALUE_DEFAULT, ''),
        ]);
    }
}
