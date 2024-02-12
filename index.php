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
 * UI for testing and starting the ad-hoc task.
 *
 * @package    tool_adhocbug
 * @copyright  2024 Mihaly Barasz <klao@nilcons.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/course/modlib.php');

$moduleid = optional_param('moduleid', 2, PARAM_INT);
$postmoduleid = optional_param('postmoduleid', null, PARAM_INT);
$urlParams = [];
if ($moduleid) {
    $urlParams['moduleid'] = $moduleid;
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/admin/tool/adhocbug/index.php', $urlParams);
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Ad-hoc task bug testing');
$PAGE->set_heading('Ad-hoc task bug testing');

echo $OUTPUT->header();

// Display task status:
echo $OUTPUT->heading('Task status');
$taskmoduleid = get_config('tool_adhocbug', 'task_moduleid');
$taskstatus = get_config('tool_adhocbug', 'task_status');
if (!$taskstatus) {
    $taskstatus = 'unknown';
}
$tasklastupdate = get_config('tool_adhocbug', 'task_last_update');
if ($tasklastupdate) {
    $tasklastupdate = userdate($tasklastupdate, '%A, %d %B %Y, %H:%M:%S');
} else {
    $tasklastupdate = get_string('never');
}

echo "<p>Task module id: $taskmoduleid<br>";
echo "Status: $taskstatus<br>";
echo "Last update: $tasklastupdate</p>";
echo '<hr>';
////////////////////////////////////////////////////////////////////////////////////////

// Display form:
echo $OUTPUT->heading('Run ad-hoc task');
echo '<form action="index.php" method="post">';
echo '<input type="hidden" name="postmoduleid" value="' . $moduleid . '">';
echo '<input type="submit" value="Run task">';
echo '</form>';

if ($postmoduleid) {
    set_config('task_moduleid', $postmoduleid, 'tool_adhocbug');
    set_config('task_status', 'scheduled', 'tool_adhocbug');
    set_config('task_last_update', time(), 'tool_adhocbug');

    $task = new \tool_adhocbug\task\task();
    \core\task\manager::queue_adhoc_task($task);

    redirect(new moodle_url('/admin/tool/adhocbug/index.php', ['moduleid' => $postmoduleid]));
}

echo '<hr>';
////////////////////////////////////////////////////////////////////////////////////////

$cm = get_coursemodule_from_id('quiz', $moduleid, 0, false, MUST_EXIST);
$course = get_course($cm->course);

// This works correctly:
$modinfo_data = get_moduleinfo_data($cm, $course);

echo '<pre>';
print_r($modinfo_data[4]);
echo '</pre>';

echo $OUTPUT->footer();
