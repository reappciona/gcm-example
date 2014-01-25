<?php
/**
 * @package		appkr/gcm-example
 * @file		/config.php
 * @author		Juwon Kim <juwonkim@me.com>
 * @brief		configuration
 */

date_default_timezone_set('Asia/Seoul');

//if set to TRUE, send gcm and get response from google server
//but clients do not receive gcm message actually. namely simulation mode.
$dry_run = false;

//application log config
$log_config = array(
	'turn'	=> true, //TRUE to save application log, FALSE not to save.
	'level'	=> 2 //1 error, 2 all
);

//project name
//ex. $project = 'gcm-example';
$project = 'YOUR_PROJECT_NAME_HERE';

//list of return message languages (in response to api call)
//corresponding strings_xx.php file must be under /api/ folder
$list_of_languages = array(
	'ko'	=> '한국어',
	'en'	=> 'English'
);

//for the situation in which $Gcm::log method cannot save logs safely
//the api & Gcm class tries to send email to say so.
$email_from = 'no-reply@'.$_SERVER['HTTP_HOST'];
$email_to = 'YOUR_EMAIL_ADDRESS';
$email_cc = 'CCED_EMAIL_ADDRESS';//leave it blank if not needed.

//get execution environment
//here the production env is the pagodabox.com
//ex. $production = (in_array($_SERVER['HTTP_HOST'], array('192.168.11.1', 'promote.airplug.com')))
//		? true
//		: false;
$production = (in_array($_SERVER['HTTP_HOST'], array('YOUR_PRODUCTION_SERVER_IP_ADDRESS', 'YOUR_PRODUCTION_SERVER_DOMAIN_NAME_WITH_HOST_NAME')))
		? true
		: false;

//get one from https://code.google.com/apis/console
//ex. $google_api_key = 'AIzaSyDKcUwUTMCkX4I7xcajhwtKviOYVUTqgbk';
$google_api_key = 'YOUR_API_KEY_HERE';
$google_gcm_url = 'https://android.googleapis.com/gcm/send';//leave it as it is

//database info
$db_config = ($production === true)
	? array( //production
		'host'	=> 'YOUR_MYSQL_HOST',
		'user'	=> 'YOUR_DB_USER_NAME',
		'pass'	=> 'YOUR_DB_USER_PASS',
		'db'	=> 'YOUR_DB_NAME'
	)
	: array( //dev
		'host'	=> 'localhost',
		'user'	=> 'YOUR_DB_USER_NAME',
		'pass'	=> 'YOUR_DB_USER_PASS',
		'db'	=> 'YOUR_DB_NAME'
	);

//table info
//IT'S YOUR CHOICE TO CHANGE TABLE NAME OR NOT
//IN SOME SITUATION, users OR logs NAME WAS ALREADY TAKEN
//THEN, CHANGE THESE VALUE AND MODIFY sql/gcm-example_yyyy-mm-dd.sql BEFORE IMPORTING IT.
$table = 'users';
$log_table = 'logs';

//set debug options
if ($production) {
	error_reporting(0);
	ini_set('display_errors', 0);
}
?>
