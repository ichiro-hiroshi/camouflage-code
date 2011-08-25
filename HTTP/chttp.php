<?php

function util_array2query($in_array)
{
	$params = array();
	foreach ($in_array as $key => $val)
		array_push($params, "{$key}=" . urlencode($val));
	return implode('&', $params);
}

define('CHTTP_SOCKET_ERROR_TIMEOUT', 20);
define('CHTTP_SOCKET_ABORT_TIMEOUT', 5);

define('CHTTP_READYSTATE_UNINITIALIZED', '0');
define('CHTTP_READYSTATE_LOADING_H', '1-1');
define('CHTTP_READYSTATE_LOADING_B', '1-2');
define('CHTTP_READYSTATE_LOADED', '2');
define('CHTTP_READYSTATE_INTERACTIVE', '3');
define('CHTTP_READYSTATE_COMPLETED', '4');

define('CHTTP_RCEVRESPONSE_WOULDBLOCK', 1);
define('CHTTP_RCEVRESPONSE_IOSLEEP', 2);
define('CHTTP_RCEVRESPONSE_DONE', 3);

define('CRLF', "\r\n");
define('CRLFLEN', strlen(CRLF));

class CHttp
{
	function CHttp($in_url, $in_proxy = NULL) {
		// $in_proxy : "hostname" or "hostname:port"
		$this->_parsedUrl = parse_url($in_url);
		if (!array_key_exists('path', $this->_parsedUrl)) {
			$this->_parsedUrl['path'] = '/';
		}
		if ($in_proxy) {
			$pos = strpos($in_proxy, ':');
			if ($pos === FALSE) {
				$host = $in_proxy;
				$port = 80;
			} else {
				$host = substr($this->_proxy, 0, $pos);
				$port = substr($this->_proxy, ($pos + 1));
			}
			$this->_proxy = array('host' => $host, 'port' => $port);
		} else {
			$this->_proxy = NULL;
		}
		$this->_readyState = CHTTP_READYSTATE_UNINITIALIZED;
		$this->_socket = NULL;
		$this->_headers = array();
		$this->_setDefaultHeaders();
		$this->_rawRequest = NULL;
		$this->_rawResponse = NULL;
		$this->_bodyOffset = 0;
		$this->_statusLine = NULL;
		$this->_responseHeaders = array();
		$this->_method = 'GET';
	}

	function _DP($in_string = NULL) {
		header('Content-Type: text/plain');
		if ($in_string) {
			print $in_string;
		} else {
			print_r(debug_backtrace());
		}
		print "\n\n-----\n\n";
		print_r($this);
		exit;
	}

	function sendRequest($in_method = NULL, $in_body = NULL) {
		if ($this->_readyState != CHTTP_READYSTATE_UNINITIALIZED) {
			$this->_DP('uninitialized object is required.');
		}
		if ($in_method) {
			$this->_method = strtoupper($in_method);
		}
		if ($in_body) {
			$this->setHeader('Content-Length', strlen($in_body));
		}
		$request_line = $this->_parsedUrl['path'];
		if (array_key_exists('query', $this->_parsedUrl)) {
			$request_line .= "?{$this->_parsedUrl['query']}";
		}
		if ($this->_proxy) {
			if ($this->_parsedUrl['scheme'] == 'https') {
				// refer to Zend_Http_Client
				$this->_DP('https & proxy are not supported at the same time.');
			} else {
				$host = "tcp://{$this->_proxy['host']}";
				$port = $this->_proxy['port'];
			}
			$this->_rawRequest = "{$this->_method} {$this->_parsedUrl['scheme']}://{$this->_parsedUrl['host']}{$request_line} HTTP/1.1\r\n";
		} else {
			if ($this->_parsedUrl['scheme'] == 'https') {
				$host = "ssl://{$this->_parsedUrl['host']}";
				$port = 443;
			} else {
				$host = "tcp://{$this->_parsedUrl['host']}";
				$port = 80;
			}
			$this->_rawRequest = "{$this->_method} {$request_line} HTTP/1.1\r\n";
		}
		$host = str_replace('://localhost', '://127.0.0.1', $host);
		$this->_socket = @fsockopen($host, $port, $errno, $errstr, CHTTP_SOCKET_ERROR_TIMEOUT);
		if (!$this->_socket) {
			$this->_DP("{$errno}\n{$errstr}");
		}
		stream_set_blocking($this->_socket, FALSE);
		foreach ($this->_headers as $key => $val) {
			$this->_rawRequest .= "{$key}: {$val}\r\n";
		}
		$this->_rawRequest .= "\r\n";
		if ($in_body) {
			$this->_rawRequest .= $in_body;
		}
		fputs($this->_socket, $this->_rawRequest);
		$this->_readyState = CHTTP_READYSTATE_LOADING_H;
	}

