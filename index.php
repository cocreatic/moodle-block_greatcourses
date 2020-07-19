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

require_once('../../config.php');
require_once('classes/output/catalog.php');

$query = optional_param('q', '', PARAM_TEXT);
$spage = optional_param('spage', 0, PARAM_INT);
$sort = optional_param('sort', '', PARAM_TEXT);

$syscontext = context_system::instance();

$PAGE->set_context($syscontext);
$PAGE->set_url('/blocks/greatcourses/index.php');
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('amountcourses', 'block_greatcourses'));
$PAGE->set_title(get_string('coursedetail', 'block_greatcourses'));


echo $OUTPUT->header();

$amount = get_config('block_greatcourses', 'amount');

if (!$amount || !is_numeric($amount)) {
    $amount = 20;
}

$availablesorting = array('default', 'recents');

$bmanager = new \block_manager($PAGE);
if ($bmanager->is_known_block_type('rate_course')) {
    $availablesorting[] = 'greats';
}

if (empty($sort) || !in_array($sort, $availablesorting)) {
    $sort = 'default';
}

$select = 'visible = 1 AND id != ' . SITEID;
$params = array();

if (!empty($query)) {
    $q = trim($query);
    $q = str_replace(' ', '%', $q);
    $q = '%' . $q . '%';
    $select .= " AND (fullname LIKE :query1 OR summary LIKE :query2)";
    $params['query1'] = $q;
    $params['query2'] = $q;
}

if ($sort == 'greats') {
    $selectgreats = str_replace(' AND id ', ' AND c.id ', $select);
    $sql = "SELECT c.*, AVG(r.rating) AS rating
                FROM {course} AS c
                LEFT JOIN {block_rate_course} AS r ON r.course = c.id
                WHERE " . $selectgreats .
                " GROUP BY c.id
                ORDER BY rating DESC";
    $courses = $DB->get_records_sql($sql, $params, $spage * $amount, $amount);
} else {
    if ($sort == 'recents') {
        $courses = $DB->get_records_select('course', $select, $params, 'startdate DESC', '*', $spage * $amount, $amount);
    } else {
        $courses = $DB->get_records_select('course', $select, $params, '', '*', $spage * $amount, $amount);
    }
}

$coursescount = $DB->count_records_select('course', $select, $params);

$pagingbar = new paging_bar($coursescount, $spage, $amount, "/blocks/greatcourses/index.php?q={$query}&amp;sort={$sort}&amp;");
$pagingbar->pagevar = 'spage';

$renderable = new \block_greatcourses\output\catalog($courses, $query, $sort);
$renderer = $PAGE->get_renderer('block_greatcourses');

echo $renderer->render($renderable);

echo $OUTPUT->render($pagingbar);

echo $OUTPUT->footer();