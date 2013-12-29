<?php

/*
	HTTP Function
*/

define('CRLF', "\r\n");
define('CRLFLEN', strlen(CRLF));

function util_array2query($in_array)
{
	$params = array();
	foreach ($in_array as $key => $val)
		array_push($params, "{$key}=" . urlencode($val));
	return implode('&', $params);
}

function util_array2multipartFormData($in_array, $in_boundary)
{
	$lines = array();
	foreach ($in_array as $key => $val) {
		array_push($lines, "--{$in_boundary}");
		array_push($lines, "Content-Disposition: form-data; name=\"{$key}\"");
		array_push($lines, "");
		array_push($lines, $val);
	}
	array_push($lines, "--{$in_boundary}--");
	return implode(CRLF, $lines);
}

function util_parse_url($in_url)
{
	$res = parse_url($in_url);
	if (!array_key_exists('path', $res)) {
		$res['path'] = '/';
	}
	return $res;
}

function util_dp($in_obj, $in_string = NULL) {
	header('Content-Type: text/plain');
	if (is_null($in_string)) {
		print_r(debug_backtrace());
	} else {
		print $in_string;
	}
	print "\n\n-----\n\n";
	print_r($in_obj);
	exit;
}

define('CHTTP_SOCKET_OPEN_TIMEOUT', 1);
define('CHTTP_SOCKET_BLOCKING_TIMEOUT', 5);
define('CHTTP_IOSLEEP_USEC', 50000); // 50msec

define('CHTTP_READYSTATE_UNINITIALIZED', '0');
define('CHTTP_READYSTATE_LOADING_H', '1-1');
define('CHTTP_READYSTATE_LOADING_B', '1-2');
define('CHTTP_READYSTATE_LOADED', '2');
define('CHTTP_READYSTATE_INTERACTIVE', '3');
define('CHTTP_READYSTATE_COMPLETED', '4');

define('CHTTP_RCEVRESPONSE_WOULDBLOCK', 1);
define('CHTTP_RCEVRESPONSE_IOSLEEP', 2);
define('CHTTP_RCEVRESPONSE_DONE', 3);

class CHttp
{
	function CHttp($in_url, $in_proxy = NULL) {
		// $in_proxy : "hostname" or "hostname:port"
		$this->_parsedUrl = util_parse_url($in_url);
		if ($in_proxy) {
			$pos = strpos($in_proxy, ':');
			if ($pos === FALSE) {
				$host = $in_proxy;
				$port = 80;
			} else {
				$host = substr($in_proxy, 0, $pos);
				$port = substr($in_proxy, ($pos + 1));
			}
			$this->_proxy = array('host' => $host, 'port' => $port);
		} else {
			$this->_proxy = NULL;
		}
		$this->_readyState = CHTTP_READYSTATE_UNINITIALIZED;
		$this->_handle = NULL;
		$this->_headers = array();
		$this->_setDefaultHeaders();
		$this->_rawRequest = NULL;
		$this->_rawResponse = NULL;
		$this->_bodyOffset = 0;
		$this->_statusLine = NULL;
		$this->_responseHeaders = array();
		$this->_method = 'GET';
		$this->_cache = NULL;
		$this->_cacheLife = 0;
		$this->_cacheRead = FALSE;
		$this->_cacheUpdated = FALSE;
	}

	function _DP($in_string = NULL) {
		util_dp($this, $in_string);
	}

	function url() {
		$u = $this->_parsedUrl;
		$url = "{$u['scheme']}://{$u['host']}{$u['path']}";
		if (array_key_exists('query', $u)) {
			$url .= "?{$u['query']}";
		}
		return $url;
	}

	function useCache($in_cache_path, $in_cache_life) {
		if (is_dir($in_cache_path)) {
			$path = "{$in_cache_path}/" . md5($this->url());
			if (!file_exists($path)) {
				$h = @fopen($path, 'w+');
				if ($h) {
					fclose($h);
					unlink($path);
				} else {
					return FALSE;
				}
			}
			$this->_cache = $path;
			$this->_cacheLife = $in_cache_life;
			return TRUE;
		}
		return FALSE;
	}

