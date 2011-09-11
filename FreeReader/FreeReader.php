<?php

/*
	[how to use]
	- SCRIPT : view
	- SCRIPT?json
		- GET : script returns json-data using default feed-data.
		- POST (API) : script returns json-data using posted URL-set sparated by "\n".
	- SCRIPT?ut : unit test
*/

define('USE_CACHE', TRUE);
//define('USE_CACHE', FALSE);
if (USE_CACHE) {
	define('CACHE_PATH', './.FreeReaderCache');
	define('CACHE_LIFE', 3600);
	if (!is_dir(CACHE_PATH)) {
		mkdir(CACHE_PATH);
	}
} else {
	define('CACHE_PATH', NULL);
	define('CACHE_LIFE', 0);
}

$JSON_DEBUG = 'json_debug';
//define('USE_RESPONSE_DEBUG', TRUE);
define('USE_RESPONSE_DEBUG', FALSE);

$SELF = substr(str_replace(__DIR__, '', __FILE__), 1);
$DATE = 'date';
$TERM = 'term';
$JSON = 'json';

if (array_key_exists('ut', $_GET)) {
	// (0) unit test
	$testml =<<<EOML
<testml>
	<e1>t1</e1>
	<e1>t1</e1>
	<e11>t11</e11>
	<e2 a2='v2'>t2</e2>
	<e21 a21='v21'>t21</e21>
	<e3 a3="v3">t3</e3>
	<e4 a4='v4'>
		t4
	</e4>
	<e5 a51='v51'
		a52='v52'
		a53='v53'>
		t5
	</e5>
	<e6/>
	<e7 a7='v7'/>
	<e8 a8='v8' />
	<e9 a91='v91'
		a92='v92'
		a93='v93' />
	<e0><e01>t01</e01></e0>
</testml>
EOML;
	$tests = array(
		'util_innerString($testml, "<e1>", "</e1>", FALSE)' => 't1',
		'util_innerString($testml, "<e1>", "</e1>", TRUE)' =>  't1</e1> <e1>t1',
		'util_innerString($testml, "e1", "e1")' =>  '>t1</',
		'util_textContent($testml, "e1")' => 't1',
		'util_textContent($testml, "e2")' => 't2',
		'util_textContent($testml, "e4")' => ' t4 ',
		'util_textContent($testml, "e5")' => ' t5 ',
		'util_textContent($testml, "e6")' => '',
		'util_textContent($testml, "e0")' => '<e01>t01</e01>',
		'util_getAttribute($testml, "e1", "a1")' => '',
		'util_getAttribute($testml, "e2", "a2")' => 'v2',
		'util_getAttribute($testml, "e2", "aX")' => '',
		'util_getAttribute($testml, "e3", "a3")' => 'v3',
		'util_getAttribute($testml, "e4", "a4")' => 'v4',
		'util_getAttribute($testml, "e5", "a51")' => 'v51',
		'util_getAttribute($testml, "e5", "a52")' => 'v52',
		'util_getAttribute($testml, "e5", "a53")' => 'v53',
		'util_getAttribute($testml, "e7", "a7")' => 'v7',
		'util_getAttribute($testml, "e8", "a8")' => 'v8',
		'util_getAttribute($testml, "e9", "a91")' => 'v91',
		'util_getAttribute($testml, "e9", "a92")' => 'v92',
	);
	header('Content-Type: text/plain');
	foreach ($tests as $test => $expected) {
		$result = preg_replace('/\s+/', ' ', eval("return {$test};"));
		if ($result != $expected) {
			print "failed : {$test} => {$result}";
			exit;
		}
	}
	print 'successful !!';
	exit;
} elseif (!array_key_exists($JSON, $_GET)) {
	// (1) root content

	print <<<EOHTML
<html>
<head>
<style type='text/css'>
* {background-color: black; color: white;}
A {text-decoration: none; color: silver;}
INPUT {border: solid 1px silver;}
INPUT#t1 {width: 5%;}
INPUT#t2 {width: 50%;}
TABLE {margin-top: 10px;}
TABLE, TD {border-collapse: collapse;}
.c250 {max-width: 250px;}
.c350 {max-width: 350px; font-size: smaller; color: gray;}
TD {border: solid 1px silver; padding: 3px;};
</style>
</head>
<body onload='json_read();'>
<div>
<input id='t1' type='text' value='5' />
<input id='t2' type='text' />
</div>
<div>
<table id='view'>
</table>
</div>
<script type=text/javascript>

HTMLInputElement.prototype.KeyCode13 = function(in_callback)
{
	var type = 'keydown';
	var handler = (function () {
		return function (in_e) {
			if (in_e.keyCode == 13) {
				(in_callback)();
				/* cancel input */
				return false;
			}
			return true;
		}
	})();
	if (document.addEventListener) {
		// Firefox, Opera
		this.addEventListener(type, handler, false);
	} else if (document.attachEvent) {
		// IE
		this.attachEvent('on' + type, handler);
	}
}

document.getElementById('t1').KeyCode13(json_read);
document.getElementById('t2').KeyCode13(json_read);

function create_element(in_e, in_text, in_url, in_classname)
{
	var name = in_e.toUpperCase();
	var text = document.createTextNode(in_text);
	if (in_url) {
		var a = document.createElement('A');
		a.href = in_url;
		a.target = '_blank';
		a.appendChild(text);
		if (name == 'A') {
			var e = a;
		} else {
			var e = document.createElement(name);
			e.appendChild(a);
		}
	} else {
		var e = document.createElement(name);
		e.appendChild(text);
	}
	if (in_classname) {
		e.className = in_classname;
	}
	return e;
}

function {$JSON_DEBUG}(in_json)
{
	document.write(in_json);
}

function sort_table(in_col)
{
	var view_id = 'view';
	var table1 = document.getElementById(view_id);
	var tmp = [];
	for (var i = 0; i < table1.rows.length; i++) {
		tmp.push([i, table1.rows[i].cells[in_col].textContent]);
	}
	tmp.sort(function(e1, e2)
	{
		if (e1[1] < e2[1]) {
			return 1;
		} else {
			return -1;
		}
	});
	var table2 = document.createElement('TABLE');
	for (var i = 0; i < tmp.length; i++) {
		table2.appendChild(table1.rows[tmp[i][0]].cloneNode(true));
	}
	table1.parentNode.replaceChild(table2, table1);
	table2.id = view_id;
}

function json_show(in_json)
{
	/*
	[
		{
			URL : "http://～"
			LIST : [
				{
					CATEGORY : "～",
					TITLE : "～",
					LINK : "～",
					DESC : "～",
					DATE : "～"
				},
				{ ... },
				{ ... },
			]
		},
		{ ... },
		{ ... },
	]
	*/
	var hash = eval(in_json);
	var table = document.getElementById('view');
	table.innerHTML = '';
	for (i = 0; i < hash.length; i++) {
		var cp = hash[i];
		/*
			cp.URL
			cp.LIST
		*/
		var domain = (cp.URL.split('/'))[2];
		for (j = 0; j < cp.LIST.length; j++) {
			var entry = cp.LIST[j];
			/*
				entry.CATEGORY
				entry.TITLE
				entry.LINK
				entry.DESC
				entry.DATE
			*/
			var tr = document.createElement('TR');
			tr.appendChild(create_element('TD', entry.DATE, null, null));
			tr.appendChild(create_element('TD', domain, cp.URL, null));
			tr.appendChild(create_element('TD', entry.CATEGORY, null, 'c250'));
			tr.appendChild(create_element('TD', entry.TITLE, entry.LINK, 'c250'));
			tr.appendChild(create_element('TD', entry.DESC, null, 'c350'));
			table.appendChild(tr);
		}
	}
	sort_table(0);
}

function json_read()
{
	var v1 = document.getElementById('t1').value;
	var v2 = document.getElementById('t2').value;
	if (window.ActiveXObject) {
		xhr = new ActiveXObject('Msxml2.XMLHTTP');
	} else {
		xhr = new XMLHttpRequest();
	}
	xhr.onreadystatechange = function () {
		if (this.readyState != 4) {
			return;
		}
		if (this.status == 200) {
			json_show(this.responseText);
		}
	}
	var url = './{$SELF}?{$JSON}';
	if (v1) {
		url += '&{$DATE}=' + v1;
	}
	if (v2) {
		url += '&{$TERM}=' + v2;
	}
	xhr.open('GET', url);
	xhr.send();
}

</script>
</body>
</html>
EOHTML;
	exit;
} else {
	// (2) json
	define('F_DATE', (array_key_exists($DATE, $_GET) ? $_GET[$DATE] : NULL));
	define('F_TERM', (array_key_exists($TERM, $_GET) ? $_GET[$TERM] : NULL));
}

