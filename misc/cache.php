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
		'entityInitiator' => '<img src="_SRC_" onload="console.log(Math.random())" />',
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
$mode = array_key_exists('mode', $_GET) ? $_GET['mode'] : NULL;
$test = array_key_exists('test', $_GET) ? intval($_GET['test']) : 0;
$ctxt = $typedb[$type];
$next = "{$self}?type={$type}&test={$test}";

function putlog($txt)
{
	$h = fopen('log.txt', 'a+');
	fwrite($h, date(DATE_RFC822) . " [{$txt}]\n");
	fclose($h);
}

function testIndex()
{
	global $self, $typedb, $headerdb;
	$via = array(
		'direct' => 'entityInitiator',
		'script' => 'scriptInitiator'
	);
	print "<ul>\n";
	foreach ($typedb as $k1 => $v1) {
		print "\t<li>{$k1}\n";
		print "\t<ul>\n";
		foreach ($via as $k2 => $v2) {
			print "\t\t<li>{$k2}\n";
			print "\t\t<ul>\n";
			for ($i = 0; $i < count($headerdb); $i++) {
				print "\t\t\t<li><a href='{$self}?type={$k1}&test={$i}&mode={$v2}'>{$headerdb[$i]}</a></li>\n";
			}
			print "\t\t</ul>\n";
			print "\t\t</li>\n";
		}
		print "\t</ul>\n";
		print "\t</li>\n";
	}
	print "</ul>\n";
}

switch ($mode) {
case 'entityInitiator' :
case 'scriptInitiator' :
	header("Content-Type: text/html");
	print "<div id='viewLog'></div>";
	if ($mode == 'entityInitiator') {
		print str_replace('_SRC_', "{$next}&mode=entity", "{$ctxt['entityInitiator']}\n");
	} else {
		print "<script type='text/javascript' src='{$next}&mode=script'></script>";
	}
	print <<<EOC
<style type='text/css'>
#viewLog {
	width: 80%;
	color: white;
	background-color: black;
	padding: 3px;
}
</style>
<script type='text/javascript'>
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
			div.appendChild(document.createTextNode(cnt + ' : ' + last));
		};
	})(xhr);
	xhr.open('GET', 'log.txt', true);
	xhr.send();
}, 500);
</script>
</style>
<hr />
<a href='../'>[../]</a>
<a href='{$self}'>[index]</a>
EOC;
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
	header("Content-Type: text/html");
	testIndex();
	break;
}

exit;

?>
