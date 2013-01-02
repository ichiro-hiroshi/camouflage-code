<?php

define('HOSTA', "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}");
define('HOSTB', "http://127.0.0.1{$_SERVER['SCRIPT_NAME']}");
define('HOOK', 'hook');
define('REQ', 'req');
define('WAIT', 3);

function make_extjs()
{
	sleep(WAIT);
	$HOOK = HOOK;
	header('Content-Type: text/javascript');
	if (array_key_exists($HOOK, $_GET)) {
		print <<<EOC
{$_GET[$HOOK]}({'msg' : 'hello'});
EOC;
	} else {
		print <<<EOC
{'msg' : 'hello'}
EOC;
	}
}

function make_worker()
{
	$HOOK = HOOK;
	header('Content-Type: text/javascript');
	print <<<EOC
function hook(response)
{
    postMessage(response);
}

onmessage = function (e) {
	var url = e.data.entry + '&{$HOOK}=hook';
	for (var prop in e.data.params) {
		url += '&' + prop + '=' + e.data.params[prop];
	}
    importScripts(url);
};
EOC;
}

$handler = array('extjs', 'worker');
$HOSTA = HOSTA;
$HOSTB = HOSTB;
$HOOK = HOOK;
$REQ = REQ;
if (array_key_exists(REQ, $_GET) && in_array($_GET[REQ], $handler)) {
	call_user_func("make_{$_GET[$REQ]}");
} else {
	print <<<EOC
<div>before script</div>
<script>

function callback(response)
{
    //alert(response.msg);
	document.write(response.msg);
}

window.onload = function() {
    alert('onload');
};

var ENTRY = '{$HOSTA}?{$REQ}=extjs';
//var ENTRY = '{$HOSTB}?{$REQ}=extjs';

/* test#2 */
if (false) {
    var t = document.getElementsByTagName('SCRIPT')[0];
    var s = document.createElement('SCRIPT');
    s.src = ENTRY + '&{$HOOK}=callback';
    t.parentNode.insertBefore(s, t);
}

/* test#1 */
if (false) {
    document.write('<script type="text/javascript" src="');
    document.write(ENTRY + '&{$HOOK}=callback"></s' + 'cript>');
}

/* test#3 */
if (true) {
	xhr = new XMLHttpRequest();
	xhr.onreadystatechange = (function(in_xhr, in_callback) {
		return function() {
			if ((in_xhr.readyState == 4) && (in_xhr.status == 200)) {
				(in_callback)(eval('(' + in_xhr.responseText + ')'));
			}
		}
	})(xhr, callback);
	xhr.open('GET', ENTRY);
	xhr.send();
}

/* test#1 */
if (false) {
    var worker = new Worker('{$HOSTA}?{$REQ}=worker');
    worker.postMessage({entry : ENTRY, params : {}});
	worker.addEventListener('message', function(e) {
        callback(e.data);
	}, false);
}

</script>
<div>after script</div>
EOC;
}

?>
