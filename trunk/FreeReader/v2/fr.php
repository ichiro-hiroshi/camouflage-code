<?php

/*
	Debug Utility
*/

class infoStack
{
	private $currBuff = array();
	private $ctxStack = array();
	private $dupCount = array();
	function pushCtx($in_info = NULL) {
		if (count($this->currBuff) > 0) {
			for ($i = 0; $i < count($this->currBuff['msg']); $i++) {
				$msg = $this->currBuff['msg'][$i];
				$this->currBuff['msg'][$i] = sprintf('%04d', $this->dupCount[$msg]) . " : {$msg}";
			}
			array_push($this->ctxStack, $this->currBuff);
			$this->dupCount = array();
		}
		if ($in_info) {
			$this->currBuff['ctx'] = $in_info;
			$this->currBuff['msg'] = array();
		} else {
			$this->currBuff = array();
		}
	}
	function pushMsg($in_info) {
		if (array_key_exists($in_info, $this->dupCount)) {
			$this->dupCount[$in_info]++;
		} else {
			$this->dupCount[$in_info] = 1;
			array_push($this->currBuff['msg'], $in_info);
		}
	}
	function printMsg() {
		$cnt = 1;
		$indent = "\t";
		$this->pushCtx();
		header('Content-Type: text/plain');
		foreach ($this->ctxStack as $buff) {
			print sprintf('%02d', $cnt) . ". {$buff['ctx']}\n";
			foreach ($buff['msg'] as $msg) {
				print "{$indent}{$msg}\n";
			}
			$cnt++;
		}
		exit;
	}
}

$STACK = new infoStack;

function INFO1($in_info)
{
	global $STACK;
	$STACK->pushCtx($in_info);
}

function INFO2($in_info)
{
	global $STACK;
	$STACK->pushMsg($in_info);
}

function DP()
{
	global $STACK;
	INFO1("backtrace");
	$stack = debug_backtrace();
	foreach ($stack as $scope) {
		if (array_key_exists('function', $scope)) {
			INFO2($scope['function']);
		}
	}
	$STACK->printMsg();
}

/*
	XML Utility
*/

function util_white2space($in_text)
{
	return preg_replace('/\s+/', ' ', $in_text);
}

function util_innerString($in_haystack, $in_s1, $in_s2, $in_maximum_match = FALSE)
{
	$p1 = strpos($in_haystack, $in_s1);
	if ($p1 === FALSE) {
		return NULL;
	}
	$offset = $p1 + strlen($in_s1);
	$p2 = strpos($in_haystack, $in_s2, $offset);
	if ($p2 === FALSE) {
		return NULL;
	}
	if ($in_maximum_match) {
		$pos = $p2;
		while (TRUE) {
			$offset = $pos + 1;
			$pos = strpos($in_haystack, $in_s2, $offset);
			if ($pos) {
				$p2 = $pos;
			} else {
				break;
			}
		}
	}
	$p1 += strlen($in_s1);
	// string or FALSE
	return substr($in_haystack, $p1, ($p2 - $p1));
}

function util_textContent($in_haystack, $in_tag)
{
	$inner = util_innerString($in_haystack, "<{$in_tag}", "</{$in_tag}>", FALSE);
	if ($inner) {
		$first = substr($inner, 0, 1);
		if (($first == ' ') or ($first == '>')) {
			// string or FALSE
			return substr($inner, (strpos($inner, '>') + 1));
		}
	}
	return FALSE;
}

function util_getAttribute($in_haystack, $in_tag, $in_attr)
{
	$attrs = util_innerString($in_haystack, "<{$in_tag} ", '>', FALSE);
	if ($attrs) {
		if (substr($attrs, -1) == '/') {
			$attrs = substr($attrs, 0, -1);
		}
	}else {
		return FALSE;
	}
	foreach (explode(' ', util_white2space($attrs)) as $candidate) {
		$attr = explode('=', $candidate, 2);
		if (count($attr) != 2) {
			continue;
		}
		if ($attr[0] == $in_attr) {
			// string or FALSE
			return substr($attr[1], 1, strlen($attr[1]) - 2);
		}
	}
	return FALSE;
}

/*
	RSS/Atom Utility
*/

