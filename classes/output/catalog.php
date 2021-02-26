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
 * Class containing renderers for the block.
 *
 * @package   block_greatcourses
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_greatcourses\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for the courses catalog.
 *
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catalog implements renderable, templatable {

    /**
     * @var array Courses list to show.
     */
    private $courses = null;

    /**
     * @var array Query to filter the courses list.
     */
    private $query = null;

    /**
     * @var array Sort type.
     */
    private $sort = null;

    /**
     * Constructor.
     *
     * @param array $courses A courses list
     */
    public function __construct($courses = array(), $query = '', $sort = '') {
        global $CFG, $OUTPUT;

        // Load the course image.
        foreach ($courses as $course) {
            \block_greatcourses\controller::course_preprocess($course);
        }

        $this->courses = $courses;
        $this->query = $query;
        $this->sort = $sort;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $PAGE;

        $defaultvariables = [
            'courses' => array_values($this->courses),
            'baseurl' => $CFG->wwwroot,
            'query' => $this->query,
            'sort' => $this->sort
        ];

        $bmanager = new \block_manager($PAGE);
        if ($bmanager->is_known_block_type('rate_course')) {
            $defaultvariables['rateavailable'] = true;
        }

        $defaultvariables['premiumavailable'] = \block_greatcourses\controller::premium_available();

        return $defaultvariables;
    }
}
