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
 * Grade rule for 'Dummy Item' status
 *
 * @package   graderule_dummy
 * @author    Marcus Boon <marcus@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace graderule_dummy;

defined('MOODLE_INTERNAL') || die('');

use core\grade\rule;
use core\grade\status;

class dummy implements \core\grade\rule\rule_interface {

    /** @var integer */
    private $dummyitemid;

    /** @var integer */
    private $instanceid;

    private $needsupdate;

    /** @var boolean */
    private $enabled = false;


    public function __construct($enabled, $dummyitemid, $instanceid = -1) {
        $this->enabled     = $enabled;
        $this->dummyitemid = $dummyitemid;
        $this->instanceid  = $instanceid;
        $this->needsupdate = false;
    }

    /**
     * Returns whether or not a grade item is an dummy grade item
     *
     * @return bool
     */
    public function enabled() {

        return $this->enabled;
    }

    /**
     * We do not need to modify the final grade so just return the current value.
     *
     * @param \grade_item  $item
     * @param int          $userid
     * @param float        $currentvalue
     *
     * @return float
     */
    public function final_grade_modifier(&$item, $userid, $currentvalue) {

        return $currentvalue;
    }

    /**
     * We do not have to modify the symbol either so just return current symbol.
     *
     * @param \grade_item  $item
     * @param float        $value
     * @param int          $userid
     * @param string       $currentsymbol
     *
     * @return string
     */
    public function symbol_modifier(&$item, $value, $userid, $currentsymbol) {

        return $currentsymbol;
    }

    /**
     * There are no status messages for dummy status.
     *
     * @param \grade_item $item
     * @param int         $userid
     *
     * @return status
     */
    public function get_status_message(&$item, $userid) {

        return null;
    }

    /**
     * Inject settings into the edit grade item form.
     *
     * @param \moodle_form $mform
     *
     * @return void
     */
    public function edit_form_hook(&$mform) {

        $element = $mform->createElement(
            'advcheckbox',
            'dummy_enabled',
            get_string('enabled', 'graderule_dummy'),
            '',
            [],
            [0, 1]
        );

        // Only enable dummy for grade items that have a passing grade.
        if ($mform->elementExists('gradepass')) {

            $mform->insertElementBefore($element, 'gradepass');
        } else if ($mform->elementExists('grade_item_gradepass')) {

            $mform->insertElementBefore($element, 'grade_item_gradepass');
        }

        $mform->setDefault('dummy_enabled', $this->enabled ? 1 : 0);
    }

    /**
     * Process the form.
     *
     * @param \stdClass $data
     */
    public function process_form(&$data) {

        if (property_exists($data, 'dummy_enabled')
            && $data->dummy_enabled == 1) {

            $this->enabled = true;
        } else {

            $this->enabled = false;
        }
    }

    /**
     * We do not have to bother with recursing for dummy.
     *
     * @param \grade_item $grade_item
     *
     * @return void
     */
    public function recurse(&$gradeitem) {

        return null;
    }

    /**
     * Save the dummy status state for this grade item.
     *
     * @param \grade_item $gradeitem
     *
     * @return void
     */
    public function save(&$gradeitem) {

        // New grade item.
        if ($this->enabled && $this->instanceid == -1) {

            if ($this->dummyitemid == 0) {

                $this->dummyitemid = $gradeitem->id;
            }

            $record = new \stdClass();
            $record->id = $this->instanceid;
            $record->gradeitem = $this->dummyitemid;
            $record->dummy = $this->enabled;

            // Save this dummy instance to the database.
            $this->instanceid = dao::save_instance($record);

            // Save this dummy instance to the grading_rules table.
            \core\grade\rule::save_rule_association($gradeitem->id, $this->get_type(), $this->get_id());

            // Sets needsupdate.
            $this->needsupdate = true;
        } else if (!$this->enabled && $this->instanceid != -1 && $this->owned_by($gradeitem->id)) {

            $this->delete_instance();
            $this->needsupdate = true;
        }
    }

    /**
     * Delete the dummy status state for this grade item.
     *
     * @param \grade_item $gradeitem
     *
     * @return void
     */
    public function delete(&$gradeitem) {

        $rules = rule::load_for_grade_item_by_type($gradeitem->id, 'dummy');

        if (!empty($rules)) {

            foreach ($rules as $rule) {

                $rule->delete_instance();
            }

            $this->needsupdate = true;
        }
    }

    /**
     * Carry out the deletion process.
     *
     * @return void.
     */
    public function delete_instance() {

        // Delete from the grading_rules table.
        rule::delete_rule_association('dummy', $this->instanceid);

        // Delete the instance from the graderule_dummy table.
        dao::delete_instance($this->instanceid);
    }


    /**
     * Returns the ID.
     *
     * @return int
     */
    public function get_id() {

        return $this->instanceid;
    }

    /**
     * @return string
     */
    public function get_type() {
        return 'dummy';
    }

    /**
     *
     * Which item owns this rule.
     *
     * @param int $itemid
     *
     * @return boolean
     */
    public function owned_by($itemid) {
        global $DB;

        if ($DB->record_exists('graderule_dummy', ['gradeitem' => $itemid])) {

            return true;
        } else {

            return false;
        }
    }

    /**
     *
     * Whether this dummy needs updating.
     *
     * @return boolean
     */
    public function needs_update() {

        return $this->needsupdate;
    }
}