	function _fclose() {
		if ($this->_socket) {
			fclose($this->_socket);
			$this->_socket = NULL;
			$this->_readyState = CHTTP_READYSTATE_LOADED;
		}
	}

	function _rcevResponse($in_header_only = FALSE) {
		$ret = CHTTP_RCEVRESPONSE_WOULDBLOCK;
		switch ($this->_readyState) {
		case CHTTP_READYSTATE_UNINITIALIZED :
			$this->_DP('sendRequest is required before _rcevResponse.');
			break;
		case CHTTP_READYSTATE_LOADING_H :
		case CHTTP_READYSTATE_LOADING_B :
			if (feof($this->_socket)) {
				$this->_fclose();
				$ret = CHTTP_RCEVRESPONSE_DONE;
			} else {
				$line = fgets($this->_socket);
				if (!$line) {
					// no-data
					$ret = CHTTP_RCEVRESPONSE_IOSLEEP;
					break;
				} else {
					$this->_rawResponse .= $line;
				}
				if ($this->_readyState == CHTTP_READYSTATE_LOADING_H) {
					$line = trim($line);
					if ($line) {
						// header
						if ($this->_statusLine === NULL) {
							$this->_statusLine = $line;
						} else {
							array_push($this->_responseHeaders, trim($line));
						}
					} else {
						// header --> entity body
						$this->_bodyOffset = strlen($this->_rawResponse);
						if ($in_header_only) {
							$this->_fclose();
							$ret = CHTTP_RCEVRESPONSE_DONE;
						} else {
							$this->_readyState = CHTTP_READYSTATE_LOADING_B;
						}
					}
				}
			}
			break;
		case CHTTP_READYSTATE_LOADED :
		case CHTTP_READYSTATE_INTERACTIVE :
		case CHTTP_READYSTATE_COMPLETED :
		default :
			$ret = CHTTP_RCEVRESPONSE_DONE;
			break;
		}
		return $ret;
	}

	function _rcevResponseBlocking($in_header_only, $in_timeout) {
		// $in_timeout : if timeout, socket will be closed.
		$start = time();
		while (TRUE) {
			switch ($this->_rcevResponse($in_header_only)) {
			case CHTTP_RCEVRESPONSE_WOULDBLOCK :
				break;
			case CHTTP_RCEVRESPONSE_IOSLEEP :
				// 50msec
				usleep(50000);
				break;
			case CHTTP_RCEVRESPONSE_DONE :
				break 2;
			default :
				$this->_DP('invalid return from _rcevResponse.');
				break;
			}
			if ($in_timeout && (time() - $start > $in_timeout)) {
				$this->_fclose();
				break;
			}
		}
	}

	function GET($in_header_only = FALSE, $in_timeout = CHTTP_SOCKET_ABORT_TIMEOUT) {
		$this->sendRequest('GET');
		$this->_rcevResponseBlocking($in_header_only, $in_timeout);
	}

	function POST($in_postdata, $in_header_only = FALSE, $in_timeout = CHTTP_SOCKET_ABORT_TIMEOUT) {
		if (is_array($in_postdata)) {
			$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
			$body = util_array2query($in_postdata);
		} else {
			$body = $in_postdata;
		}
		$this->sendRequest('POST', $body);
		$this->_rcevResponseBlocking($in_header_only, $in_timeout);
	}

	function setAuthHeader($in_user, $in_pass) {
		$encoded = base64_encode("{$in_user}:{$in_pass}");
		$this->setHeader('Authorization', "Basic {$encoded}");
	}