function util_whitespace($in_text)
{
	return preg_replace('/\s+/', ' ', $in_text);
}

function util_hash2json($in_hash, $indent = 0)
{
	$objs = array();
	$tab = '';
	while ($indent-- > 0) {
		$tab .= "\t";
	}
	foreach ($in_hash as $key => $val) {
		array_push($objs, "{$tab}\t{$key}:\"{$val}\"");
	}
	return "{$tab}{\n" . implode(",\n", $objs) . "\n{$tab}}";
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
	return substr($in_haystack, $p1, ($p2 - $p1));
}

function util_textContent($in_haystack, $in_tag)
{
	// there may be "<TAG" + "CRLF" ...
	$innerstring = util_innerString($in_haystack, "<{$in_tag} ", "</{$in_tag}>", FALSE);
	if ($innerstring) {
		return substr($innerstring, (strpos($innerstring, '>') + 1));
	} else {
		return util_innerString($in_haystack, "<{$in_tag}>", "</{$in_tag}>", FALSE);
	}
}

function util_getAttribute($in_haystack, $in_tag, $in_attr)
{
	// there may be "<TAG" + "CRLF" ...
	$attrs = util_innerString($in_haystack, "<{$in_tag} ", '>', FALSE);
	if ($attrs) {
		if (substr($attrs, -1) == '/') {
			$attrs = substr($attrs, 0, -1);
		}
	}else {
		return NULL;
	}
	foreach (explode(' ', util_whitespace($attrs)) as $candidate) {
		$attr = explode('=', $candidate, 2);
		if (count($attr) != 2) {
			continue;
		}
		if ($attr[0] == $in_attr) {
			return substr($attr[1], 1, strlen($attr[1]) - 2);
		}
	}
	return NULL;
}

