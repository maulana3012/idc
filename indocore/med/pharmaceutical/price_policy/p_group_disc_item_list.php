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


$sqlagotik = "
SELECT ag,*, to_char(ag_date_from, 'dd-Mon-yyyy') AS date_from, to_char(ag_date_to, 'dd-Mon-yyyy') AS date_to, cug.cug_code, cug.cug_name
FROM ".ZKP_SQL."_tb_group_policy AS ag JOIN ".ZKP_SQL."_tb_customer_group AS cug USING(cug_code) WHERE ag.ag_idx=$_idx";

isZKError($res =& query($sqlagotik)) ? $M->printMessage($res) : 0;
$column = fetchRowAssoc($res);

$sql = "
SELECT
it. it_code, it.it_model_no, it.it_desc, ".ZKP_SQL."_getUserPrice(it.it_code, CURRENT_DATE) AS user_price
FROM ".ZKP_SQL."_tb_group_price JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ag_idx=" . $_idx . " ORDER BY it.it_code";


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
<title>GROUP DISCOUNT ITEM LIST</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<h4>GROUP DISCOUNT ITEM LIST</h4>
<table width="100%" class="table_box">
	<tr>
		<th width="20%">GROUP NAME</th><td width="60%"><?php echo "[".$column['cug_code']."] ".$column['cug_name']?></td>
		<th width="20%">BASIC DISC%</th><td align="right"><?php echo $column['ag_basic_disc_pct']?></td>
	</tr>
	<tr>
		<th>PERIOD</th><td>FROM: <?php echo $column['date_from']?> ~ TO : <?php echo $column['date_to']?></td>
		<th>ADD/ DISC%</th><td align="right"><?php echo $column['ag_disc_pct']?></td>
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
	$agotik_price = round(($user_price -($user_price * $column['ag_basic_disc_pct']/100))/1.1);
	$disc_price = round(($user_price -($user_price *($column['ag_basic_disc_pct']+ $column['ag_disc_pct'])/100))/1.1);
?>
	<tr>
		<td><?php echo trim($item['it_code'])?></td>
		<td><?php echo cut_string($item['it_model_no'], 14)?></td>
		<td><?php echo cut_string($item['it_desc'], 65)?></td>
		<td align="right"><?php echo number_format((double)$user_price)?></td>
		<td align="right"><?php echo number_format((double)$agotik_price)?></td>
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