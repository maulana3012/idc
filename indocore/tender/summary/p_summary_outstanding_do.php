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

$_code	  = $_GET['_code'];
$doc_name = array();

//DEFAULT PROCESS
$doc_sql = "SELECT book_code FROM ".ZKP_SQL."_tb_booking WHERE book_idx IN ($_code)";

$sql = "
SELECT
 a.it_code,
 a.it_model_no,
 substr(a.it_desc,1,30) AS it_desc,
 SUM(c.boit_qty) AS qty
FROM
 ".ZKP_SQL."_tb_booking AS b
 JOIN ".ZKP_SQL."_tb_booking_item AS c USING(book_idx)
 JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE book_idx IN ($_code)
GROUP BY a.it_code, a.it_model_no, a.it_desc
ORDER BY it_code";

$result =& query($doc_sql);
while($column =& fetchRowAssoc($result)) {
	$doc_name[] = $column["book_code"];
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body style="margin:10pt">
<table class="table_layout" width="100%">
	<tr height="35px">
		<td style="font-size:15px;font-weight:bold">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DO OUTSTANDING SUMMARY</td>
	</tr>
	<tr>
		<td><i class="comment"><b>Delivery No :</b> <?php echo implode(', ', $doc_name);  ?></i></td>
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
		<th align="right"><input type="text" name="_total" class="fmtn" style="width:100%" value="<?php echo number_format((double)$total,2) ?>" readonly></th>
	</tr>
</table>
<p align="right"><button name="btnClose" class="input_sky" onclick="window.close()"><img src="../../_images/icon/delete_2.gif"> &nbsp; Close</button></p>
</body>
</html>