<?php

define('POST_ENTRY', substr(str_replace(__DIR__, '', __FILE__), 1));
define('POST_DB', str_replace('.php', '', POST_ENTRY) . '.db');
define('POST_RESPONSE', 10);
define('POST_KEEP', 20);

if (!is_dir(POST_DB)) {
	if (!mkdir(POST_DB)) {
		print 'can not mkdir (POST_DB).';
		exit;
	}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$posted = '';
	$sh = fopen('php://input', 'rb');
	while (!feof($sh)) {
		$posted .= fread($sh, 8129);
	}
	fclose($sh);
	if ($posted) {
		$fname = sprintf('%-015s', microtime(TRUE)) . '.txt';
		$fh = @fopen(POST_DB . "/{$fname}", 'w');
		if ($fh) {
			fwrite($fh, $posted);
			fclose($fh);
		} else {
			print 'can not fwrite.';
			exit;
		}
	}
	$files = array();
	if ($dh = opendir(POST_DB)) {
		while (($fname = readdir($dh)) !== FALSE) {
			$path = POST_DB . "/{$fname}";
			if (is_file($path)) {
				array_push($files, $path);
			}
		}
		closedir($dh);
	} else {
		print 'can not opendir.';
		exit;
	}
	rsort($files);
	$jsons = array();
	for ($i = 0; $i < count($files); $i++) {
		if ($i < POST_RESPONSE) {
			// escape & make json
			$fh = @fopen($files[$i], 'r');
			if ($fh) {
				flock($fh, LOCK_SH);
				$name = trim(fgets($fh));
				$data = '';
				while (!feof($fh)) {
					if ($data) {
						$data .= '\n';
					}
					$data .= trim(fgets($fh));
				}
				flock($fh, LOCK_UN);
				fclose($fh);
				array_push($jsons, "\t{\n\t\tNAME:'{$name}',\n\t\tDATA:'{$data}'\n\t}");
			}
		}
		if ($i > POST_KEEP) {
			@unlink($files[$i]);
		}
	}
	header('Content-Type: application/json');
	print "[\n" . implode(",\n", $jsons) . "\n]";
	exit;
}

?>

<style type='text/css'>

TABLE { border-collapse: collapse;}
TD { padding: 0px;}
INPUT, TEXTAREA {
	border-top: solid 1px #999999;
	border-left: solid 1px #999999;
	border-right: solid 1px #dddddd;
	border-bottom: solid 1px #dddddd;
}
INPUT, BUTTON, TEXTAREA {
	margin-top: 5px;
	margin-left: 5px;
}
TEXTAREA {
	width: 90%;
	height: 60%;
}

