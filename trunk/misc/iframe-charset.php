<?php

define('TEXT', '£Å£Õ£Ã');

function print_html($in_test)
{
	list($enc, $meta_cs, $http_cs) = explode(',', $in_test);
	if ($http_cs) {
		header("Content-Type: text/html; charset={$http_cs}");
	}
	if ($meta_cs) {
		$meta = "<meta http-equiv='content-type' content='text/html; charset={$meta_cs}'>";
	} else {
		$meta = "<meta http-equiv='content-type' content='text/html'>";
	}
	$str = TEXT;
	if ($enc != 'EUC-JP') {
		$str = mb_convert_encoding($str, $enc, 'EUC-JP');
	}
	print <<<EOC
<html>
<head>
{$meta}
</head>
<body>
<p>{$str} (html-enc : {$enc}, meta : {$meta_cs}, http : {$http_cs})</p>
</body>
</html>
EOC;
}

function print_js($in_test)
{
	list($js, $enc, $attr_cs, $http_cs) = explode(',', $in_test);
	if ($js == 'xjs') {
		if ($http_cs) {
			header("Content-Type: text/javascript; charset={$http_cs}");
		}
		$str = TEXT;
		if ($enc != 'EUC-JP') {
			$str = mb_convert_encoding($str, $enc, 'EUC-JP');
		}
		print <<<EOC
document.write("{$str}");
EOC;
	} else {
		if ($attr_cs) {
			$script = "<script type='text/javascript' src='iframe-charset.php?t=x{$in_test}' charset='{$attr_cs}'></script>";
		} else {
			$script = "<script type='text/javascript' src='iframe-charset.php?t=x{$in_test}'></script>";
		}
		print <<<EOC
<html>
<body>
<p>{$script} (javascript-enc : {$enc}, attr : {$attr_cs}, http : {$http_cs})</p>
</body>
</html>
EOC;
	}
}

if (array_key_exists('t', $_GET)) {
	switch ($_GET['t']) {
	case 'EUC-JP,,' :
	case 'EUC-JP,EUC-JP,' :
	case 'EUC-JP,,EUC-JP' :
	case 'UTF-8,,' :
	case 'EUC-JP,EUC-JP-MS,' :
	case 'EUC-JP,,EUC-JP-MS' :
		print_html($_GET['t']);
		break;
	case 'js,EUC-JP,,' :
	case 'js,EUC-JP,EUC-JP,' :
	case 'js,EUC-JP,,EUC-JP' :
	case 'js,UTF-8,,' :
	case 'js,EUC-JP,EUC-JP-MS,' :
	case 'js,EUC-JP,,EUC-JP-MS' :
	case 'xjs,EUC-JP,,' :
	case 'xjs,EUC-JP,EUC-JP,' :
	case 'xjs,EUC-JP,,EUC-JP' :
	case 'xjs,UTF-8,,' :
	case 'xjs,EUC-JP,EUC-JP-MS,' :
	case 'xjs,EUC-JP,,EUC-JP-MS' :
		print_js($_GET['t']);
		break;
	default :
		break;
	}
} else {
	header('Content-Type: text/html; charset=EUC-JP');
	print <<<EOC
<html>
<head>
<style type='text/css'>
IFRAME {width: 90%; height: 2em;}
</style>
</head>
<body>
<p>£Å£Õ£Ã</p>
<div><iframe src='iframe-charset.php?t=EUC-JP,,'></iframe></div>
<div><iframe src='iframe-charset.php?t=EUC-JP,EUC-JP,'></iframe></div>
<div><iframe src='iframe-charset.php?t=EUC-JP,,EUC-JP'></iframe></div>
<div><iframe src='iframe-charset.php?t=UTF-8,,'></iframe></div>
<div><iframe src='iframe-charset.php?t=EUC-JP,EUC-JP-MS,'></iframe></div>
<div><iframe src='iframe-charset.php?t=EUC-JP,,EUC-JP-MS'></iframe></div>
<div><iframe src='iframe-charset.php?t=js,EUC-JP,,'></iframe></div>
<div><iframe src='iframe-charset.php?t=js,EUC-JP,EUC-JP,'></iframe></div>
<div><iframe src='iframe-charset.php?t=js,EUC-JP,,EUC-JP'></iframe></div>
<div><iframe src='iframe-charset.php?t=js,UTF-8,,'></iframe></div>
<div><iframe src='iframe-charset.php?t=js,EUC-JP,EUC-JP-MS,'></iframe></div>
<div><iframe src='iframe-charset.php?t=js,EUC-JP,,EUC-JP-MS'></iframe></div>
</body>
</html>
EOC;
}

exit;

?>