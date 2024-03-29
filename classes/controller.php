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

    protected static $large = false;

    public static function course_preprocess($course, $large = false) {
        global $CFG, $OUTPUT, $DB, $PAGE, $USER;

        self::$large = $large;
        $course->haspaymentgw = false;
        $course->paymenturl = null;
        $payfieldid = self::get_payfieldid();

        if ($payfieldid) {
            $course->paymenturl = $DB->get_field('customfield_data', 'value',
                                        array('fieldid' => $payfieldid, 'instanceid' => $course->id));
        }

        // Load course context to general purpose.
        $coursecontext = \context_course::instance($course->id, $USER, '', true);

        // Load the course enrol info.
        $enrolinstances = enrol_get_instances($course->id, true);

        $course->enrollable = false;
        $course->fee = array();
        foreach ($enrolinstances as $instance) {
            if ($instance->enrol == 'self') {
                $course->enrollable = true;
                break;
            } else if ($instance->enrol == 'fee' && enrol_is_enabled('fee')) {

                $cost = (float) $instance->cost;
                if ( $cost <= 0 ) {
                    $cost = (float) get_config('enrol_fee', 'cost');
                }

                if ($cost > 0) {
                    $datafee = new \stdClass();
                    $datafee->cost = \core_payment\helper::get_cost_as_string($cost, $instance->currency);
                    $datafee->itemid = $instance->id;
                    $datafee->label = !empty($instance->name) ? $instance->name : get_string('sendpaymentbutton', 'enrol_fee');
                    $datafee->description = get_string('purchasedescription', 'enrol_fee',
                                                format_string($course->fullname, true, ['context' => $coursecontext]));

                    $course->fee[] = $datafee;
                    $course->enrollable = true;
                    $course->haspaymentgw = true;
                }

            } else if ($instance->enrol == 'guest' && enrol_is_enabled('guest')) {

                $course->enrollable = true;
                $course->enrollasguest = true;

            }
        }

        // If course has a single cost, load it for fast printing.
        if (count($course->fee) == 1) {
            $course->cost = $course->fee[0]->cost;
        }

        $course->imagepath = self::get_courseimage($course);

        $bmanager = new \block_manager($PAGE);

        if (!property_exists($course, 'rating')) {
            if ($bmanager->is_known_block_type('rate_course')) {

                if ($large) {
                    $values = $DB->get_records('block_rate_course', array('course' => $course->id), '', 'id, rating');

                    // Start default array to 1-5 stars.
                    $ratinglist = [0, 0, 0, 0, 0, 0];
                    unset($ratinglist[0]);

                    $ratingsum = 0;
                    foreach ($values as $one) {
                        $ratinglist[$one->rating]++;
                        $ratingsum += $one->rating;
                    }

                    $ratings = count($values);
                    $rating = $ratings > 0 ? $ratingsum / $ratings : 0;

                    $ratingpercents = array();
                    foreach ($ratinglist as $key => $one) {
                        $ratingpercents[$key] = $ratings > 0 ? round($one * 100 / $ratings) : 0;
                    }
                } else {
                    $sql = "SELECT AVG(rating) AS rating, COUNT(1) AS ratings  FROM {block_rate_course} WHERE course = :courseid";
                    $rate = $DB->get_record_sql($sql, array('courseid' => $course->id));
                    $ratinglist = null;
                    $rating = $rate->rating;
                    $ratings = $rate->ratings;
                }


                $course->rating = new \stdClass();
                $course->rating->total = $rating;
                $course->rating->count = $ratings;

                if ($ratinglist) {
                    $course->rating->detail = array();
                    foreach ($ratinglist as $key => $one) {
                        $detail = new \stdClass();
                        $detail->value = $key;
                        $detail->count = $one;
                        $detail->avg = round($ratingpercents[$key]);
                        $course->rating->detail[] = $detail;
                    }
                } else {
                    $course->rating->detail = null;
                }

            }

        }

        if (property_exists($course, 'rating') && $course->rating) {

            if (!is_object($course->rating)) {
                $rating = $course->rating;
                $course->rating = new \stdClass();
                $course->rating->total = $rating;
                $course->rating->count = property_exists($course, 'ratings') ? $course->ratings : 0;
                $course->rating->detail = null;
            }

            // Not rating course.
            if ($course->rating->total == 0) {
                $course->rating = null;
            } else {
                $course->rating->total = round($course->rating->total, 1);
                $course->rating->percent = round($course->rating->total * 20);
                $course->rating->formated = str_pad($course->rating->total, 3, '.0');
                $course->rating->stars = $course->rating->total > 0 ? range(1, $course->rating->total) : null;
            }
        }

        // If course is active or waiting.
        $course->active = $course->startdate <= time();

        // Load data for course detail.
        if ($large) {
            $fullcourse = new \core_course_list_element($course);

            $course->commentscount = $DB->count_records('comments', array('contextid' => $coursecontext->id, 'component' => 'block_comments'));

            if ($course->commentscount > 0) {
                $course->hascomments = true;
                // Get 20 newest records.
                $course->comments = $DB->get_records('comments', array('contextid' => $coursecontext->id, 'component' => 'block_comments'),
                                                        'timecreated DESC', '*', 0, 20);

                $course->comments = array_values($course->comments);

                $strftimeformat = get_string('strftimerecentfull', 'langconfig');

                foreach ($course->comments as $comment) {
                    $user = $DB->get_record('user', array('id' => $comment->userid));
                    $userpicture = new \user_picture($user, array('alttext'=>false, 'link'=>false));
                    $userpicture->size = 200;
                    $comment->userpicture = $userpicture->get_url($PAGE);
                    $comment->timeformated = userdate($comment->timecreated, $strftimeformat);
                    $comment->userfirstname = $user->firstname;
                }
            } else {
                $course->hascomments = false;
                $course->comments = null;
            }

            // Search related courses by tags.
            $course->hasrelated = false;
            $course->related = array();
            $related = array();
            $relatedlimit = 3;

            $categories = get_config('block_greatcourses', 'categories');

            $categoriesids = array();
            $catslist = explode(',', $categories);
            foreach($catslist as $catid) {
                if (is_numeric($catid)) {
                    $categoriesids[] = (int)trim($catid);
                }
            }

            $categoriescondition = '';
            if (count($categoriesids) > 0) {
                $categoriescondition = " AND c.category IN (" . implode(',', $categoriesids) . ")";
            }

            if (\core_tag_tag::is_enabled('core', 'course')) {
                // Get the course tags.
                $tags = \core_tag_tag::get_item_tags_array('core', 'course', $course->id);

                if (count($tags) > 0) {
                    $ids = array();
                    foreach ($tags as $key => $tag) {
                        $ids[] = $key;
                    }

                    $sqlintances = "SELECT c.id, c.category FROM {tag_instance} AS t " .
                                    " INNER JOIN {course} AS c ON t.itemtype = 'course' AND c.id = t.itemid" .
                                    " WHERE t.tagid IN (" . (implode(',', $ids)) . ") " . $categoriescondition .
                                    " GROUP BY c.id, c.category" .
                                    " ORDER BY t.timemodified DESC";

                    $instances = $DB->get_records_sql($sqlintances);

                    foreach ($instances as $instance) {
                        if ($instance->id != $course->id &&
                                $instance->id != SITEID &&
                                count($related) < $relatedlimit &&
                                !in_array($instance->id, $related)) {

                            $related[] = $instance->id;
                        }
                    }
                }
            }

            if (count($related) < $relatedlimit) {
                // Exclude previous related courses, current course and the site.
                $relatedids = implode(',', array_merge($related, array($course->id, SITEID)));
                $sql = "SELECT id FROM {course} AS c " .
                        " WHERE visible = 1 AND (enddate > :enddate OR enddate IS NULL) AND id NOT IN ($relatedids)" .
                        $categoriescondition .
                        " ORDER BY startdate DESC";
                $params = array('enddate' => time());
                $othercourses = $DB->get_records_sql($sql, $params, 0, $relatedlimit - count($related));

                foreach ($othercourses as $other) {
                    $related[] = $other->id;
                }
            }

            if (count($related) > 0) {
                $course->hasrelated = true;

                $coursesinfo = $DB->get_records_list('course', 'id', $related);

                // Load other info about the courses.
                foreach ($coursesinfo as $one) {

                    $one->imagepath = self::get_courseimage($one);

                    if ($bmanager->is_known_block_type('rate_course')) {
                        $sql = "SELECT AVG(rating) AS rating, COUNT(1) AS ratings  FROM {block_rate_course} WHERE course = :cid";
                        $rate = $DB->get_record_sql($sql, array('cid' => $one->id));

                        $one->rating = new \stdClass();
                        $one->rating->total = 0;
                        $one->rating->count = 0;
                        $one->rating->detail = null;

                        if ($rate) {
                            $one->rating->total = round($rate->rating, 1);
                            $one->rating->count = $rate->ratings;
                            $one->rating->percent = round($one->rating->total * 20);
                            $one->rating->formated = str_pad($one->rating->total, 3, '.0');
                            $one->rating->stars = $one->rating->total > 0 ? range(1, $one->rating->total) : null;
                        }
                    }

                    $course->related[] = $one;
                }
            }

            // Load the teachers information.
            $course->hasinstructors = false;

            if ($fullcourse->has_course_contacts()) {
                $course->hasinstructors = true;
                $course->instructors = array();
                $instructors = $fullcourse->get_course_contacts();

                foreach ($instructors as $key => $instructor) {

                    $user = $DB->get_record('user', array('id' => $key));
                    $userpicture = new \user_picture($user, array('alttext' => false, 'link' => false));
                    $userpicture->size = 200;
                    $user->userpicture = $userpicture->get_url($PAGE);
                    $user->profileurl = $CFG->wwwroot . '/user/profile.php?id=' . $key;

                    $course->instructors[] = $user;
                }
            }

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

    public static function get_courseimage($course) {
        global $CFG, $OUTPUT;

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
                                                                (self::$large ? 'course' : 'course_small') . '.png');
            }
        }

        return $courseimage;
    }

    public static function include_templatecss() {

        global $CFG, $PAGE;

        $template = get_config('block_greatcourses', 'templatetype');
        $csspath = $CFG->dirroot . '/blocks/greatcourses/templates/' . $template . '/styles.css';

        if ($template != 'default' && file_exists($csspath)) {
            $PAGE->requires->css('/blocks/greatcourses/templates/' . $template . '/styles.css');
        }

    }
}