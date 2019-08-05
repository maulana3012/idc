<?php
// index
// 0 => midx
// 1 => pidx
// 2 => depth
// 3 => code
// 4 => name

require_once "../zk_config.php";
require_once APP_DIR . "../_lib/zk_dbconn.php";

$sql = "SELECT icat_midx, icat_pidx, icat_depth, icat_code, icat_name FROM ".ZKP_SQL."_tb_item_cat WHERE icat_midx > 0 ORDER BY 1";
$result =& query($sql);
echo "var icat = new Array();\n";
$i = 0;
while ($rows =& fetchRow($result,0)) {
	foreach($rows as $key => $val) {
		$rows[$key] = addslashes($val);
	}
	$element = implode("', '", $rows);
	echo "icat[".$i++."] = ['".$element."'];\n";
}
?>