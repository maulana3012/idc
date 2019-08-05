<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "summary_arrival_by_supplier.php";
if (!isset($_GET['_inlc_idx']) || $_GET['_inlc_idx'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_pl_by_supplier.php");
} else {
	$version	= urldecode($_GET['_ver']);
	$_inlc_idx	= $_GET['_inlc_idx'];
}

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_packing_list.php";
require_once "detail_incoming_m.php";

//DEFAULT PROCESS
$col[0] = getPLDetail($version, 'Local', '', $_inlc_idx, 'info'); 
$col[1] = getPLDetail($version, 'Local', '', $_inlc_idx, 'item'); 
$col[2] = getPLDetail($version, 'Local', '', $_inlc_idx, 'item_ed'); 

// raw data
$rd 	= array();
$rdIdx  = 0;
$cache  = array("","");
$group0 = array();
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

while($col_ed =& fetchRowAssoc($col[2])) {

	$rd[] = array(
		$col_ed['it_code'],	//0
		$col_ed['it_model_no'],	//1
		$col_ed['expired_date'],//2
		$col_ed['qty']		//3
	);

	//1st grouping
	if($cache[0] != $col_ed['it_code']) {
		$cache[0] = $col_ed['it_code'];
		$group0[$col_ed['it_code']] = array();
	}

	if($cache[1] != $col_ed['expired_date']) {
		$cache[1] = $col_ed['expired_date'];
	}
	$group0[$col_ed['it_code']][$col_ed['expired_date']] = 1;
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
<script type="text/javascript">
function reCalculateAmount() {
	var count		= window.rowPosition.rows.length;
	var sumOfQty	= 0;

	for (var i=0; i<count; i++) {
		var oRow	= window.rowPosition.rows(i);
		sumOfQty	= sumOfQty + parseFloat(parseInt(removecomma(oRow.cells(3).innerText)));
	}

	window.document.all.totalQty.value	= numFormatval(sumOfQty+'',0);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="reCalculateAmount()">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#9CBECC">
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
						<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL CONFIRMED INCOMING PL<br />
</strong>
<small class="comment">* Source by PO Local</small>
<hr><br />
<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><span class="bar_bl">PL INFORMATION</span></td>
		<td colspan="3" align="right"><i>Inputed by: <?php echo $col[0]["inlc_created_by_account"] . date(', j-M-Y g:i:s', strtotime($col[0]["inlc_created_timestamp"]))?></i></td>
	</tr>
	<tr>
		<th width="15%">PL NO</th>
		<td width="22%"><?php echo $col[0]["po_code"] ?>&nbsp; &nbsp;#<?php echo $col[0]["pl_no"] ?></td>
		<th width="7%">
			<a href="../purchasing/revise_po.php?_code=<?php echo $col[0]["po_code"] ?>" target="_blank"><img src="../../_images/icon/list_mini.gif" alt="View detail PO"></a>
		</th>
		<td width="5%"></td>
		<th width="15%">PL DATE</th>
		<td><?php echo date('j-M-Y', strtotime($col[0]["pl_date"])) ?></td>
	</tr>
	<tr>
		<th width="15%">ISSUED BY</th>
		<td colspan="3"><?php echo $col[0]["pl_issued_by"] ?></td>
		<th>DELIVERY DATE</th>
		<td><?php echo date('j-M-Y', strtotime($col[0]["pl_delivery_date"])) ?></td>
	</tr>
	<tr>
		<th width="15%">SUPPLIER</th>
		<td colspan="5"><?php echo '['.$col[0]["sp_code"].'] '.$col[0]["po_sp_name"] ?></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
</table><br />
<span class="bar_bl">CONFIRM ARRIVAL ITEMS</span>
<table width="75%" class="table_l">
	<thead>
		<tr height="35px">
			<th width="15%">CODE</th>
			<th width="17%">ITEM</th>
			<th>DESC</th>
			<th width="15%">QTY</th>
			<th width="3%"></th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php while($items =& fetchRow($col[1])) { ?>
		<tr>
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format($items[3])?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="75%" class="table_box">
	<tr>
		<th align="right">GRAND TOTAL</th>
		<th width="12%" align="right"><input type="text" name="totalQty" class="fmtn" style="width:100%" readonly></th>
		<th width="3%"></th>
	</tr>
</table><br />
<span class="bar_bl">DETAIL E/D PER ITEM</span>
<table width="50%" class="table_l">
	<tr height="35px">
		<th width="20%">ITEM CODE</th>
		<th>ITEM NAME</th>
		<th width="30%">EXPIRED DATE</th>
		<th width="15%">QTY</th>
	</tr>
<?php
foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');

	print "<tr>\n";
	cell($rd[$rdIdx][0], ' valign="top" rowspan="'.$rowSpan.'"');	//IT CODE
	cell($rd[$rdIdx][1], ' valign="top" rowspan="'.$rowSpan.'"');	//IT MODEL NO

	$total = 0;
	$print_tr_1 = 0;
	foreach($group1 as $total2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][2]);					//INVOICE DATE
		cell(number_format($rd[$rdIdx][3]), ' align="right"');	//INVOICE QTY
		print "</tr>\n";
		$total += $rd[$rdIdx][3];
		$model = $rd[$rdIdx][1];
		$rdIdx++;
	}
	print "<tr>\n";
	cell("<b>[$total1] $model</b>", ' colspan="3" align="right" valign="middle" style="color:brown; background-color:lightyellow"');
	cell(number_format($total), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
}
?>
</table><br />
<span class="bar_bl">CONFIRM INFORMATION</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">ARRIVAL DATE</th>
		<td width="40%"><?php echo date("j-M-Y", strtotime($col[0]["inlc_checked_date"])) ?></td>
		<th width="15%">CHECKED BY</th>
		<td><?php echo $col[0]["inlc_checked_by"] ?></td>
	</tr>
	<tr>
		<th>WAREHOUSE</th>
		<td colspan="3">
			<input type="radio" name="_warehouse_name" value="1" disabled <?php echo ($col[0]["inlc_warehouse"] == 1) ? 'checked' : '' ?>> INDOCORE &nbsp;
			<input type="radio" name="_warehouse_name" value="2" disabled <?php echo ($col[0]["inlc_warehouse"] == 2) ? 'checked' : '' ?>> DNR
		</td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="3" readonly><?php echo $col[0]["inlc_remark"] ?></textarea></td>
	</tr>
</table><br />
<form name='frmReconfirm' method='POST'>
<input type='hidden' name='p_mode' value="modify_pl_local">
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="center">
<?php if($S->getValue("ma_authority") & 32 && $version == 'v2') { ?>
			<button name='btnModify' onclick="modify()" class='input_btn' style='width:130px;'><img src="../../_images/icon/setting_mini.gif" align="middle" alt="Update incoming PL"> &nbsp; Modify</button>&nbsp;
<?php } ?>
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	function modify() {
		if(confirm("Are you sure to reconfirm incoming item?\n"+
					"Modify confirmed PL will be affect the previous data.")) {
			window.document.frmReconfirm.submit();
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/summary_arrival_by_supplier.php" ?>';
	}

</script>
<!--END Button-->
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