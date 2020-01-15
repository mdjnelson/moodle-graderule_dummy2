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
 * Factory class for dummy
 *
 * @package     graderule_dummy
 * @author      Marcus Boon<marcus@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace graderule_dummy;

use core\grade\rule;

defined('MOODLE_INTERNAL') || die('');

class factory implements \core\grade\rule\factory_interface {

    /**
     * @param string $data
     * @return \graderule_dummy\dummy
     */
    public static function create($plugin, $instanceid) {
        if ($instanceid == -1) {
            return new dummy(false, 0);
        }
        return dao::get_instance_from_id($instanceid);
    }
}
