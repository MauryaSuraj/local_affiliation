<?php
/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('AJAX_SCRIPT', true);

/**
 * NO_DEBUG_DISPLAY - disable moodle specific debug messages and any errors in output
 */
define('NO_DEBUG_DISPLAY', true);

/**
 * NO_MOODLE_COOKIES - no cookies with web service
 */
define('NO_MOODLE_COOKIES', false);

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (empty($_POST)) {
    die('Direct access not permitted');
}

require_once('../../config.php');
require_once __DIR__.'/lib.php';

use local_affiliation\util\Affiliation;

if (!empty($_REQUEST) && isset($_REQUEST['courseid']) && isset($_REQUEST['shareid']) && $_REQUEST['shareid'] ) {
	$affiliation = new Affiliation;
	$affiliation->enrol_me_in_course($_REQUEST['courseid'], $_REQUEST['shareid']);
	echo json_encode( array('reload' => 1) );
}