function xml_explode($in_elem, $in_xml)
{
	$c_e = "</{$in_elem}>";
	$fragments = explode($c_e, $in_xml);
	if (count($fragments) == 1) {
		INFO2("can not explode using {$c_e}");
		return NULL;
	}
	$items = array();
	foreach ($fragments as $fragment) {
		$o_e = "<{$in_elem}";
		$p = strrpos($fragment, $o_e);
		if ($p === FALSE) {
			continue;
		}
		$p += strlen($o_e);
		$char = util_white2space(substr($fragment, $p, 1));
		if (($char == '>') || ($char == ' ')) {
			$p = strpos($fragment, '>', $p);
			if ($p === FALSE) {
				INFO2("not find '>' for {$o_e}");
				continue;
			}
			array_push($items, substr($fragment, $p + 1));
		} else {
			INFO2("not find open-element");
		}
	}
	if (count($items) > 0) {
		return $items;
	} else {
		return NULL;
	}
}

date_default_timezone_set('Asia/Tokyo');
define('DATEFORMAT', 'Ymd His');

function strip($in_str)
{
	if (strpos($in_str, '<![CDATA[') === FALSE) {
		/* for "&lt;p&gt; foo &lt;/p&gt;" */
		$html = htmlspecialchars_decode($in_str);
	} else {
		/* for "<![CDATA[<p> foo </p>]]>" */
		$html = util_innerString($in_str, '<![CDATA[', ']]>');
	}
	return util_white2space(strip_tags($html));
}

function parse($in_xml)
{
	foreach (array('item', 'entry') as $sep) {
		$items = xml_explode($sep, $in_xml);
		if ($items) {
			INFO2("xml_explode using {$sep}");
			break;
		} else {
			INFO2("can not xml_explode using {$sep}");
		}
	}
	if (!$items) {
		return NULL;
	}
	$_elems = array(
		'TITLE' => array(
			'must' => TRUE,
			'cand' => array(
				/* Atom, RSS 1.0, RSS 2.0 */
				'title'
			),
			'hook' => function($in_data) {
				return strip($in_data);
			}
		),
		'LINK' => array(
			'must' => TRUE,
			'cand' => array(
				/* RSS 1.0, RSS 2.0 */
				'link',
				/* Atom */
				array('link', 'href')
			),
			'hook' => function($in_data) {
				return $in_data;
			}
		),
		'CATEGORY' => array(
			'must' => FALSE,
			'cand' => array(
				/* RSS 2.0 */
				'category',
				/* RSS 1.0 */
				'dc:subject',
				/* Atom */
				array('category', 'term')
			),
			'hook' => function($in_data) {
				return strip($in_data);
			}
		),
		'DESC' => array(
			'must' => FALSE,
			'cand' => array(
				/* RSS 1.0, RSS 2.0 */
				'description',
				/* Atom */
				'content',
				/* Atom */
				'summary'
			),
			'hook' => function($in_data) {
				return strip($in_data);
			}
		),
		'DATE' => array(
			'must' => TRUE,
			'cand' => array(
				/* RSS 2.0 */
				'pubDate',
				/* RSS 1.0 */
				'dc:date',
				/* Atom */
				'published'
			),
			'hook' => function($in_data) {
				if (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2})/', $in_data, $m)) {
					return "{$m[1]}{$m[2]}{$m[3]} {$m[4]}{$m[5]}00";
				} elseif ($tm = strtotime($in_data)) {
					return date(DATEFORMAT, $tm);
				} else {
					return date(DATEFORMAT, time());
				}
			}
		)
	);
	$ret = array();
	foreach ($items as $item) {
		$tmp = array();
		foreach ($_elems as $dstElem => $meta) {
			if ($meta['must']) {
				INFO2("{$dstElem} is mandatory");
			}
			foreach ($meta['cand'] as $src) {
				if (is_array($src)) {
					$found = util_getAttribute($item, $src[0], $src[1]);
				} else {
					$found = util_textContent($item, $src);
				}
				if ($found) {
					INFO2("find {$dstElem}");
					$tmp[$dstElem] = call_user_func($meta['hook'], $found);
					break;
				} else {
					INFO2("not find {$dstElem}");
				}
			}
			if (!$found && $meta['must']) {
				continue 2;
			}
		}
		array_push($ret, $tmp);
	}
	//DP();
	return $ret;
}

/*
	HTTP & Cache
*/

define('DB_PATH', './' . substr(str_replace(__DIR__, '', __FILE__), 1) . '.db');
define('RECORD', DB_PATH . '/record.txt');

