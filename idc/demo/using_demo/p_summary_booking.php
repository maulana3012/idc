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

//CHECK PARAMETER
if(!isset($_GET['_code']) || $_GET['_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code		= $_GET['_code'];
$_arr_code	= spliti(',',$_code);
foreach($_arr_code as $key => $val) { $_arr_code[$key] = "'".trim($val)."'"; }
$_arr_code	= implode(', ', $_arr_code);

//DEFAULT PROCESS
$sql = "
SELECT
 a.it_code,
 a.it_model_no,
 substr(a.it_desc,1,30) AS it_desc,
 SUM(c.usit_qty) AS qty
FROM
 ".ZKP_SQL."_tb_using_demo AS b
 JOIN ".ZKP_SQL."_tb_using_demo_item AS c USING(use_code)
 JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE use_code IN ($_arr_code)
GROUP BY a.it_code, a.it_model_no, a.it_desc
ORDER BY it_code";
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body style="margin:10pt">
<table class="table_layout" width="100%">
	<tr height="35px">
		<td style="font-size:15px;font-weight:bold">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] BOOKING DEMO SUMMARY</td>
	</tr>
	<tr>
		<td><i class="comment"><b>Request No :</b> <?php echo $_code ?></i></td>
	</tr>
</table><br />
<table width="100%" class="table_nn">
	<tr height="30">
		<th width="8%">No</th>
		<th width="15%">CODE</th>
		<th width="20%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="12%">QTY</th>
	</tr>
<?php
$i		= 1;
$total	= 0;
$result =& query($sql);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td align="center"><?php echo $i++ ?></td>
		<td><?php echo $column['it_code'] ?></td>
		<td><?php echo $column['it_model_no'] ?></td>
		<td><?php echo $column['it_desc'] ?></td>
		<td align="right"><?php echo $column['qty'] ?></td>
	</tr>
<?php
	$total += $column['qty'];
}
?>
	<tr>
		<th colspan="4" align="right">TOTAL</th>
		<th align="right"><input type="text" name="_total" class="fmtn" style="width:100%" value="<?php echo number_format($total,2) ?>" readonly></th>
	</tr>
</table>
<p align="right"><button name="btnClose" class="input_sky" onclick="window.close()"><img src="../../_images/icon/delete_2.gif"> &nbsp; Close</button></p>
</body>
</html>