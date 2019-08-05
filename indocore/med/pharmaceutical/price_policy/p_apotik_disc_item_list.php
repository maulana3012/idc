<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 25-May, 2007 16:16:33
* @author    : daesung kim
*/
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

$_idx = $_GET['_idx'];


$sqlApotik = "
SELECT ap,*, to_char(ap_date_from, 'dd-Mon-yyyy') AS date_from, to_char(ap_date_to, 'dd-Mon-yyyy') AS date_to, cus.cus_full_name, cus.cus_address FROM ".ZKP_SQL."_tb_apotik_policy AS ap JOIN ".ZKP_SQL."_tb_customer AS cus ON ap.cus_code = cus.cus_code WHERE ap.ap_idx=$_idx";

isZKError($res =& query($sqlApotik)) ? $M->printMessage($res) : 0;
$column = fetchRowAssoc($res);

$sql = "
SELECT
it.it_code, it.it_model_no, it.it_desc, ".ZKP_SQL."_getUserPrice(it.it_code, CURRENT_DATE) as user_price
FROM ".ZKP_SQL."_tb_apotik_price JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ap_idx=" . $_idx . " ORDER BY it.it_code";

isZKError($result =& query($sql)) ? $M->printMessage($result) : 0;

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sql, 8);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$_GET = delArrayByKey($_GET, 'curpage');
$oPage->strGet = getQueryString();

if(isZKError($result =& query($oPage->getListQuery()))) {
	$M->goErrorPage($result, "javascript:window.close();");
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>ITEM LIST</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<h4>APOTIK DISCOUNT ITEM LIST</h4>
<table width="100%" class="table_box">
	<tr>
		<th width="20%">APOTIK NAME</th><td width="60%"><?php echo $column['cus_full_name']?></td>
		<th width="20%">BASIC DISC%</th><td align="right"><?php echo $column['ap_basic_disc_pct']?></td>
	</tr>
	<tr>
		<th>PERIOD</th><td>FROM: <?php echo $column['date_from']?> ~ TO : <?php echo $column['date_to']?></td>
		<th>ADD/ DISC%</th><td align="right"><?php echo $column['ap_disc_pct']?></td>
	</tr>
</table><br>

Total Record : <?php echo number_format((double)$numRow) ?>
<table width="100%" class="table_l">
	<tr>
		<th width="8%">CODE</th>
		<th width="12%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="15%">USER PRICE<br>(CURRENT @PRICE)</th>
		<th width="10%">A/ PRICE<br/>(W/O VAT)</th>
		<th width="10%">DISC PRICE<br/>(W/O VAT)</th>
	</tr>
<?php
while($item = fetchRowAssoc($result)) {
	$user_price = $item['user_price'];
	$apotik_price = round(($user_price -($user_price * $column['ap_basic_disc_pct']/100))/1.1);
	$disc_price = round(($user_price -($user_price *($column['ap_basic_disc_pct']+ $column['ap_disc_pct'])/100))/1.1);
?>
	<tr>
		<td><?php echo trim($item['it_code'])?></td>
		<td><?php echo cut_string($item['it_model_no'], 14)?></td>
		<td><?php echo cut_string($item['it_desc'], 65)?></td>
		<td align="right"><?php echo number_format((double)$user_price)?></td>
		<td align="right"><?php echo number_format((double)$apotik_price)?></td>
		<td align="right"><?php echo number_format((double)$disc_price)?></td>
	</tr>
<?php
	} //while
?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
    <td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
</body>
</html>