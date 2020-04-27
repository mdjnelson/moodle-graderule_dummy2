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
 * Restore support for graderule_dummy plugin
 *
 * @package    core_backup
 * @category   backup
 * @copyright  2020 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class restore_graderule_dummy_plugin extends restore_graderule_plugin {

    /**
     * Declares the dummy XML paths attached to the grade_rule element
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_grade_rule_plugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('graderule_dummy',
            $this->get_pathfor('/dummy'));

        return $paths;
    }

    /**
     * Declares the dummy XML patchs attached to the activity_grade_rule element
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_activity_grade_rule_plugin_structure() {
        return $this->define_grade_rule_plugin_structure();
    }

    /**
     * Processes graderule_dummy element data
     *
     * @param stdClass|array $data
     */
    public function process_graderule_dummy($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->gradeitem = $this->get_new_parentid('grade_item');

        $newid = $DB->insert_record('graderule_dummy', $data);

        if (!$graderuleid = $this->get_new_parentid('grade_rule')) {
            if (!$graderuleid = $this->get_new_parentid('activity_grade_rule')) {
                // TODO: Do we want to throw an exception here?
            }
        }

        $this->adjust_grading_rule_record($graderuleid, $newid);
    }
}
