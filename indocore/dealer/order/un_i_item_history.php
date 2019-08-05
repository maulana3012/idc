<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $Id: i_item_history.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//GLOBAL
$_it_code	= urldecode($_GET['_it_code']);
$_it_desc	= urldecode($_GET['_it_desc']);
$_cus_code	= urldecode($_GET['_cus_code']);

//PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_apotik_item_log WHERE cus_code = '$_cus_code' AND it_code = '$_it_code' ORDER BY ilog_idx DESC";
isZKError($result =& query($sql)) ? $M->printMessage($result) : true;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr" />
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
</script>
<title>History</title>
</head>
<body>
Item : <?php echo $_it_code . "($_it_desc)";?><br/>
<table width="100%" class="table_ly_01">
	<tr>
		<th width="11%">DATE</th>
		<th width="11%">Ref/ CODE</th>
		<th width="6%">JK</th>
		<th width="6%">JO</th>
		<th width="6%">RTN</th>
		<th width="6%">SALES</th>
		<th width="12%">Ret/Price</th>
		<th>REMARK</th>

	</tr>
<?php
	while ($column = fetchRowAssoc($result)) {
?>
	<tr>
		<td><?php echo $column['ilog_date'];?></td>
		<td><?php echo $column['ilog_ref_code'];?></td>
		<td align="right"><?php echo $column['ilog_jk'];?></td>
		<td align="right"><?php echo $column['ilog_jo'];?></td>
		<td align="right"><?php echo $column['ilog_return'];?></td>
		<td align="right"><?php echo $column['ilog_sales'];?></td>
		<td align="right">Rp.<?php echo $column['ilog_retailer_price'];?></td>
		<td><?php echo $column['ilog_remark'];?></td>
	</tr>
<?php
}
?>
</table>
</body>
</html>