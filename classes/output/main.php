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
 * Class containing data for the block.
 *
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * @var array Courses list to show.
     */
    private $courses = null;

    /**
     * Constructor.
     *
     * @param array $courses A courses list
     */
    public function __construct($courses = array()) {
        global $CFG, $OUTPUT;

        // Load the course image.
        foreach ($courses as $course) {
            $coursefull = new \core_course_list_element($course);

            $courseimage = '';
            foreach ($coursefull->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                if ($isimage) {
                    $courseimage = $url;
                    break;
                }
            }

            if (empty($courseimage)) {
                $courseimage = $OUTPUT->get_generated_image_for_id($course->id);
                //new \moodle_url($CFG->wwwroot . '/blocks/greatcourses/pix/course_small.png');
            }

            $course->imagepath = $courseimage;

            // If course is active or waiting.
            $course->active = $course->startdate <= time();
        }

        $this->courses = $courses;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $defaultvariables = [
            'courses' => array_values($this->courses),
            'baseurl' => $CFG->wwwroot
        ];

        return $defaultvariables;
    }
}
