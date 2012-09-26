<?php

error_reporting(E_ALL ^ E_NOTICE);

function png($value)
{
	$w = 200;
	$h = 1;
	$gd = imagecreatetruecolor($w, $h);
	$dat = str_split($value);
	$x = 0;
	$y = 0;
	for ($i = 0; $i < count($dat); $i += 3) {
		$color = imagecolorallocate($gd, ord($dat[$i]), ord($dat[$i + 1]), ord($dat[$i + 2]));
		imagesetpixel($gd, $x++, $y, $color);
	}
	header('Content-Type: image/png');
	header('Last-Modified: Wed, 30 Jun 2010 21:36:48 GMT');
	header('Expires: Tue, 31 Dec 2030 23:30:45 GMT');
	header('Cache-Control: private, max-age=630720000');
	imagepng($gd);
}

if (array_key_exists('QUERY_STRING', $_SERVER)) {
	$params = explode('&', $_SERVER['QUERY_STRING']);
	$fv = explode('=', $params[0]);
	if (count($fv) == 2) {
		if ($fv[1]) {
			/* write */
			$fh = fopen($fv[0], 'w+');
			fwrite($fh, $fv[1]);
			fclose($fh);
			header("Location: http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?{$fv[0]}=");
		} else {
			/* read */
			if (is_file($fv[0])) {
				$fh = fopen($fv[0], 'r');
				png(fread($fh, filesize($fv[0])));
				fclose($fh);
				unlink($fv[0]);
			} else {
				header("HTTP/1.1 304 Not Modified");
			}
		}
	}
}

exit;

?>
