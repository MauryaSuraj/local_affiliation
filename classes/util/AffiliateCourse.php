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
 * Plugin administration functionality is defined here.
 *
 * @package     local_affiliation
 * @category    admin
 * @copyright   2021 Suraj Maurya surajmaurya450@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_affiliation\util;

class AffiliateCourse extends Affiliation
{
	
	private $database;
	public $user;

	function __construct() {
		global $DB, $USER;
		$this->database = $DB;
		$this->user = $USER;
	}

	/**
	 * @method return all the category.
	 * @return array()
	 * */

	public function get_All_Affliate_Course_Category($cat = array()){
		if ($this->database->record_exists("course_categories", array( "visible" => 1 ) )) {
		 	foreach ($this->database->get_records("course_categories", array( "visible" => 1 ),'name', $fields='id, name') as $key => $value) {
		 		array_push($cat, $value);
		 	}

		 	return $cat;
		}
		return array();
	}

	/**
	 * @method return all courses.
	 * @return array.
	 * */

	public function get_All_Course_list($catagory = null) {
		if (!is_null($catagory)) {
			if ($this->database->record_exists("course", array( "visible" => 1, 'catagory' => $catagory ) ) ) {
				return $this->covertToArray($this->database->get_records("course", array("visible" => 1, 'catagory' => $catagory )));
			}else{
				return array();
			}
		}else{
			if ($this->database->record_exists("course", array( "visible" => 1) ) ) {
				return $this->covertToArray($this->database->get_records("course", array("visible"=>1)));
			}else{
				return array();
			}
		}
		return array();
	}

	/**
	 * @method return single course details.
	 * @return object.
	 * @param course id.
	 * */

	public function get_Course_details() {

	}

	/**
	 * @return I.P. address of client side.
	 * */
	public function get_client_ip() {
	    $ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	       $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}

	/**
	 * @method create Url of course.
	 * @return course link.
	 * */
	public function create_affiliation_link() {
		
	}

	private function covertToArray($data = array(), $rdata = array()) {
		global $CFG;
		foreach ($data as $key => $value) {
			$value->courseurl = $CFG->wwwroot.'/course/view.php?id='.$value->id;
			$value->categoryname = isset($this->get_Course_Category_details($value->category)->name) ? $this->get_Course_Category_details($value->category)->name : '';
			$value->langs = $this->get_c_languages($value->id);
			$value->image = $this->course_image($value->id);
			$value->price = $this->preminum_instance($value->id);
			$value->summary = substr(strip_tags($value->summary), 0, 100);
			if ($value->id == 1) {
			}else{
				array_push($rdata, $value);
			}
		}
		return $rdata;
	}

	private function get_Course_Category_details($id) {
		if ($this->database->record_exists("course_categories", array( "visible" => 1, 'id' => $id ) )) {
		 	return $this->database->get_record("course_categories", array( "visible" => 1, 'id' => $id ),'name', $fields='id, name');
		}
		return null;
	}

	private function get_c_languages($courseid = null, $clangs = array()){
		 $slnags = $this->database->get_records_sql("SELECT {customfield_field}.name FROM {customfield_field} JOIN {customfield_data} ON {customfield_data}.fieldid = {customfield_field}.id JOIN {course} ON {course}.id = {customfield_data}.instanceid WHERE {customfield_data}.value = 1  AND {customfield_field}.categoryid = 2  AND {course}.visible = 1 AND {course}.id = :courseid GROUP BY {customfield_field}.name ", array('courseid' => $courseid));
		 if (!empty($slnags)) {
		 	foreach ($slnags as $key => $value) {
		 		array_push($clangs, $value->name);
		 	}
		 }
		 if (empty($clangs)) {
		 	return 'English';
		 }
		 return implode(', ', $clangs);
	}

	private function course_image ($courseid) {
        global $CFG;
        $courserecord = $this->database->get_record('course', array('id' => $courseid));
        $course = new \core_course_list_element($courserecord);
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            if ($isimage) {
                return $url;
            } else {
                return $CFG->wwwroot.'/local/catalogue/pix/noimage.png';
            }
        }
    }

 	private function preminum_instance($courseid, $p = array()){
 		if (!is_null($courseid)) {
 			$prices = $this->database->get_records_sql("SELECT CONCAT({enrol}.currency, ' ' , {enrol}.cost ) as price  FROM {enrol} WHERE  {enrol}.cost <> '' AND  {enrol}.courseid = :courseid ", array('courseid' => $courseid ));
 			if ($prices) {
 				foreach ($prices as $price) {
 					array_push($p, $price->price);
 				}		
 			}		
 		}
 		return implode(' | ', $p);	
 	}   

}