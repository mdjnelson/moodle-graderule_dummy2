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
 * Data access object class for the threshold
 *
 * @package     graderule_dummy
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace graderule_dummy;

defined('MOODLE_INTERNAL') || die();

/**
 * Data access object class for the threshold
 *
 * @package     graderule_dummy
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dao {

    public static function get_instance($dummyitemid) {
        global $DB;

        $record = $DB->get_record(
            'graderule_dummy',
            ['gradeitem' => $dummyitemid]
        );

        if ($record != false) {

            return new dummy(true, $dummyitemid, $record->id);
        }

        return null;
    }

    public static function get_instance_from_id($id) {
        global $DB;

        $record = $DB->get_record('graderule_dummy', ['id' => $id]);

        if ($record != false) {

            return new dummy(true, $record->gradeitem, $id);
        }

        return null;
    }

    public static function delete_instance($instanceid) {
        global $DB;
        $DB->delete_records('graderule_dummy', ['id' => $instanceid]);
    }

    /**
     * @param \stdClass $dummy
     * @return int
     * @throws \moodle_exception
     */
    public static function save_instance($dummy) {
        global $DB;

        if ($dummy->id != -1) {

            $success = $DB->update_record('graderule_dummy', $dummy);

            if ($success) {

                return $dummy->id;
            } else {

                throw new \moodle_exception(get_string('graderule_errorsave', 'core_grades'));
            }
        } else {

            unset($dummy->id);
            return $DB->insert_record('graderule_dummy', $dummy);
        }
    }
}
