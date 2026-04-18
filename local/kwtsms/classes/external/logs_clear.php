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
 * External service: delete SMS log entries within an optional date range.
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
 * Delete SMS log entries, optionally restricted to a date range.
 */
class logs_clear extends external_api {
    /**
     * Describe the input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'datefrom' => new external_value(PARAM_INT, 'Lower bound Unix timestamp, 0 for none', VALUE_DEFAULT, 0),
            'dateto'   => new external_value(PARAM_INT, 'Upper bound Unix timestamp, 0 for none', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Delete matching log entries.
     *
     * @param int $datefrom Lower bound Unix timestamp, 0 for none.
     * @param int $dateto Upper bound Unix timestamp, 0 for none.
     * @return array Success flag.
     */
    public static function execute(int $datefrom = 0, int $dateto = 0): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'datefrom' => $datefrom,
            'dateto'   => $dateto,
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/kwtsms:manage', $context);

        $conditions = [];
        $whereparams = [];
        if ($params['datefrom'] > 0) {
            $conditions[] = 'timecreated >= :fromts';
            $whereparams['fromts'] = $params['datefrom'];
        }
        if ($params['dateto'] > 0) {
            $conditions[] = 'timecreated <= :tots';
            $whereparams['tots'] = $params['dateto'];
        }
        $where = !empty($conditions) ? implode(' AND ', $conditions) : '1=1';
        $DB->delete_records_select('local_kwtsms_log', $where, $whereparams);

        return ['success' => true];
    }

    /**
     * Describe the return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the clear succeeded'),
        ]);
    }
}