$_FEED2JSON_PARSE_META_DB = array(
	'TITLE' => array(
		'required' => TRUE,
		'candidates' => array(
			/* Atom, RSS 1.0, RSS 2.0 */
			array(
				'E' => 'title',
				'A' => NULL
			)
		)
	),
	'LINK' => array(
		'required' => TRUE,
		'candidates' => array(
			/* RSS 1.0, RSS 2.0 */
			array(
				'E' => 'link',
				'A' => NULL
			),
			/* Atom */
			array(
				'E' => 'link',
				'A' => 'href'
			)
		)
	),
	'CATEGORY' => array(
		'required' => FALSE,
		'candidates' => array(
			/* RSS 2.0 */
			array(
				'E' => 'category',
				'A' => NULL
			),
			/* RSS 1.0 */
			array(
				'E' => 'dc:subject',
				'A' => NULL
			),
			/* Atom */
			array(
				'E' => 'category',
				'A' => 'term'
			)
		)
	),
	'DESC' => array(
		'required' => FALSE,
		'candidates' => array(
			/* RSS 1.0, RSS 2.0 */
			array(
				'E' => 'description',
				'A' => NULL
			),
			/* Atom */
			array(
				/* 'E' => 'summary', */
				'E' => 'content',
				'A' => NULL
			)
		)
	),
	'DATE' => array(
		'required' => TRUE,
		'candidates' => array(
			/* RSS 2.0 */
			array(
				'E' => 'pubDate',
				'A' => NULL
			),
			/* RSS 1.0 */
			array(
				'E' => 'dc:date',
				'A' => NULL
			),
			/* Atom */
			array(
				'E' => 'published',
				'A' => NULL
			)
		)
	)
);

