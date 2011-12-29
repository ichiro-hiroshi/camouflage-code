<?php

define('SEC', substr(time(), -3));
define('SELF', 'http://OTHER_HOSTNAME/PATH/' . substr(str_replace(__DIR__, '', __FILE__), 1));

$type = array_key_exists('type', $_GET) ? $_GET['type'] : 'html';
$from = array_key_exists('from', $_GET) ? $_GET['from'] : 'html';

$ctx = array(
	'html' => array(
		'type' => 'text/html',
		'func' => 'mk_html'
	),
	'iframe' => array(
		'type' => 'text/html',
		'func' => 'mk_iframe'
	),
	'javascript' => array(
		'type' => 'text/javascript',
		'func' => 'mk_javascript'
	),
	'css' => array(
		'type' => 'text/css',
		'func' => 'mk_css'
	),
	'png' => array(
		'type' => 'image/png',
		'func' => 'mk_png'
	)
);

function mk_html()
{
	list($SEC, $SELF) = array(SEC, SELF);
	print <<<EOHTML
<html>
<head>
<link rel='stylesheet' href='{$SELF}?type=css' type='text/css' />
<meta http-equiv='Set-Cookie' content='html-meta=1' />
</head>
<body>
<p>HTML</p>
<p>{$SEC}</p>
<p><img src='{$SELF}?type=png' /></p>
<p><iframe src='{$SELF}?type=iframe'></iframe></p>
<script type='text/javascript' src='{$SELF}?type=javascript'></script>
</body>
</html>
EOHTML;
}

function mk_iframe()
{
	list($SEC, $SELF) = array(SEC, SELF);
	print <<<EOHTML
<html>
<head>
<link rel='stylesheet' href='{$SELF}?type=css&from=iframe' type='text/css' />
<meta http-equiv='Set-Cookie' content='iframe-meta=1' />
</head>
<body>
<p>IFRAME</p>
<p>{$SEC}</p>
<p><img src='{$SELF}?type=png&from=iframe' /></p>
<script type='text/javascript' src='{$SELF}?type=javascript&from=iframe'></script>
</body>
</html>
EOHTML;
}

function mk_javascript()
{
	print 'alert(' . SEC . ');';
}

function mk_css()
{
	print '* {color: red;}';
}

function mk_png()
{
	$image = imagecreatetruecolor(50, 50);
	$bg = imagecolorallocate($image, 0, 150, 0);
	imagefill($image, 0, 0, $bg);
	$col_ellipse = imagecolorallocate($image, 255, 255, 255);
	imageellipse($image, 25, 25, 45, 45, $col_ellipse);
	imagepng($image);
}

if (array_key_exists($type, $ctx)) {
	header("Content-Type: {$ctx[$type]['type']}");
	setcookie("{$from}-{$type}", SEC, time() + 3600);
	call_user_func($ctx[$type]['func']);
} else {
	print 'missing quesy-string';
}

exit;

?>
