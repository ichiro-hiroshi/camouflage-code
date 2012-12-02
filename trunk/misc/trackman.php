<?php

/*

[tracking]

<html>
<body>
<script type='text/javascript' src='http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}'>
</script>
</body>
</html>

[user]

<html>
<body>
<script type='text/javascript' src='http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?user'>
</script>
<script type='text/javascript'>
// use prepared data
</script>
</body>
</html>

*/

date_default_timezone_set('Asia/Tokyo');

function scriptName($in_path)
{
	$e1 = explode('/', $in_path);
	$e2 = explode('.', array_pop($e1));
	return $e2[0];
}

function h($in_seed, $in_len = 4)
{
	return 'h' . substr(md5($in_seed), 0, $in_len);
}

define('SELF', "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}");
define('PCOOKIE', h('pcookie'));
define('MODE', h('mode'));
define('JSNAMESPACE', h(scriptName($_SERVER['SCRIPT_NAME'])));
define('CACHE_MODE', h('cache'));
define('TRACK_MODE', h('track'));

function _fingerPrint($in_fields, $in_prefix)
{
	foreach ($in_fields as $field) {
		if (array_key_exists($field, $_SERVER)) {
			$in_prefix .= $_SERVER[$field];
		}
	}
	return md5($in_prefix);
}

function fingerPrintLong($in_prefix = '')
{
	$fields = array(
		'HTTP_USER_AGENT',
		'HTTP_ACCEPT',
		'HTTP_REFERER',
		'HTTP_ACCEPT_ENCODING',
		'HTTP_ACCEPT_LANGUAGE',
		'HTTP_ACCEPT_CHARSET'
	);
	return _fingerPrint($fields, $in_prefix);
}

function fingerPrintShort($in_prefix = '')
{
	$fields = array(
		'REMOTE_ADDR',
		'HTTP_USER_AGENT'
	);
	return _fingerPrint($fields, $in_prefix);
}

define('DB', './.db');
if (!is_dir(DB)) {
	mkdir(DB);
}

function flashDB()
{
	$dh = opendir(DB);
	if (!$dh) {
		print '// opendir failed';
		exit;
	}
	while (($fname = readdir($dh)) !== FALSE) {
		$path = DB . "/{$fname}";
		if (is_file($path)) {
			unlink($path);
		}
	}
	closedir($dh);
}

function getCand($in_dh, $in_prefix)
{
	$cand = array();
	while (($fname = readdir($in_dh)) !== FALSE) {
		if (strpos($fname, $in_prefix) === 0) {
			array_push($cand, $fname);
		}
	}
	return $cand;
}

define('RW_MUST', 1);
define('RW_MAY', 2);
define('RW_NOT', 3);

function writeDB($in_val, $in_key_p, $in_key_s = NULL)
{
	$ret = array('cd' => RW_NOT, 'may' => NULL);
	$path = '';
	if ($in_key_s) {
		$path = DB . "/{$in_key_p}-{$in_key_s}";
		$ret['cd'] = RW_MUST;
	} else {
		$dh = opendir(DB);
		if (!$dh) {
			print '// opendir failed';
			exit;
		}
		$cand = getCand($dh, $in_key_p);
		closedir($dh);
		switch (count($cand)) {
		case 0 :
			$path = DB . "/{$in_key_p}";
			$ret['cd'] = RW_MUST;
			break;
		case 1 :
			$path = DB . "/{$cand[0]}";
			if ($in_key_p == $cand[0]) {
				$ret['cd'] = RW_MUST;
			} else {
				$ret['cd'] = RW_MAY;
				$ret['may'] = substr($cand[0], strlen($in_key_p) + 1);
			}
			break;
		default :
			break;
		}
	}
	if ($path) {
		$fh = fopen($path, 'w');
		fwrite($fh, $in_val);
		fclose($fh);
	}
	return $ret;
}

function readDB($in_key_p, $in_key_s = NULL)
{
	$ret = array('cd' => RW_NOT, 'may' => NULL, 'data' => NULL);
	$dh = opendir(DB);
	if (!$dh) {
		print '// opendir failed';
		exit;
	}
	$path = '';
	if ($in_key_s) {
		while (($fname = readdir($dh)) !== FALSE) {
			if ($fname == "{$in_key_p}-{$in_key_s}") {
				$path = DB . "/{$fname}";
				$ret['cd'] = RW_MUST;
				break;
			}
		}
	} else {
		$cand = getCand($dh, $in_key_p);
		if (count($cand) == 1) {
			$path = DB . "/{$cand[0]}";
			if ($in_key_p == $cand[0]) {
				$ret['cd'] = RW_MUST;
			} else {
				$ret['cd'] = RW_MAY;
				$ret['may'] = substr($cand[0], strlen($in_key_p) + 1);
			}
		}
	}
	closedir($dh);
	if ($path && is_file($path)) {
		$ret['data'] = file_get_contents($path);
	}
	return $ret;
}

