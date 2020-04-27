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
 * Backup support for graderule_dummy plugin
 *
 * @package    core_backup
 * @category   backup
 * @copyright  2020 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

class backup_graderule_dummy_plugin extends backup_graderule_plugin {

    /**
     * Returns the dummy rule information to attach to grade_rule element
     */
    protected function define_grade_rule_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../plugin', $this->pluginname);

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        $dummy = new backup_nested_element('dummy', ['id'], ['dummy']);

        // Now the own qtype tree.
        $pluginwrapper->add_child($dummy);

        // Set source to populate the data.
        $dummy->set_source_table('graderule_dummy',
            ['id' => '../../../../pluginid']);

        return $plugin;
    }

    /**
     * Returns the dummy rile information to attach to activity_grade_rule element
     */
    protected function define_activity_grade_rule_plugin_structure() {
        return $this->define_grade_rule_plugin_structure();
    }
}
