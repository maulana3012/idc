<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @author : daesung kim
*
* $Id: util_html.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/

require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

function printHtmlSelect($arg, $strClass, $selected = "") {

	global $M;

	if($arg == "customer_group") {
		$sql = "SELECT cug_code, substring(cug_name from 1 for 16) || '..' AS cug_name FROM ".ZKP_SQL."_tb_customer_group ORDER BY cug_name";
	}

	$result = & query($sql);

	if(numQueryRows($result) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the $arg first. you can find the [new ". ucfirst($arg) ."] under the BASIC DATA menu");
		$M->goErrorPage($o, HTTP_DIR . "apotik/basic_data/index.php");
	} else {

		print "<select name=\"_" . $arg . "\" class=\"$strClass\">\n";
		print "\t<option value=\"\">==SELECT==</option>\n";

		while ($columns = fetchRow($result)) {
			print "\t<option value=\"".$columns[0] . "\"".(($selected == $columns[0]) ? " selected":"").">".$columns[1]."</option>\n";
		}
		print "</select>\n";
	}
}
?>