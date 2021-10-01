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

namespace local_affiliation\eventgroups;

defined('MOODLE_INTERNAL') || die();

use local_affiliation\util\Affiliation;

class observers {

    public static function course_viewed() {
        global $OUTPUT, $COURSE, $USER, $CFG;
        $data = new \stdClass;
        $data->enrolurl = $CFG->wwwroot.'/local/affiliation/enrol-self-vai-share.php';
        $data->reloadcrs = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id;

        $is_admin = false;

        $affiliation = new Affiliation($COURSE);

        if (!empty( $admins = get_admins())) {
            $ids = array_map( fn($v): int => $v->id, $admins);
        }

        if (!empty($ids) && in_array($USER->id, $ids) ) {
            $is_admin = true;
        }

        // If course is shared....
        // Show the modal message...
        if ($affiliation->check_for_valid_share() && $COURSE->id != SITEID ) {
            // Check if user is logged or not.
            if (isguestuser()) {
                // User is not logged in ... Show message to login.
                // Hide Enrol button..
                $data->showenrolbutton = false;
                echo $OUTPUT->render_from_template('local_affiliation/courseviewpopup', $data);
            }

            if (!isguestuser() && isloggedin() && !$is_admin && !$affiliation->is_user_enroled($COURSE->id) ) {
                // User is logged in but not admin...
                // Show Enrol button also.
                $data->showenrolbutton = true;
                echo $OUTPUT->render_from_template('local_affiliation/courseviewpopup', $data);
            }
        }
    }
}