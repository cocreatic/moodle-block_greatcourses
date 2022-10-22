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
 * Block renderer
 *
 * @package   block_greatcourses
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_greatcourses\output;
defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;

/**
 * greatcourses block renderer
 *
 * @copyright 2020 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Return the template content for the block.
     *
     * @param main $main The main renderable
     * @return string HTML string
     */
    public function render_main(main $main) {
        global $CFG;

        $template = get_config('block_greatcourses', 'templatetype');
        $path = $CFG->dirroot . '/blocks/greatcourses/templates/' . $template . '/main.mustache';

        if ($template != 'default' && file_exists($path)) {
            $templatefile = 'block_greatcourses/' . $template . '/main';
        } else {
            $templatefile = 'block_greatcourses/main';
        }

        return $this->render_from_template($templatefile, $main->export_for_template($this));
    }

    /**
     * Return the template content for the block.
     *
     * @param catalog $catalog The catalog renderable
     * @return string HTML string
     */
    public function render_catalog(catalog $catalog) {
        global $CFG;

        $template = get_config('block_greatcourses', 'templatetype');
        $path = $CFG->dirroot . '/blocks/greatcourses/templates/' . $template . '/catalog.mustache';

        if ($template != 'default' && file_exists($path)) {
            $templatefile = 'block_greatcourses/' . $template . '/catalog';
        } else {
            $templatefile = 'block_greatcourses/catalog';
        }

        return $this->render_from_template($templatefile, $catalog->export_for_template($this));
    }

    /**
     * Return the template content for the block.
     *
     * @param detail $detail The detail renderable
     * @return string HTML string
     */
    public function render_detail(detail $detail) {
        global $CFG;

        $template = get_config('block_greatcourses', 'templatetype');
        $path = $CFG->dirroot . '/blocks/greatcourses/templates/' . $template . '/detail.mustache';

        if ($template != 'default' && file_exists($path)) {
            $templatefile = 'block_greatcourses/' . $template . '/detail';
        } else {
            $templatefile = 'block_greatcourses/detail';
        }

        return $this->render_from_template($templatefile, $detail->export_for_template($this));
    }
}