if (!is_dir(DB_PATH)) {
	if (!mkdir(DB_PATH)) {
		DP();
	}
}

function updateTimeRecord($in_rec)
{
	$buf = @file_get_contents(RECORD);
	if ($buf) {
		$rec = unserialize($buf);
	} else {
		$rec = array();
	}
	foreach ($in_rec as $url => $mtime) {
		$rec[$url] = $mtime;
	}
	if (@file_put_contents(RECORD, serialize($rec))) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function timeRecord()
{
	$buf = @file_get_contents(RECORD);
	if ($buf) {
		return unserialize($buf);
	}
	return NULL;
}

function url2cache($in_url)
{
	$parsed = parse_url($in_url);
	return DB_PATH . '/' . "{$parsed['host']}-" . md5($in_url) . '.txt';
}

define('LIFE', 3600 * 24 * 5);

function dateFilter($in_elem)
{
	return ($in_elem['DATE'] > date(DATEFORMAT, time() - LIFE));
}

$UNIQLINKS = array();

function linkFilter($in_elem)
{
	global $UNIQLINKS;
	if (in_array($in_elem['LINK'], $UNIQLINKS)) {
		return FALSE;
	} else {
		array_push($UNIQLINKS, $in_elem['LINK']);
		return TRUE;
	}
}

function filterChain($in_elem)
{
	if (!dateFilter($in_elem)) {
		return FALSE;
	}
	if (!linkFilter($in_elem)) {
		return FALSE;
	}
	return TRUE;
}

function responseFromCach($in_urls)
{
	$ret = array();
	foreach ($in_urls as $url) {
		INFO1("search cache ... {$url}");
		$buf = @file_get_contents(url2cache($url));
		if (!$buf) {
			INFO2("can not read cache");
			continue;
		}
		$fresh = array_filter(unserialize($buf), 'filterChain');
		if (count($fresh) > 0) {
			array_push($ret, $fresh);
		} else {
			INFO2("not find fresh cache-data");
		}
	}
	if (count($ret) > 0) {
		return $ret;
	} else {
		return NULL;
	}
}

function responseFromNet($in_urls)
{
	require_once('chttp.php');
	$pool = new CHttpRequestPool(NULL, 0, 30);
	foreach ($in_urls as $url) {
		$pool->attach(new CHttp($url));
	}
	$pool->send();
	$ret = array();
	$rec = array();
	foreach ($in_urls as $url) {
		INFO1("search net ... {$url}");
		$r = $pool->getRequest($url);
		$parsed = parse($r->getBody());
		if (!$parsed) {
			INFO2("can not parse");
			continue;
		}
		$rec[$url] = $pool->getTimeRecord($url);
		$fresh = array_filter($parsed, 'filterChain');
		if (count($fresh) > 0) {
			array_push($ret, $fresh);
			if (!@file_put_contents(url2cache($url), serialize($fresh))) {
				INFO2("can not update cahce");
			}
		} else {
			INFO2("not find fresh net-data");
		}
	}
	if (!updateTimeRecord($rec)) {
		INFO2("can not update time-record");
	}
	if (count($ret) > 0) {
		return $ret;
	} else {
		return NULL;
	}
}

function priorUrls($in_threshold = 2)
{
	$tr = timeRecord();
	if (!$tr) {
		INFO2("can not use time-record");
		return NULL;
	}
	$urls = array();
	$min = array('sec' => 99, 'url' => NULL);
	foreach ($tr as $url => $mtime) {
		if ($mtime < $in_threshold) {
			array_push($urls, $url);
		}
		if ($mtime < $min['sec']) {
			$min['sec'] = $mtime;
			$min['url'] = $url;
		}
	}
	if (count($urls) > 0) {
		INFO2("use time-record * " . count($urls));
		return $urls;
	} else {
		INFO2("use min-record : " . count($urls));
		return array($min['url']);
	}
}

/*
	RSS/Atom List
*/

$RSS_LIST = array(
	'http://blogs.msdn.com/b/ie_jp/rss.aspx',
	'http://feeds.journal.mycom.co.jp/rss/mycom/enterprise/javascript',
	'http://feeds.journal.mycom.co.jp/rss/mycom/net/web',
	'http://feeds.journal.mycom.co.jp/rss/mycom/net/webmarketing',
	'http://feeds.journal.mycom.co.jp/rss/mycom/net/socialmedia',
	'http://feeds.journal.mycom.co.jp/rss/mycom/devse/programming',
	'http://feeds.journal.mycom.co.jp/rss/mycom/creative/webdesign',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/windowsmobile',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/mobileservice',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/iphone',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/android',
	'http://mozilla.jp/blog/feed/',
	'http://www.publickey1.jp/atom.xml',
	'http://techwave.jp/index.rdf',
	'http://www.infoq.com/jp/rss/',
	'http://feeds.feedburner.com/FineSoftwareWritings',
	'http://rss.rssad.jp/rss/gihyo/wdpress/feed/rss2',
	'http://pg.thumbnailcloud.net/rss/18/rss20.xml',
	'http://feeds.feedburner.com/coliss',
	'http://blogs.itmedia.co.jp/saito/index.rdf',
	'http://efcl.info/category/firefox/feed/',
	'http://efcl.info/category/javascript/feed/',
	'http://www.jpcert.or.jp/rss/jpcert.rdf',
	'http://adgang.jp/feed',
	'http://markezine.jp/rss/new/20/index.xml'
);

INFO1("RSS_LIST : " . count($RSS_LIST));

/*
	Handle Query Parameter
*/

define('PRIORITY', 'p');

function parsePriority($in_raw)
{
	if (($in_raw == 'C') || ($in_raw == 'TR')) {
		return $in_raw;
	} elseif (preg_match('/^\d[,\d]*$/', $in_raw)) {
		return explode(',', $in_raw);
	} else {
		return NULL;
	}
}

function createPriority()
{
	if (array_key_exists(PRIORITY, $_GET)) {
		$tmp = explode('|', $_GET[PRIORITY]);
		$ret = array();
		for ($i = 0; $i < count($tmp); $i++) {
			$parsed = parsePriority($tmp[$i]);
			if ($parsed) {
				array_push($ret, $parsed);
			}
		}
		/*
			I : C|1|2,3,4
			O : array('C', array(1), array(2,3,4))
		*/
		if (count($ret) > 0) {
			return $ret;
		}
	}
	global $RSS_LIST;
	return array(array_keys($RSS_LIST));
}

/*
	Main
*/

if (array_key_exists('debug', $_GET)) {
	switch ($_GET['debug']) {
	case 'rec' :
		header('Content-Type: text/plain');
		print_r(timeRecord());
		exit;
	case 'log' :
	default :
		define('SHOWLOG', TRUE);
		break;
	}
}

$priority = createPriority();
$data = NULL;
for ($i = 0; $i < count($priority); $i++) {
	if (is_array($priority[$i])) {
		INFO1("query ... array(" . implode(',', $priority[$i]) . ")");
	} else {
		INFO1("query ... {$priority[$i]}");
	}
	if ($priority[$i] == 'C') {
		/* Cache */
		$data = responseFromCach($RSS_LIST);
		$next = array_keys($RSS_LIST);
	} elseif ($priority[$i] == 'TR') {
		/* Time-Record */
		$urls = priorUrls();
		$data = responseFromNet($urls);
		$next = array_values(array_diff_key(array_flip($RSS_LIST), array_flip($urls)));
	} else {
		$urls = array_keys(array_intersect(array_flip($RSS_LIST), $priority[$i]));
		$data = responseFromNet($urls);
		$next = array_values(array_diff(array_keys($RSS_LIST), $priority[$i]));
	}
	/*
		$data = array(
			// URL1
			array(
				array(
					'TITLE' => ...
					'LINK' => ...
					'DESC' => ...
					'DATE' => ...
				),
				array(
					'TITLE' => ...
					'LINK' => ...
					'DESC' => ...
					'DATE' => ...
				),
				...
			),
			// URL2
			array(
				...
			),
			...
		)
	*/
	if ($data) {
		INFO1("done");
		break;
	} else {
		INFO1("failed & next-step");
	}
}

if (defined('SHOWLOG')) {
	DP();
} else {
	header('Content-Type: text/plain');
	print json_encode(array('data' => $data, 'next' => $next));
}

/*

usage :

	SCRIPT
	SCRIPT?p=C|TR
	SCRIPT?p=0,1,2|3,4,5&debug=log
	SCRIPT?debug=rec

*/

?>
