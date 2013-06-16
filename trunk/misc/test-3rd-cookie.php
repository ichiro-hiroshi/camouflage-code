<?php

date_default_timezone_set('UTC');

define('DOMAIN1', 'test1.localhost.co.jp');
define('DOMAIN2', 'localhost');
define('LOGFILE', 'log.txt');

function scriptName()
{
	$e = explode('/', $_SERVER['SCRIPT_NAME']);
	return $e[count($e) - 1];
}

function getInclusionPath($in_file)
{
	$domain = array(DOMAIN1, DOMAIN2);
	if (in_array($_SERVER['HTTP_HOST'], $domain)) {
		$inclusionDomain = ($_SERVER['HTTP_HOST'] == $domain[0]) ? $domain[1] : $domain[0];
	} else {
		$inclusionDomain = $domain[0];
	}
	$path = "http://{$inclusionDomain}{$_SERVER['SCRIPT_NAME']}";
	return preg_replace('/\/[^\/]*$/', "/{$in_file}", $path);
}

function getInclusionHTML()
{
	$name = scriptName();
	$type = array_key_exists('type', $_GET) ? $_GET['type'] : 'img';
	$inclusion = getInclusionPath("{$name}?test=inclusion&type={$type}&rand=" . rand());
	$html = array(
		'script' => "<script type='text/javascript' src='{$inclusion}'></script>",
		'img' => "<img src='{$inclusion}' onload='alert(1);'>"
	);
	return $html[$type];
}

function putlog($txt)
{
	$h = fopen(LOGFILE, 'a+');
	fwrite($h, date(DATE_RFC822) . " [{$txt}]\n");
	fclose($h);
}

if (array_key_exists('test', $_GET) && ($_GET['test'] == 'inclusion')) {
	$flg = 'T';
	$received = '(cookie does not exist in request header)';
	if (array_key_exists('update', $_COOKIE) && array_key_exists('flg', $_COOKIE)) {
		$received = "{$_COOKIE['flg']}-{$_COOKIE['update']}";
		putlog($received);
		if ($_COOKIE['flg'] == $flg) {
			$flg = 'F';
		}
	}
	setcookie('flg', $flg, time() + 3600);
	setcookie('update', date(DATE_RFC822), time() + 3600);
	switch ($_GET['type']) {
	case 'img' :
		$dat = array(
			0x47,0x49,0x46,0x38,0x39,0x61,0x01,0x00,
			0x01,0x00,0x80,0x00,0x00,0x00,0x00,0x00,
			0xff,0xff,0xff,0x21,0xf9,0x04,0x01,0x00,
			0x00,0x00,0x00,0x2c,0x00,0x00,0x00,0x00,
			0x01,0x00,0x01,0x00,0x00,0x02,0x01,0x44,
			0x00,0x3b
		);
		$gif = '';
		for ($i = 0; $i < count($dat); $i++) {
			$gif .= pack('C', $dat[$i]);
		}
		header('Content-Type: imag/gif');
		print $gif;
		break;
	case 'script' :
	default :
		header('Content-Type: text/javascript');
		print "document.write('{$received}');";
		break;
	}
	exit;
} else {
	// header('Content-Type: text/plain');
	$html = getInclusionHTML();
	$log = LOGFILE;
	print <<<EOC
{$html}
<div id='viewLog'></div>
<script>
window.setTimeout(function() {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = (function(self) {
		return function() {
			if ((self.readyState != 4) || (self.status != 200)) {
				return;
			}
			var div = document.getElementById('viewLog');
			var lines = self.responseText.split("\\n");
			var cnt = 0;
			var last = null;
			for (var i = 0; i < lines.length; i++) {
				if (lines[i]) {
					cnt++;
					last = lines[i];
				}
			}
			div.appendChild(document.createTextNode('received : ' + cnt + ' : ' + last));
		};
	})(xhr);
	xhr.open('GET', '{$log}?r=' + Math.random() , true);
	xhr.send();
}, 500);
</script>
EOC;
}

?>
