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

    // Course fields.
    $name = 'block_greatcourses/settingsheaderfields';
    $heading = get_string('settingsheaderfields', 'block_greatcourses');
    $setting = new admin_setting_heading($name, $heading, '');
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

    // Experts field.
    $name = 'block_greatcourses/experts';
    $title = get_string('expertsfield', 'block_greatcourses');
    $help = get_string('expertsfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Short experts field.
    $name = 'block_greatcourses/expertsshort';
    $title = get_string('expertsshortfield', 'block_greatcourses');
    $help = get_string('expertsshortfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Payment fields.
    $name = 'block_greatcourses/settingsheaderpayment';
    $heading = get_string('settingsheaderpayment', 'block_greatcourses');
    $setting = new admin_setting_heading($name, $heading, '');
    $settings->add($setting);

    // Payment url field.
    $name = 'block_greatcourses/paymenturl';
    $title = get_string('paymenturlfield', 'block_greatcourses');
    $help = get_string('paymenturlfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Premium type user field.
    $name = 'block_greatcourses/premiumfield';
    $title = get_string('premiumfield', 'block_greatcourses');
    $help = get_string('premiumfield_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);

    // Premium type value.
    $name = 'block_greatcourses/premiumvalue';
    $title = get_string('premiumvalue', 'block_greatcourses');
    $help = get_string('premiumvalue_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, '');
    $settings->add($setting);


    // Appearance.
    $name = 'block_greatcourses/settingsheaderappearance';
    $heading = get_string('settingsheaderappearance', 'block_greatcourses');
    $setting = new admin_setting_heading($name, $heading, '');
    $settings->add($setting);

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

    // Days to upcoming courses.
    $name = 'block_greatcourses/daystoupcoming';
    $title = get_string('daystoupcoming', 'block_greatcourses');
    $help = get_string('daystoupcoming_help', 'block_greatcourses');
    $setting = new admin_setting_configtext($name, $title, $help, 0, PARAM_INT, 3);
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

    // Block detail info.
    $name = 'block_greatcourses/detailinfo';
    $title = get_string('detailinfo', 'block_greatcourses');
    $help = get_string('detailinfo_help', 'block_greatcourses');
    $setting = new admin_setting_confightmleditor($name, $title, $help, '');
    $settings->add($setting);

    // Cover image type.
    $options = [
        'default' => get_string('coverimagetype_default', 'block_greatcourses'),
        'generated' => get_string('coverimagetype_generated', 'block_greatcourses'),
        'none' => get_string('coverimagetype_none', 'block_greatcourses'),
    ];

    $name = 'block_greatcourses/coverimagetype';
    $title = get_string('coverimagetype', 'block_greatcourses');
    $help = get_string('coverimagetype_help', 'block_greatcourses');
    $setting = new admin_setting_configselect($name, $title, $help, 'default', $options);
    $settings->add($setting);

    // Template type.
    $options = ['default' => ''];

    $path = $CFG->dirroot . '/blocks/greatcourses/templates/';
    $files = array_diff(scandir($path), array('..', '.'));

    foreach ($files as $file) {
        if (is_dir($path . $file)) {
            $options[$file] = $file;
        }
    }

    $name = 'block_greatcourses/templatetype';
    $title = get_string('templatetype', 'block_greatcourses');
    $help = get_string('templatetype_help', 'block_greatcourses');
    $setting = new admin_setting_configselect($name, $title, $help, 'default', $options);
    $settings->add($setting);

}
