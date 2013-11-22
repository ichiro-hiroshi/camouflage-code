<?php

define('SELF', substr(str_replace(__DIR__, '', __FILE__), 1));

$TMPL = array(
	'DIV' => "<div style='display:_P_;'><div style='display:_S_; background-image:url(_URL_&mod=i);'>hoge</div></div>",
	'OBJECT' => "<div style='display:_P_;'><object style='display:_S_;' data='_URL_&mod=i'></object></div>",
	'IMG' => "<div style='display:_P_;'><img style='display:_S_;' src='_URL_&mod=i' /></div>",
	'IFRAME' => "<div style='display:_P_;'><iframe style='display:_S_;' src='_URL_&mod=i'></iframe></div>"
);

$SUFF = "<script type='text/javascript' src='_URL_&mod=u'></script>\n<a href='" . SELF. "'>[index]</a><br />";

$REPL = array(
	array('_P_' => 'none', '_S_' => 'none'),
	array('_P_' => 'block', '_S_' => 'none'),
	array('_P_' => 'none', '_S_' => 'block')
);

if (array_key_exists('t', $_GET)) {
	$html = "<body>\n{$TMPL[$_GET['t']]}\n{$SUFF}\n</body>";
	$html = str_replace('_P_', $_GET['_P_'], $html);
	$html = str_replace('_S_', $_GET['_S_'], $html);
	$html = str_replace('_URL_', './beacon-logger.php?sig=' . rand(), $html);
	print $html;
} else {
	print "<ul>\n";
	foreach ($TMPL as $k => $v) {
		for ($i = 0; $i < count($REPL); $i++) {
			$display = $REPL[$i];
			$url = SELF . "?t={$k}&_P_={$display['_P_']}&_S_={$display['_S_']}";
			print "<li><a href='{$url}'>{$k} ({$display['_P_']}, {$display['_S_']})</a></li>\n";
		}
	}
	print "</ul>\n";
}

?>