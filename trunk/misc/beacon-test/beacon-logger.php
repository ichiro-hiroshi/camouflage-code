<?php

define('SELF', substr(str_replace(__DIR__, '', __FILE__), 1));
define('LOG', SELF . '.txt');
/*
	sig=n : signature
*/
define('SIG', array_key_exists('sig', $_GET) ? $_GET['sig'] : 0);
/*
	mod=i : get image
	mod=l : get log
	mod=u : get utility
*/
define('MOD', array_key_exists('mod', $_GET) ? $_GET['mod'] : 'i');

date_default_timezone_set('UTC');

function responsePNG($in_size = 10)
{
	$w = $in_size;
	$h = $in_size;
	header('Cache-Control: no-store');
	header('Content-Type: image/png');
	$image = imagecreatetruecolor($w, $h);
	$color = imagecolorallocate($image, 255, 0, 0);
	imagefilledrectangle($image, 0, 0, $w, $h, $color);
	imagepng($image);
	imagedestroy($image);
}

function userAgent()
{
	$ua = strtoupper($_SERVER['HTTP_USER_AGENT']);
	$cand = array('Firefox', 'Chrome', 'MSIE');
	for ($i = 0; $i < count($cand); $i++) {
		if (strpos($ua, strtoupper($cand[$i])) === FALSE) {
			continue;
		}
		return $cand[$i];
	}
	return 'Unknown';
}

function logging($in_text)
{
	$ua = userAgent();
	$h = fopen(LOG, 'a+');
	fwrite($h, date('H:i:s') . " : [{$in_text}] from {$ua}\n");
	fclose($h);
}

function responseLogHTML($in_sig = 0, $in_max = 8)
{
	header('Content-Type: text/html');
	$lines = @file(LOG) or die('no log');
	for ($i = 0; $i < min($in_max, count($lines)); $i++) {
		$line = trim($lines[count($lines) - $i - 1]);
		if (strpos($line, ": [{$in_sig}]") === FALSE) {
			$color = 'silver';
		} else {
			$color = 'black';
		}
		print "<div style='color:{$color};'>{$line}</div>\n";
	}
}

if (MOD == 'i') {
	logging(SIG);
	responsePNG();
} else {
	if (MOD == 'l') {
		responseLogHTML(SIG);
	} else {
		/* utility javascript */
		header('Content-Type: text/javascript');
		$SELF = SELF;
		$SIG = SIG;
		print <<<EOJS
function showLog(in_delay)
{
	window.setTimeout(function() {
		var iframe = document.getElementById('log');
		if (!iframe) {
			iframe = document.createElement('IFRAME');
			iframe.id = 'log';
			document.body.appendChild(iframe);
		}
		iframe.src = '{$SELF}?sig={$SIG}&mod=l&random=' + Math.random();
	}, in_delay);
}

showLog(500);

window.addEventListener('keydown', function(e) {
	console.log(e.keyCode);
	if (e.keyCode != 32) {
		return false;
	}
	var elems = document.getElementsByTagName('*');
	for (var i = 0; i < elems.length; i++) {
		if (elems.item(i).style.display == 'none') {
			elems.item(i).style.display = 'block'
		}
	}
	showLog(500);
}, false);
EOJS;
	}
}

exit;

?>
