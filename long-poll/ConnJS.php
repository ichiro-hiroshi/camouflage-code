<?php

/*

(1) [Client] ---> [GW] : start

- request (GET)
- response
-- header
	- APP_XHEADER
		== APP_ERR_SUCCESS : success, and [Client] can use CLIENT-ID in body
		== APP_ERR_GENERIC : failed. [Client] need re-starting
-- body
	- CLIENT-ID

(2) [Client] ---> [GW] : send

- request (POST)
	> {
	>     APP-PROP1 : VALUE1,
	>     APP-PROP2 : VALUE2,
	>     ...
	> }
- response
-- header
	- APP_XHEADER
		== APP_ERR_SUCCESS : success
		== APP_ERR_CCDB_NOID : failed. [Client] need re-starting
		== APP_ERR_CCDB_TIMEOUT : failed. [Client] need re-sending

(3) [Client] <--- [GW] : receive (long-poll response)

- reguest (GET)
- response
-- header
	- APP_XHEADER
		== APP_ERR_SUCCESS : success
		== APP_ERR_CCDB_NOID : failed. [Client] need re-starting
-- body
	> {
	>     NAME1 : {
	>         APP-PROP1 : VALUE1,
	>         APP-PROP2 : VALUE2,
	>         ...
	>     },
	>     NAME2 : {
	>         APP-PROP1 : VALUE1,
	>         APP-PROP2 : VALUE2,
	>         ...
	>     },
	>     ...
	> }

*/

// utility

function h($in_seed, $in_len)
{
	return substr(md5($in_seed), 0, $in_len);
}

function entry($in_array)
{
	$query = NULL;
	foreach ($in_array as $key => $val) {
		if ($query) {
			$query .= '&';
		}
		$query .= "{$key}={$val}";
	}
	return APP_SELF . '?' . $query;
}

function util_us()
{
	// format : 9999999999.9999
	return sprintf('%-015s', microtime(TRUE));
}

date_default_timezone_set('Asia/Tokyo');

function util_dp($in_string, $in_stdout = TRUE)
{
	if ($in_stdout) {
		header('Content-Type: text/plain');
		print $in_string;
	} else {
		$fh = @fopen('util_dp.txt', 'a+');
		fwrite($fh, "\n[" . date('h:i:s') . "]\n");
		fwrite($fh, "{$in_string}\n");
		fclose($fh);
	}
}

function util_dp2($in_string)
{
	util_dp($in_string, FALSE);
}

// database

define('CCDB_SSUFFIX', '.txt');

define('CCDB_MAX_CLIENT', 10);
define('CCDB_LIFE', 10);
define('CCDB_ID_LENGTH', 10);
define('CCDB_SLEEP_USEC', 200000);
define('CCDB_SLEEP_RETRY', 5);

define('CCDB_RWERR_SUCCESS', 1);
define('CCDB_RWERR_NOID', 2);
define('CCDB_RWERR_TIMEOUT', 3);

