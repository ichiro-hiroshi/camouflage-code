<ul>
<?php

function getFiles($in_path)
{
	$ret = array();
	$dh = opendir($in_path);
	if (!$dh) {
		print '// opendir failed';
		exit;
	}
	while (($fname = readdir($dh)) !== FALSE) {
		if (($fname == '.') || ($fname == '..')) {
			continue;
		}
		$path = "{$in_path}/{$fname}";
		if (is_dir($path)) {
			$ret[$fname] = array('p' => $path, 'c' => getFiles($path));
		} elseif (is_file($path)) {
			$ret[$fname] = array('p' => $path, 'c' => NULL);
		}
	}
	closedir($dh);
	uasort($ret, function($e1, $e2) {
		if ($e1['c'] && !$e2['c']) {
			return -1;
		}
		if (!$e1['c'] && $e2['c']) {
			return 1;
		}
		return strcasecmp($e1['p'], $e2['p']);
	});
	return $ret;
}

function makeLinks($in_tree, $in_open)
{
	if ($in_open) {
		$display = 'block';
		$button = '-';
	} else {
		$display = 'none';
		$button = '+';
	}
	foreach ($in_tree as $key => $val) {
		if ($val['c']) {
			print "<li><button onclick='action(this)'>{$button}</button> <span>{$key}</span>\n";
			print "<ul style='display:{$display};'>\n";
			makeLinks($val['c'], $in_open);
			print "</ul>\n";
			print "</li>\n";
		} else {
			print "<li><a href='{$val['p']}'>{$key}</a></li>\n";
		}
	}
}

makeLinks(getFiles('.'), array_key_exists('o', $_GET));

?>
</ul>
<style type='text/css'>

BUTTON {
	border: solid 1px silver;
	color: gray;
	font-family: monospace;
	padding: 3px;
}
UL {
	list-style-type: none;
}

</style>
<script>

function action(in_elem)
{
	var ul = in_elem.parentNode.getElementsByTagName('UL')[0];
	if (ul.style.display == 'none') {
		in_elem.innerHTML = '-';
		ul.style.display = 'block';
	} else {
		in_elem.innerHTML = '+';
		ul.style.display = 'none';
	}
}

</script>