#VTBL, #CTBL {
	margin-top: 5px;
	margin-left: 5px;
}
#VTBL TD, #CTBL TD {
	padding: 2px 3px 3px 2px;
	border: solid 1px #aaaaaa;
	background: -moz-linear-gradient(top, #eeeeee, #dddddd);
	background: -webkit-gradient(linear, left top, left bottom, from(#eeeeee), to(#dddddd));
	xbackground-color: #dddddd;
}

#MVIEW { width: 400px; height: 302px;}
#CHECK { width: 400px; height: 200px;}
#INPUT { width: 400px; height: 100px;}
#MVIEW, #INPUT, #CHECK {
	border-top: solid 1px #eeeeee;
	border-left: solid 1px #eeeeee;
	border-right: solid 1px #aaaaaa;
	border-bottom: solid 1px #aaaaaa;
	border-radius: 2px;
}

.cName {
	word-break: break-all;
	width: 50px;
}
.cData {
	word-break: break-all;
	width: 320px;
}

.cShow { display: block;}
.cHide { display: none;}

.cOn  { background-color: #cccccc;}
.cOff { background-color: #bbbbbb;}

</style>
<table>
<tr>
<td>
	<div id='MVIEW' class='cOff'><table id='VTBL'></table></div>
</td>
<td>
	<table>
	<tr>
		<td><div id='CHECK' class='cOff'><table id='CTBL'></table></div></td>
	</tr>
	<tr>
	<td>
		<div id='INPUT' class='cOff'>
			<div id='LOGIN' class='cShow'><input id='NAME' /><br /><button onclick='login();'>LOGIN</button></div>
			<div id='WRITE' class='cHide'><textarea id='POST'></textarea><br /><button onclick='post();'>POST</button><button onclick='logout();'>LOGOUT</button></div>
		</div>
	</td>
	</tr>
	</table>
</td>
</tr>
</table>
<script type='text/javascript' src='ConnJS.php'></script>
<script type='text/javascript'>

var gE = (function(){
	return (function(e){
		var hash = {};
		if (e.id) {
			hash[e.id] = e;
		}
		for (var i = 0; i < e.childNodes.length; i++) {
			var tmp = arguments.callee(e.childNodes.item(i));
			for (var id in tmp) {
				hash[id] = tmp[id];
			}
		}
		return hash;
	})(document.body);
})();

// extension for prototype object
var STRING_EXTENSION = {
	_cmdlen : 1,
	toOrg : function(in_text) {
		return this.substr(this._cmdlen);
	},
	toUCmd : function() {
		return 'U' + this;
	},
	isUCmd : function() {
		return (this.substr(0, this._cmdlen) == 'U');
	},
	toPCmd : function() {
		return 'P' + this;
	},
	isPCmd : function() {
		return (this.substr(0, this._cmdlen) == 'P');
	}
};

for (prop in STRING_EXTENSION) {
	String.prototype[prop] = STRING_EXTENSION[prop];
}

// extension for instance
var TEXTAREA_EXTENSION = {
	_onSending : null,
	_afterSent : null,
	_postTweet : function(in_err) {
		if (in_err == APP_ERR_SUCCESS) {
			this._afterSent = this._onSending;
		} else {
			alert('send-error(' + in_err + ')');
		}
	},
	_send : function(in_posted) {
		if (in_posted) {
			this.value = '';
			var data = this.value.toPCmd();
		} else {
			if (this._afterSent == this.value) {
				// already sent
				return;
			}
			var data = this.value.toUCmd();
		}
		if (ConnJS.send2(data, this._postTweet)) {
			this._onSending = this.value;
		}
	},
	tweet : function() {
		this._send(false);
	},
	refresh : function() {
		this._send(true);
	}
};

for (prop in TEXTAREA_EXTENSION) {
	gE.POST[prop] = TEXTAREA_EXTENSION[prop];
}

var gCHECK = {
	_list : {
		/* ID : ELEMENT, ... */
	},
	update : function(in_obj) {
		for (var id in this._list) {
			if (in_obj.ID == id) {
				this._list[id].textContent = in_obj.DATA.toOrg();
				return;
			}
		}
		var row = gE.CTBL.insertRow(gE.CTBL.rows.length);
		// in_obj.NAME
		var div1 = document.createElement('DIV');
		div1.textContent = in_obj.NAME;
		with (row.insertCell(0)) {
			appendChild(div1);
			className = 'cName';
		}
		// in_obj.DATA
		var div2 = document.createElement('DIV');
		div2.textContent = in_obj.DATA.toOrg();
		with (row.insertCell(1)) {
			appendChild(div2);
			className = 'cData';
		}
		this._list[in_obj.ID] = div2;
	}
};

function cb_receive(in_err, in_data)
{
	if (in_err == APP_ERR_SUCCESS) {
		var pcmd = false;
		for (var i = 0; i < in_data.length; i++) {
			var obj = in_data[i];
			if (obj.DATA.isPCmd()) {
				pcmd = true;
			}
			gCHECK.update(obj);
		}
		if (pcmd) {
			// someone has posted.
			reload();
		}
	} else {
		alert('receive-error(' + in_err + ')');
	}
}

function update_view(in_data)
{
	gE.VTBL.innerHTML = '';
	for (var i = 0; i < in_data.length; i++) {
		var row = gE.VTBL.insertRow(gE.VTBL.rows.length);
		// NAME
		var div1 = document.createElement('DIV');
		div1.textContent = in_data[i].NAME;
		with (row.insertCell(0)) {
			appendChild(div1);
			className = 'cName';
		}
		// DATA
		var div2 = document.createElement('DIV');
		div2.textContent = in_data[i].DATA;
		with (row.insertCell(1)) {
			appendChild(div2);
			className = 'cData';
		}
	}
}

var gXHR = {
	_lock : false,
	send : function(in_data, in_callback) {
		if (gXHR._lock) {
			return false;
		}
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = (function() {
			return function() {
				if (xhr.readyState != 4) {
					return;
				} else {
					gXHR._lock = false;
					(in_callback)(xhr.status, eval(xhr.responseText));
				}
			};
		})();
		xhr.open('POST', '<?php print POST_ENTRY; ?>', true);
		gXHR._lock = true;
		xhr.send(in_data);
		return true;
	}
};

function reload()
{
	var callback = function(in_status, in_data) {
		if (in_status == 200) {
			update_view(in_data);
		}
	};
	gXHR.send('', callback);
}

function post()
{
	if (!gE.POST.value) {
		return;
	}
	var callback = function(in_status, in_data) {
		if (in_status == 200) {
			update_view(in_data);
			gE.POST.refresh();
		}
	};
	gXHR.send(gE.NAME.value + "\n" + gE.POST.value, callback);
}

function listener(in_ev)
{
	gE.POST.tweet();
}

function cb_start(in_started)
{
	if (in_started) {
		window.addEventListener('keydown', listener, false);
	} else {
		alert('start-error(' + in_started + ')');
	}
}

function logout()
{
	gE.MVIEW.className = 'cOff';
	gE.INPUT.className = 'cOff';
	gE.CHECK.className = 'cOff';
	gE.LOGIN.className = 'cShow';
	gE.WRITE.className = 'cHide';
	ConnJS.end();
	window.removeEventListener('keydown', listener, false);
}

function login()
{
	reload();
	if (!gE.NAME.value) {
		alert('input name');
		return;
	}
	gE.MVIEW.className = 'cOn';
	gE.INPUT.className = 'cOn';
	gE.CHECK.className = 'cOn';
	gE.LOGIN.className = 'cHide';
	gE.WRITE.className = 'cShow';
	ConnJS.start(gE.NAME.value, cb_start, cb_receive);
}


</script>
