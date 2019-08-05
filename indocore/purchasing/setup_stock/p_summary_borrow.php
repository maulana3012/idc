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
$doc_sql = "SELECT out_code FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_borrow using(out_idx) WHERE bor_idx IN ($_code)";

$sql = "
SELECT
 a.it_code,
 a.it_model_no,
 CASE 
	WHEN bor_from_wh=1 then 'IDC'
	WHEN bor_from_wh=2 then 'DNR'
 END AS location,
 CASE 
	WHEN bor_from_type=1 then 'VAT'
	WHEN bor_from_type=2 then 'NON'
 END AS from_type,
 CASE 
	WHEN bor_to_type=1 then 'VAT'
	WHEN bor_to_type=2 then 'NON'
 END AS to_type,
 SUM(c.bor_qty) AS qty
FROM
 ".ZKP_SQL."_tb_outgoing AS b
 JOIN ".ZKP_SQL."_tb_borrow AS c USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE bor_idx IN ($_code)
GROUP BY a.it_code, a.it_model_no, c.bor_from_wh, c.bor_from_type, c.bor_to_type
ORDER BY it_code";

$result =& query($doc_sql);
while($column =& fetchRowAssoc($result)) {
	$doc_name[] = $column["out_code"];
}
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
		<td style="font-size:15px;font-weight:bold">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DO BORROW SUMMARY</td>
	</tr>
	<tr>
		<td><i class="comment"><b>Delivery No :</b> <?php echo implode(', ', $doc_name);  ?></i></td>
	</tr>
</table><br />
<table width="100%" class="table_nn" cellspacing="1">
	<tr height="30">
		<th width="5%" rowspan="2">No</th>
		<th width="10%" rowspan="2">CODE</th>
		<th width="20%" rowspan="2">ITEM NO</th>
		<th width="8%" rowspan="2">LOC</th>
		<th width="16%" colspan="2">HISTORY</th>
		<th width="12%" rowspan="2">QTY</th>
	</tr>
	<tr>
		<th>Borrow<br />From</th>
		<th>Move<br />To</th>
	</tr>
<?php
$i		= 1;
$total	= 0;
$result =& query($sql);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td align="center"><?php echo $i++ ?></td>
		<td><b><?php echo $column['it_code'] ?></b></td>
		<td><?php echo $column['it_model_no'] ?></td>
		<td align="center"><?php echo $column['location'] ?></td>
		<td align="center"><?php echo $column['from_type'] ?></td>
		<td align="center"><?php echo $column['to_type'] ?></td>
		<td align="right"><?php echo $column['qty'] ?></td>
	</tr>
<?php
	$total += $column['qty'];
}
?>
	<tr>
		<th colspan="6" align="right">TOTAL</th>
		<th align="right"><input type="text" name="_total" class="fmtn" style="width:100%" value="<?php echo number_format($total,2) ?>" readonly></th>
	</tr>
</table>
<p align="right"><button name="btnClose" class="input_sky" onclick="window.close()">CLOSE</button></p>
</body>
</html>