	function setHeader($in_key, $in_val) {
		$this->_headers[$in_key] = $in_val;
	}

	function setHeaders($in_array) {
		foreach ($in_array as $key => $val) {
			$this->setHeader($key, $val);
		}
	}

	function _setDefaultHeaders() {
		$default = array(
			'Host'			=> $this->_parsedUrl['host'],
			'Connection'	=> 'Close'
		);
		$browser_headers = apache_request_headers();
		$asis = array('User-Agent', 'Accept', 'Accept-Language', 'Accept-Charset');
		foreach ($browser_headers as $key => $val) {
			if (in_array($key, $asis)) {
				$default[$key] = $asis;
			}
		}
		$this->setHeaders($default);
	}

	function getStatusLine() {
		return explode(' ', $this->_statusLine, 3);
	}

	function getResponseHeaders($in_require_hash = TRUE) {
		/*
			$in_require_hash : TRUE
				return array(
					'KEY-1' => 'VALUE-1',
					'KEY-2' => 'VALUE-2',
					...)
			$in_require_hash : FALSE
				return array(
					'KEY-1: VALUE-1',
					'KEY-2: VALUE-2',
					...)
		*/
		if ($in_require_hash) {
			// duplicate key is ignored !!
			$headers = array();
			foreach ($this->_responseHeaders as $header) {
				list($key, $val) = explode(':', $header, 2);
				$headers[trim($key)] = trim($val);
			}
			// hash
			return $headers;
		} else {
			// array
			return $this->_responseHeaders;
		}
	}

	function getResponseHeader($in_key) {
		foreach ($this->_responseHeaders as $header) {
			if (stristr($header, "{$in_key}:") === FALSE) {
				continue;
			} else {
				list($key, $val) = explode(':', $header, 2);
				return trim($val);
			}
		}
		return NULL;
	}

	function _substr($in_s, $in_e = NULL) {
		if (!$this->_rawResponse) {
			return '';
		}
		if ($in_e) {
			return substr($this->_rawResponse, $in_s, ($in_e - $in_s));
		} else {
			return substr($this->_rawResponse, $in_s);
		}
	}

	function getBody($in_raw = FALSE) {
		if ($in_raw) {
			return $this->_substr($this->_bodyOffset);
		}
		$chunked = $this->getResponseHeader('Transfer-Encoding');
		$compressed = $this->getResponseHeader('Content-Encoding');
		if ($chunked && (strtoupper($chunked) == 'CHUNKED')) {
			if ($compressed && (strtoupper($compressed) == 'GZIP')) {
				return gzdecode($this->_decodeChunkedBody());
			} else {
				return $this->_decodeChunkedBody();
			}
		} else {
			if ($compressed && (strtoupper($compressed) == 'GZIP')) {
				return gzdecode($this->_substr($this->_bodyOffset));
			} else {
				return $this->_substr($this->_bodyOffset);
			}
		}
	}

	function _decodeChunkedBody() {
		/*
			Chunked-Body =
				*chunk
				last-chunk
				trailer
				CRLF
			chunk =
				chunk-size [ chunk-extension ] CRLF
				chunk-data CRLF
			last-chunk =
				1*("0") [ chunk-extension ] CRLF
		*/
		$decoded = NULL;
		$size_s = $this->_bodyOffset;
		$rawlen = strlen($this->_rawResponse);
		while ($rawlen > $size_s) {
			$size_e = strpos($this->_rawResponse, CRLF, $size_s);
			$chunksize = hexdec($this->_substr($size_s, $size_e));
			if ($chunksize == 0) {
				return $decoded;
			} else {
				$data_s = $size_e + CRLFLEN;
				$data_e = $data_s + $chunksize;
				$decoded .= $this->_substr($data_s, $data_e);
				$size_s = $data_e + CRLFLEN;
			}
		}
		$this->_DP('invalid chunked-format');
	}
}

class CHttpRequestPool
{
	function CHttpRequestPool($in_timeout_all = 10) {
		$this->_pool = array();
		$this->_timeout_all = $in_timeout_all;
	}

