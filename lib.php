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


/**
 * 
 * @method render course list.
 * @return template.
 * 
 * */

use local_affiliation\util\AffiliateCourse;
use local_affiliation\util\Affiliation;

/**
 * Affiliation renderer
 */
final class AffiliationRender extends Affiliation
{
	
	public function __construct() { }

	/**
	 * Static function to render course.
	 * */

	public static function affiliation_course_render(){
		global $OUTPUT;

		$affiliation_course = new AffiliateCourse();
		$category 			= $affiliation_course->get_All_Affliate_Course_Category();
		$course 			= $affiliation_course->get_All_Course_list();

		$data 				= new stdClass;
		$data->hascategory  = (!empty($category)) ? true : false;
		$data->categories 	= $category;
		$data->hascourse 	= (!empty($course)) ? true : false;
		$data->courses 		= $course;

		return $OUTPUT->render_from_template('local_affiliation/affiliationcourse', $data);
	}

	/**
	 * Check for the affiliation link.
	 * */
	public static function check_affiliation_link_exits(){
		$affiliation = new Affiliation;
		if ($affiliation->check_affiliation_link()) {
			return true;
		}
		return false;
	}

	/**
	 * Redirect to the Affiliation 
	 * */

	public static function redirection_affiliation_link(){
		global $CFG;
		$affiliation = new Affiliation;
		$cal = $affiliation->check_affiliation_link();
		redirect($CFG->wwwroot.'/course/'.$cal);
	}

	public static function crsbase64( $courseid = null ){
		if (is_null($courseid)) {
			return false;
		}
		$affiliation = new Affiliation;
		return $affiliation->encode_course_view_data_in_base64($courseid);
	}

	private static function is_admin_logged_in(){
		global $USER;
		$ids = array();

		if (!empty( $admins = get_admins())) {
			$ids = array_map( fn($v): int => $v->id, $admins);
		}

		if (!empty($ids) && in_array($USER->id, $ids) ) {
			return true;
		}
		return false;
	}

	public static function login_redirection_user(){
		global $CFG;
		if (self::is_admin_logged_in()) {
			// Can access affilation page.
		}else{
			// Redirect user to login page.
			redirect($CFG->wwwroot.'/login/index.php');
		}
	}

}