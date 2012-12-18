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
			array_push($ret, getFiles($path));
		} elseif (is_file($path)) {
			array_unshift($ret, $path);
		}
	}
	closedir($dh);
	return $ret;
}

function makeLinks($in_list)
{
	for ($i = 0; $i < count($in_list); $i++) {
		if (is_array($in_list[$i])) {
			$dir = preg_replace('/[^\/]+$/', '', $in_list[$i][0]);
			print "<li>{$dir}\n";
			print "<ul>\n";
			makeLinks($in_list[$i]);
			print "</ul>\n";
			print "</li>\n";
		} else {
			print "<li><a href='{$in_list[$i]}'>{$in_list[$i]}</a></li>\n";
		}
	}
}

print "<ul>\n";
makeLinks(getFiles('.'));
print "</ul>\n";

?>
