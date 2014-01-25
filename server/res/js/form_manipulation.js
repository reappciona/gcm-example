/**
 * @package		appkr/gcm-example
 * @file		/res/js/form_manipulation.js
 * @author		Juwon Kim <juwonkim@me.com>
 */

var package = document.getElementById('package');
var counter = document.getElementById('counter');
var only = document.getElementById('only');
var uuid = document.getElementById('uuid');
var ticker = document.getElementById('ticker');
var title = document.getElementById('title');
var message = document.getElementById('message');

var packageHolder = document.getElementById('packageHolder');
var uuidHolder = document.getElementById('uuidHolder');
var tickerHolder = document.getElementById('tickerHolder');
var titleHolder = document.getElementById('titleHolder');
var messageHolder = document.getElementById('messageHolder');

function disable () {
	for (ii=0; ii<arguments.length; ii++) {
		arguments[ii].disabled = true;
	}
	return;
};

function enable() {
	for (ii=0; ii<arguments.length; ii++) {
		arguments[ii].disabled = false;
	}
	return;
};

function hide() {
	for (ii=0; ii<arguments.length; ii++) {
		arguments[ii].style.display = 'none';
	}
	return;
};

function show() {
	for (ii=0; ii<arguments.length; ii++) {
		arguments[ii].style.display = 'block';
	}
	return;
};

function formToggle() {
	var chk = only.checked

	if(chk) {
		counter.innerHTML = ' <span class="glyphicon glyphicon-user"></span> 1 receiver ';
		show(uuidHolder);
		enable(uuid);
	} else {
		hide(uuidHolder);
		disable(uuid);
		getReceiverCount();
	}
	return;
}

function getErrorDiv(ss) {
	for (var ii=0; ii<ss.childNodes.length; ii++) {
		if (ss.childNodes[ii].className == 'errorDiv')
			return ss.childNodes[ii]; break;
	}
	return;
}

function markError (node, nodeHolder, strReplace) {
	var markup = '<div class="errorDiv"><small class="text-danger"><span class="glyphicon glyphicon-question-sign"></span> '+strReplace+'</small></div>';
	nodeHolder.className = 'form-group has-error';
	node.insertAdjacentHTML('afterend', markup);
	return;
}

function fixClass (nodeHolder) {
	nodeHolder.className = 'form-group';
	if (document.getElementsByClassName('errorDiv') !== undefined) {
		var elem = document.getElementsByClassName('errorDiv');
		while (elem[0]) {
			elem[0].parentNode.removeChild(elem[0]);
		}
	}
	return;
}

function validation() {
	var valid = false;

	if (package.value == '') {
		markError(package, packageHolder, 'Select a package');
		valid = false;
	} else {
		fixClass(packageHolder);
		valid = true;
	}

	if (only.checked && uuid.value.length < 15) {
		markError(uuid, uuidHolder, 'Invalid "uuid" value');
		valid = false;
	} else {
		fixClass(uuidHolder);
		valid = true;
	}

	if (ticker.value == '') {
		markError(ticker, tickerHolder, '"ticker" value id required');
		valid = false;
	} else {
		fixClass(tickerHolder);
		valid = true;
	}

	if (title.value == '') {
		markError(title, titleHolder, '"title" value id required');
		valid = false;
	} else {
		fixClass(titleHolder);
		valid = true;
	}

	if (message.value == '') {
		markError(message, messageHolder, '"message" value id required');
		valid = false;
	} else {
		fixClass(messageHolder);
		valid = true;
	}

	if (valid === false) {
		return false;
	} else {
		if (document.getElementsByClassName('errorDiv') !== undefined) {
			var elem = document.getElementsByClassName('errorDiv');
			while (elem[0]) {
				elem[0].parentNode.removeChild(elem[0]);
			}
		}
	}

	var serializedData = $('#gcmForm').serialize();

	$.post('./api/', serializedData, function(response) {
		if (response) {
			$('#resDiv').show();
		}

		$('#res')
			.empty()
			.html(JSON.stringify(response));
	});
}

function getReceiverCount() {
	var receiverCount = [];

	$.get('./?app=xhr', function(response) {
		if (response.results == 'error') {
			counter.innerHTML = ' error ';
			return;
		} else {
			var iter = response.results.length;
			$.each(response.results, function(iter, output) {
				receiverCount.push([output.package, output.cnt]);
			});
		}

		for (ii=0; ii<receiverCount.length; ii++) {
			if (receiverCount[ii][0] == package.value) {
				counter.innerHTML = ' <span class="glyphicon glyphicon-user"></span> '+receiverCount[ii][1]+' receiver(s) ';
				break;
			}
		}
	});
}

formToggle();
getReceiverCount();