	function attach($in_chttp) {
		array_push($this->_pool, $in_chttp);
		return count($this->_pool);
	}

	function send($is_1by1 = FALSE) {
		if ($is_1by1) {
			foreach ($this->_pool as $http) {
				$http->sendRequest();
				$http->_rcevResponseBlocking(FALSE, CHTTP_SOCKET_ABORT_TIMEOUT);
			}
		} else {
			$start = time();
			foreach ($this->_pool as $http) {
				$http->sendRequest();
			}
			do {
				$finishd = 0;
				$iosleep = TRUE;
				foreach ($this->_pool as $http) {
					switch ($http->_rcevResponse()) {
					case CHTTP_RCEVRESPONSE_WOULDBLOCK :
						$iosleep = FALSE;
						break;
					case CHTTP_RCEVRESPONSE_IOSLEEP :
						break;
					case CHTTP_RCEVRESPONSE_DONE :
						$finishd++;
						$iosleep = FALSE;
						break;
					default :
						$this->_DP('invalid return from _rcevResponse.');
						break;
					}
				}
				if ((time() - $start) > $this->_timeout_all) {
					foreach ($this->_pool as $http) {
						$http->_fclose();
					}
					break;
				}
				if ($iosleep) {
					// 50msec
					usleep(50000);
				}
			} while ($finishd != count($this->_pool));
		}
	}

	function getFinishedRequests() {
		return $this->_pool;
	}
}

/*
	unittest
*/

//return;

define('UT_SERVERMODE', (array_key_exists('UT_SERVERMODE', $_GET) ? $_GET['UT_SERVERMODE'] : NULL));

function ut_servermode_url($in_mode, $in_additional_query = '')
{
	if ($in_additional_query) {
		$additional_query = "&{$in_additional_query}";
	} else {
		$additional_query = '';
	}
	return "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?UT_SERVERMODE={$in_mode}{$additional_query}";
}

define('UT_SERVERMODE_GET', 1);
define('UT_SERVERMODE_POST', 2);
define('UT_SERVERMODE_AUTH', 3);
define('UT_SERVERMODE_3xxFROM', 4);
define('UT_SERVERMODE_3xxTO', 5);
define('UT_SERVERMODE_DELAYED', 6);
define('UT_SERVERMODE_CHUNKEDBODY', 7);
define('UT_SERVERMODE_GZIP', 8);
define('UT_SERVERMODE_DUPHEADER', 9);

define('UT_DELAYEDSEC', 1);

define('UT_CHUNK_ELEM', "<p>*\r*\r\n*\n*</p>");
define('UT_CHUNK_REPETITION', 750);

define('UT_GZIP_ELEM', "<span>Hello world</p><hr />");
define('UT_GZIP_REPETITION', 500);

define('UT_USER', 'user');
define('UT_PASS', 'pass%=%pass');

function __unittest__profile($in_id)
{
	array_push($profile, array('(1)', (microtime(TRUE) - $start)));
}

class CProf
{
	function CProf()
	{
		$this->_timeStamp = array();
	}

	function rec($in_id = NULL)
	{
		if ($in_id) {
			$id = $in_id;
		} else {
			$id = count($this->_timeStamp);
		}
		array_push($this->_timeStamp, array($id, microtime(TRUE)));
	}

	function showScore()
	{
		$before = 0;
print <<<EOHTML
<html>
<head>
<style type='text/css'>
TABLE {border-collapse: collapse;}
TABLE, TR, TH, TD {border: solid black 1px;}
TH {background-color: silver;}
</style>
</head>
<body>
<table>
<tr><th>id</th><th>timestamp</th><th>diff</th>
EOHTML;
		foreach ($this->_timeStamp as $val) {
			print "<tr><td>{$val[0]}</td>";
			print "<td>{$val[1]}</td>";
			if ($before == 0) {
				$diff = 0;
			} else {
				$diff = $val[1] - $before;
			}
			$diff = sprintf("%.6s", $diff); 
			print "<td>{$diff}</td></tr>\n";
			$before = $val[1];
		}
print <<<EOHTML
</table>
</body>
EOHTML;
		exit;
	}
}

