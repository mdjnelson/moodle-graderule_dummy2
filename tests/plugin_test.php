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
 * Tests for the dummy grading rule plugin
 *
 * @package     graderule_dummy
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace graderule_dummy;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/grade/lib.php');

class graderule_dummy_plugin_testcase extends \advanced_testcase {

    private $course;
    private $context;
    private $generator;
    private $gradeitems;

    public function setUp(): void {

        parent::setUp();

        // Pre-test initialisation.
        $this->resetAfterTest();

        $this->generator = $this->getDataGenerator();
        $this->course = $this->generator->create_course();
        $this->context = \context_course::instance($this->course->id);

        // Create grade items.
        $this->gradeitems = [];

        for ($i = 1; $i <= 5; $i++) {

            $params = ['itemname' => "Item $i", 'idnumber' => "gi$i", 'courseid' => $this->course->id];
            $this->gradeitems[$i] = new \grade_item($this->generator->create_grade_item($params), false);
        }
    }

    public function test_save() {
        global $DB;

        // Check that one of the grade item is there.
        $this->assertTrue($DB->record_exists('grade_items', ['id' => $this->gradeitems[1]->id]));

        $dummy = new dummy(1, $this->gradeitems[1]->id);
        $dummy->save($this->gradeitems[1]);

        $this->assertTrue($DB->record_exists(
            'graderule_dummy', ['gradeitem' => $this->gradeitems[1]->id]
        ));
    }

    public function test_delete() {
        global $DB;

        // Check that another one of the grade items is there.
        $this->assertTrue($DB->record_exists('grade_items', ['id' => $this->gradeitems[2]->id]));

        // Make it into a dummy item.
        $dummy = new dummy(1, $this->gradeitems[2]->id);
        $dummy->save($this->gradeitems[2]);

        $this->assertTrue($DB->record_exists(
            'graderule_dummy', ['gradeitem' => $this->gradeitems[2]->id]
        ));

        // Delete it.
        $dummy->delete($this->gradeitems[2]);
        $this->assertFalse($DB->record_exists(
            'graderule_dummy', ['gradeitem' => $this->gradeitems[2]->id]
        ));
    }

    public function test_grade_item_grading_rules() {
        global $DB;

        // Use the third grade item.
        $this->assertTrue($DB->record_exists('grade_items', ['id' => $this->gradeitems[2]->id]));

        // Make it into a dummy item.
        $dummy = new dummy(1, $this->gradeitems[3]->id);
        $dummy->save($this->gradeitems[3]);

        $this->assertTrue($DB->record_exists(
            'graderule_dummy', ['gradeitem' => $this->gradeitems[3]->id]
        ));

        $fetched = \grade_item::fetch(['id' => $this->gradeitems[3]->id]);

        $this->assertEquals('dummy', $fetched->rules[0]);
    }

    public function test_grade_items_grading_rules() {
        global $DB;

        // Use the remaining grade items.
        $this->assertTrue($DB->record_exists('grade_items', ['id' => $this->gradeitems[4]->id]));
        $this->assertTrue($DB->record_exists('grade_items', ['id' => $this->gradeitems[5]->id]));

        // Make them into a dummy items.
        $dummy = new dummy(1, $this->gradeitems[4]->id);
        $dummy->save($this->gradeitems[4]);
        $dummy = new dummy(1, $this->gradeitems[5]->id);
        $dummy->save($this->gradeitems[5]);

        $fetched = \grade_item::fetch_all(['courseid' => $this->course->id, 'itemtype' => 'manual']);
        $this->assertEquals('dummy', $fetched[$this->gradeitems[4]->id]->rules[0]);
        $this->assertEquals('dummy', $fetched[$this->gradeitems[5]->id]->rules[0]);
    }
}
