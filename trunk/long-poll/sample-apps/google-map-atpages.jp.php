<?php
$SIG = md5(time());
print <<<EOHTML
<html>
<body>
<script type='text/javascript'>
window.addEventListener('load', function() {
	with (document) {
		var noscripts = getElementsByTagName('noscript');
		for (var i = 0; i < noscripts.length; i++) {
			if (noscripts.item(i).getAttribute('sig') == '{$SIG}') {
				var actualdoc = noscripts.item(i).textContent;
				break;
			}
		}
		open();
		write(actualdoc.replace(/add:/g, ''));
		close();
	}
}, false);
</script>
<noscript sig='{$SIG}'>
<add:html>
<add:meta name='viewport' content='initial-scale=1.0, user-scalable=no' />
<add:script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></add:script>
<add:script type='text/javascript' src='ConnJS.php'></add:script>
<add:body>
<add:div id='map_canvas' style='width:80%; height:80%; background-color:gray;'></add:div>
<add:script type='text/javascript'>

function cb_send(in_err)
{
	if (in_err != APP_ERR_SUCCESS) {
		alert('send-error(' + in_err + ')');
	}
}

function register_listener(in_type)
{
	window.addEventListener(in_type,
		function(e) {
			ConnJS.send1(
				/* data (application depended format) */
				in_type + ',' + e.clientX + ',' + e.clientY,
				/* callback-function called after sending */
				cb_send
			);
		}, false);
}

function cb_start(in_started)
{
	if (in_started) {
		register_listener('mousedown');
		register_listener('mouseup');
	} else {
		alert('start-error(' + in_started + ')');
	}
}

function cb_receive(in_err, in_data)
{
	if (in_err == APP_ERR_SUCCESS) {
		var recv = in_data[0].DATA.split(',');
		switch (recv[0]) {
		case 'mousedown' :
			GOOGLE.start_x = recv[1];
			GOOGLE.start_y = recv[2];
			break;
		case 'mouseup' :
			GOOGLE.map.panBy((GOOGLE.start_x - recv[1]), (GOOGLE.start_y - recv[2]));
			break;
		default :
			break;
		}
	} else {
		alert('receive-error(' + in_err + ')');
	}
}

var GOOGLE = {
	map : null,
	start_x : 0,
	start_y : 0
};

function init()
{
	var latlng = new google.maps.LatLng(-34.397, 150.644);
	var myOptions = {
		zoom : 8,
		center : latlng,
		disableDefaultUI : true, 
		mapTypeId : google.maps.MapTypeId.ROADMAP
	};
	GOOGLE.map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);
	var ua = navigator.userAgent.split(' ');
	ConnJS.start(
		/* user-name */
		ua[ua.length - 1],
		/* callback-function called after starting */
		cb_start,
		/* callback-function called when receive data */
		cb_receive
	);
}

window.setTimeout(function() {
	// onload is not fired, in case of Firefox.
	if (!GOOGLE.map) {
		init();
	}
}, 1000);

</add:script>
</add:body>
</add:html>
</noscript>
</body>
</html>
EOHTML;
?>