function cdata($in_val)
{
	$cdata = util_innerString($in_val, "<![CDATA[", "]]>");
	if (!$cdata) {
		$cdata = html_entity_decode($in_val);
	}
	return addslashes(strip_tags($cdata));
}

function _FEED2JSON_TITLE($in_val)
{
	return cdata($in_val);
}

function _FEED2JSON_CATEGORY($in_val)
{
	return cdata($in_val);
}

function _FEED2JSON_DESC($in_val)
{
	return util_whitespace(cdata($in_val));
}

function _FEED2JSON_DATE($in_val)
{
	if (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2})/', $in_val, $m)) {
		return "{$m[1]}{$m[2]}{$m[3]} {$m[4]}{$m[5]}00";
	} elseif ($tm = strtotime($in_val)) {
		return date("Ymd His", $tm);
	} else {
		return date("Ymd His", time());
	}
}

function feed2json_fragment2hash($in_fragment)
{
	global $_FEED2JSON_PARSE_META_DB;
	foreach ($_FEED2JSON_PARSE_META_DB as $elem => $meta) {
		$hash[$elem] = NULL;
		foreach ($meta['candidates'] as $candidate) {
			if ($candidate['A']) {
				$hash[$elem] = util_getAttribute($in_fragment, $candidate['E'], $candidate['A']);
			} else {
				$hash[$elem] = util_textContent($in_fragment, $candidate['E']);
			}
			if ($hash[$elem]) {
				// found !!
				$func = "_FEED2JSON_{$elem}";
				if (function_exists($func)) {
					$hash[$elem] = call_user_func($func, $hash[$elem]);
				}
				break;
			}
		}
		if (!$hash[$elem] && $meta['required']) {
			return NULL;
		}
	}
	return $hash;
}

function feed2json($in_rss, $in_date = NULL, $in_term = NULL, $in_filter = NULL)
{
	/*
		$in_date    : date-filter
		$in_term : keyword-filter
		$in_filter  : special-finter (function)
	*/
	$items = explode('<item', $in_rss);
	if (count($items) == 1) {
		$items = explode('<entry', $in_rss);
	}
	$jsons = array();
	for ($i = 0; $i < count($items); $i++) {
		$hash = feed2json_fragment2hash($items[$i]);
		if (!$hash) {
			continue;
		}
		if ($in_date) {
			if ($hash['DATE'] < date("Ymd His", (time() - ($in_date * 24 * 60 * 60)))) {
				continue;
			}
		}
		if ($in_term) {
			while (1) {
				if (stripos($hash['TITLE'], $in_term) !== FALSE) {
					break;
				}
				if (stripos($hash['CATEGORY'], $in_term) !== FALSE) {
					break;
				}
				if (stripos($hash['DESC'], $in_term) !== FALSE) {
					break;
				}
				continue 2;
			}
		}
		if ($in_filter) {
			if (!call_user_func($in_filter, $hash)) {
				continue;
			}
		}
		array_push($jsons, util_hash2json($hash, 3));
	}
	/*
		[
			{
				CATEGORY : "...",
				TITLE    : "...",
				LINK     : "...",
				DESC     : "...",
				DATE     : "..."
			},
			{
				CATEGORY : "...",
				TITLE    : "...",
				LINK     : "...",
				DESC     : "...",
				DATE     : "..."
			},
			...
		],
	*/
	return "[\n" . implode(",\n", $jsons) . "\n\t\t]";
}

