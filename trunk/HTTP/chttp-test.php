<?php

include('chttp.php');

if (array_key_exists('url', $_GET)) {
	define('URL', $_GET['url']);
} else {
	define('URL', 'http://tools.ietf.org/rfc/rfc2616.txt');
}

/* request */
$http = new CHttp(URL);
$http->setHeaders(apache_request_headers());
$http->GET();

/* response */
$headers = $http->getResponseHeaders();
foreach ($headers as $key => $val) {
	header("{$key}: {$val}");
}
print $http->getBody(TRUE);

?>