	function _isFreshCache() {
		if ($this->_cache) {
			if (file_exists($this->_cache) && (time() - filemtime($this->_cache) < $this->_cacheLife)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	function _saveCache() {
		if ($this->_cache) {
			$h = fopen($this->_cache, 'w+');
			if ($h) {
				fwrite($h, $this->_rawResponse);
				fclose($h);
				chmod($this->_cache, 0666);
				clearstatcache();
				return TRUE;
			}
		}
		return FALSE;
	}

	function cacheRead() {
		return $this->_cacheRead;
	}

	function cacheUpdated() {
		return $this->_cacheUpdated;
	}

	function _streamOpen($in_host, $in_port) {
		if ($this->_isFreshCache()) {
			$this->_cacheRead = TRUE;
			$this->_handle = @fopen($this->_cache, 'r');
			if (!$this->_handle) {
				$this->_DP('check filesystem etc to use cache.');
			}
		} else {
			$this->_handle = @fsockopen($in_host, $in_port, $errno, $errstr, CHTTP_SOCKET_OPEN_TIMEOUT);
			if (!$this->_handle) {
				return FALSE;
			}
			stream_set_blocking($this->_handle, FALSE);
		}
		return TRUE;
	}

	function _streamPuts($in_data) {
		if (!$this->_handle) {
			// error of fputs
			return FALSE;
		}
		if ($this->cacheRead()) {
			// cache
			return TRUE;
		} else {
			// network
			return fputs($this->_handle, $in_data);
		}
	}

	function _streamGets() {
		if (!$this->_handle) {
			// error of fgets
			return FALSE;
		}
		return fgets($this->_handle);
	}

	function _streamEof() {
		if (!$this->_handle) {
			// error of feof
			return FALSE;
		}
		return feof($this->_handle);
	}

	function _streamClose() {
		if (!$this->_handle) {
			return;
		}
		fclose($this->_handle);
		$this->_handle = NULL;
		$this->_readyState = CHTTP_READYSTATE_LOADED;
		if ($this->cacheRead()) {
			// cache
			return;
		} else {
			// network
			$s = $this->getStatusLine();
			if (!$s) {
				return;
			}
			if (($s[1] == 200) && ($this->getBodyLength() > 0)) {
				$this->_cacheUpdated = $this->_saveCache();
			}
		}
	}

	function sendRequest($in_method = NULL, $in_body = NULL) {
		/*
			TRUE  : sent
			FALSE : timeout (fsockopen)
		*/
		if ($this->_readyState != CHTTP_READYSTATE_UNINITIALIZED) {
			$this->_DP('can not re-use this object. fix your code.');
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
				$this->_DP('https & proxy are not supported at the same time. fix your code.');
			} else {
				$host = "tcp://{$this->_proxy['host']}";
				$port = $this->_proxy['port'];
			}
			$this->_rawRequest = "{$this->_method} {$this->_parsedUrl['scheme']}://{$this->_parsedUrl['host']}{$request_line} HTTP/1.1" . CRLF;
		} else {
			if ($this->_parsedUrl['scheme'] == 'https') {
				$host = "ssl://{$this->_parsedUrl['host']}";
				$port = 443;
			} else {
				$host = "tcp://{$this->_parsedUrl['host']}";
				$port = 80;
			}
			$this->_rawRequest = "{$this->_method} {$request_line} HTTP/1.1" . CRLF;
		}
		$host = str_replace('://localhost', '://127.0.0.1', $host);
		if (!$this->_streamOpen($host, $port)) {
			return FALSE;
		}
		foreach ($this->_headers as $key => $val) {
			$this->_rawRequest .= "{$key}: {$val}" . CRLF;
		}
		$this->_rawRequest .= CRLF;
		if ($in_body) {
			$this->_rawRequest .= $in_body;
		}
		if ($this->_streamPuts($this->_rawRequest)) {
			$this->_readyState = CHTTP_READYSTATE_LOADING_H;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function _rcevResponse($in_header_only = FALSE) {
		$ret = CHTTP_RCEVRESPONSE_WOULDBLOCK;
		switch ($this->_readyState) {
		case CHTTP_READYSTATE_UNINITIALIZED :
			$this->_DP('sendRequest is required before _rcevResponse. fix your code.');
			break;
		case CHTTP_READYSTATE_LOADING_H :
		case CHTTP_READYSTATE_LOADING_B :
			if ($this->_streamEof()) {
				$this->_streamClose();
				$ret = CHTTP_RCEVRESPONSE_DONE;
			} else {
				$line = $this->_streamGets();
				if ($line) {
					$this->_rawResponse .= $line;
				} else {
					// no-data
					$ret = CHTTP_RCEVRESPONSE_IOSLEEP;
					break;
				}
				if ($this->_readyState == CHTTP_READYSTATE_LOADING_H) {
					$line = trim($line);
					if ($line) {
						// header
						if ($this->_statusLine === NULL) {
							$this->_statusLine = $line;
						} else {
							array_push($this->_responseHeaders, $line);
						}
					} else {
						// header --> entity body
						$this->_bodyOffset = strlen($this->_rawResponse);
						if ($in_header_only) {
							$this->_streamClose();
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
		/*
			TRUE  : done
			FALSE : timeout (I/O)
		*/
		$start = time();
		while (TRUE) {
			switch ($this->_rcevResponse($in_header_only)) {
			case CHTTP_RCEVRESPONSE_WOULDBLOCK :
				break;
			case CHTTP_RCEVRESPONSE_IOSLEEP :
				usleep(CHTTP_IOSLEEP_USEC);
				break;
			case CHTTP_RCEVRESPONSE_DONE :
			default :
				break 2;
			}
			if ($in_timeout && (time() - $start > $in_timeout)) {
				$this->_streamClose();
				return FALSE;
			}
		}
		return TRUE;
	}

	function GET($in_header_only = FALSE, $in_timeout = CHTTP_SOCKET_BLOCKING_TIMEOUT) {
		/*
			TRUE  : done
			FALSE : timeout
		*/
		if ($this->sendRequest('GET')) {
			return $this->_rcevResponseBlocking($in_header_only, $in_timeout);
		}
		return FALSE;
	}

	function POST($in_postdata, $in_is_urlenc = TRUE, $in_header_only, $in_timeout) {
		/*
			TRUE  : done
			FALSE : timeout
		*/
		if (is_array($in_postdata)) {
			if ($in_is_urlenc) {
				$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
				$body = util_array2query($in_postdata);
			} else {
				$boundary = 'chttp' . rand();
				$this->setHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);
				$body = util_array2multipartFormData($in_postdata, $boundary);
			}
		} else {
			$body = $in_postdata;
		}
		if ($this->sendRequest('POST', $body)) {
			return $this->_rcevResponseBlocking($in_header_only, $in_timeout);
		}
		return FALSE;
	}

	function POST_URLENC($in_postdata, $in_header_only = FALSE, $in_timeout = CHTTP_SOCKET_BLOCKING_TIMEOUT) {
		$this->POST($in_postdata, TRUE, $in_header_only, $in_timeout);
	}

	function POST_MULTIPART($in_postdata, $in_header_only = FALSE, $in_timeout = CHTTP_SOCKET_BLOCKING_TIMEOUT) {
		$this->POST($in_postdata, FALSE, $in_header_only, $in_timeout);
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
				$default[$key] = $val;
			}
		}
		$this->setHeaders($default);
	}

	function getStatusLine() {
		if ($this->_statusLine) {
			return explode(' ', $this->_statusLine, 3);
		} else {
			return NULL;
		}
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

	function getBodyLength() {
		if ($this->_bodyOffset > 0) {
			return strlen($this->_rawResponse) - $this->_bodyOffset;
		} else {
			return 0;
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

define('POOL_ESTATE_DEFAULT', '1');
define('POOL_ESTATE_TIMEOUT_OPEN', '2');
define('POOL_ESTATE_TIMEOUT_IO', '3');
define('POOL_ESTATE_DONE', '4');

class CHttpRequestPool
{
	function CHttpRequestPool($in_cache_path = NULL, $in_cache_life = 0, $in_timeout_all = 10) {
		$this->_pool = array();
		$this->_cache_path = $in_cache_path;
		$this->_cache_life = $in_cache_life;
		$this->_timeout_all = $in_timeout_all;
	}

	function attach($in_chttp, $in_method = NULL, $in_entity = NULL) {
		if ($this->_cache_path) {
			$in_chttp->useCache($this->_cache_path, $this->_cache_life);
		}
		array_push($this->_pool,
			array(
				'chttp'  => $in_chttp,
				'method' => $in_method,
				'entity' => $in_entity,
				'mtime' => -1,
				'state'  => POOL_ESTATE_DEFAULT));
		return count($this->_pool);
	}

	function send($is_1by1 = FALSE, $in_header_only = FALSE) {
		$entries_in_pool = count($this->_pool);
		if ($is_1by1) {
			for ($i = 0; $i < $entries_in_pool; $i++) {
				$e =& $this->_pool[$i];
				if ($e['chttp']->sendRequest($e['method'], $e['entity'])) {
					if ($e['chttp']->_rcevResponseBlocking(FALSE, CHTTP_SOCKET_BLOCKING_TIMEOUT)) {
						$e['state'] = POOL_ESTATE_DONE;
					} else {
						$e['state'] = POOL_ESTATE_TIMEOUT_IO;
					}
				} else {
					$e['state'] = POOL_ESTATE_TIMEOUT_OPEN;
				}
			}
		} else {
			$finished = 0;
			$start = microtime(TRUE);
			$limit = $start + $this->_timeout_all;
			for ($i = 0; $i < $entries_in_pool; $i++) {
				$e =& $this->_pool[$i];
				if (!$e['chttp']->sendRequest($e['method'], $e['entity'])) {
					$e['state'] = POOL_ESTATE_TIMEOUT_OPEN;
					$finished++;
				}
			}
			do {
				$iosleep = TRUE;
				for ($i = 0; $i < $entries_in_pool; $i++) {
					if ($this->_pool[$i]['state'] == POOL_ESTATE_DEFAULT) {
						$e =& $this->_pool[$i];
						switch ($e['chttp']->_rcevResponse($in_header_only)) {
						case CHTTP_RCEVRESPONSE_WOULDBLOCK :
							$iosleep = FALSE;
							break;
						case CHTTP_RCEVRESPONSE_IOSLEEP :
							break;
						case CHTTP_RCEVRESPONSE_DONE :
						default :
							$iosleep = FALSE;
							$e['mtime'] = microtime(TRUE) - $start;
							$e['state'] = POOL_ESTATE_DONE;
							$finished++;
							break;
						}
					}
				}
				if (microtime(TRUE) > $limit) {
					for ($i = 0; $i < $entries_in_pool; $i++) {
						$e =& $this->_pool[$i];
						$e['chttp']->_streamClose();
						if ($e['state'] == POOL_ESTATE_DEFAULT) {
							$e['state'] = POOL_ESTATE_TIMEOUT_IO;
						}
					}
					return;
				}
				if ($iosleep) {
					usleep(CHTTP_IOSLEEP_USEC);
				}
			} while ($finished != $entries_in_pool);
		}
	}

	function getRequest($in_url, $in_target = 'chttp') {
		// fragment must be removed in "$in_url".
		foreach ($this->_pool as $entry) {
			if ($in_url != $entry['chttp']->url()) {
				continue;
			} else {
				return $entry[$in_target];
			}
		}
		return NULL;
	}

	function getTimeRecord($in_url) {
		return $this->getRequest($in_url, 'mtime');
	}

	function getFinishedRequests() {
		$ret = array();
		foreach ($this->_pool as $entry) {
			if ($entry['state'] == POOL_ESTATE_DONE) {
				array_push($ret, $entry['chttp']);
			}
		}
		return $ret;
	}
}

/*
	Cookie Function
*/

date_default_timezone_set('Asia/Tokyo');

class CCookie
{
	function CCookie($in_string, $in_url = NULL) {
		/*
			$in_url :
			- http://aaa/bbb/ccc/ ... "ccc" means path
			- http://aaa/bbb/ccc  ... "ccc" means file
		*/
		$this->_data = NULL;
		$this->_props = array();
		foreach (explode(';', $in_string) as $piece) {
			$piece = trim($piece);
			$s = explode('=', $piece, 2);
			if (count($s) == 2) {
				if (!$this->_data) {
					$this->_data = $piece;
				} else {
					$this->_props[$s[0]] = $s[1];
				}
			} else {
				$this->_props[$piece] = TRUE;
			}
		}
		$domain = $this->_getProp('domain');
		if ($domain) {
			$this->_setNormalizedDomain($domain);
		}
		$path = $this->_getProp('path');
		if ($path) {
			$this->_setNormalizedPath($path, FALSE);
		}
		if ($in_url) {
			$url = util_parse_url($in_url);
			if (!$domain) {
				$this->_setNormalizedDomain($url['host']);
			}
			if (!$path) {
				$this->_setNormalizedPath($url['path'], TRUE);
			}
		}
	}

	function _DP($in_string = NULL) {
		util_dp($this, $in_string);
	}

	function _prop($in_key, $in_val = NULL) {
		foreach ($this->_props as $key => $val) {
			if (strtoupper($key) == strtoupper($in_key)) {
				if ($in_val) {
					$this->_props[$key] = $in_val;
				}
				return $val;
			}
		}
		if ($in_val) {
			$this->_props[$in_key] = $in_val;
		}
		return NULL;
	}

	function _getProp($in_key) {
		return $this->_prop($in_key);
	}

	function _setProp($in_key, $in_val) {
		$this->_prop($in_key, $in_val);
	}

	function _setNormalizedDomain($in_domain) {
		if (substr($in_domain, 0, 1) == '.') {
			// first char is "."
			$this->_setProp('domain', $in_domain);
		} else {
			// add "."
			$this->_setProp('domain', ".{$in_domain}");
		}
	}

	function _setNormalizedPath($in_path, $in_assume_file) {
		$pos = strrpos($in_path, '/');
		if ($pos == (strlen($in_path) - 1)) {
			// last char is "/"
			$this->_setProp('path', $in_path);
		} else {
			if ($in_assume_file) {
				// remove "file"
				$this->_setProp('path', substr($in_path, 0, ($pos + 1)));
			} else {
				// add "/"
				$this->_setProp('path', "{$in_path}/");
			}
		}
	}

	function _canSend($in_dst_url) {
		// expires
		$expires = $this->_getProp('expires');
		if ($expires) {
			if (time() > strtotime($expires)) {
				return FALSE;
			}
		}
		// domain & path
		$dst = util_parse_url($in_dst_url);
		// domain : "$d_d" should be sub-domain.
		if (strpos(strrev($dst['host']), strrev($this->_getProp('domain'))) === 0) {
			// "$d_d" is sub-domain, since domain in CCookie has been alerady normalized.
		} else {
			if (".{$dst['host']}" != $this->_getProp('domain')) {
				return FALSE;
			}
		}
		// path : "$d_p" should be sub-path.
		if (strpos($dst['path'], $this->_getProp('path')) === 0) {
			// "$d_p" is sub-path, since path in CCookie has been alerady normalized.
		} else {
			if ("{$dst['path']}/" != $this->_getProp('path')) {
				return FALSE;
			}
		}
		return TRUE;
	}

	function compose($in_dst_url) {
		if ($this->_canSend($in_dst_url)) {
			return $this->_data;
		} else {
			return NULL;
		}
	}
}

class CHttpCookie extends CCookie
{
	function CHttpCookie($in_http) {
		$u = $in_http->_parsedUrl;
		$url = "{$u['scheme']}://{$u['host']}{$u['path']}"; 
		// sometimes, server may send some "Set-Cookie" headers !! e.g. Google
		$str = $in_http->getResponseHeader('Set-Cookie');
		parent::CCookie($str, $url);
	}
}

class CCookiePool
{
	function CCookiePool() {
		$this->_pool = array();
	}

	function _DP($in_string = NULL) {
		util_dp($this, $in_string);
	}

	function addCookie($in_string, $in_url = NULL) {
		$cookie = new CCookie($in_string, $in_url);
		array_push($this->_pool, $cookie);
	}

	function addCHttpCookie($in_http) {
		$cookie = new CHttpCookie($in_http);
		array_push($this->_pool, $cookie);
	}

	function compose($in_dst_url) {
		$pool = array();
		foreach ($this->_pool as $cookie) {
			if ($cookie->_canSend($in_dst_url)) {
				array_push($pool, $cookie->_data);
			} else {
				continue;
			}
		}
		return implode('; ', $pool);
	}
}

/*
	Unit Test
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
define('UT_SERVERMODE_SETCOOKIE', 10);
define('UT_SERVERMODE_RAND_RES', 11);
define('UT_SERVERMODE_LEN1', 12);
define('UT_SERVERMODE_LEN0', 13);
define('UT_SERVERMODE_404', 14);

define('UT_DELAYEDSEC', 1);

define('UT_CHUNK_ELEM', "<p>*\r*\r\n*\n*</p>");
define('UT_CHUNK_REPETITION', 750);

define('UT_GZIP_ELEM', "<span>Hello world</p><hr />");
define('UT_GZIP_REPETITION', 500);

define('UT_USER', 'user');
define('UT_PASS', 'pass%=%pass');

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
		case UT_SERVERMODE_SETCOOKIE :
			setcookie('n', 'v', time() + 3600, '/');
			break;
		case UT_SERVERMODE_RAND_RES :
			$output_default_body = FALSE;
			print rand();
			break;
		case UT_SERVERMODE_LEN1 :
			$output_default_body = FALSE;
			print '*';
			break;
		case UT_SERVERMODE_LEN0 :
			$output_default_body = FALSE;
			break;
		case UT_SERVERMODE_404 :
			header('HTTP/1.1 404 Not Found');
			$output_default_body = FALSE;
			print '*';
			break;
		default :
			break;
		}
		if ($output_default_body) {
			header("X-CHttp-Test: {$in_servermode}");
			header('Content-Type: text/plain');
			print "SERVERMODE: {$in_servermode}";
		}
		return;
	} else {
		$cachedir = './.cache';
		if (is_dir($cachedir)) {
			if ($dh = opendir($cachedir)) {
				while (($file = readdir($dh)) !== FALSE) {
					if (is_file($file)) {
						unlink($file);
					}
				}
				closedir($dh);
			}
		} else {
			mkdir($cachedir);
		}
		$prof = new CProf();
$symbol = <<<EOSYMBOL
(Cookie-1) path #1
EOSYMBOL;
		$prof->rec($symbol);
		$d_paths = array(
			'/' => FALSE,
			'/aaa' => FALSE,
			'/aaa/' => FALSE,
			'/aaa/bb' => FALSE,
			'/aaa/bbb' => TRUE,
			'/aaa/bbbb' => FALSE,
			'/aaa/bb/' => FALSE,
			'/aaa/bbb/' => TRUE,
			'/aaa/bbbb/' => FALSE,
			'/aaa/bb/ccc' => FALSE,
			'/aaa/bbb/ccc' => TRUE,
			'/aaa/bbbb/ccc' => FALSE,
			'/aaa/bb/ccc/' => FALSE,
			'/aaa/bbb/ccc/' => TRUE,
			'/aaa/bbbb/ccc/' => FALSE
		);
		$template = 'n=v; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=_REPLACE_ME_; domain=xxx; secure; httponly';
		$c_paths = array('/aaa/bbb/', '/aaa/bbb');
		foreach ($c_paths as $c_path) {
			$cookie = new CCookie(str_replace('_REPLACE_ME_', $c_path, $template));
			foreach ($d_paths as $d_path => $expected) {
				if ($cookie->compose("http://xxx{$d_path}") == 'n=v') {
					$composed = TRUE;
				} else {
					$composed = FALSE;
				}
				if ($composed != $expected) {
					$hint = $expected ? 'sent' : 'ignored';
					$cookie->_DP("test (Cookie-1) failed. (from '{$c_path}' to '{$d_path}' should be {$hint})");
				}
			}
		}
$symbol = <<<EOSYMBOL
(Cookie-2) path #2
EOSYMBOL;
		$prof->rec($symbol);
		$template = 'n=v; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/; domain=xxx; secure; httponly';
		$cookie = new CCookie($template);
		if ($cookie->compose("http://xxx") != 'n=v') {
			$cookie->_DP('test (Cookie-2.1) failed.');
		}
		if ($cookie->compose("http://xxx/") != 'n=v') {
			$cookie->_DP('test (Cookie-2.2) failed.');
		}
		if ($cookie->compose("http://xxx/aaa") != 'n=v') {
			$cookie->_DP('test (Cookie-2.3) failed.');
		}
		if ($cookie->compose("http://xxx/aaa/") != 'n=v') {
			$cookie->_DP('test (Cookie-2.4) failed.');
		}
$symbol = <<<EOSYMBOL
(Cookie-3) domain
EOSYMBOL;
		$prof->rec($symbol);
		$d_hosts = array(
			'aaa.com' => FALSE,
			'bbb.aaa.com' => TRUE,
			'ccc.aaa.com' => FALSE,
			'ddd.bbb.aaa.com' => TRUE
		);
		$template = 'n=v; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/; domain=_REPLACE_ME_; secure; httponly';
		$c_hosts = array('bbb.aaa.com', '.bbb.aaa.com');
		foreach ($c_hosts as $c_host) {
			$cookie = new CCookie(str_replace('_REPLACE_ME_', $c_host, $template));
			foreach ($d_hosts as $d_host => $expected) {
				if ($cookie->compose("http://{$d_host}/") == 'n=v') {
					$composed = TRUE;
				} else {
					$composed = FALSE;
				}
				if ($composed != $expected) {
					$hint = $expected ? 'sent' : 'ignored';
					$cookie->_DP("test (Cookie-1) failed. (from '{$c_host}' to '{$d_host}' should be {$hint})");
				}
			}
		}
$symbol = <<<EOSYMBOL
(Cookie-4) expires
EOSYMBOL;
		$prof->rec($symbol);
		$cookie = new CCookie('n=v; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/; domain=xxx; secure; httponly');
		if ($cookie->compose('http://xxx/') != 'n=v') {
			$cookie->_DP('test (Cookie-4.1) failed.');
		}
		$cookie = new CCookie('n=v; expires=Thu, 01-Jan-2010 01:00:00 GMT; path=/; domain=xxx; secure; httponly');
		if ($cookie->compose('http://xxx/') == 'n=v') {
			$cookie->_DP('test (Cookie-4.2) failed.');
		}
		$cookie = new CCookie('n=v; expires=XXXXXXXXXXXXXXXXXXXXXXXXX GMT; path=/; domain=xxx; secure; httponly');
		if ($cookie->compose('http://xxx/') == 'n=v') {
			$cookie->_DP('test (Cookie-4.3) failed.');
		}
$symbol = <<<EOSYMBOL
(Cookie-5) Cookie with HTTP
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_SETCOOKIE);
		$http = new CHttp($url);
		$http->GET();
		$cookie = new CHttpCookie($http);
		if ($cookie->compose("http://sub-domain.{$_SERVER['HTTP_HOST']}") != 'n=v') {
			$cookie->_DP('test (Cookie-5.1) failed.');
		}
		$url = ut_servermode_url(UT_SERVERMODE_GET);
		$http = new CHttp($url);
		$http->GET();
		$cookie = new CHttpCookie($http);
		if ($cookie->compose("http://{$_SERVER['HTTP_HOST']}/")) {
			$cookie->_DP('test (Cookie-5.2) failed.');
		}
$symbol = <<<EOSYMBOL
(Cookie-6) CCookiePool
EOSYMBOL;
		$prof->rec($symbol);
		$pool = new CCookiePool();
		$raws = array(
			'n1=v1; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/;      domain=x',
			'n2=v2; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/a;     domain=x',
			'n3=v3; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/a/;    domain=x',
			'n4=v4; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/a/b;   domain=x',
			'n5=v5; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/a/b/;  domain=x',
			'n6=v6; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/a/b/c; domain=x',
			'n7=v7; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/;      domain=.x',
			'n8=v8; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/;      domain=y.x',
			'n9=v9; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/;      domain=.y.x',
			'n0=v0; expires=Thu, 01-Jan-2020 01:00:00 GMT; path=/;      domain=z.y.x',
		);
		foreach ($raws as $raw) {
			$pool->addCookie($raw);
		}
		$url = ut_servermode_url(UT_SERVERMODE_SETCOOKIE);
		$http = new CHttp($url);
		$http->GET();
		$pool->addCHttpCookie($http);
		if ($pool->compose('http://y.x/a/b') != 'n1=v1; n2=v2; n3=v3; n4=v4; n5=v5; n7=v7; n8=v8; n9=v9') {
			$pool->_DP('test (Cookie-6) failed.');
		}
$symbol = <<<EOSYMBOL
(HTTP-1) simple GET method
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_GET);
		$http = new CHttp($url);
		$http->GET();
		$r = $http->getResponseHeaders();
		if ($r['X-CHttp-Test'] != UT_SERVERMODE_GET) {
			$http->_DP('test (HTTP-1) failed.');
		}
$symbol = <<<EOSYMBOL
(HTTP-2) simple POST method
EOSYMBOL;
		$prof->rec($symbol);
		$post_params = array(
			'p1' => 'abc',
			'p2' => '===',
			'p3' => '%%%'
		);
		$url = ut_servermode_url(UT_SERVERMODE_POST);
		$http = new CHttp($url);
		$http->POST_URLENC($post_params);
		$r = $http->getResponseHeaders();
		foreach ($post_params as $key => $val) {
			if (array_key_exists("X-{$key}", $r)) {
				if ($r["X-{$key}"] != $val) {
					$http->_DP("test (HTTP-2-1) failed. [value : {$val}]");
				}
			} else {
				$http->_DP("test (HTTP-2-1) failed. [key : {$key}]");
			}
		}
		$http = new CHttp($url);
		$http->POST_MULTIPART($post_params);
		$r = $http->getResponseHeaders();
		foreach ($post_params as $key => $val) {
			if (array_key_exists("X-{$key}", $r)) {
				if ($r["X-{$key}"] != $val) {
					$http->_DP("test (HTTP-2-2) failed. [value : {$val}]");
				}
			} else {
				$http->_DP("test (HTTP-2-2) failed. [key : {$key}]");
			}
		}
$symbol = <<<EOSYMBOL
(HTTP-3) Basic Authorization
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_AUTH);
		$http = new CHttp($url);
		$http->setAuthHeader(UT_USER, UT_PASS);
		$http->GET();
		$r = $http->getResponseHeaders();
		if ($r['X-UT_USER'] != UT_USER) {
			$http->_DP('test (HTTP-3) failed. (UT_USER)');
		}
		if ($r['X-UT_PASS'] != UT_PASS) {
			$http->_DP('test (HTTP-3) failed. (UT_PASS)');
		}
$symbol = <<<EOSYMBOL
(HTTP-4) HTTP Redirection
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_3xxFROM);
		$http = new CHttp($url);
		$http->GET();
		$r = $http->getResponseHeaders();
		$s = $http->getStatusLine();
		if ($s[1] != 302) {
			$http->_DP("test (HTTP-4) failed. (status code : {$s[1]})");
		}
		$http = new CHttp($r['Location']);
		$http->GET();
		$r = $http->getResponseHeaders();
		if ($r['X-CHttp-Test'] != UT_SERVERMODE_3xxTO) {
			$http->_DP('test (HTTP-4) failed.');
		}
$symbol = <<<EOSYMBOL
(HTTP-5) 50 Requests at the same time
EOSYMBOL;
		$prof->rec($symbol);
		$request = 50;
		$start = time();
		$pool = new CHttpRequestPool();
		for ($i = 0; $i < $request; $i++) {
			$pool->attach((new CHttp(ut_servermode_url(UT_SERVERMODE_DELAYED, "i={$i}"))));
		}
		// $pool->send(TRUE);
		$pool->send(FALSE);
		$responses = $pool->getFinishedRequests();
		for ($i = 0; $i < $request; $i++) {
			$r = $responses[$i]->getResponseHeaders();
			if ($r['X-CHttp-Test'] != UT_SERVERMODE_DELAYED) {
				$responses[$i]->_DP("test (HTTP-5) failed. ({$i})");
			}
		}
		$test_url = ut_servermode_url(UT_SERVERMODE_DELAYED, "i=" . rand(0, ($request - 1)));
		if ($pool->getRequest($test_url)->url() != $test_url) {
			print 'test (HTTP-5) failed. getRequest API.';
			exit;
		}
		if ((time() - $start) > UT_DELAYEDSEC * 5) {
			print 'test (HTTP-5) failed. time is over.';
			exit;
		}
$symbol = <<<EOSYMBOL
(HTTP-6) Transfer-Encoding: chunked
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_CHUNKEDBODY);
		$http = new CHttp($url);
		$http->GET();
		$body_received = $http->getBody();
		$body_sent = NULL;
		for ($i = 0; $i < UT_CHUNK_REPETITION; $i++) {
			$body_sent .= UT_CHUNK_ELEM;
		}
		if (strpos($body_received, $body_sent) !== 0) {
			$http->_DP('test (HTTP-6) failed.');
		}
		// (HTTP-7) Content-Encoding: gzip
/*
		// debugging now !!
		$prof->rec();
		$url = ut_servermode_url(UT_SERVERMODE_GZIP);
		$http = new CHttp($url);
		$http->setHeader('Accept-Encoding', 'gzip');
		$http->GET();
		exit;
*/
$symbol = <<<EOSYMBOL
(HTTP-8) duplicate
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_DUPHEADER);
		$http = new CHttp($url);
		$http->GET();
		$dup = $http->getResponseHeader('X-Dup');
		if (!$dup || ($dup != '1st')) {
			$http->_DP('test (HTTP-8) failed.');
		}
		$headers = $http->getResponseHeaders(FALSE);
		if (!in_array('X-DUP: 1st', $headers)) {
			$http->_DP('test (HTTP-8) failed. (1st is not found)');
		}
		if (!in_array('x-dup: 2nd', $headers)) {
			$http->_DP('test (HTTP-8) failed. (2st is not found)');
		}
$symbol = <<<EOSYMBOL
(HTTP-9) cache
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_RAND_RES);
		$r1 = new CHttp($url);
		if (!$r1->useCache($cachedir, 3600)) {
			$r1->_DP('test (HTTP-9) failed.');
		}
		$r1->GET();
		$r2 = new CHttp($url);
		if (!$r2->useCache($cachedir, 3600)) {
			$r2->_DP('test (HTTP-9) failed.');
		}
		$r2->GET();
		$r3 = new CHttp($url);
		if (!$r3->useCache($cachedir, -1)) {
			$r3->_DP('test (HTTP-9) failed.');
		}
		$r3->GET();
		// org-1 vs cache
		if ($r1->getBody() != $r2->getBody()) {
			$r1->_DP('test (HTTP-9) failed. (cache is broken ?)');
		}
		// org-1 vs org-2
		if ($r1->getBody() == $r3->getBody()) {
			$r1->_DP('test (HTTP-9) failed. (shoud not use cache)');
		}
$symbol = <<<EOSYMBOL
(HTTP-10) safe API
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_RAND_RES);
		$http = new CHttp($url);
		$s = $http->getStatusLine();
		if (is_array($s)) {
			$http->_DP('test (HTTP-10-1) failed.');
		}
		$r = $http->getResponseHeader('hoge');
		if ($r !== NULL) {
			$http->_DP('test (HTTP-10-2) failed.');
		}
		$r = $http->getResponseHeaders(TRUE);
		if (!is_array($r)) {
			$http->_DP('test (HTTP-10-3) failed.');
		}
		$r = $http->getResponseHeaders(FALSE);
		if (!is_array($r)) {
			$http->_DP('test (HTTP-10-4) failed.');
		}
		$r = $http->getBody();
		if ($r !== '') {
			$http->_DP('test (HTTP-10-5) failed.');
		}
$symbol = <<<EOSYMBOL
(HTTP-11) response & cache
EOSYMBOL;
		$prof->rec($symbol);
		$url = ut_servermode_url(UT_SERVERMODE_LEN1);
		$http = new CHttp($url);
		if (!$http->useCache($cachedir, -1)) {
			$http->_DP('test (HTTP-11-1) failed.');
		}
		$http->GET();
		if (!$http->cacheUpdated()) {
			$http->_DP('test (HTTP-11-2) failed. cache should be updated.');
		}
		$url = ut_servermode_url(UT_SERVERMODE_LEN0);
		$http = new CHttp($url);
		if (!$http->useCache($cachedir, -1)) {
			$http->_DP('test (HTTP-11-3) failed.');
		}
		$http->GET();
		if ($http->cacheUpdated()) {
			$http->_DP('test (HTTP-11-4) failed. cache should not be updated.');
		}
		$url = ut_servermode_url(UT_SERVERMODE_404);
		$http = new CHttp($url);
		if (!$http->useCache($cachedir, -1)) {
			$http->_DP('test (HTTP-11-5) failed.');
		}
		$http->GET();
		if ($http->cacheUpdated()) {
			$http->_DP('test (HTTP-11-6) failed. cache should not be updated.');
		}
		$prof->rec('finished !!');
		$prof->showScore();
		return;
	}
}

define('CHTTP_SELF', substr(str_replace(__DIR__, '', __FILE__), 1));
define('CHTTP_CALLER', substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1));

if (UT_SERVERMODE) {
	__unittest__CHttp(UT_SERVERMODE);
	exit;
} else {
	if (CHTTP_SELF == CHTTP_CALLER) {
		__unittest__CHttp();
	} else {
		return;
	}
}

?>