class CCDB
{
	function CCDB($in_dir) {
		$this->conf = array(
			'maxClient' => CCDB_MAX_CLIENT,
			'life' => CCDB_LIFE,
			'idLength' => CCDB_ID_LENGTH
		);
		$this->dir = $in_dir;
		if (!is_dir($this->dir)) {
			if (!mkdir($this->dir)) {
				$this->_errExit('mkdir() failed');
			}
		}
	}
	function setConfig($in_prop, $in_value) {
		$this->conf[$in_prop] = $in_value;
	}
	function _idList() {
		/*
			returns array(
				'R_LATEST' => microsec,
				'W_LATEST' => microsec,
				'R_OLDEST' => microsec,
				'W_OLDEST' => microsec,
				'LIST' => array(
					ID1 => array(r_us1, w_us1),
					ID2 => array(r_us2, w_us2),
					...
				)
			);
		*/
		$list = array();
		$r_latest = 0;
		$w_latest = 0;
		$r_oldest = PHP_INT_MAX;
		$w_oldest = PHP_INT_MAX;
		if ($dh = opendir($this->dir)) {
			while (($fname = readdir($dh)) !== FALSE) {
				$id = substr($fname, 0, -strlen(CCDB_SSUFFIX));
				$read = $this->_read($id);
				if (!$read) {
					continue;
				}
				$list[$id] = array($read[1], $read[2]);
				$r_latest = ($read[1] > $r_latest) ? $read[1] : $r_latest;
				$w_latest = ($read[2] > $w_latest) ? $read[2] : $w_latest;
				$r_oldest = ($read[1] < $r_oldest) ? $read[1] : $r_oldest;
				$w_oldest = ($read[2] < $w_oldest) ? $read[2] : $w_oldest;
			}
			closedir($dh);
		} else {
			$this->_errExit('opendir() failed');
		}
		return array(
			'R_LATEST' => $r_latest,
			'W_LATEST' => $w_latest,
			'R_OLDEST' => $r_oldest,
			'W_OLDEST' => $w_oldest,
			'LIST' => $list);
	}
	function _errExit($in_reason) {
		if (is_null($in_reason)) {
			$out = "[backtrace]\n" . print_r(debug_backtrace(), TRUE);
		} else {
			$out = $in_reason;
		}
		$out .= "\n\n-----\n\n";
		$out .= print_r($this, TRUE);
		util_dp($out);
		exit;
	}
	function dpExit($in_msg = NULL) {
		if ($in_msg) {
			$msg = "[MSG]\n{$in_msg}\n";
		} else {
			$msg = "";
		}
		$this->_errExit("{$msg}\n[DB]\n" . print_r($this->_idList(), TRUE) . "\n[OBJECT]");
	}
	function _id2path($in_id) {
		return "{$this->dir}/{$in_id}" . CCDB_SSUFFIX;
	}
	function cleanUp() {
		if ($dh = opendir($this->dir)) {
			while (($fname = readdir($dh)) !== FALSE) {
				$id = substr($fname, 0, -strlen(CCDB_SSUFFIX));
				$this->end($id);
			}
			closedir($dh);
		} else {
			$this->_errExit('opendir() failed');
		}
	}
	function _read($in_id, $in_read_data = FALSE) {
		/*
			$in_read_data == TRUE
				returns array(name, read-usec, written-usec, data)
			$in_read_data == FALSE
				returns array(name, read-usec, written-usec)
		*/
		$path = $this->_id2path($in_id);
		$fh = @fopen($path, 'r');
		if ($fh) {
			flock($fh, LOCK_SH);
			$ret = explode(',', trim(fgets($fh)));
			if ($in_read_data) {
				$data = '';
				while (!feof($fh)) {
					$data .= fgets($fh);
				}
				array_push($ret, $data);
			}
			flock($fh, LOCK_UN);
			fclose($fh);
			return $ret;
		} else {
			// equal to "!is_file($path)"
			return NULL;
		}
	}
	function _initId($in_name) {
		$id = h(microtime(TRUE), $this->conf['idLength']);
		$path = $this->_id2path($id);
		$fh = @fopen($path, 'w');
		if ($fh) {
			$r_us = util_us();
			$w_us = $r_us - $this->conf['life'];
			flock($fh, LOCK_EX);
			fwrite($fh, "{$in_name},{$r_us},{$w_us}\n");
			flock($fh, LOCK_UN);
			fclose($fh);
			chmod($path, 0666);
		} else {
			$this->_errExit('fopen() failed');
		}
		return $id;
	}
	function start($in_name) {
		// remove "," in "{$in_name}".
		$tmp = explode(',', $in_name);
		$name = array_shift($tmp);
		$list = $this->_idList();
		$cnt = count($list['LIST']);
		if ($cnt < $this->conf['maxClient']) {
			return $this->_initId($name);
		} else {
			foreach ($list['LIST'] as $id => $sec) {
				if ($list['W_LATEST'] > max($sec)) {
					// (1) "{$sec[0]}" sould be updated.
					if (util_us() - $list['W_LATEST'] > $this->conf['life']) {
						// (1-1) connection may be lost.
						$cnt--;
						$this->end($id);
					} else {
						// (1-2) need to wait for a while
					}
				} else {
					// (2) "{$sec[0]}" sould not be updated.
				}
				if ($cnt < $this->conf['maxClient']) {
					return $this->_initId($name);
				}
			}
			return NULL;
		}
	}
	function end($in_id) {
		$path = $this->_id2path($in_id);
		if (is_file($path)) {
			unlink($path);
		}
	}
	function _waitReading($in_id, $in_us) {
		$list = $this->_idList();
		// someone has not read my written-data.
		if ($list['R_OLDEST'] < $in_us) {
			$not_read = array();
			foreach ($list['LIST'] as $id => $sec) {
				if (($sec[0] < $in_us) && ($in_id != $id)) {
					$not_read[$id] = $sec[0];
				}
			}
			$retry = CCDB_SLEEP_RETRY;
			do {
				// 0.2sec
				usleep(CCDB_SLEEP_USEC);
				$waiting = FALSE;
				if ($retry > 0) {
					$retry--;
				} else {
					// timeout (someone has not read my written-data yet)
					return FALSE;
				}
				foreach ($not_read as $id => $r_us) {
					$read = $this->_read($id);
					if (!$read) {
						continue;
					}
					if ($read[1] < $in_us) {
						$waiting = TRUE;
						break;
					}
				}
			} while ($waiting);
			return TRUE;
		} else {
			return TRUE;
		}
	}
	function _update($in_id, $in_data, $in_wait_reading) {
		$path = $this->_id2path($in_id);
		$fh = @fopen($path, 'r+');
		if ($fh) {
			flock($fh, LOCK_SH);
			list($name, $r_us, $w_us) = explode(',', trim(fgets($fh)));
			flock($fh, LOCK_UN);
			if ($in_wait_reading) {
				if (!$this->_waitReading($in_id, $w_us)) {
					fclose($fh);
					return CCDB_RWERR_TIMEOUT;
				}
			}
			$w_us = util_us();
			flock($fh, LOCK_EX);
			ftruncate($fh, 0);
			rewind($fh);
			fwrite($fh, "{$name},{$r_us},{$w_us}\n{$in_data}");
			flock($fh, LOCK_UN);
			fclose($fh);
		} else {
			// equal to "!is_file($path)"
			return CCDB_RWERR_NOID;
		}
		return CCDB_RWERR_SUCCESS;
	}
	function update1($in_id, $in_data) {
		return $this->_update($in_id, $in_data, FALSE);
	}
	function update2($in_id, $in_data) {
		return $this->_update($in_id, $in_data, TRUE);
	}
	function _getUpdated4Id($in_id, $in_us, $in_targets = NULL) {
		$ret = array();
		if ($dh = opendir($this->dir)) {
			while (($fname = readdir($dh)) !== FALSE) {
				$id = substr($fname, 0, -strlen(CCDB_SSUFFIX));
				if ($in_id == $id) {
					continue;
				}
				if ($in_targets && !in_array($id, $in_targets)) {
					continue;
				}
				$read = $this->_read($id, TRUE);
				if (!$read) {
					continue;
				}
				// written-time time is more recent than "{$in_us}".
				if ($read[2] > $in_us) {
					$ret[$id] = array(
						'NAME' => $read[0],
						'WRIT' => $read[2],
						'DATA' => $read[3]
					);
				}
			}
			closedir($dh);
		} else {
			$this->_errExit('opendir() failed');
		}
		return $ret;
	}
	function getUpdated4Id($in_id, $in_targets = NULL) {
		$path = $this->_id2path($in_id);
		$fh = @fopen($path, 'r+');
		if ($fh) {
			flock($fh, LOCK_SH);
			list($name, $r_us, $w_us) = explode(',', trim(fgets($fh)));
			flock($fh, LOCK_UN);
			$updates = $this->_getUpdated4Id($in_id, $r_us, $in_targets);
			$r_us = util_us();
			flock($fh, LOCK_EX);
			rewind($fh);
			fwrite($fh, "{$name},{$r_us},{$w_us}\n");
			flock($fh, LOCK_UN);
			fclose($fh);
		} else {
			// equal to "!is_file($path)"
			return CCDB_RWERR_NOID;
		}
		return $updates;
	}
	function browseAllData() {
		$all = $this->_getUpdated4Id(NULL, (util_us() - $this->conf['life']));
		return $all;
	}
}

