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
	for ($i = 0; $i < count($files); $i++) {
		if ($i < POST_RESPONSE) {
			// ********************
			// escape & make json
		}
		if ($i > POST_KEEP) {
			@unlink($files[$i]);
		}
	}
	header('Content-Type: application/json');
	exit;
}

?>

<html>
<head>
<style>

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

#CLIST {
	margin-top: 5px;
	margin-left: 5px;
}

#CLIST TD {
	padding: 2px 3px 3px 2px;
	border: solid 1px #aaaaaa;
	background-color: #dddddd;
}

#MVIEW { width: 400px; height: 402px;}
#CHECK { width: 400px; height: 300px;}
#INPUT { width: 400px; height: 100px;}
#MVIEW, #INPUT, #CHECK {
	border-top: solid 1px #eeeeee;
	border-left: solid 1px #eeeeee;
	border-right: solid 1px #aaaaaa;
	border-bottom: solid 1px #aaaaaa;
	border-radius: 2px;
}

#xMVIEW { display: none;}
#xCHECK { width: 400px; height: 50px;}

.cShow { display: block;}
.cHide { display: none;}

.cOn  { background-color: #cccccc;}
.cOff { background-color: #bbbbbb;}

</style>
</head>
<body>
<table>
<tr>
<td>
	<div id='MVIEW' class='cOff'></div>
</td>
<td>
	<table>
	<tr>
		<td><div id='CHECK' class='cOff'><table id='CLIST'></table></div></td>
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
		var row = gE.CLIST.insertRow(gE.CLIST.rows.length);
		// in_obj.NAME
		var div1 = document.createElement('DIV');
		div1.textContent = in_obj.NAME;
		(row.insertCell(0)).appendChild(div1);
		// in_obj.DATA
		var div2 = document.createElement('DIV');
		div2.textContent = in_obj.DATA.toOrg();
		(row.insertCell(1)).appendChild(div2);
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

function cb_start(in_started)
{
	if (in_started) {
		window.addEventListener('keydown',
			function(e) {
				// tweet every key-typing
				gE.POST.tweet();
			}, false);
	} else {
		alert('start-error(' + in_started + ')');
	}
}

function update_view(in_data)
{
	// ********************
	alert(in_data);
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
					(in_callback)(xhr.status, xhr.responseText);
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
	var callback = function(in_status, in_data) {
		if (in_status == 200) {
			update_view(in_data);
			gE.POST.refresh();
		}
	};
	gXHR.send(gE.NAME.value + "\n" + gE.POST.value, callback);
}

function logout()
{
	gE.MVIEW.className = 'cOff';
	gE.INPUT.className = 'cOff';
	gE.CHECK.className = 'cOff';
	gE.LOGIN.className = 'cShow';
	gE.WRITE.className = 'cHide';
	ConnJS.end();
}

function login()
{
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
</body>
</html>
