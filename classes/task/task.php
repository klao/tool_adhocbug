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
 * Adhoc task
 *
 * @package    tool_adhocbug
 * @copyright  2024 Mihaly Barasz <klao@nilcons.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_adhocbug\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/modlib.php');

function set_status($status)
{
    set_config('task_last_update', time(), 'tool_adhocbug');
    set_config('task_status', $status, 'tool_adhocbug');
    echo "Status: $status\n";
}

class task extends \core\task\adhoc_task
{
    public function execute()
    {
        set_status('running');

        try {
            global $DB;
            $cm = $DB->get_record_sql('SELECT * FROM {course_modules} LIMIT 1');
            $course = get_course($cm->course);

            // This fails with `Class "grade_item" not found` if run from `admin/cli/adhoc_task.php --execute --keep-alive=59`,
            // but works if started by the regular cron job.
            $modinfo_data = get_moduleinfo_data($cm, $course);

            echo '<pre>';
            print_r($cm);
            print_r($course);
            print_r($modinfo_data);
            echo '</pre>';
        } catch (\Exception $e) {
            // When run from `admin/cli/adhoc_task.php --execute --keep-alive=59`, the failure is a fatal error,
            // so there is no exception and no way to recover.
            set_status($e->getMessage());
            return;
        }

        echo "\n\nYAAAAAAYYYYYY! The ad-hoc task ran successfully!\n\n";

        set_status('complete');
    }
}
