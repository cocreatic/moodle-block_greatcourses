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
 * Class containing renderers for details.
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
 * Class containing data for details.
 *
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class detail implements renderable, templatable {

    /**
     * @var object Course.
     */
    private $course = null;

    /**
     * Constructor.
     *
     * @param object $course A course
     */
    public function __construct($course) {

        $this->course = $course;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $OUTPUT, $PAGE, $USER, $DB;

        // Course detail info.
        $detailinfo = get_config('block_greatcourses', 'detailinfo');
        $detailinfo = format_text($detailinfo, FORMAT_MOODLE);

        // Load social networks.
        $networks = get_config('block_greatcourses', 'networks');
        $networkslist = explode("\n", $networks);
        $socialnetworks = array();

        $courseurl = new \moodle_url('/blocks/greatcourses/detail.php', array('id' => $this->course->id));
        foreach ($networkslist as $one) {

            $row = explode('|', $one);
            if (count($row) >= 2) {
                $network = new \stdClass();
                $network->icon = trim($row[0]);
                $network->url = trim($row[1]);
                $network->url = str_replace('{url}', $courseurl, $network->url);
                $network->url = str_replace('{name}', $this->course->fullname, $network->url);
                $socialnetworks[] = $network;
            }
        }

        // Build course images list.
        $coursefull = new \core_course_list_element($this->course);

        $courseimages = '';
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
            $type = get_config('block_greatcourses', 'coverimagetype');

            switch ($type) {
                case 'generated':
                    $courseimage = $OUTPUT->get_generated_image_for_id($course->id);
                break;
                case 'none':
                    $courseimage = '';
                break;
                default:
                    $courseimage = new \moodle_url($CFG->wwwroot . '/blocks/greatcourses/pix/course.png');
            }
        }
        // End Build course images list.

        // Load custom course fields.
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $datas = $handler->get_instance_data($this->course->id);
        $fields = array('thematic', 'units', 'requirements', 'license', 'media', 'duration', 'expertsshort', 'experts');
        $custom = new \stdClass();

        $fieldsnames = array();
        foreach ($fields as $field) {
            $name = get_config('block_greatcourses', $field);

            if (!empty($name)) {
                $fieldsnames[$field] = $name;
            }
        }

        foreach ($datas as $data) {
            $key = $data->get_field()->get('shortname');

            foreach ($fieldsnames as $field => $name) {
                if ($name == $key) {
                    $c = new \stdClass();
                    $c->title = $data->get_field()->get('name');

                    $c->value = $data->export_value();

                    if ($field == 'license') {
                        if (get_string_manager()->string_exists('license-' . $c->value, 'block_greatcourses')) {
                            $c->text = get_string('license-' . $c->value, 'block_greatcourses');
                            $c->path = $c->value == 'cc-0' ? 'zero/1.0' : trim($c->value, 'cc-') . '/4.0';
                        } else {
                            $c->text = $c->value;
                        }
                    }

                    if (!empty($c->value)) {
                        $custom->$field = $c;
                    }

                    break;
                }
            }
        }
        // End Load custom course fields.
        $enrolinstances = enrol_get_instances($this->course->id, true);

        $custom->enrollable = false;
        foreach($enrolinstances as $instance) {
            if ($instance->enrol == 'self') {
                $custom->enrollable = true;
                break;
            }
        }

        $completed = $DB->get_record('course_completions', array('userid' => $USER->id, 'course' => $this->course->id));

        // Special format to the course name.
        $coursename = $this->course->fullname;
        $m = explode(' ', $coursename);

        $first = '';
        $last = '';
        foreach ($m as $k => $n) {
            if ($k < (count($m) / 2)) {
                $first .= $n . ' ';
            } else {
                $last .= $n . ' ';
            }
        }

        $coursename = $first . '<span>' . $last . '</span>';
        // End


        // Check enroled status.
        $coursecontext = \context_course::instance($this->course->id, $USER, '', true);
        $custom->enrolled = !(isguestuser() || !isloggedin() || !is_enrolled($coursecontext));

        $custom->completed = $completed && $completed->timecompleted;

        $enrollstate = $custom->completed ? 'completed' : ($custom->enrolled ? 'enrolled' : 'none');

        // End Check enroled status.

        $defaultvariables = [
            'course' => $this->course,
            'courseimages' => $courseimages,
            'custom' => $custom,
            'baseurl' => $CFG->wwwroot,
            'networks' => $socialnetworks,
            'detailinfo' => $detailinfo,
            'enrollstate' => $enrollstate,
            'coursename' => $coursename
        ];

        return $defaultvariables;
    }
}
