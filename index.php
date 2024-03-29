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
$PAGE->set_heading(get_string('catalog', 'block_greatcourses'));
$PAGE->set_title(get_string('catalog', 'block_greatcourses'));

\block_greatcourses\controller::include_templatecss();

echo $OUTPUT->header();

$summary = get_config('block_greatcourses', 'summary');

echo format_text($summary, FORMAT_HTML, array('trusted' => true, 'noclean' => true));

$amount = get_config('block_greatcourses', 'amount');

if (!$amount || !is_numeric($amount)) {
    $amount = 20;
}

$availablesorting = array('default', 'recents');

$bmanager = new \block_manager($PAGE);
if ($bmanager->is_known_block_type('rate_course')) {
    $availablesorting[] = 'greats';
}

if (\block_greatcourses\controller::premium_available()) {
    $availablesorting[] = 'premium';
}


if (empty($sort) || !in_array($sort, $availablesorting)) {
    $sort = 'default';
}

$select = 'visible = 1 AND id != ' . SITEID;
$params = array();

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
    $sql = "SELECT c.*, AVG(r.rating) AS rating, COUNT(1) AS ratings
                FROM {course} AS c
                INNER JOIN {block_rate_course} AS r ON r.course = c.id
                WHERE " . $selectgreats .
                " GROUP BY c.id HAVING rating > 3
                ORDER BY rating DESC";
    $courses = $DB->get_records_sql($sql, $params, $spage * $amount, $amount);

    $sql_count = "SELECT COUNT(DISTINCT c.id)
                FROM {course} AS c
                INNER JOIN {block_rate_course} AS r ON r.course = c.id
                WHERE " . $selectgreats;

    $coursescount = $DB->count_records_sql($sql_count, $params);
} else  if ($sort == 'premium') {


    $params['fieldid'] = \block_greatcourses\controller::get_payfieldid();

    $selectpremium = str_replace(' AND id ', ' AND c.id ', $select);
    $sql = "SELECT c.*
                FROM {course} AS c
                INNER JOIN {customfield_data} AS cd ON cd.fieldid = :fieldid AND cd.value != '' AND cd.instanceid = c.id
                WHERE " . $selectpremium .
                " ORDER BY c.fullname ASC";
    $courses = $DB->get_records_sql($sql, $params, $spage * $amount, $amount);

    $sql_count = "SELECT COUNT(1)
                    FROM {course} AS c
                    INNER JOIN {customfield_data} AS cd ON fieldid = :fieldid AND cd.value != '' AND cd.instanceid = c.id
                    WHERE " . $selectpremium;

    $coursescount = $DB->count_records_sql($sql_count, $params);
} else {
    if ($sort == 'recents') {
        $courses = $DB->get_records_select('course', $select, $params, 'startdate DESC', '*', $spage * $amount, $amount);
    } else {
        $courses = $DB->get_records_select('course', $select, $params, 'fullname ASC', '*', $spage * $amount, $amount);
    }
    $coursescount = $DB->count_records_select('course', $select, $params);
}


$pagingbar = new paging_bar($coursescount, $spage, $amount, "/blocks/greatcourses/index.php?q={$query}&amp;sort={$sort}&amp;");
$pagingbar->pagevar = 'spage';

$renderable = new \block_greatcourses\output\catalog($courses, $query, $sort);
$renderer = $PAGE->get_renderer('block_greatcourses');

echo $renderer->render($renderable);

echo $OUTPUT->render($pagingbar);

echo $OUTPUT->footer();