function __unittest__CHttp($in_servermode = NULL)
{
	if ($in_servermode) {
		$output_default_body = TRUE;
		switch ($in_servermode) {
		case UT_SERVERMODE_GET :
			break;
		case UT_SERVERMODE_POST :
			foreach ($_POST as $key => $val) {
				header("X-{$key}: {$val}");
			}
			break;
		case UT_SERVERMODE_AUTH :
			if (isset($_SERVER['PHP_AUTH_USER'])) {
				header("X-UT_USER: {$_SERVER['PHP_AUTH_USER']}");
			}
			if (isset($_SERVER['PHP_AUTH_PW'])) {
				header("X-UT_PASS: {$_SERVER['PHP_AUTH_PW']}");
			}
			break;
		case UT_SERVERMODE_3xxFROM :
			header('Location: ' . ut_servermode_url(UT_SERVERMODE_3xxTO));
			break;
		case UT_SERVERMODE_3xxTO :
			break;
		case UT_SERVERMODE_DELAYED :
			sleep(UT_DELAYEDSEC);
			break;
		case UT_SERVERMODE_CHUNKEDBODY :
			$output_default_body = FALSE;
			for ($i = 0; $i < UT_CHUNK_REPETITION; $i++) {
				print UT_CHUNK_ELEM;
			}
			break;
		case UT_SERVERMODE_GZIP :
			header('Content-Encoding: gzip');
			header('Content-Type: text/html');
			$output_default_body = FALSE;
			$body = NULL;
			for ($i = 0; $i < UT_GZIP_REPETITION; $i++) {
				$body .= UT_GZIP_ELEM;
			}
			print gzencode($body);
			break;
		case UT_SERVERMODE_DUPHEADER : 
			header('X-DUP: 1st');
			header('x-dup: 2nd', FALSE);
			break;
		default :
			break;
		}
		if ($output_default_body) {
			header("X-CHttp-Test: {$in_servermode}");
			header('Content-Type: text/plain');
			print "SERVERMODE: {$in_servermode}";
		}
	} else {
		$prof = new CProf();
		$prof->rec();
		// (1) simple GET method
		$url = ut_servermode_url(UT_SERVERMODE_GET);
		$http = new CHttp($url);
		$http->GET();
		$r = $http->getResponseHeaders();
		if ($r['X-CHttp-Test'] != UT_SERVERMODE_GET) {
			$http->_DP('test (1) failed.');
		}
		// (2) simple POST method
		$prof->rec();
		$url = ut_servermode_url(UT_SERVERMODE_POST);
		$http = new CHttp($url);
		$post_params = array(
			'p1' => 'abc',
			'p2' => '===',
			'p3' => '%%%'
		);
		$http->POST($post_params);
		$r = $http->getResponseHeaders();
		foreach ($post_params as $key => $val) {
			if (array_key_exists("X-{$key}", $r)) {
				if ($r["X-{$key}"] != $val) {
					$http->_DP("test (2) failed. [value : {$val}]");
				}
			} else {
				$http->_DP("test (2) failed. [key : {$key}]");
			}
		}
		// (3) Basic Authorization
		$prof->rec();
		$url = ut_servermode_url(UT_SERVERMODE_AUTH);
		$http = new CHttp($url);
		$http->setAuthHeader(UT_USER, UT_PASS);
		$http->GET();
		$r = $http->getResponseHeaders();
		if ($r['X-UT_USER'] != UT_USER) {
			$http->_DP('test (3) failed. (UT_USER)');
		}
		if ($r['X-UT_PASS'] != UT_PASS) {
			$http->_DP('test (3) failed. (UT_PASS)');
		}
		// (4) HTTP Redirection
		$prof->rec();
		$url = ut_servermode_url(UT_SERVERMODE_3xxFROM);
		$http = new CHttp($url);
		$http->GET();
		$r = $http->getResponseHeaders();

		$s = $http->getStatusLine();
		if ($s[1] != 302) {
			$http->_DP("test (4) failed. (status code : {$s[1]})");
		}
		$http = new CHttp($r['Location']);
		$http->GET();
		$r = $http->getResponseHeaders();
		if ($r['X-CHttp-Test'] != UT_SERVERMODE_3xxTO) {
			$http->_DP('test (4) failed.');
		}
		// (5) 50 Requests at the same time
		$prof->rec();
		$request = 50;
		$start = time();
		$pool = new CHttpRequestPool();
		for ($i = 0; $i < $request; $i++) {
			$pool->attach((new CHttp(ut_servermode_url(UT_SERVERMODE_DELAYED))));
		}
		$pool->send(FALSE);
		$responses = $pool->getFinishedRequests();
		for ($i = 0; $i < $request; $i++) {
			$r = $responses[$i]->getResponseHeaders();
			if ($r['X-CHttp-Test'] != UT_SERVERMODE_DELAYED) {
				$responses[$i]->_DP("test (5) failed. ({$i})");
			}
		}
		if ((time() - $start) > UT_DELAYEDSEC * 5) {
			print 'test (5) failed. time is over.';
			exit;
		}
		// (6) Transfer-Encoding: chunked
		$prof->rec();
		$url = ut_servermode_url(UT_SERVERMODE_CHUNKEDBODY);
		$http = new CHttp($url);
		$http->GET();
		$body_received = $http->getBody();
		$body_sent = NULL;
		for ($i = 0; $i < UT_CHUNK_REPETITION; $i++) {
			$body_sent .= UT_CHUNK_ELEM;
		}
		if (strpos($body_received, $body_sent) !== 0) {
			$http->_DP('test (6) failed.');
		}
		// (7) Content-Encoding: gzip
/*
		// debugging now !!
		$prof->rec();
		$url = ut_servermode_url(UT_SERVERMODE_GZIP);
		$http = new CHttp($url);
		$http->setHeader('Accept-Encoding', 'gzip');
		$http->GET();
		exit;
*/
		// (8) duplicate
		$prof->rec();
		$url = ut_servermode_url(UT_SERVERMODE_DUPHEADER);
		$http = new CHttp($url);
		$http->GET();
		$dup = $http->getResponseHeader('X-Dup');
		if (!$dup || ($dup != '1st')) {
			$http->_DP('test (8) failed.');
		}
		$headers = $http->getResponseHeaders(FALSE);
		if (!in_array('X-DUP: 1st', $headers)) {
			$http->_DP('test (8) failed. (1st is not found)');
		}
		if (!in_array('x-dup: 2nd', $headers)) {
			$http->_DP('test (8) failed. (2st is not found)');
		}
		// (9) safe API
		$prof->rec();
		$http = new CHttp($url);
		$r = $http->getStatusLine();
		if (!is_array($r)) {
			$http->_DP('test (9-1) failed.');
		}
		$r = $http->getResponseHeader('hoge');
		if ($r !== NULL) {
			$http->_DP('test (9-2) failed.');
		}
		$r = $http->getResponseHeaders(TRUE);
		if (!is_array($r)) {
			$http->_DP('test (9-3) failed.');
		}
		$r = $http->getResponseHeaders(FALSE);
		if (!is_array($r)) {
			$http->_DP('test (9-4) failed.');
		}
		$r = $http->getBody();
		if ($r !== '') {
			$http->_DP('test (9-5) failed.');
		}
		$prof->rec();
		$prof->showScore();
	}
}

function __externaltest__CHttp($in_url)
{
	$http = new CHttp($in_url);
	$http->GET();
	$headers = $http->getResponseHeaders();
	foreach ($headers as $key => $val) {
		header("{$key}: {$val}");
	}
	print $http->getBody(TRUE);
}

define('CHTTP_SELF', substr(str_replace(__DIR__, '', __FILE__), 1));
define('CHTTP_CALLER', substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1));

if (UT_SERVERMODE) {
	__unittest__CHttp(UT_SERVERMODE);
} else {
	if (CHTTP_SELF == CHTTP_CALLER) {
		__unittest__CHttp();
		//__externaltest__CHttp('http://tools.ietf.org/rfc/rfc2616.txt');
		//__externaltest__CHttp('http://www.yahoo.co.jp/');
	} else {
		return;
	}
}

?> 
