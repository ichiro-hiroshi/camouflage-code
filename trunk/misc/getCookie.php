<?php

/*

[how to use]

<html>
<body>
<script type='text/javascript' src='http://HOST/PATH/getCookie.php'></script>
</body>
</html>

*/

define('SELF', "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}");

function fingerPrint($in_prefix = '')
{
	$elems = array('REMOTE_ADDR', 'HTTP_USER_AGENT', 'HTTP_ACCEPT_LANGUAGE',);
	foreach ($elems as $elem) {
		$in_prefix .= $_SERVER[$elem];
	}
	return md5($in_prefix);
}

function js1($in_cookie)
{
	$SELF = SELF;
	print <<<EOJS
(function() {
	var target = document.getElementsByTagName('SCRIPT').item(0);
	var makeScript = function(in_src) {
		var ret = document.createElement('SCRIPT');
		ret.type = 'text/javascript';
		ret.src = in_src;
		return ret;
	};
	window.setTimeout(function() {
		if (window.getCookie) {
			return;
		}
		target.parentNode.insertBefore(makeScript('{$SELF}?cookie={$in_cookie}'), target);
	}, 1000);
	target.parentNode.insertBefore(makeScript('{$SELF}?cookie='), target);
})();
EOJS;
}

function js2($in_cookie)
{
	print <<<EOJS
(function() {
	if (!window.getCookie) {
		window.getCookie = {};
	}
	window.getCookie.cookie = '{$in_cookie}';
	alert(window.getCookie.cookie);
})();
EOJS;
}

date_default_timezone_set('Asia/Tokyo');

if ($_SERVER['QUERY_STRING']) {
	$params = explode('&', $_SERVER['QUERY_STRING']);
	$fv = explode('=', $params[0]);
	if (count($fv) != 2) {
		print '// error';
		exit;
	}
	$fname = fingerPrint($fv[0]);
	if ($fv[1]) {
		/* 1st-2 */
		$fh = fopen($fname, 'w+');
		fwrite($fh, $fv[1]);
		fclose($fh);
		header("Location: " . SELF . "?{$fv[0]}=");
	} else {
		if (is_file($fname)) {
			/* 1st-3 */
			$fh = fopen($fname, 'r');
			header('Content-Type: text/javascript');
			header('Last-Modified: ' . date(DATE_RFC822));
			header('X-Finger-Print: ' . $fname);
			js2(fread($fh, filesize($fname)));
			fclose($fh);
			unlink($fname);
		} else {
			/* 2nd, 3rd, ... */
			header("HTTP/1.1 304 Not Modified");
		}
	}
} else {
	/* 1st-1 */
	header('Content-Type: text/javascript');
	js1(fingerPrint(rand()));
}

?>
