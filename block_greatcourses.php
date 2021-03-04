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
 * Form for editing greatcourses block instances.
 *
 * @package   block_greatcourses
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_greatcourses extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_greatcourses');
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        if (isset($this->config->title)) {
            $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('newblocktitle', 'block_greatcourses');
        }
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        $amount = get_config('block_greatcourses', 'singleamount');

        if (!$amount || !is_numeric($amount)) {
            $amount = 4;
        }

        $params = array();
        $select = 'visible = 1 AND id != ' . SITEID;

        $daystoupcoming = get_config('block_greatcourses', 'daystoupcoming');
        if (isset($daystoupcoming) && is_numeric($daystoupcoming)) {
            $select .= ' AND startdate < :startdate ';
            $params['startdate'] = time() + ($daystoupcoming * 24 * 60 * 60);
        }

        // Categories filter.
        $categories = get_config('block_greatcourses', 'categories');

        $categoriesids = array();
        $catslist = explode(',', $categories);
        foreach($catslist as $catid) {
            if (is_numeric($catid)) {
                $categoriesids[] = (int)trim($catid);
            }
        }

        if (count($categoriesids) > 0) {
            $select .= ' AND category IN (' . implode(',', $categoriesids) . ')';
        }

        // End Categories filter.
        $courses = $DB->get_records_select('course', $select, $params, 'startdate DESC', '*', 0, $amount);

        $html = '';

        if ($courses && is_array($courses)) {
            // Load templates to display courses;
            $renderable = new \block_greatcourses\output\main($courses);
            $renderer = $this->page->get_renderer('block_greatcourses');
            $html .= $renderer->render($renderable);
        }

        $this->content->text = $html;

        return $this->content;
    }

    public function instance_can_be_docked() {
        return false;
    }

}
