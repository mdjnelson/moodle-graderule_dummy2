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
 * Tests for the backup and restore of dummy rows, which are included in a course backup.
 *
 * @package     graderule_dummy
 * @author      Peter Lock<peterlock@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace graderule_dummy;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/grade/grade_item.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');


/**
 * Test backup and restore
 *
 * We backup a course with a manual grade item and a dummy item,
 * and check that the restored course has the two items faithfully copied.
 */
class graderule_dummy_backup_test extends \advanced_testcase {

    /**
     * Test course
     */
    protected $course;


    public function test_backup_restore() {
        global $CFG, $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $params = [
            'itemname' => 'Grade Item',
            'iteminfo' => 'blah',
            'idnumber' => '1111',
            'gradetype' => '1',
            'grademax'  => '100.00',
            'grademin'  => '',
            'gradepass' => '',
            'display'   => '0',
            'decimals'  => '2',
            'courseid' => $course->id
        ];
        $gradeitem = new \grade_item($this->getDataGenerator()->create_grade_item($params), false);

        $params = [
            'itemname' => 'Dummy Item',
            'iteminfo' => 'blah',
            'idnumber' => '1111',
            'gradetype' => '1',
            'grademax'  => '100.00',
            'grademin'  => '',
            'gradepass' => '',
            'display'   => '0',
            'decimals'  => '2',
            'courseid' => $course->id
        ];
        $dummyitem = new \grade_item($this->getDataGenerator()->create_grade_item($params), false);

        // Make it into a dummy item.
        $dummy = new dummy(1, $dummyitem->id);
        $dummy->save($dummyitem);

        $fetcheddummyitem = \grade_item::fetch(['id' => $dummyitem->id]);
        $this->assertTrue(in_array('dummy', $fetcheddummyitem->rules));

        // Backup the course.
        $bc = new \backup_controller(\backup::TYPE_1COURSE, $course->id, \backup::FORMAT_MOODLE,
                \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, 2);
        $bc->execute_plan();
        $bc->destroy();

        // Get the backup file.
        $coursecontext = \context_course::instance($course->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($coursecontext->id, 'backup', 'course', false, 'id ASC');
        $backupfile = reset($files);

        // Extract backup file.
        $backupdir = "restore_" . uniqid();
        $path = $CFG->tempdir . DIRECTORY_SEPARATOR . "backup" . DIRECTORY_SEPARATOR . $backupdir;

        $fp = get_file_packer('application/vnd.moodle.backup');
        $fp->extract_to_pathname($backupfile, $path);

        // Create restore controller.
        $newcourseid = \restore_dbops::create_new_course(
                $course->fullname, $course->shortname . '_2', $course->category);
        $rc = new \restore_controller($backupdir, $newcourseid,
                \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, 2,
                \backup::TARGET_NEW_COURSE);
        if (!$rc->execute_precheck()) {
                $check = $rc->get_precheck_results();
        }
        $rc->execute_plan();
        $restoredcourse = $DB->get_record('course', array('id' => $rc->get_courseid()));

        $rc->destroy();

        $items = \grade_item::fetch_all(array('courseid' => $restoredcourse->id));
        $itemcount = 0;
        foreach ($items as $item) {
            $itemcount ++;
            if ($item->itemname == 'Dummy Item') {
                $this->compare($item, $dummyitem);
                $this->assertTrue(in_array('dummy', $item->rules));
            } else if ($item->itemname == 'Grade Item') {
                $this->compare($item, $gradeitem);
            } else if ($item->itemname !== null) {
                // This is a fail - we don't want to see this case.
                $this->assertEquals('Nothing', $item->itemname);
            }
        }

        // There should have been 3 items in this test.
        $this->assertEquals($itemcount, 3);
    }

    private function compare($item, $data) {
        $this->assertEquals($item->itemname, $data->itemname);
        $this->assertEquals($item->iteminfo, $data->iteminfo);
        $this->assertEquals($item->idnumber, $data->idnumber);
        $this->assertEquals($item->gradetype, $data->gradetype);
        $this->assertEquals($item->grademax, "100.00000");
        $this->assertEquals($item->grademin, "0.00000");
        $this->assertEquals($item->gradepass, "0.00000");
        $this->assertEquals($item->display, $data->display);
        $this->assertEquals($item->decimals, $data->decimals);
        $this->assertEquals($item->locked, 0);
        $this->assertEquals($item->weightoverride, $data->weightoverride);
        $this->assertEquals($item->itemtype, $data->itemtype);
    }

}
