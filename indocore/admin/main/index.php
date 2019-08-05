<?php
/**
 * Copyright PT. ZONEKOM All right reserved
 * Contact us dskim at zonekom.com
 *
 * @generated : 18-May, 2007 15:20:22
 * @author    : daesung kim
 */

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PARAMETER
$showNull = isset($_GET["showNull"]) ? $_GET["showNull"] : "yes";
$setGroup = isset($_POST["_workgroup"]) ? $_POST["_workgroup"] : "";
$showMsg = isset($_REQUEST["showMsg"]) ? $_REQUEST["showMsg"] : "";
if ($setGroup != "") {
    $S->setValue("ma_workgroup", $setGroup);
}
/*
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";
 */
?>
<html>
<head>
<title>PT INDOCORE PERKASA MANAGEMENT SYSTEM</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body topmargin="0" leftmargin="0">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#eaecee">
  <tr>
    <td>
			<?php require_once APP_DIR . "_include/tpl_header.php"?>
    </td>
  </tr>
  <tr>
    <td style="padding:5 10 0 10" valign="bottom">
			<?php require_once APP_DIR . "_include/tpl_topMenu.php";?>
    </td>
  </tr>
  <tr>
    <td style="padding:0 3 3 3">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
			<?php require_once "_left_menu.php";?>
			<td style="padding:10;" height="480" valign="top">
          	<!--START: BODY-->
<?php
if ($S->getValue("ma_idx") != 0) {
    $sql = "SELECT " . ZKP_SQL . "_isSeeCriticalStock('" . ZKP_FUNCTION . "', " . $S->getValue("ma_idx") . ")";
    $col = fetchRow(query($sql));

    if ($showMsg && $col[0] == 't') {
        include "tpl_current_critical_stock.php";
    }

}
?>
            <!--END: BODY-->
          </td>
        </tr>
      </table>
      </td>
  </tr>
  <tr>
    <td style="padding:5 10 5 10" bgcolor="#FFFFFF">
			<?php require_once APP_DIR . "_include/tpl_footer.php"?>
    </td>
  </tr>
</table>
</body>
</html>
