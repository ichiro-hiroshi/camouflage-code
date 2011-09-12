<?php

require_once('chttp.php');

define('EMULATOR_URI', "http://{$_SERVER['HTTP_HOST']}" . preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']));
define('MAX_REDIRECT', 5);
define('TIMEOUT', 5);

define('TESTMODE_302_2TIMES', 'redirection_2times');
define('TESTMODE_302_COOKIE', 'redirection_cookie');
define('TESTMODE_302_LOOP', 'redirection_loop');
define('TESTMODE_TIMEOUT', 'timeout');
define('TESTMODE_TYPE', 'content_type');
define('TESTMODE_INDEX', 'index');
define('TESTMODE_TABLE', 'table');

function HTMLScriptElement_onerror_test()
{
	header('HTTP/1.1 500 Internal Server Error');
	// header('HTTP/1.1 404 Not Found');
	print 'error response';
	exit;
}

if (array_key_exists('url', $_GET) && array_key_exists('callback', $_GET)) {
	/*
		(1) compose JSON-Response using  emulator's result.
	*/
	// HTMLScriptElement_onerror_test();
	$next_dst = $_GET['url'];
	$history = array();
	$cnt = MAX_REDIRECT;
	do {
		if (strpos($next_dst, 'http://') !== 0) {
			array_push($history, "not target : {$next_dst}");
			break;
		}
		$http = new CHttp($next_dst);
		$http->GET(TRUE, TIMEOUT);
		$next_dst = $http->getResponseHeader('Location');
		$check_fields = array('Set-Cookie', 'P3P');
		foreach ($check_fields as $field) {
			$value = $http->getResponseHeader($field);
			if ($value) {
				array_push($history, "{$field}: {$value}");
			}
		}
		if ($next_dst) {
			array_push($history, $next_dst);
		} else {
			$s = $http->getStatusLine();
			if (count($s) == 3) {
				if ($s[1] != 200) {
					array_push($history, "{$s[1]} {$s[2]}");
				} else {
					$type = $http->getResponseHeader('Content-Type');
					array_push($history, $type);
				}
			} else {
				array_push($history, 'invalid response (timeout etc)');
			}
		}
	} while (($next_dst) && (--$cnt > 0));
	$JSON = '["' . implode('","', $history) . '"]';
	if (array_key_exists('debug', $_GET)) {
		header('Content-Type: text/plain');
	} else {
		header('Content-Type: text/javascript');
	}
	print "{$_GET['callback']}({$JSON})";
	exit;
} elseif (array_key_exists('test', $_GET)) {
	/*
		(2) test-mode for (1).

		[how to use]
			http://host/parh/file?test=TESTMODE_INDEX
			http://host/parh/file?test=TESTMODE_TABLE
	*/
	$tests = array(TESTMODE_302_2TIMES, TESTMODE_302_COOKIE, TESTMODE_302_LOOP, TESTMODE_TIMEOUT, TESTMODE_TYPE);
	switch ($_GET['test']) {
	case TESTMODE_302_2TIMES :
		header('Location: ' . EMULATOR_URI .'?test=' . TESTMODE_302_COOKIE);
		break;
	case TESTMODE_302_COOKIE :
		header('Location: ' . EMULATOR_URI .'?test=' . TESTMODE_TYPE);
		setcookie('TestCookie', 'test', time(), '/', 'localhost', TRUE, TRUE);
		break;
	case TESTMODE_302_LOOP :
		header('Location: ' . EMULATOR_URI .'?test=' . TESTMODE_302_LOOP);
		break;
	case TESTMODE_TIMEOUT :
		sleep(TIMEOUT * 2);
		print 'dummy';
		break;
	case TESTMODE_TYPE :
		header('Content-Type: application/x-emulator-test');
		print 'dummy';
		break;
	case TESTMODE_INDEX :
		print "<html>\n<body>\n<ul>\n";
		foreach ($tests as $test) {
			$href = EMULATOR_URI . '?url=' . urlencode(EMULATOR_URI ."?test={$test}") . '&callback=dummy&debug';
			print "\t<li><a href='{$href}'>test ({$test})</a></li>\n";
		}
		print "</ul>\n</body>\n</html>\n";
		break;
	case TESTMODE_TABLE :
	default :
		print "<html><head></head>\n<body>\n<table>\n";
		foreach ($tests as $test) {
			print "<tr><td>" . EMULATOR_URI ."?test={$test}</td><td>textContent</td><td></td></tr>\n";
		}
		print "</table>\n<script src='" . EMULATOR_URI . "'></script>\n</body>\n</html>\n";
		break;
	}
	exit;
} else {
	/*
		(3) javascript to compose TABLE element. this assumes structure of TABLE.

		[before]
			<table>
			<tr><td> URL1 </td><td> TEXT1 </td><td></td><tr>
			<tr><td> URL2 </td><td> TEXT2 </td><td></td><tr>
			...

		[after]
			<table>
			<tr><td> URL1 </td><td> TEXT1 </td><td> RESULT1 </td><tr>
			<tr><td> URL2 </td><td> TEXT2 </td><td> RESULT2 </td><tr>
			...
	*/
	header('Content-Type: text/javascript');
	$gCSS = <<<EOCSS
TABLE {
	width: 99%;
	border-collapse: collapse;
}
TD {
	width: 33%;
	padding: 3px;
	border: solid 1px gray;
	word-break: break-all;
	word-wrap: break-word;
}
HR {
	border: dotted 1px #cccccc;
}
EOCSS;
	$gCSS = preg_replace('/[\r\n\t]+/', ' ', $gCSS);
}

?>

function emulateRequest(in_url, in_callback)
{
	var s = document.createElement('SCRIPT');
	s.src = '<?php print EMULATOR_URI; ?>?url=' + encodeURIComponent(in_url) + '&callback=' + in_callback;
	s.onerror = function () {
		gURLStock.handleJSON(["(loading error)"]);
	}
	document.body.appendChild(s);
}

var COL_URL = 0;
var COL_TEXT = 1;
var COL_RESULT = 2;

var gURLStock = {
	_current : 0,
	_rows : document.getElementsByTagName('TABLE').item(0).rows,
	_get : function(in_col) {
		return this._rows[this._current].cells.item(in_col).textContent;
	},
	_set : function(in_col, in_value) {
		this._rows[this._current].cells.item(in_col).innerHTML = in_value;
	},
	_setCurrentColor : function(in_color) {
		this._rows[this._current].style.backgroundColor = in_color;
	},
	currentURL : function() {
		return this._get(COL_URL);
	},
	handleJSON : function(in_json) {
		this._setCurrentColor('#eeeeee');
		this._set(COL_RESULT, in_json.join('<hr />'));
		this._current++;
		if (this._current < this._rows.length) {
			this._setCurrentColor('#ffff99');
			emulateRequest(this.currentURL(), 'gURLStock.handleJSON');
		} else {
			var style = document.createElement('STYLE');
			style.type = 'text/css';
			style.textContent = "<?php print $gCSS; ?>";
			document.head.appendChild(style);
		}
	},
};

emulateRequest(gURLStock.currentURL(), 'gURLStock.handleJSON');

