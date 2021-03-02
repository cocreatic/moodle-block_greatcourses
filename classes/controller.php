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
 * Class containing the general controls.
 *
 * @package   block_greatcourses
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_greatcourses;
defined('MOODLE_INTERNAL') || die();


/**
 * Component controller.
 *
 * @copyright 2021 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controller {

    protected static $PAYFIELDID = null;

    public static function course_preprocess($course, $large = false) {
        global $CFG, $OUTPUT, $DB, $PAGE;

        $course->paymenturl = null;
        $payfieldid = self::get_payfieldid();

        if ($payfieldid) {
            $course->paymenturl = $DB->get_field('customfield_data', 'value',
                                        array('fieldid' => $payfieldid, 'instanceid' => $course->id));
        }

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
            $type = get_config('block_greatcourses', 'coverimagetype');

            switch ($type) {
                case 'generated':
                    $courseimage = $OUTPUT->get_generated_image_for_id($course->id);
                break;
                case 'none':
                    $courseimage = '';
                break;
                default:
                    $courseimage = new \moodle_url($CFG->wwwroot . '/blocks/greatcourses/pix/' .
                                                                ($large ? 'course' : 'course_small') . '.png');
            }
        }

        $course->imagepath = $courseimage;

        if (!property_exists($course, 'rating')) {
            $bmanager = new \block_manager($PAGE);
            if ($bmanager->is_known_block_type('rate_course')) {

                $sql = "SELECT AVG(rating) AS rating, COUNT(1) AS ratings  FROM {block_rate_course} WHERE course = :courseid";

                $rate = $DB->get_record_sql($sql, array('courseid' => $course->id));

                $course->rating = $rate->rating;
                $course->ratings = $rate->ratings;
                $course->ratingstars = $rate->rating > 0 ? range(1, $rate->rating) : null;
            }

        }

        if (property_exists($course, 'rating')) {
            $course->rating = round($course->rating, 1);
            $course->ratingpercent = round($course->rating * 20);
            $course->ratingformated = str_pad($course->rating, 3, '.0');
            $course->ratingstars = $course->rating > 0 ? range(1, $course->rating) : null;
        }

        // If course is active or waiting.
        $course->active = $course->startdate <= time();

        // Load data for course detail.
        if ($large) {
            $contextid = $DB->get_field('context', 'id', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $course->id));
            $course->commentscount = $DB->count_records('comments', array('contextid' => $contextid, 'component' => 'block_comments'));

            // Get 20 newest records.
            $course->comments = $DB->get_records('comments', array('contextid' => $contextid, 'component' => 'block_comments'),
                                                    'timecreated DESC', '*', 20);
        }

    }

    public static function premium_available() {

        $payfieldid = self::get_payfieldid();
        return $payfieldid ? true : false;
    }

    public static function get_payfieldid() {
        global $DB;

        if (!self::$PAYFIELDID) {
            $paymenturlfield = get_config('block_greatcourses', 'paymenturl');
            if (!empty($paymenturlfield)) {
                self::$PAYFIELDID = $DB->get_field('customfield_field', 'id', array('shortname' => $paymenturlfield));
            }
        }

        return self::$PAYFIELDID;
    }

    public static function is_user_premium($user = null) {
        global $USER, $DB;
        if (!$user) {
            $user = $USER;
        }


        $premiumfield = get_config('block_greatcourses', 'premiumfield');
        $premiumvalue = get_config('block_greatcourses', 'premiumvalue');

        if (empty($premiumfield)) {
            return false;
        }

        if (isset($user->profile[$premiumfield]) && $user->profile[$premiumfield] == $premiumvalue) {
            return true;
        }

        return false;
    }
}