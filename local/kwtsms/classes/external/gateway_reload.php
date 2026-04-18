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
 * External service: refresh cached kwtSMS gateway data from the API.
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
 * Refresh balance, sender IDs, and coverage from the kwtSMS API.
 */
class gateway_reload extends external_api {
    /**
     * Describe the input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Refresh gateway data.
     *
     * @return array Reload result with success flag, balance, and error message.
     */
    public static function execute(): array {
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/kwtsms:manage', $context);

        $result = \local_kwtsms\api_client::reload();

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
            'success' => new external_value(PARAM_BOOL, 'Whether the reload succeeded'),
            'balance' => new external_value(PARAM_INT, 'Account balance after reload', VALUE_DEFAULT, 0),
            'error'   => new external_value(PARAM_TEXT, 'Error message on failure', VALUE_DEFAULT, ''),
        ]);
    }
}
