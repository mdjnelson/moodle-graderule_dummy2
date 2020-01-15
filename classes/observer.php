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
 * Events observer class.
 *
 * @package     graderule_dummy
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace graderule_dummy;

use core\event\course_restored;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * Listen for the course_restored event and then process the rules.
     *
     * @param course_restored $event
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function course_restored(course_restored $event) {
        global $DB;

        $sql = "SELECT gr.*
            FROM {grading_rules} gr
            JOIN {grade_items} gi ON gi.id = gr.gradeitem
            WHERE gi.courseid = :courseid
            AND gr.plugin = 'dummy'
            AND gr.pluginid = -1";
        $params = ['courseid' => $event->courseid];
        $dummies = $DB->get_records_sql($sql, $params);

        if (!empty($dummies)) {

            foreach ($dummies as $dummy) {

                $gradeitem = \grade_item::fetch(['id' => $dummy->gradeitem]);
                $rule = new dummy(1, $dummy->gradeitem);
                $rule->save($gradeitem);

                $DB->delete_records(
                    'grading_rules',
                    ['id' => $dummy->id, 'gradeitem' => $dummy->gradeitem, 'pluginid' => -1]
                );
            }
        }
    }
}