define('TEST_DB', 'testdb');

function ccdb_test()
{
	$ccdb = new CCDB(TEST_DB);
	$ccdb->cleanUp();
	$ccdb->setConfig('maxClient', 2);
	$ccdb->setConfig('life', 1);
// test-1 : maxClient
	// id1 : start
	// id2 : [x]
	// id3 : [x]
	$id1 = $ccdb->start('user1');
	if (!$id1) {
		$ccdb->dpExit('1-1 failed');
	}
	// id1 : [o]
	// id2 : start
	// id3 : [x]
	$id2 = $ccdb->start('user2');
	if (!$id2) {
		$ccdb->dpExit('1-2 failed');
	}
	// id1 : [o]
	// id2 : [o]
	// id3 : can't start
	$id3 = $ccdb->start('user3');
	if ($id3) {
		$ccdb->dpExit('1-3 failed');
	}
	// id1 : [o]
	// id2 : end
	// id3 : [o]
	$ccdb->end($id2);
	$id3 = $ccdb->start('user3');
	if (!$id3) {
		$ccdb->dpExit('1-4 failed');
	}
// test-2 : expire & browseAllData
	$ccdb->cleanUp();
	// id1 : start & update1
	// id2 : [x]
	// id3 : [x]
	$id1 = $ccdb->start('user1');
	if (!$id1) {
		$ccdb->dpExit('2-1 failed');
	}
	if ($ccdb->update1($id1, "{$id1}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('2-2 failed');
	}
	if (count($ccdb->browseAllData()) != 1) {
		$ccdb->dpExit('2-3 failed');
	}
	// id1 : [o]
	// id2 : start & update1
	// id3 : [x]
	$id2 = $ccdb->start('user2');
	if (!$id2) {
		$ccdb->dpExit('2-4 failed');
	}
	if ($ccdb->update1($id2, "{$id2}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('2-5 failed');
	}
	if (count($ccdb->browseAllData()) != 2) {
		$ccdb->dpExit('2-6 failed');
	}
	sleep(2);
	// id1 : expire
	// id2 : expire
	// id3 : start & update1
	$id3 = $ccdb->start('user3');
	if (!$id3) {
		$ccdb->dpExit('2-7 failed');
	}
	if ($ccdb->update1($id3, "{$id3}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('2-8 failed');
	}
	if (count($ccdb->browseAllData()) != 1) {
		$ccdb->dpExit('2-8 failed');
	}
// test-3 : getUpdated4Id
	// id1 : start
	// id2 : start
	// id3 : start
	$ccdb->setConfig('maxClient', 3);
	$ccdb->cleanUp();
	$id1 = $ccdb->start('user1');
	if (!$id1) {
		$ccdb->dpExit('3-1 failed');
	}
	$id2 = $ccdb->start('user2');
	if (!$id2) {
		$ccdb->dpExit('3-2 failed');
	}
	$id3 = $ccdb->start('user3');
	if (!$id3) {
		$ccdb->dpExit('3-3 failed');
	}
	// id1 : update1
	// id2 : update1
	// id3 : getUpdated4Id
	if ($ccdb->update1($id1, "{$id1}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('3-4 failed');
	}
	if ($ccdb->update1($id2, "{$id2}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('3-5 failed');
	}
	$updates = $ccdb->getUpdated4Id($id3);
	if (($updates === CCDB_RWERR_NOID) || (count($updates) != 2)) {
		$ccdb->dpExit('3-6 failed');
	}
	// id1 : update1
	// id2 : [o]
	// id3 : getUpdated4Id
	if ($ccdb->update1($id1, "{$id1}-2") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('3-7 failed');
	}
	$updates = $ccdb->getUpdated4Id($id3);
	if (($updates === CCDB_RWERR_NOID) || (count($updates) != 1)) {
		$ccdb->dpExit('3-8 failed');
	}
	if ($updates[$id1]['DATA'] != "{$id1}-2") {
		$ccdb->dpExit('3-9 failed');
	}
	// id1 : [o]
	// id2 : [o]
	// id3 : getUpdated4Id
	$updates = $ccdb->getUpdated4Id($id3);
	if (($updates === CCDB_RWERR_NOID) || (count($updates) != 0)) {
		$ccdb->dpExit('3-10 failed');
	}
// test-4 : update2
	// id1 : start
	// id2 : start
	$ccdb->setConfig('maxClient', 2);
	$ccdb->cleanUp();
	$id1 = $ccdb->start('user1');
	if (!$id1) {
		$ccdb->dpExit('4-1 failed');
	}
	$id2 = $ccdb->start('user2');
	if (!$id2) {
		$ccdb->dpExit('4-2 failed');
	}
	// id1-update2 --> success
	if ($ccdb->update2($id1, "{$id1}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('4-3 failed');
	}
	// id1-update2 --> failed
	if ($ccdb->update2($id1, "{$id1}-2") == CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('4-4 failed');
	}
	// id2-getUpdated4Id
	$updates = $ccdb->getUpdated4Id($id2);
	if (($updates === CCDB_RWERR_NOID) || (count($updates) != 1)) {
		$ccdb->dpExit('4-5 failed');
	}
	// id1-update2 --> success
	if ($ccdb->update2($id1, "{$id1}-2") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('4-6 failed');
	}
// test-5 : getUpdated4Id with "$in_targets"
	$ccdb->setConfig('maxClient', 3);
	$ccdb->cleanUp();
	$id1 = $ccdb->start('user1');
	if (!$id1) {
		$ccdb->dpExit('5-1 failed');
	}
	if ($ccdb->update1($id1, "{$id1}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('5-2 failed');
	}
	$id2 = $ccdb->start('user2');
	if (!$id2) {
		$ccdb->dpExit('5-3 failed');
	}
	if ($ccdb->update1($id2, "{$id2}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('5-4 failed');
	}
	$id3 = $ccdb->start('user2');
	if (!$id3) {
		$ccdb->dpExit('5-5 failed');
	}
	if ($ccdb->update1($id3, "{$id3}-1") != CCDB_RWERR_SUCCESS) {
		$ccdb->dpExit('5-6 failed');
	}
	$updates = $ccdb->getUpdated4Id($id1, array($id2));
	if (count($updates) != 1) {
		$ccdb->dpExit('5-8 failed');
	}
// finished
	// $ccdb->cleanUp();
	$ccdb->dpExit('finished');
}

// frontend

define('APP_SELF', substr(str_replace(__DIR__, '', __FILE__), 1));
define('APP_PREFIX', str_replace('.php', '', APP_SELF));
define('APP_XHEADER', 'X-' . APP_PREFIX);
define('DB', APP_PREFIX . '.db');

define('Q_CMD', 'cmd');
define('Q_TYPE', 'type');

define('CMD_START', 'start');
define('CMD_END', 'end');
define('CMD_SEND', 'send');
define('CMD_RECEIVE', 'reseive');
define('CMD_BROWSE', 'browse');
define('CMD_TEST', 'test');
define('CMD_TEST_DB', 'test_db');
define('CMD_TEST_JS', 'test_js');

define('TYPE_OVERUPDATE', 'over');
define('TYPE_WAITUPDATE', 'wait');

define('P_CLIENTID', 'clientid');
define('P_NAME', 'name');
define('P_TARGET', 'target');

define('R_CLIENTID', h(P_CLIENTID, 5));
define('R_NAME', h(P_NAME, 5));
define('R_TARGET', h(P_TARGET, 5));

define('URL_START', entry(array(Q_CMD => CMD_START, P_NAME => R_NAME)));
define('URL_END', entry(array(Q_CMD => CMD_END, P_CLIENTID => R_CLIENTID)));
define('URL_SEND1', entry(array(Q_CMD => CMD_SEND, P_CLIENTID => R_CLIENTID, Q_TYPE => TYPE_OVERUPDATE)));
define('URL_SEND2', entry(array(Q_CMD => CMD_SEND, P_CLIENTID => R_CLIENTID, Q_TYPE => TYPE_WAITUPDATE)));
define('URL_RECEIVE', entry(array(Q_CMD => CMD_RECEIVE, P_CLIENTID => R_CLIENTID, P_TARGET => R_TARGET)));
define('URL_BROWSE', entry(array(Q_CMD => CMD_BROWSE)));
define('URL_TEST', entry(array(Q_CMD => CMD_TEST)));
define('URL_TEST_DB', entry(array(Q_CMD => CMD_TEST_DB)));
define('URL_TEST_JS', entry(array(Q_CMD => CMD_TEST_JS)));

function APP_ERR_CCDB($in_ccdb_rwerr)
{
	return APP_ERR_CCDB_XXX + $in_ccdb_rwerr;
}

define('APP_ERR_SUCCESS', 10);
define('APP_ERR_CCDB_XXX', 20);
define('APP_ERR_CCDB_NOID', APP_ERR_CCDB(CCDB_RWERR_NOID));
define('APP_ERR_CCDB_TIMEOUT', APP_ERR_CCDB(CCDB_RWERR_TIMEOUT));
define('APP_ERR_GENERIC', 30);

// 0.05 sec
define('CONN_LONGPOLL_SLEEP', 50000);
// 5 min
define('CONN_LONGPOLL_MAXSTAY', 60 * 5);

function DefaultHeader($in_status_code)
{
	header("Content-Type: text/plain");
	header("Cache-Control: no-cache");
	header("X-" . APP_PREFIX . ": {$in_status_code}");
}

function print_json($in_hash)
{
	$jsons = array();
	foreach ($in_hash as $id => $dat) {
		foreach ($dat as $key => $val) {
			$dat[$key] = str_replace("'", "\'", $val);
		}
		$jo = <<<EOJO
	{
		ID : '{$id}',
		NAME : '{$dat['NAME']}',
		WRIT : '{$dat['WRIT']}',
		DATA : '{$dat['DATA']}'
	}
EOJO;
		array_push($jsons, $jo);
	}
	print "[\n" . implode(",\n", $jsons) . "\n]";
}


if (array_key_exists(CMD_TEST, $_GET)) {
	header('Location: ' . URL_TEST);
	exit;
} elseif  (array_key_exists(Q_CMD, $_GET)) {
	$ccdb = new CCDB(DB);
	switch ($_GET[Q_CMD]) {
	case CMD_START :
		$id = $ccdb->start($_GET[P_NAME]);
		if ($id) {
			DefaultHeader(APP_ERR_SUCCESS);
			print $id;
		} else {
			DefaultHeader(APP_ERR_GENERIC);
		}
		break;
	case CMD_END :
		$ccdb->end($_GET[P_CLIENTID]);
		DefaultHeader(APP_ERR_SUCCESS);
		break;
	case CMD_SEND :
		$sh = fopen('php://input', 'rb');
		$posted = '';
		while (!feof($sh)) {
			$posted .= fread($sh, 8129);
		}
		fclose($sh);
		switch ($_GET[Q_TYPE]) {
		case TYPE_OVERUPDATE :
			if (($rc = $ccdb->update1($_GET[P_CLIENTID], $posted)) == CCDB_RWERR_SUCCESS) {
				DefaultHeader(APP_ERR_SUCCESS);
			} else {
				DefaultHeader(APP_ERR_CCDB($rc));
				print 'update failed';
			}
			break;
		case TYPE_WAITUPDATE :
			if (($rc = $ccdb->update2($_GET[P_CLIENTID], $posted)) == CCDB_RWERR_SUCCESS) {
				DefaultHeader(APP_ERR_SUCCESS);
			} else {
				DefaultHeader(APP_ERR_CCDB($rc));
				print 'update failed';
			}
			break;
		default;
			DefaultHeader(APP_ERR_GENERIC);
			print 'invalid' . Q_TYPE;
			break;
		}
		break;
	case CMD_RECEIVE :
		$start = time();
		while (TRUE) {
			$targets = null;
			if (array_key_exists(P_TARGET, $_GET) && $_GET[P_TARGET]) {
				$targets = explode(',', $_GET[P_TARGET]);
			}
			$updates = $ccdb->getUpdated4Id($_GET[P_CLIENTID], $targets);
			if ($updates === CCDB_RWERR_NOID) {
				DefaultHeader(APP_ERR_CCDB_NOID);
				break;
			}
			if (count($updates) > 0) {
				DefaultHeader(APP_ERR_SUCCESS);
				header('Content-Type: application/json');
				print_json($updates);
				break;
			} else {
				if (time() - $start > CONN_LONGPOLL_MAXSTAY) {
					$ccdb->end($_GET[P_CLIENTID]);
					DefaultHeader(APP_ERR_GENERIC);
					print 'long-poll timeout';
					break;
				} else {
					usleep(CONN_LONGPOLL_SLEEP);
					set_time_limit(3);
				}
			}
		}
		break;
	case CMD_BROWSE :
		$all = $ccdb->browseAllData();
		DefaultHeader(APP_ERR_SUCCESS);
		header('Content-Type: application/json');
		print_json($all);
		break;
	case CMD_TEST :
		header('Content-Type: text/html');
		$URL_TEST_JS = URL_TEST_JS;
		$URL_TEST_DB = URL_TEST_DB;
		print <<<EOTEST
<style type='text/css'>
DIV.f11 {
	height: 150px;
}
DIV.f12 {
	height: 75px;
}
DIV.f2 {
	height: 450px;
}
TABLE {
	width: 100%;
}
IFRAME {
	margin: 0px;
	padding: 0px;
	border: solid 1px gray;
	width: 100%;
	height: 100%;
}
</style>
<table>
<tr>
	<td>
<!--
		<div class='f11'><iframe src='{$URL_TEST_JS}'></iframe></div>
		<div class='f11'><iframe src='{$URL_TEST_JS}'></iframe></div>
		<div class='f11'><iframe src='{$URL_TEST_JS}'></iframe></div>
-->
		<div class='f12'><iframe src='{$URL_TEST_JS}'></iframe></div>
		<div class='f12'><iframe src='{$URL_TEST_JS}'></iframe></div>
		<div class='f12'><iframe src='{$URL_TEST_JS}'></iframe></div>
		<div class='f12'><iframe src='{$URL_TEST_JS}'></iframe></div>
		<div class='f12'><iframe src='{$URL_TEST_JS}'></iframe></div>
		<div class='f12'><iframe src='{$URL_TEST_JS}'></iframe></div>
	</td>
	<td>
		<div class='f2'><iframe src='{$URL_TEST_DB}'></iframe></div>
	</td>
</tr>
</table>
EOTEST;
		break;
	case CMD_TEST_DB :
		header('Content-Type: text/plain');
		ccdb_test();
		break;
	case CMD_TEST_JS :
		header('Content-Type: text/html');
		$APP_SELF = APP_SELF;
		$APP_PREFIX = APP_PREFIX;
		print <<<EOTEST
<div id='report'></div>
<script type='text/javascript' src='{$APP_SELF}'></script>
<script type='text/javascript'>

// common

function report(in_text)
{
	document.getElementById('report').innerHTML += '<br />' + in_text;
}

// {$APP_PREFIX}

function cb_start(in_started)
{
	if (in_started) {
		var rep = 5;
		var max = 5000;
		while (rep-- > 0) {
			var tm = Math.floor(Math.random() * max);
			window.setTimeout((function(tm) {
				return function() {
					{$APP_PREFIX}.send1('send-' + tm, cb_send);
				};
			})(tm), tm);
		}
		window.setTimeout(function() {
			{$APP_PREFIX}.end();
			report('finished.');
		}, max);
	} else {
		report('failed to start.');
	}
}

function cb_receive(in_err, in_data)
{
	if (in_err != APP_ERR_SUCCESS) {
		{$APP_PREFIX}.end();
		report('failed to receive.');
	} else {
		for (var i = 0; i < in_data.length; i++) {
			var obj = in_data[i];
			report('receive (' + obj.NAME + ', ' + obj.DATA + ')');
		}
	}
}

function cb_send(in_err)
{
	if (in_err != APP_ERR_SUCCESS) {
		{$APP_PREFIX}.end();
		report('failed to send.');
	}
}

// p2p

function appStarted(in_started)
{
	if (in_started) {
		report(p2p.name + ' vs ' + {$APP_PREFIX}.hisId2Name(p2p.partner));
		p2p.sendAppData(' ... hello, ' + {$APP_PREFIX}.hisId2Name(p2p.partner) + '! from ' + p2p.name);
	} else {
		report('ng (appStarted)');
	}
}

function recvAppData(in_data)
{
	if (in_data) {
		report(in_data);
	} else {
		report(' ... ignore null');
	}
}

function lostConnection()
{
	report('ng (lostConnection)');
}

var name = 'No.' + Math.floor(Math.random() * 1000);

if (false) {
	report(name);
	{$APP_PREFIX}.start(name, cb_start, cb_receive);
} else {
	window.setTimeout(function() {
		report(name);
		p2p.start(name, appStarted, recvAppData, lostConnection);
	}, Math.floor(Math.random() * 500));
}

</script>
EOTEST;
		break;
	default :
		break;
	}
} else {
	header('Content-Type: text/javascript');
	// php vars in javascript
	$APP_PREFIX = APP_PREFIX;
	$APP_XHEADER = APP_XHEADER;
	$URL_START = URL_START;
	$URL_END = URL_END;
	$URL_SEND1 = URL_SEND1;
	$URL_SEND2 = URL_SEND2;
	$URL_RECEIVE = URL_RECEIVE;
	$URL_BROWSE = URL_BROWSE;
	$R_CLIENTID = R_CLIENTID;
	$R_NAME = R_NAME;
	$R_TARGET = R_TARGET;
	$APP_ERR_SUCCESS = APP_ERR_SUCCESS;
	$APP_ERR_CCDB_NOID = APP_ERR_CCDB_NOID;
	$APP_ERR_CCDB_TIMEOUT = APP_ERR_CCDB_TIMEOUT;
	$APP_ERR_GENERIC = APP_ERR_GENERIC;
	// returns javascript
	print <<<EOJS
var APP_ERR_SUCCESS = '{$APP_ERR_SUCCESS}';
var APP_ERR_CCDB_NOID = '{$APP_ERR_CCDB_NOID}';
var APP_ERR_CCDB_TIMEOUT = '{$APP_ERR_CCDB_TIMEOUT}';
var APP_ERR_GENERIC = '{$APP_ERR_GENERIC}';

var serializer = {
	_obj2txt : function(o) {
		var type = typeof(o);
		if (o === null) {
			return 'null';
		}
		var value = o.toString();
		switch (type) {
		case 'string' :
			return '"' + value + '"';
			break;
		case 'boolean' :
		case 'number' :
			return value;
			break;
		case 'object' :
			if (typeof(o.length) == 'undefined') {
				var props = [];
				for (var prop in o) {
					props.push(prop + ':' + this._obj2txt(o[prop]));
				}
				return '{' + props.join(',') + '}';
			} else {
				var nodes = value.split(',');
				for (var i = 0; i < nodes.length; i++) {
					if (nodes[i].match(/[^0-9]/)) {
						nodes[i] = '"' + nodes[i] + '"';
					}
				}
				return '[' + nodes.join(',') + ']';
			}
			break;
		default :
			break;
		}
	},
	test : function() {
		var checker = function(o1, o2) {
			if (typeof(o1) == 'object') {
				for (var prop in o1) {
					if (!checker(o1[prop], o2[prop])) {
						return false;
					}
				}
				return true;
			} else {
				if (o1 === o2) {
					return true;
				} else {
					this._reason = o1 + ' !== ' + o2;
					return false;
				}
			}
		}
		var org = {
			p1 : {
				p11 : 1,
				p12 : false
			},
			p2 : ' ',
			p3 : '',
			p4 : null,
			p5 : [1, 'x', 5],
		};
		if (checker(org, this.deserialize(this.serialize(org)))) {
			alert('success');
		} else {
			alert('failed : ' + this._reason);
		}
	},
	serialize : function(in_obj) {
		return encodeURIComponent(this._obj2txt(in_obj));
	},
	deserialize : function(in_txt) {
		return eval('(' + decodeURIComponent(in_txt) + ')');
	}
};

(function() {
	var ArrayExtension = {
		index : function(in_entry) {
			for (var i = 0; i < this.length; i++) {
				if (this[i] == in_entry) {
					return i;
				}
			}
			return -1;
		},
		inArray : function(in_entry) {
			return (this.index(in_entry) < 0) ? false : true;
		},
		randomEntry : function() {
			return this[Math.floor(Math.random() * this.length)];
		},
		chainRef : function(in_ref) {
			for (var i = 0; i < this.length; i++) {
				if (!this[i]) {
					this[i] = in_ref;
					return;
				}
			}
			this.push(in_ref);
		},
		unChainRef : function(in_ref) {
			for (var i = 0; i < this.length; i++) {
				if (this[i] == in_ref) {
					this[i] = null;
					return;
				}
			}
		}
	};
	for (var prop in ArrayExtension) {
		Array.prototype[prop] = ArrayExtension[prop];
	}
})();

XMLHttpRequest.prototype.get{$APP_PREFIX}Err = function() {
	if (this.readyState != 4) {
		return null;
	} else if (this.status == 200) {
		return this.getResponseHeader('{$APP_XHEADER}');
	} else if (this.status == 0) {
		// xhr has been aborted
		return null;
	} else {
		return APP_ERR_GENERIC;
	}
}

var {$APP_PREFIX} = {
	_id : null,
	_waitLock : false,
	_pollTarget : [],
	_xhrBuff : [],
	_latest : {},
	_latestJo : function(in_jo) {
		var ret = [];
		var tmp = eval('(' + in_jo + ')');
		for (var i = 0; i < tmp.length; i++) {
			if ((typeof(this._latest[tmp[i].ID]) != 'undefined')
				&& (this._latest[tmp[i].ID].WRIT > tmp[i].WRIT)) {
				ret.push(this._latest[tmp[i].ID]);
			} else {
				tmp[i].DATA = serializer.deserialize(tmp[i].DATA);
				this._latest[tmp[i].ID] = tmp[i];
				ret.push(tmp[i]);
			}
		}
		return ret;
	},
	resetPollTarget : function() {
		this._pollTarget = [];
	},
	appendPollTarget : function(in_target_id) {
		if (!this._pollTarget.inArray(in_target_id)) {
			this._pollTarget.push(in_target_id);
		}
	},
	hisId2Name : function(in_id) {
		if (typeof(this._latest[in_id]) != 'undefined') {
			return this._latest[in_id].NAME;
		} else {
			return null;
		}
	},
	myId : function() {
		return this._id;
	},
	start : function(in_name, in_cb_start, in_cb_poll) {
		/*
			in_cb_start(in_err)
				in_err == true : start
				in_err == false : can't start (retry later)
			in_cb_poll(in_err, in_data)
				in_err == APP_ERR_SUCCESS : you can use in_data.
				in_err == APP_ERR_CCDB_NOID : your id is expired (need re-start).
				in_err == APP_ERR_GENERIC : need retry.
		*/
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = (function(self) {
			return function() {
				var err = xhr.get{$APP_PREFIX}Err();
				if (!err) {
					return;
				}
				if (err == APP_ERR_SUCCESS) {
					self._id = xhr.responseText;
					(in_cb_start)(true);
					self._poll(in_cb_poll);
				} else {
					(in_cb_start)(false);
				}
			};
		})(this);
		var url = '{$URL_START}';
		xhr.open('GET', url.replace(/{$R_NAME}/, in_name), true);
		xhr.send();
	},
	end : function() {
		for (var i = 0; i < this._xhrBuff.length; i++) {
			if (this._xhrBuff[i]) {
				this._xhrBuff[i].abort();
				this._xhrBuff[i] = null;
			}
		}
		var xhr = new XMLHttpRequest();
		var url = '{$URL_END}';
		xhr.open('GET', url.replace(/{$R_CLIENTID}/, this._id), true);
		xhr.send();
		this._id = null;
	},
	_send : function(in_url, in_data, in_cb_send) {
		/*
			in_cb_send(in_err)
				in_err == APP_ERR_SUCCESS : sent-data has been accepted.
				in_err == APP_ERR_CCDB_NOID : your id is expired (need re-start).
				in_err == APP_ERR_CCDB_TIMEOUT : need retry.
				in_err == APP_ERR_GENERIC : need retry.
		*/
		if (!this._id) {
			return false;
		}
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = (function(self) {
			return function() {
				var err = xhr.get{$APP_PREFIX}Err();
				if (!err) {
					return;
				}
				self._waitLock = false;
				if (err) {
					(in_cb_send)(err);
				} else {
					(in_cb_send)(APP_ERR_GENERIC);
				}
				self._xhrBuff.unChainRef(xhr);
			};
		})(this);
		xhr.open('POST', in_url.replace(/{$R_CLIENTID}/, this._id), true);
		xhr.send(serializer.serialize(in_data));
		this._xhrBuff.chainRef(xhr);
		return true;
	},
	_send_1by1 : function(in_url, in_data, in_cb_send) {
		if (this._waitLock) {
			return false;
		}
		if (this._send(in_url, in_data, in_cb_send)) {
			this._waitLock = true;
			return true;
		} else {
			return false;
		}
	},
	send1 : function(in_data, in_cb_send) {
		/*
			client : send soon
			server : overwrite
		*/
		return this._send('{$URL_SEND1}', in_data, in_cb_send);
	},
	send2 : function(in_data, in_cb_send) {
		/*
			client : wait response before sending
			server : overwrite
		*/
		return this._send_1by1('{$URL_SEND1}', in_data, in_cb_send);
	},
	send3 : function(in_data, in_cb_send) {
		/*
			client : wait response before sending
			server : wait other's reading
		*/
		return this._send_1by1('{$URL_SEND2}', in_data, in_cb_send);
	},
	_poll : function(in_cb_poll) {
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = (function(self) {
			return function() {
				var err = xhr.get{$APP_PREFIX}Err();
				if (!err) {
					return;
				}
				if (err == APP_ERR_SUCCESS) {
					in_cb_poll(err, self._latestJo(xhr.responseText));
					// next long-poll
					self._poll(in_cb_poll);
				} else {
					in_cb_poll(err, null);
				}
				self._xhrBuff.unChainRef(xhr);
			};
		})(this);
		var url = '{$URL_RECEIVE}';
		url = url.replace(/{$R_CLIENTID}/, this._id);
		if (this._pollTarget.length > 0) {
			url = url.replace(/{$R_TARGET}/, this._pollTarget.join(','));
		} else {
			url = url.replace(/{$R_TARGET}/, '');
		}
		xhr.open('GET', url, true);
		xhr.send();
		this._xhrBuff.chainRef(xhr);
	},
	browseAllData : function(in_cb_browse) {
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = (function(self) {
			return function() {
				var err = xhr.get{$APP_PREFIX}Err();
				if (!err) {
					return;
				}
				if (err == APP_ERR_SUCCESS) {
					in_cb_browse(err, self._latestJo(xhr.responseText));
				} else {
					in_cb_browse(err, null);
				}
			};
		})(this);
		xhr.open('GET', '{$URL_BROWSE}', true);
		xhr.send();
	}
};

var p2p = {
	_C : {
		S_UNKNOWN : 0,
		S_HELLO1 : 1,
		S_HELLO2 : 2,
		S_APP_WAITING : 3,
		S_APP_CURRENT : 4,
		TIMEOUT_HELLO : 10 * 1000,
		TIMEOUT_APP : 60 * 1000,
		HELLO : 'hello',
		DATA : 'app',
	},
	_sendHello : function(p) {
		if (p.me.length > 0) {
			this._helloTo = p.me.randomEntry();
			this._state = this._C.S_HELLO2;
		} else {
			if (p.none.length > 0) {
				this._helloTo = p.none.randomEntry();
				this._state = this._C.S_HELLO2;
			} else {
				this._helloTo = null;
				this._state = this._C.S_HELLO1;
			}
		}
		this._send(null);
	},
	_checkHello : function(p) {
		if (p.me.inArray(this._helloTo)) {
			this._confirm();
		} else {
			if (p.none.inArray(this._helloTo)) {
				return;
			} else {
				this._sendHello(p);
			}
		}
	},
	_handleError : function() {
		{$APP_PREFIX}.end();
		switch (this._state) {
		case this._C.S_UNKNOWN :
		case this._C.S_HELLO1 :
		case this._C.S_HELLO2 :
			(this.appStarted)(false);
			break;
		case this._C.S_APP_WAITING :
		case this._C.S_APP_CURRENT :
			(this.lostConnection)();
			break;
		default :
			break;
		}
		if (this._timer) {
			window.clearTimeout(this._timer);
		}
		this._init();
	},
	_confirm : function() {
		{$APP_PREFIX}.browseAllData(
			(function(self) {
				return function(in_err, in_data) {
					if (in_err != APP_ERR_SUCCESS) {
						self._handleError();
						return;
					}
					for (var i = 0; i < in_data.length; i++) {
						var obj = in_data[i];
						if (obj.ID != self._helloTo) {
							continue;
						}
						if (obj.DATA[self._C.HELLO] != {$APP_PREFIX}.myId()) {
							break;
						}
						self._partner = self._helloTo;
						{$APP_PREFIX}.appendPollTarget(self._partner);
						self._state = self._C.S_APP_WAITING;
						self._timestamp = (new Date()).getTime();
						(self.appStarted)(true);
						return;
					}
					{$APP_PREFIX}.resetPollTarget();
					self._helloTo = null;
					self._state = self._C.S_HELLO1;
					self._send(null);
				};
			})(this));
	},
	_cb_receive : function(in_err, in_data) {
		if (in_err != APP_ERR_SUCCESS) {
			this._handleError();
			return;
		}
		switch (this._state) {
		case this._C.S_HELLO1 :
		case this._C.S_HELLO2 :
			var p = {
				me : [],
				him : [],
				none : []
			};
			for (var i = 0; i < in_data.length; i++) {
				var obj = in_data[i];
				if (obj.DATA[this._C.HELLO] == {$APP_PREFIX}.myId()) {
					p.me.push(obj.ID);
				} else if (obj.DATA[this._C.HELLO]) {
					p.him.push(obj.ID);
				} else {
					p.none.push(obj.ID);
				}
			}
			if (this._state == this._C.S_HELLO1) {
				this._sendHello(p);
			} else if (this._state == this._C.S_HELLO2) {
				this._checkHello(p);
			}
			break;
		case this._C.S_APP_WAITING :
		case this._C.S_APP_CURRENT :
			for (var i = 0; i < in_data.length; i++) {
				var obj = in_data[i];
				if (obj.ID != this._partner) {
					continue;
				}
				if (obj.DATA[this._C.HELLO] == {$APP_PREFIX}.myId()) {
					this.recvAppData(obj.DATA[this._C.DATA]);
					break;
				} else {
					{$APP_PREFIX}.end();
					(this.lostConnection)();
					return;
				}
			}
			break;
		case this._C.S_UNKNOWN :
		default :
			break;
		}
	},
	_send : function(in_data) {
		var obj = {};
		obj[this._C.HELLO] = (this._helloTo ? this._helloTo : '');
		obj[this._C.DATA] = in_data;
		{$APP_PREFIX}.send2(
			obj,
			(function(self) {
				return function(in_err) {
					if (in_err != APP_ERR_SUCCESS) {
						self._handleError();
						return;
					}
				};
			})(this));
	},
	_cb_start : function(in_started) {
		if (in_started) {
			this._timestamp = (new Date()).getTime();
			this._state = this._C.S_HELLO1;
			this._send(null);
			this._timer = window.setTimeout(
				(function(self) {
					return function() {
						if ((self._state == self._C.S_HELLO1) || (self._state == self._C.S_HELLO2)) {
							self._handleError();
						}
					};
				})(this),
				this._C.TIMEOUT_HELLO);
		} else {
			this._handleError();
		}
	},
	_init : function() {
		this._state = this._C.S_UNKNOWN;
		this._timestamp = 0;
		this._timer = null;
		this._helloTo = null;
		this._partner = null;
	},
	start : function(playre_name, appStarted, recvAppData, lostConnection) {
		this._init();
		this.name = playre_name;
		this.appStarted = appStarted;
		this.recvAppData = recvAppData;
		this.lostConnection = lostConnection;
		{$APP_PREFIX}.start(
			this.name,
			(function(self) {
				return function(in_started) {
					self._cb_start(in_started);
				};
			})(this),
			(function(self) {
				return function(in_err, in_data) {
					self._cb_receive(in_err, in_data);
				};
			})(this));
	},
	sendAppData : function(in_data) {
		if ((this._state == this._C.S_APP_WAITING) || (this._state == this._C.S_APP_CURRENT)) {
			this._send(in_data);
			return true;
		} else {
			return false;
		}
	}
};

EOJS;
}

exit;

?>
