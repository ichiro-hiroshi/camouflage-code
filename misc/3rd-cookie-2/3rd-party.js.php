<?php

function js_1st()
{
	print <<<EOJS
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g, '');
}

var makeScript = function(in_src) {
	var ret = document.createElement('SCRIPT');
	ret.type = 'text/javascript';
	ret.src = in_src;
	return ret;
};

var cookies = document.cookie.split(';')
var q = '';
for (var i = 0; i < cookies.length; i++) {
	if (q) {
		q += '&';
	}
	q += cookies[i].trim();
}
var js = 'http://test2.localhost.co.jp/test-env/20121102-Expires/3rd-party.js.php';
if (q) {
	var script = makeScript(js + '?beacon=true&' + q);
} else {
	var script = makeScript(js + '?set');
}
var target = document.getElementsByTagName('SCRIPT').item(0);
target.parentNode.insertBefore(script, target);
EOJS;
}

function js_set()
{
	date_default_timezone_set('Asia/Tokyo');
	$date = date(DATE_RFC822, time() + 60 * 60 * 24 * 365);
	setcookie('browser_id_http', 'js_set');
	header('X-Test: fuga');
	$cookie = md5(rand());
	print <<<EOJS
document.cookie = 'browser_id={$cookie}; expires={$date}; path=/';
window.setTimeout(function() {
	alert(document.cookie);
}, 1000);
EOJS;
}

function js_beacon()
{
	setcookie('browser_id_http', 'js_beacon');
	$ret = '';
	foreach ($_GET as $key => $val) {
		$ret .= "({$key}, {$val}), ";
	}
	foreach ($_COOKIE as $key => $val) {
		$ret .= "({$key}, {$val}), ";
	}
	print <<<EOJS
alert('{$ret}');
EOJS;
}

header('Content-Type: text/javascript');

if (array_key_exists('set', $_GET)) {
	js_set();
} else {
	if (array_key_exists('beacon', $_GET)) {
		js_beacon();
	} else {
		js_1st();
	}
}

exit;

?>
