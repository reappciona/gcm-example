<?php
/**
  * @package	gcm-example
  * @file		/apidoc.php
  * @author		Juwon Kim <juwonkim@me.com>
  * @brief		api document
  */

require(__DIR__.'/./config.php');

$doc_title = $project.' :: api doc';

$content =<<<EOT
		<div class="container">
			<div class="page-header">
				<h3>{$doc_title}</h3>
				<p class="text-muted">Reference implementation of Google Cloud Messaging - 3rd application server in php and Android client in java.</p>
			</div>

			<h4 class="text-info">Overview</h4>
			<p>
				GCM is an abbreviation of Google Cloud Messaging, powered by Google, that enables send a push message to a selected group of Android clients.<br/>
				The apis below are the functions that a '3rd party application server(let's call it as "the SERVER")' must provide so the whole GCM process to work properly.
			</p>
			<p class="text-center">
				<a href="http://developer.android.com/google/gcm/index.html" target="_blank">
				<img src="http://developer.android.com/images/gcm/gcm-logo.png" class="img-responsive" style="margin-left:auto; margin-right:auto;"/><br/>
				Move to Google Guide</a>
			</p>
			<p>
				A GCM client can call 'register' and 'unregister' api with <code>registration_id</code> that was returned from Google. Upon client's api call "the SERVER" save or delete a record to its database. <br/>
			<pre>
GCM client --register/unregister--> "the Server"
			</pre>
				With this records, the 'api test tool' can call 'send' api to actully push messages.<br/>
			<pre>
'api test tool' --send--> "the SERVER"
			</pre>
				Upon 'api test tool' request, "the SERVER" then makes a list of receipients and posts a request to 'GCM Connection Server'. With the 'GCM Connection Server' response, "the SERVER" does some records management job also.
			<pre>
"the SERVER" --HTTP POST--> 'GCM Connection Server'
			</pre>
			</p>

			<div class="clearfix">&nbsp;</div>

			<h4 class="text-info">Request</h4>
			<div class="alert alert-warning">
				<small calss="text-muted">API end point :</small><br/>
				<strong>http(s)://{$_SERVER['HTTP_HOST']}/{$project}/api/</strong>
			</div>
			<blockquote>
				<ul class="list-unstyled">
					<li><strong>HTTP POST</strong></li>
					<li><strong>@param [string] query</strong> register/unregister</li>
					<li><strong>@param [strong] package</strong> the Android client package name</li>
					<li><strong>@param [string] uuid</strong> 16byte androidID</li>
					<li><strong>@param [string] gcmID</strong> 162byte <code>registration_id</code> 	</li>
					<li><strong>@param [string] l(lowercase alpha)</strong> ko/en (default en)</li>
					<li><strong>@param [int] v(lowercase alpha)</strong> api version</li>
				</ul>
			</blockquote>

			<div class="clearfix">&nbsp;</div>

			<h4 class="text-info">Response</h4>
			<blockquote>
				<ul class="list-unstyled">
					<li>{"code":200,"message":"Success"}</li>
					<li>{"code":201,"message":"%d record(s) inserted."}</li>
					<li>{"code":202,"message":"%d record(s) updated."}</li>
					<li>{"code":501,"message":"Duplicate entry."}</li>
					<li>{"code":502,"message":"o matching entry."}</li>
					<li>{"code":503,"message":"Send failed. Try again after cheching the Internet connection."}</li>
					<li>{"code":504,"message":"Database error: %s"}</li>
					<li>{"code":505,"message":"Missing argument(s)."}</li>
					<li>{"code":506,"message":"Illegal call."}</li>
				</ul>
			</blockquote>

			<div class="clearfix">&nbsp;</div>

			<h4 class="text-info">Example</h4>
			<pre>
Request Headers:
	POST /gcm/ HTTP/1.1
	Host: promote.airplug.com

Form Data:
	query:register
	package:com.airplug.abc.agent
	uuid:65016cxxx55cxxx3
	gcmID:APA91...bCq
	l:ko
	v:1

Response:
	{"code":501,"message":"Duplicate entry."}
			</pre>

			<div class="clearfix">&nbsp;</div>

			<h4 class="text-info">API Security</h4>
			<p>Not implemented. If somebody out there, who has an idea or experience on this topic, you are more than welcomed to pork it and do some contribution.</p>

			<div class="clearfix">&nbsp;</div>

			<div class="btn-group pull-right">
				<a href="./" class="btn btn-default" target="_top">Go back to api test tool</a>
			</div>

			<div class="clearfix">&nbsp;</div>

		</div>
		<hr/>\n
EOT;

include(__DIR__.'/./layout/master.php');
exit();
?>

