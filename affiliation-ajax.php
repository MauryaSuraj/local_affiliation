<?php
/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('AJAX_SCRIPT', true);

/**
 * NO_DEBUG_DISPLAY - disable moodle specific debug messages and any errors in output
 */
define('NO_DEBUG_DISPLAY', false);

/**
 * NO_MOODLE_COOKIES - no cookies with web service
 */
define('NO_MOODLE_COOKIES', true);

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (empty($_POST)) {
    die('Direct access not permitted');
}

require_once('../../config.php');
require_once __DIR__.'/lib.php';

/**
 * 
 * Create Link Here.
 * 
 * */

if (isset($_REQUEST['courseid']) && $_REQUEST['courseid'] != "" ) {
    echo json_encode(array('url' => AffiliationRender::crsbase64($_REQUEST['courseid']) ));
}