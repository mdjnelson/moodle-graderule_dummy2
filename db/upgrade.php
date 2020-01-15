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
 * This file contains database upgrade code for graderule_dummy.
 *
 * @package     graderule_dummy
 * @author      Marcus Boon <marcus@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function xmldb_graderule_dummy_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019110400 ) {

        // Replace binary field with int field.
        $table = new xmldb_table('graderule_dummy');

        $field = new xmldb_field('dummy');
        // Conditionally launch drop field dummy.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('dummy', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'gradeitem');
        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2019110400, 'graderule', 'dummy');
    }

    return true;
}
