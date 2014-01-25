<?php
/**
  * @package	appkr/gcm-example
  * @file		/index.php
  * @author		Juwon Kim <juwonkim@me.com>
  * @brief		api test tool
  */

extract($_GET);

require(__DIR__.'/./config.php');
require(__DIR__.'/./functions/commons.php');
require(__DIR__.'/./functions/Database.php');

$db = new Database($db_config);

//to present the number of receivers when a user change the 'package' or 'only' form element
//this api test tool makes xhr request to itself with 'app=xhr' query.
//on receiving the xhr request this tool page response json format statistics data
if ($app == 'xhr') {
	$query = "SELECT package, count(package) AS cnt
		FROM {$table}
		WHERE gcmID IS NOT NULL
		GROUP BY package";
	$receiver_count = $db->queryTable($query);

	if ($receiver_count) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array(
			'results' => $receiver_count
		));
	} else {
		echo json_encode(array(
			'results' => 'error'
		));
	}

	exit();
}

//get package list and make select 'package' options elements
$query = "SELECT DISTINCT(package)
	FROM {$table}
	ORDER BY package ASC";
$list_of_packages = $db->queryTable($query);

foreach($list_of_packages as $e) {
	$options_of_packages .= ($e['package'] == 'kr.appkr.gcm-example')
		? "<option value=\"{$e['package']}\" selected=\"selected\">{$e['package']}</option>"
		: "<option value=\"{$e['package']}\">{$e['package']}</option>\n";
}

foreach($list_of_languages as $k => $v) {
	$options_of_languages .= ($k == 'en')
		? "<option value=\"{$k}\" selected=\"selected\">{$v}</option>"
		: "<option value=\"{$k}\">{$v}</option>\n";
}

//when this tool was called from gcm-example client
//this tool manipulate some form value to send message only to her/himself
$only_value = cv($only)
	? 'checked="checked"'
	: null;

$uuid_value = cv($uuid)
	? "value=\"{$uuid}\""
	: null;

$doc_title = $project.' :: api test tool';

$content =<<<EOT
		<div class="container">
			<div class="page-header">
				<h3>{$doc_title}</h3>
				<p class="text-muted">
					Reference implementation of Google Cloud Messaging - 3rd application server in php and Android client in java.<br/>
					With this api test tool, you can send gcm message to a selected group of gcm clients. For 'register' or 'unregister' api call test, use the Android apk files that is bundled with this project. Check <a href="./apidoc.php">the api doc</a> for available apis. <strong class="text-danger">Do not try to abuse this api test tool.</strong>
				</p>
			</div>

			<form name="gcmForm" id="gcmForm" action="./api/" method="post" role="form" class="form-horizontal" onSubmit="validation(); return false;">

				<input name="v" type="hidden" value="1"/>
				<input name="query" type="hidden" value="send"/>

				<div class="form-group" id="languageHolder">
					<label for="language" class="col-sm-2 control-label"> language</span></label>
					<div class="col-sm-2">
						<select name="l" id="language" class="form-control">
							{$options_of_languages}
						</select>
					</div>
				</div>

				<div class="form-group" id="packageHolder">
					<label for="package" class="col-sm-2 control-label"> package</span></label>
					<div class="col-sm-2">
						<select name="package" id="package" class="form-control" onChange="getReceiverCount();">
							{$options_of_packages}
						</select>
					</div>

					<div class="checkbox col-sm-8">
						<label>
							<input type="checkbox" name="only" id="only" onClick="formToggle();" {$only_value}/> Specify a receiver
							<div class="badge" id="counter"> <span class="glyphicon glyphicon-user"></span> 0 receiver(s) </div>
						</label>
					</div>
				</div>

				<div class="form-group" id="uuidHolder" style="display:none;">
					<label for="uuid" class="col-sm-2 control-label"> uuid</label>
					<div class="col-sm-8">
						<input type="text" name="uuid" id="uuid" class="form-control" placeholder="An androidID to which you want to send A gcm message" {$uuid_value}/>
					</div>
				</div>

				<div class="form-group" id="tickerHolder">
					<label for="ticker" class="col-sm-2 control-label">ticker</label>
					<div class="col-sm-8">
						<input type="text" name="ticker" id="ticker" class="form-control" placeholder="Strings for notification area"/>
					</div>
				</div>

				<div class="form-group" id="titleHolder">
					<label for="title" class="col-sm-2 control-label">title</label>
					<div class="col-sm-8">
						<input type="text" name="title" id="title" class="form-control" placeholder="The title of gcm message"/>
					</div>
				</div>

				<div class="form-group" id="messageHolder">
					<label for="message" class="col-sm-2 control-label">message</label>
					<div class="col-sm-8">
						<textarea name="message"  id="message"class="form-control" placeholder="The body of the message"></textarea>
					</div>
				</div>

				<div class="form-group" id="dryrunHolder">
					<label for="package" class="col-sm-2 control-label"> dryrun</span></label>
					<div class="checkbox col-sm-10">
						<label>
							<input type="checkbox" name="dry-run" id="dryrun""/>
							<span class="help-block">If checked, send gcm and get response from google server,
							but clients do not receive gcm message actually. That is to say, it's a simulation mode.</span>
						</label>
					</div>
				</div>

				<div class="clearfix">&nbsp;</div>

				<div class="btn-group pull-right">
					<button type="submit" class="btn btn-primary"> Submit </button>
					<a  href="./" class="btn btn-default"> Start over </a>
					<a  href="./apidoc.php" class="btn btn-default"> View api doc </a>
				</div>

				<div class="clearfix">&nbsp;</div>

			</form>
		</div>\n
EOT;

$content .=<<<EOT
		<div class="container"><hr/></div>

		<div class="container" id="resDiv" style="display:none;">

			<h3 class="text-center text-muted">Response</h3>

			<section id="res"></section>

		</div>\n
EOT;

include(__DIR__.'/./layout/master.php');

exit();
?>


