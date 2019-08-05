<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 29-May, 2007 23:50:32
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
</script>
</head>
<body style="margin:8px">
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] E/D SUMMARY<br />
</strong>
<hr><br />
<span style="font-family:verdana;font-size:0.9em">Critical expired date management :<br /></span>
<table style="width:100%" class="table_l">
	<tr>
		<th width="60%%"></th>
		<th width="10%">A</th>
		<th width="10%">D</th>
		<th width="10%">H</th>
		<th width="10%">P</th>
	</tr>
	<tr>
		<td>[2101] AGS-50</td>
		<td align="center">8</td>
		<td align="center">8</td>
		<td align="center">4</td>
		<td align="center">4</td>
	</tr>
	<tr>
		<td>[2101NE] AGS-50SS</td>
		<td align="center">8</td>
		<td align="center">8</td>
		<td align="center">4</td>
		<td align="center">4</td>
	</tr>
	<tr>
		<td>[2200] AGL-28</td>
		<td align="center">6</td>
		<td align="center">6</td>
		<td align="center">4</td>
		<td align="center">4</td>
	</tr>
	<tr>
		<td><i>== All sekisui ==</i></td>
		<td align="center">-</td>
		<td align="center">6</td>
		<td align="center">2</td>
		<td align="center">2</td>
	</tr>
	<tr>
		<th colspan="5" align="right"><span class="comment"><i>* in month</i></span></th>
	</tr>
</table><br />
<div align="right">
	<a href="javascript:window.close()"><img src="../../_images/icon/close.gif" alt="Close pop-up"></a>
</div>
</body>
</html>