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


class Affiliation
{
	

	private $database;
	private $loggeduser;
	private $course;
	private $affiliation_table = 'local_affiliation';

	function __construct($course = null){

		global $DB, $USER;
		
		$this->database 	= $DB;
		$this->loggeduser 	= $USER;
		$this->course 		= $course;

	}

	/**
	 * @method return status of enrolled users.
	 * @param course and user
	 * @return Boolean.
	 * */

	public function is_user_enroled($courseid){
		global $USER;
		$coursecontext =  \context_course::instance($courseid);
		return is_enrolled($coursecontext, $USER);
	}

	/**
	 * @method change link status.
	 * 
	 * */
	public function set_link_status(){
		echo "HEEEEE";
	}

	private function get_the_course( $courseid = null ){
		if (is_null($courseid)) {
			$this->course = null;
		}
		$this->course = $this->database->get_record('course' , array( 'id' => $courseid ) );
	}

	protected function check_affiliation_link(){
		if (!empty($_GET) && isset(array_keys($_GET)[0]) ) {
			return  $this->course_view_link(array_keys($_GET)[0]);
		}
		return false;
	}

	/**
	 * @method check page count.
	 * */

	private function course_view_link($md5){
		if ($course = $this->get_the_course_from_md5_data($md5)) {
			return 'view.php?id='.$course->course_id.'&sharedid='.$md5;
		}
		return false;
	}

	private function get_the_course_from_md5_data($md5) : object {
		if ($this->database->record_exists($this->affiliation_table, array('course_generated_url' => $md5 ))) {
			return $this->database->get_record($this->affiliation_table, array('course_generated_url' => $md5 ));
		}
		return false;
	}

	protected  function encode_course_view_data_in_base64($courseid = null){
	
		$this->get_the_course($courseid);
		$md5 = $this->generate_md5_for_course($courseid);
		$this->save_generated_links($md5);
		return $this->generate_link($md5);

	}

	private function generate_link($md5) : ?string {
		global $CFG;
		return $CFG->wwwroot.'/local/affiliation/?'.$md5;	
	}

	private function generate_md5_for_course($courseid) : string {
		return md5($courseid);
	}

	protected function save_generated_links(string $generated_url = null) : int {

		if (!is_null($this->course) && is_object($this->course) && !is_null($generated_url) && is_string($generated_url) ) {
			
		$affiliation_link 			   			= new \stdClass;
		$affiliation_link->course_name 			= $this->course->fullname;
		$affiliation_link->course_id 			= $this->course->id;
		$affiliation_link->user_id 				= $this->loggeduser->id;
		$affiliation_link->course_generated_url = $generated_url;
		$affiliation_link->view_count			= 0;
		$affiliation_link->generated_at 		= time();

		if (!$this->check_for_the_enrolment_options()) {
			$this->add_self_enrolment_instance();
		}

		return $this->database->insert_record($this->affiliation_table, $affiliation_link);
		
		}

		return 0;
	}

	private function generate_random_password_for_self_enrolment(){
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $pass = array(); 
	    $alphaLength = strlen($alphabet) - 1; 
	    for ($i = 0; $i < 8; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass);
	}

	protected function check_for_the_enrolment_options(){
		if (is_null($this->course->id)) {
			return false;
		}

		if ($this->database->record_exists_sql("SELECT *  FROM {enrol} WHERE enrol LIKE 'self' AND password IS NOT NULL AND courseid = :courseid", array('courseid' => $this->course->id ))) {
			return $this->database->get_record_sql("SELECT *  FROM {enrol} WHERE enrol LIKE 'self' AND password IS NOT NULL AND courseid = :courseid", array('courseid' => $this->course->id ));
		}

		return false;
	}

	private function add_self_enrolment_instance () {
		
		$fields['expirynotify'] = 1;
        $fields['notifyall'] = 1;        

        if ($this->course->id == SITEID) {
            throw new coding_exception('Invalid request to add enrol instance to frontpage.');
        }

        $course = $this->course;

        $instance = new \stdClass();
        $instance->enrol 		  = 'self';
        $instance->name           = $this->course->fullname.' '.get_string('pluginname', 'local_affiliation');
        $instance->status         = ENROL_INSTANCE_ENABLED;
        $instance->courseid       = $course->id;
        $instance->enrolstartdate = 0;
        $instance->enrolenddate   = 0;
        $instance->roleid 		  = 5;	
        $instance->timemodified   = time();
        $instance->timecreated    = $instance->timemodified;
        $instance->password 	  = $this->generate_random_password_for_self_enrolment();
        $instance->sortorder      = $this->database->get_field('enrol', 'COALESCE(MAX(sortorder), -1) + 1', array('courseid'=>$course->id));

        $fields = (array)$fields;
        unset($fields['enrol']);
        unset($fields['courseid']);
        unset($fields['sortorder']);

        foreach($fields as $field=>$value) {
            $instance->$field = $value;
        }

        $instance->id = $this->database->insert_record('enrol', $instance);

        \core\event\enrol_instance_created::create_from_record($instance)->trigger();

        return $instance->id;
    }

    public function enrol_me($instance, $password) {
    	$data = new \stdClass;
    	$data->enrolpassword = $password;
    	$this->enrol_user_in_course($instance, $data);
    }

    private function enrol_user_in_course( $instance, $data = null) {

        global $USER, $CFG;

        if ($instance->password && !isset($data->enrolpassword)) {
            return;
        }

        $timestart = time();
        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        } else {
            $timeend = 0;
        }

        if (!enrol_is_enabled('self')) {
	        return false;
	    }

	    if (!$enrol = enrol_get_plugin('self')) {
	        return false;
	    }

        $enrol->enrol_user($instance, $USER->id, $instance->roleid, $timestart, $timeend);

        \core\notification::success(get_string('youenrolledincourse', 'enrol'));

        // Send welcome message.
        if ($instance->customint4 != ENROL_DO_NOT_SEND_EMAIL) {
            try {
            	$enrol->email_welcome_message($instance, $USER);
            } catch (Exception $e) {
            	return $e->getMessage();
            }
        }
        return true;
    }

    public function check_for_valid_share(){
    	if (!empty($_GET) && isset($_GET['id']) && isset($_GET['sharedid']) ) {
    		if ($md5 = $_GET['sharedid']) {
    			return $this->get_the_course_from_md5_data($md5);
    		}
    	}
    	return false;
    }

    public function enrol_me_in_course($courseid, $shareid) {
    	$this->get_the_course($courseid);
    	
    	if ($instance = $this->check_for_the_enrolment_options()) {
    		return $this->enrol_me($instance, $instance->password);
    	}

    }

}