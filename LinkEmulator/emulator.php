<?php

require_once('chttp.php');

define('EMULATOR_URI', "http://{$_SERVER['HTTP_HOST']}" . preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']));

define('TESTMODE_302_2TIMES', 'redirection_2times');
define('TESTMODE_302_ONCE', 'redirection_once');
define('TESTMODE_TYPE', 'content_type');
define('TESTMODE_INDEX', 'index');
define('TESTMODE_TABLE', 'table');

if (array_key_exists('url', $_GET) && array_key_exists('callback', $_GET)) {
	/*
		(1) compose JSON-Response using  emulator's result.
	*/
	$next_dst = $_GET['url'];
	$history = array();
	do {
		if (strpos($next_dst, 'http://') === FALSE) {
			array_push($history, 'unknown-dst');
			break;
		}
		$http = new CHttp($next_dst);
		$http->GET(TRUE);
		$next_dst = $http->getResponseHeader('Location');
		if ($next_dst) {
			array_push($history, $next_dst);
		} else {
			$s = $http->getStatusLine();
			if ($s[1] != 200) {
				array_push($history, "{$s[1]} {$s[2]}");
			} else {
				$type = $http->getResponseHeader('Content-Type');
				array_push($history, $type);
			}
		}
	} while ($next_dst);
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
	$tests = array(TESTMODE_302_2TIMES, TESTMODE_302_ONCE, TESTMODE_TYPE);
	switch ($_GET['test']) {
	case TESTMODE_302_2TIMES :
		header('Location: ' . EMULATOR_URI .'?test=' . TESTMODE_302_ONCE);
		break;
	case TESTMODE_302_ONCE :
		header('Location: ' . EMULATOR_URI .'?test=' . TESTMODE_TYPE);
		break;
	case TESTMODE_TYPE :
		header('Content-Type: application/x-emulator-test');
		print 'emulator-test';
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
			print "<tr><td>" . EMULATOR_URI ."?test={$test}</td><td></td></tr>\n";
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
			<tr><td> URL1 </td><td></td><tr>
			<tr><td> URL2 </td><td></td><tr>
			...

		[after]
			<table>
			<tr><td> URL1 </td><td> RESULT1 </td><tr>
			<tr><td> URL2 </td><td> RESULT2 </td><tr>
			...
	*/
	header('Content-Type: text/javascript');
}

?>

function emulateRequest(in_url, in_callback)
{
	var s = document.createElement('SCRIPT');
	s.src = '<?php print EMULATOR_URI; ?>?url=' + encodeURIComponent(in_url) + '&callback=' + in_callback;
	document.body.appendChild(s);
}

var gURLStock = {
	_current : 0,
	_rows : document.getElementsByTagName('TABLE').item(0).rows,
	_get : function(in_col) {
		return this._rows[this._current].cells.item(in_col).textContent;
	},
	_set : function(in_col, in_value) {
		this._rows[this._current].cells.item(in_col).innerHTML = in_value;
	},
	currentURL : function() {
		return this._get(0);
	},
	handleJSON : function(in_json) {
		this._set(1, in_json.join('<hr />'));
		this._current++;
		if (this._current < this._rows.length) {
			emulateRequest(this.currentURL(), 'gURLStock.handleJSON');
		} else {
			var style = document.createElement('STYLE');
			style.type = 'text/css';
			style.textContent = 'TABLE, TD {border-collapse: collapse; border: solid 1px gray; padding: 3px;} HR {border: dotted 1px #dddddd;}';
			document.head.appendChild(style);
		}
	},
};

emulateRequest(gURLStock.currentURL(), 'gURLStock.handleJSON');

