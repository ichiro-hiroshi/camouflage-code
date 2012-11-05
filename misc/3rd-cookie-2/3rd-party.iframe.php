<?php

function receivedCookie()
{
	$ret = array();
	foreach ($_COOKIE as $key => $val) {
		array_push($ret, "({$key}, {$val})");
	}
	if (count($ret) > 0) {
		return implode(', ', $ret);
	} else {
		return '(not-received)';
	}
}

function response_xhr()
{
	setcookie('from', 'response_xhr');
	print receivedCookie();
}

function response_iframe()
{
	$cookie = receivedCookie();
	setcookie('from', 'response_iframe');
	print <<<EOC
<html>
<body>
<p>server-received cookie (iframe) : {$cookie}</p>
<p>server-received cookie (xhr) : <span id='xhr'></span></p>
<script>
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g, '');
}
var cookies = document.cookie.split(';')
alert('['+document.cookie+']');
var q = '';
for (var i = 0; i < cookies.length; i++) {
	if (q) {
		q += '&';
	}
	q += cookies[i].trim();
}
var xhr = new XMLHttpRequest();
xhr.open('GET', 'http://test2.localhost.co.jp/test-env/20121102-Expires/3rd-party.iframe.php?xhr=on&' + q, true);
xhr.onreadystatechange = (function(in_xhr) {
	return function() {
		if (in_xhr.readyState != 4) {
			return;
		}
		var el = document.getElementById('xhr');
		if (in_xhr.status == 200) {
			el.innerHTML = in_xhr.responseText;
		} else {
			el.innerHTML = 'invalid-status (' + in_xhr.status + ')';
		}
	};
})(xhr);
xhr.send();
</script>
</body>
</html>
EOC;
}

if (array_key_exists('xhr', $_GET)) {
	header('Content-Type: text/javascript');
	response_xhr();
} else {
	header('Content-Type: text/html');
	response_iframe();
}

exit;

?>
