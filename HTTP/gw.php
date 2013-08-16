<?php

define('USERDB', './userdb/');
define('USERID', $_SERVER['REMOTE_ADDR']);
define('SAMPLE', 'http://www.example.com/path?p1=v1&p2=v2');

function _dp($in_string, $in_stdout = TRUE)
{
	if ($in_stdout) {
		header('Content-Type: text/plain');
		print $in_string;
	} else {
		$fh = @fopen('_dp.txt', 'a+');
		if ($fh) {
			fwrite($fh, "\n[" . date('h:i:s') . "]\n");
			fwrite($fh, "{$in_string}\n");
			fclose($fh);
		}
	}
}

class USERDATA
{
	function USERDATA($in_userid) {
		$this->DATA = '';
		$this->FILE = '';
		if (!$in_userid) {
			_dp('invalid in_userid');
		}
		if (!is_dir(USERDB)) {
			if (!mkdir(USERDB)) {
				_dp('mkdir() failed');
				exit;
			}
		} else {
			$this->FILE = USERDB . $in_userid;
			$fh = @fopen($this->FILE, 'r');
			if ($fh) {
				flock($fh, LOCK_SH);
				$data = trim(fgets($fh));
				flock($fh, LOCK_UN);
				fclose($fh);
				$this->DATA = $data;
			}
		}
	}
	function save() {
		$fh = @fopen($this->FILE, 'w');
		if ($fh) {
			flock($fh, LOCK_EX);
			fwrite($fh, $this->DATA);
			flock($fh, LOCK_UN);
			fclose($fh);
		} else {
			_dp('fopen() failed');
		}
	}
	function getData() {
		return $this->DATA;
	}
	function setData($in_data) {
		$this->DATA = $in_data;
	}
}

$SELF = substr(str_replace(__DIR__, '', __FILE__), 1);
$USERDATA = new USERDATA(USERID);

if (array_key_exists('_MANAGE_', $_GET)) {
	if (array_key_exists('_UPDATE_', $_GET)) {
		$USERDATA->setData($_GET['_UPDATE_']);
		$USERDATA->save();
	}
	$data = $USERDATA->getData();
	$initURL = ($data ? $data : SAMPLE);
	print <<<EOF
<form action='{$SELF}' method='GET'>
<input type='text' class='text' name='_UPDATE_' value='{$initURL}' />
<input type='hidden' name='_MANAGE_' />
<input type='submit' />
</form>
<a href='{$SELF}'>[ gateway-test ]</a>
<style type='text/css'>
INPUT {
	border: solid 1px gray;
	height: 20px;
}
INPUT.text {
	width: 50%;
}
</style>
EOF;
} else {
	$data = $USERDATA->getData();
	if ($data) {
		require_once('chttp.php');
		$chttp = new CHttp($data);
		$replace = array(
			'HOST' => $chttp->_parsedUrl['host'],
			'CONNECTION' => 'Close'
		);
		$browser_headers = apache_request_headers();
		foreach ($browser_headers as $key => $val) {
			foreach ($replace as $rkey => $rval) {
				if (strtoupper($key) == $rkey) {
					$val = $rval;
					break;
				}
			}
			$chttp->setHeader($key, $val);
		}
		$chttp->GET();
		// $chttp->_DP();
		print $chttp->getBody();
	} else {
		header('HTTP/1.0 404 Not Found');
	}
}

exit;

?>