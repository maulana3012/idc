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

//parameter
$id = $_GET['_id'];
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
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] INFORMATION LIST<br />
</strong>
<hr><br />
<?php if ($id == 'stock_to_reject') { ?>
<span style="font-family:verdana;font-size:0.9em">Disabled item :</span>
<ul style="font-family:verdana;font-size:0.9em">
	<li>Item qty is 0 (null).</li>
	<li>Item has E/D. So, if you want to delete it, delete in <code style="color:#004686">list E/D Stock</code>.</li>
</ul><br />
<?php } ?>
<table width="100%" class="table_box">
	<tr>
		<td></td>
		<th width="20%">
			<a href="javascript:window.history.go(-1)"><img src="../../_images/icon/back.gif" alt="Back to invoice list"> Back</a> &nbsp; &nbsp;
			<a href="javascript:window.close()"><img src="../../_images/icon/close.gif" alt="Close pop-up"></a>
		</th>
	</tr>
</table>
</body>
</html>