<?php

define('SELF', substr(str_replace(__DIR__, '', __FILE__), 1));
define('LOG', SELF . '.log');

/*
	cache=n : response 'Cache-Control: no-store'
	cache=m : response 'Cache-Control: private, max-age=630720000'
*/

function getCacheControl($in_n)
{
	if ($in_n == 'n') {
		return 'Cache-Control: no-store';
	} elseif ($in_n == 'm') {
		return 'Cache-Control: private, max-age=630720000';
	} else {
		return NULL;
	}
}

define('CACHE', array_key_exists('cache', $_GET) ? getCacheControl($_GET['cache']) : NULL);

/*
	rnd=n : random to avoid caching
*/

define('RND', array_key_exists('rnd', $_GET) ? $_GET['rnd'] : 0);

/*
	req=p  : request image/png
	req=h  : request text/html
	req=1 : see last 1 log
	req=N : see last N logs
*/

define('REQ', array_key_exists('req', $_GET) ? $_GET['req'] : 'p');

$DB = array(
	'p' => array(
		'type' => 'image/png',
		'func' => function($in_text) {
			list($w, $h) = array(200, 20);
			$image = imagecreatetruecolor($w, $h);
			$color = array(
				imagecolorallocate($image, 100, 100, 100),
				imagecolorallocate($image, 255, 255, 255)
			);
			imagefilledrectangle($image, 0, 0, $w, $h, $color[0]);
			imagestring($image, 1, 0, 0, $in_text, $color[1]);
			imagepng($image);
			imagedestroy($image);
		}
	),
	'h' => array(
		'type' => 'text/html',
		'func' => function($in_text) {
			print "<html>\n<body>\n<p>{$in_text}</p>\n</body>\n</html>";
		}
	)
);

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

date_default_timezone_set('UTC');

function logging($in_text)
{
	$ua = userAgent();
	$h = fopen(LOG, 'a+');
	fwrite($h, date('H:i:s') . " : [{$in_text}] from {$ua}\n");
	fclose($h);
}

function printLogs($in_num)
{
	$lines = @file(LOG) or die('no log');
	for ($i = 0; $i < min($in_num, count($lines)); $i++) {
		print $lines[count($lines) - $i - 1];
	}
}

if (preg_match('/^[0-9]+$/', REQ, $m)) {
	header('Content-Type: text/plain');
	header('Cache-Control: no-store');
	printLogs(REQ);
} else {
	logging("{$DB[REQ]['type']} + " . RND);
	header('Content-Type: ' . $DB[REQ]['type']);
	if (CACHE) {
		header('Cache-Control: ' . CACHE);
	}
	call_user_func($DB[REQ]['func'], RND);
}

exit;

?>
