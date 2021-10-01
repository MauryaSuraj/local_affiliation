<?php
// This file is part of the Contact Form plugin for Moodle - http://moodle.org/
//
// Contact Form is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Contact Form is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Contact Form.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_affiliation
 * @category    admin
 * @copyright   2021 Suraj Maurya surajmaurya450@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once __DIR__.'/lib.php';

$PAGE->set_url('/local/affiliation/index.php');


if(AffiliationRender::check_affiliation_link_exits()):
  echo AffiliationRender::redirection_affiliation_link();
else:
  AffiliationRender::login_redirection_user();
endif;

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'local_affiliation'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_affiliation'));

$previewnode = $PAGE->navbar->add(
  get_string('pluginname', 'local_affiliation'), 
  new moodle_url('/local/affiliation/index.php'), 
  navigation_node::TYPE_CONTAINER
);

$thingnode = $previewnode->add(
  get_string('pluginname', 'local_affiliation'), 
  new moodle_url('/local/affiliation/index.php')
);

$thingnode->make_active();

echo $OUTPUT->header();
echo html_writer::start_tag('div', array('class' => 'row' ));
echo html_writer::start_tag('div', array('class' => 'col-md-12' )); 

echo AffiliationRender::affiliation_course_render();

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo $OUTPUT->footer();