function _test_rw()
{
	header('Content-Type: text/plain');
	$tester = function($ret, $expected) {
		foreach ($ret as $key => $val) {
			if ($val !== $expected[$key]) {
				print_r($ret);
				print_r($expected);
				exit;
			}
		}
	};
	// (1)
	flashDB();
		$tester(writeDB('x1', 'p1'),		array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x2', 'p1'),		array('cd' => RW_MUST, 'may' => NULL));
		$tester(readDB('p1'),				array('cd' => RW_MUST, 'may' => NULL, 'data' => 'x2'));
	flashDB();
		$tester(writeDB('x1', 'p1', 's1'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x2', 'p1', 's1'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(readDB('p1', 's1'),			array('cd' => RW_MUST, 'may' => NULL, 'data' => 'x2'));
		$tester(readDB('p1'),				array('cd' => RW_MAY,  'may' => 's1', 'data' => 'x2'));
	// (2)
	flashDB();
		$tester(writeDB('x1', 'p1', 's1'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x2', 'p1'),		array('cd' => RW_MAY,  'may' => 's1'));
		$tester(readDB('p1', 's1'),			array('cd' => RW_MUST, 'may' => NULL, 'data' => 'x2'));
		$tester(readDB('p1'),				array('cd' => RW_MAY,  'may' => 's1', 'data' => 'x2'));
	flashDB();
		$tester(writeDB('x1', 'p1'),		array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x2', 'p1', 's1'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(readDB('p1', 's1'),			array('cd' => RW_MUST, 'may' => NULL, 'data' => 'x2'));
		$tester(readDB('p1'),				array('cd' => RW_NOT,  'may' => NULL, 'data' => NULL));
	flashDB();
		$tester(writeDB('x1', 'p1', 's1'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x2', 'p1', 's2'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(readDB('p1'),				array('cd' => RW_NOT,  'may' => NULL, 'data' => NULL));
	// (3)
	flashDB();
		$tester(writeDB('x1', 'p1'),		array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x2', 'p1', 's1'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x3', 'p1'),		array('cd' => RW_NOT,  'may' => NULL));
		$tester(readDB('p1', 's1'),			array('cd' => RW_MUST, 'may' => NULL, 'data' => 'x2'));
		$tester(readDB('p1'),				array('cd' => RW_NOT,  'may' => NULL, 'data' => NULL));
	flashDB();
		$tester(writeDB('x1', 'p1', 's1'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x2', 'p1', 's2'),	array('cd' => RW_MUST, 'may' => NULL));
		$tester(writeDB('x3', 'p1'),		array('cd' => RW_NOT,  'may' => NULL));
		$tester(readDB('p1'),				array('cd' => RW_NOT,  'may' => NULL, 'data' => NULL));
	flashDB();
	print 'done';
	exit;
}

//_test_rw();

function makeHash()
{
	return substr(time(), -4) . md5(rand());
}

function proc1stReq()
{
	header('Content-Type: text/javascript');
	if (array_key_exists(PCOOKIE, $_COOKIE)) {
		$fp = fingerPrintLong();
		header("X-fingerPrint: {$fp}");
		$pc = $_COOKIE[PCOOKIE];
		$ret = readDB($fp, $pc);
		if ($ret['cd'] === RW_NOT) {
			write1stScript();
		} else {
			header('Cache-Control: no-store');
			$cnt = $ret['data'] + 1;
			writeDB($cnt, $fp, $pc);
			print "// counter : {$cnt}";
		}
	} else {
		write1stScript();
	}
}

function write1stScript()
{
	$SELF = SELF;
	$PCOOKIE = PCOOKIE;
	$JSNAMESPACE = JSNAMESPACE;
	$NEXTPCOOKIE = makeHash();
	$MODE = MODE;
	$CACHE_MODE = CACHE_MODE;
	$TRACK_MODE = TRACK_MODE;
	print <<<EOJS
(function() {

String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g, '');
}

function getCookieValue(in_name)
{
	var cookies = document.cookie.split(';')
	for (var i = 0; i < cookies.length; i++) {
		var fv = cookies[i].split('=');
		if ((fv.length == 2) && (fv[0].trim() == in_name)) {
			return fv[1].trim();
		}
	}
	return null;
}

var appendScript = function(in_src) {
	var target = document.getElementsByTagName('SCRIPT').item(0);
	var script = document.createElement('SCRIPT');
	script.type = 'text/javascript';
	script.src = in_src;
	target.parentNode.insertBefore(script, target);
};

appendScript('{$SELF}?{$MODE}={$CACHE_MODE}&{$PCOOKIE}=');

window.setTimeout(function() {
	var pc = {
		cache : null,
		first : null
	};
	if (window.{$JSNAMESPACE}) {
		pc.cache = window.{$JSNAMESPACE}.{$PCOOKIE};
	}
	pc.first = getCookieValue('{$PCOOKIE}');
	if (pc.cache) {
		if (!pc.first) {
			document.cookie = '{$PCOOKIE}=' + pc.cache + '; expires=Tue, 31 Dec 2030 00:00:00 UTC; path=/';
		}
		appendScript('{$SELF}?{$MODE}={$TRACK_MODE}&{$PCOOKIE}=' + pc.cache);
	} else {
		if (pc.first) {
			appendScript('{$SELF}?{$MODE}={$CACHE_MODE}&{$PCOOKIE}=' + pc.first);
		} else {
			appendScript('{$SELF}?{$MODE}={$CACHE_MODE}&{$PCOOKIE}={$NEXTPCOOKIE}');
		}
	}
}, 500);

})();
EOJS;
}

define('SCACHE', './.cache');
if (!is_dir(SCACHE)) {
	mkdir(SCACHE);
}

function procCacheReq()
{
	if (array_key_exists(PCOOKIE, $_GET)) {
		$server_cache = SCACHE . '/' . fingerPrintShort();
		$pc = $_GET[PCOOKIE];
		if ($pc) {
			$fh = fopen($server_cache, 'w');
			fwrite($fh, $pc);
			fclose($fh);
			header("Location: " . SELF . '?' . MODE . '=' . CACHE_MODE . '&' . PCOOKIE . '=');
		} else {
			if (is_file($server_cache)) {
				$pc = file_get_contents($server_cache);
				unlink($server_cache);
				$fp = fingerPrintLong();
				header("X-fingerPrint: {$fp}");
				$ret = readDB($fp, $pc);
				if ($ret['cd'] === RW_NOT) {
					$cnt = 0;
				} else {
					$cnt = $ret['data'] + 1;
				}
				header('Content-Type: text/javascript');
				header('Last-Modified: ' . date(DATE_RFC822));
				setcookie(PCOOKIE, $pc, time() + 3600 * 365, '/');
				write2ndScript($pc);
				writeDB($cnt, $fp, $pc);
				print "\n// counter : {$cnt}";
			} else {
				header("HTTP/1.1 304 Not Modified");
			}
		}
	} else {
		header('Content-Type: text/javascript');
		header('Cache-Control: no-store');
		print '// key-error';
	}
}

function write2ndScript($in_pc)
{
	$PCOOKIE = PCOOKIE;
	$JSNAMESPACE = JSNAMESPACE;
	print <<<EOJS
(function() {

if (!window.{$JSNAMESPACE}) {
	window.{$JSNAMESPACE} = {};
}
window.{$JSNAMESPACE}.{$PCOOKIE} = '{$in_pc}';
document.cookie = '{$PCOOKIE}={$in_pc}; expires=Tue, 31 Dec 2030 00:00:00 UTC; path=/';

})();
EOJS;
}

function procTrackReq()
{
	header('Content-Type: text/javascript');
	header('Cache-Control: no-store');
	if (array_key_exists(PCOOKIE, $_GET)) {
		$fp = fingerPrintLong();
		header("X-fingerPrint: {$fp}");
		$pc = $_GET[PCOOKIE];
		$ret = readDB($fp, $pc);
		if ($ret['cd'] === RW_NOT) {
			$cnt = 0;
		} else {
			$cnt = $ret['data'] + 1;
		}
		setcookie(PCOOKIE, $pc, time() + 3600 * 365, '/');
		writeDB($cnt, $fp, $pc);
		print "// counter : {$cnt}";
	} else {
		print '// key-error';
	}
}

function procUserReq()
{
	/* use write1stScript */
}

if (array_key_exists(MODE, $_GET)) {
	switch ($_GET[MODE]) {
	case CACHE_MODE :
		procCacheReq();
		break;
	case TRACK_MODE :
		procTrackReq();
		break;
	default :
		header('Content-Type: text/javascript');
		print '// mode-error';
		break;
	}
} else {
	if (array_key_exists('user', $_GET)) {
		procUserReq();
	} else {
		proc1stReq();
	}
}

exit;

?>
