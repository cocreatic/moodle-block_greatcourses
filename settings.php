<?php
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings for the block
 *
 * @package   block_greatcourses
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Courses in block view.
    $name = 'block_greatcourses/singleamount';
    $title = get_string('singleamountcourses', 'block_greatcourses');
    $help = get_string('singleamountcourses_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, 4, PARAM_INT, 2);
    $settings->add($setting);

    // Courses by page.
    $name = 'block_greatcourses/amount';
    $title = get_string('amountcourses', 'block_greatcourses');
    $help = get_string('amountcourses_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, 20, PARAM_INT, 5);
    $settings->add($setting);

    // Thematic field.
    $name = 'block_greatcourses/thematic';
    $title = get_string('thematicfield', 'block_greatcourses');
    $help = get_string('thematicfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Content units field.
    $name = 'block_greatcourses/units';
    $title = get_string('unitsfield', 'block_greatcourses');
    $help = get_string('unitsfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Requirements field.
    $name = 'block_greatcourses/requirements';
    $title = get_string('requirementsfield', 'block_greatcourses');
    $help = get_string('requirementsfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // License field.
    $name = 'block_greatcourses/license';
    $title = get_string('licensefield', 'block_greatcourses');
    $help = get_string('licensefield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Media field.
    $name = 'block_greatcourses/media';
    $title = get_string('mediafield', 'block_greatcourses');
    $help = get_string('mediafield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Duration field.
    $name = 'block_greatcourses/duration';
    $title = get_string('durationfield', 'block_greatcourses');
    $help = get_string('durationfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Social networks.
    $name = 'block_greatcourses/networks';
    $title = get_string('socialnetworks', 'block_greatcourses');
    $help = get_string('socialnetworks_help', 'block_greatcourses');
    $setting = new admin_setting_configtextarea($name, $title, $help, '');
    $settings->add($setting);

    // Categories filter.
    $name = 'block_greatcourses/categories';
    $title = get_string('categories', 'block_greatcourses');
    $help = get_string('categories_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Block summary.
    $name = 'block_greatcourses/summary';
    $title = get_string('summary', 'block_greatcourses');
    $help = get_string('summary_help', 'block_greatcourses');
    $setting = new admin_setting_confightmleditor($name, $title, $help, '');
    $settings->add($setting);
}