function start_with($in_haystack, $in_keywords)
{
	foreach ($in_keywords as $keyword) {
		if (strpos($in_haystack, $keyword) === 0) {
			return TRUE;
		}
	}
	return FALSE;
}

function feed_filter_asahi($in_hash)
{
	if ($in_hash['LINK'] == 'http://www.asahi.com/') {
		return FALSE;
	}
	if (start_with($in_hash['TITLE'], array('PR:', 'AD:'))) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function feed_filter_mycom($in_hash)
{
	if ($in_hash['LINK'] == 'http://journal.mycom.co.jp/') {
		return FALSE;
	}
	if (start_with($in_hash['TITLE'], array('PR:'))) {
		return FALSE;
	} else {
		return TRUE;
	}
}
function feed_filter_saito($in_hash)
{
	if (!$in_hash['CATEGORY']) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function feed_filter_thumbnailcloud($in_hash)
{
	if (strpos($in_hash['TITLE'], '【PR】') === FALSE) {
		return TRUE;
	} else {
		return FALSE;
	}
}

$_FEED_LIST = array(
/*
	'http://localhost/test-env/20110710-FreeReader/test.xml' => NULL,
	'http://127.0.0.1/test-env/20110710-FreeReader/rss20.xml' => 'feed_filter_thumbnailcloud',
*/
	'http://rss.asahi.com/f/asahi_national' => 'feed_filter_asahi',
	'http://blogs.msdn.com/b/ie_jp/rss.aspx' => NULL,
	'http://feeds.journal.mycom.co.jp/rss/mycom/enterprise/javascript' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/net/web' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/net/webmarketing' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/net/socialmedia' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/enterprise/programming' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/creative/webdesign' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/windowsmobile' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/mobileservice' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/iphone' => 'feed_filter_mycom',
	'http://feeds.journal.mycom.co.jp/rss/mycom/keitai/android' => 'feed_filter_mycom',
	'http://hacks.mozilla.org/feed/' => NULL,
	'http://mozilla.jp/blog/feed/' => NULL,
	'http://www.publickey1.jp/atom.xml' => NULL,
	'http://techwave.jp/index.rdf' => NULL,
	'http://www.infoq.com/jp/rss/rss.action?token=9XwCWgqRp8JS1R9zbh3dlwifVG25HjsI' => NULL,
	'http://feeds.feedburner.com/FineSoftwareWritings' => NULL,
	'http://rss.rssad.jp/rss/gihyo/wdpress/feed/rss2' => NULL,
	'http://pg.thumbnailcloud.net/rss/18/rss20.xml' => 'feed_filter_thumbnailcloud',
	'http://feeds.feedburner.jp/coliss' => NULL,
	'http://blogs.itmedia.co.jp/saito/index.rdf' => 'feed_filter_saito',
	'http://feeds.feedburner.jp/e0166' => NULL,
	'http://efcl.info/category/web%e3%82%b5%e3%83%bc%e3%83%93%e3%82%b9/feed/' => NULL,
	'http://efcl.info/category/firefox/feed/' => NULL,
	'http://efcl.info/category/javascript/feed/' => NULL
);

require_once('chttp.php');

$pool = new CHttpRequestPool(CACHE_PATH, CACHE_LIFE);
foreach ($_FEED_LIST as $url => $filter) {
	$pool->attach(new CHttp($url));
}
$pool->send();
$jsons = array();
foreach ($_FEED_LIST as $url => $filter) {
	$r = $pool->getFinishedRequest($url);
	$list = feed2json($r->getBody(), F_DATE, F_TERM, $filter);
	$json = <<<EOJSON
	{
		URL : "{$url}",
		LIST : {$list}
	}
EOJSON;
	array_push($jsons, $json);
}
header('Content-Type: text/javascript; charset=utf-8');
if (USE_RESPONSE_DEBUG) {
	print "{$JSON_DEBUG}(";
}
print "[\n" . implode(",\n", $jsons) . "\n]";
if (USE_RESPONSE_DEBUG) {
	print ")";
}

?>
