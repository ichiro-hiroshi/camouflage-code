<?php

date_default_timezone_set('UTC');

$typedb = array(
	'html' => array(
		'contentType' => 'text/html',
		'entityInitiator' => '<iframe src="_SRC_"></iframe>',
		'entity' => '<p>[' . date(DATE_RFC822) . ']</p>'
	),
	'script' => array(
		'contentType' => 'text/javascript',
		'entityInitiator' => '<script type="text/javascript" src="_SRC_"></script>',
		'entity' => 'document.write("[' . date(DATE_RFC822) . ']");'
	),
	'img' => array(
		'contentType' => 'image/gif',
		'entityInitiator' => '<img src="_SRC_" />',
		'entity' => call_user_func(function() {
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
			return $gif;
		})
	)
);

$headerdb = array(
	'X-None: *',
	'Cache-Control: private',
	'Cache-Control: no-store',
	'Last-Modified: ' . date(DATE_RFC822)
);

$self = substr(str_replace(__DIR__, '', __FILE__), 1);
$type = array_key_exists('type', $_GET) ? $_GET['type'] : 'html';
$mode = array_key_exists('mode', $_GET) ? $_GET['mode'] : 'scriptInitiator';
$test = array_key_exists('test', $_GET) ? intval($_GET['test']) : 0;
$ctxt = $typedb[$type];
$next = "{$self}?type={$type}&test={$test}";

function putlog($txt)
{
	$h = fopen('log.txt', 'a+');
	fwrite($h, date(DATE_RFC822) . " [{$txt}]\n");
	fclose($h);
}

switch ($mode) {
case 'entityInitiator' :
	header("Content-Type: text/html");
	print str_replace('_SRC_', "{$next}&mode=entity", "{$ctxt['entityInitiator']}\n");
	print "<hr /><a href='log.txt'>[log.txt]</a>";
	break;
case 'scriptInitiator' :
	header("Content-Type: text/html");
	print "<script type='text/javascript' src='{$next}&mode=script'></script>";
	print "<hr /><a href='log.txt'>[log.txt]</a>";
	break;
case 'script' :
	header("Content-Type: text/javascript");
	print str_replace('_SRC_', "{$next}&mode=entity", "document.write('{$ctxt['entityInitiator']}');\n");
	break;
case 'entity' :
	putlog($next);
	header("Content-Type: {$ctxt['contentType']}");
	header($headerdb[$test]);
	print $ctxt['entity'];
	break;
default :
	break;
}

exit;

?>
