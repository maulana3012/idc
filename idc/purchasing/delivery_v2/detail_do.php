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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = 'daily_delivery_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
if (!isset($_GET['_source']) || $_GET['_source'] == '') goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
else {
	$_code = urldecode($_GET['_code']);
	$_source = $_GET['_source'];
}

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_do_detail.php";
require_once "detail_do_m.php";

//DEFAULT PROCESS =======================================================================================
$col[0] = getDOItem($_source, $_code, 'info'); 
$col[1] = getDOItem($_source, $_code, 'cus_item', array('out_doc_type'=> trim($col[0]['out_doc_type']), 'out_doc_ref'=> trim($col[0]['out_doc_ref'])));
$col[2] = getDOItem($_source, $_code, 'book_item', array('book_doc_ref'=> trim($col[0]['book_doc_ref'])));
$col[3] = getDOItem($_source, $_code, 'out_item');
$col[4] = getDOItem($_source, $_code, 'ed_item');
$col[5] = getDOItem($_source, $_code, 'rev_item', array('book_doc_ref'=> trim($col[0]['book_doc_ref'])));
/*
echo '<pre>';
var_dump(fetchRowAssoc($col[1]), $col[1]);
echo '</pre>';
exit;
*/
if(count($col[0]) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($col[0]['book_doc_type']==6 || $col[0]['book_doc_type']=='DM') {
	//goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_request.php?_code=$_code&_source=$_source");
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
<body topmargin="0" leftmargin="0">
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] OUTGOING CONFIRMED DETAIL</h4>
	<table width="100%" class="table_box">
		<tr>
			<td colspan="5"><strong class="info">DO INFORMATION</strong></td>
			<td colspan="2" align="right"><span class="comment"><i>Confirmed at: <?php echo date('d-M-Y g:i:s',strtotime($col[0]["out_cfm_timestamp"])) ?></i></span></td>
		</tr>
		<tr>
			<th width="15%">DO NO</th>
			<td width="25%" colspan="2"><b><?php echo $col[0]['out_code'] ?></b></td>
			<th width="15%">DO DATE</th>
			<td><?php echo date('d-M-Y', strtotime($col[0]['book_date'])) ?></td>
			<th width="15%">RECEIVED BY</th>
			<td><?php echo $col[0]['out_received_by'] ?></td>
		</tr>
		<tr>
			<th rowspan="3">CUSTOMER<br />SHIP TO</th>
			<th width="10%">CODE</th>
			<td colspan="6"><?php echo '[' . trim($col[0]['cus_code']) . '] ' . $col[0]['cus_full_name'] ?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="6"><?php echo $col[0]['cus_address'] ?></td>
		</tr>
	</table><br>
<?php if (!($col[0]['book_doc_type']=='6' || $col[0]['book_doc_type']=='DM')) { ?>
	<span class="bar_bl">CUSTOMER ITEM LIST</span>
	<table width="100%" class="table_l">
		<tr>
			<th width="7%">CODE</th>
			<th width="20%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="5%"></th>
			<th width="15%">REMARK</th>
		</tr>
<?php
$amount = 0;
while($items =& fetchRowAssoc($col[1])) {
?>
		<tr>
			<td><?php echo $items['it_code']?></td>
			<td><?php echo cut_string($items['it_model_no'],25)?></td>
			<td><?php echo cut_string($items['it_desc'],70)?></td>
			<td align="right"><?php echo $items['qty']?></td>
			<td></td>
			<td><?php echo $items['remark']?></td>
		</tr>
<?php $amount += $items['qty']; } ?>
	</table>
	<table width="100%" class="table_l">
		<tr>
			<th align="right">TOTAL QTY</th>
			<th width="7%" align="right"><?php echo number_format((double)$amount,2) ?></th>
			<th width="20%">&nbsp;</th>
	</table><br />
<?php } ?>
	<strong class="info">WAREHOUSE ITEM LIST</strong>
	<table width="100%" class="table_l">
		<tr>
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="2%"></th>
			<th width="13%">ITEM<br />PURPOSE</th>
			<th width="15%">REMARK</th>
		</tr>
<?php
$amount = 0;
while($items =& fetchRowAssoc($col[2])) {
?>
		<tr>
			<td><?php echo $items['it_code']?></td>
			<td><?php echo $items['it_model_no']?></td>
			<td><?php echo $items['it_desc']?></td>
			<td align="right"><?php echo $items['boit_qty']?></td>
			<td></td>
			<td><?php echo $items['it_used_for']?></td>
			<td><?php echo $items['boit_remark']?></td>
		</tr>
<?php
	$amount += $items['boit_qty'];
}
?>
	</table>
	<table width="100%" class="table_l">
		<tr>
			<th align="right">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format((double)$amount,2) ?>" readonly></th>
			<th width="29%">&nbsp;</th>
	</table><br />
	<strong class="info">SUMMARY ITEM</strong>
	<table width="100%" class="table_box" cellspacing="1">
	  <thead>
		<tr>
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">QTY</th>
		</tr>
	  </thead>
	  <tbody id="itemStockPosition">
<?php
$i=0; $amount=0;
while($items =& fetchRowAssoc($col[3])) {
?>
		<tr>
			<td>
				<input type="hidden" name="_it_code[]" value="<?php echo $items['it_code'] ?>">
				<input type="hidden" name="_it_ed[]" value="<?php echo $items[3] ?>">
				<?php echo $items['it_code'] ?>
			</td>
			<td><?php echo $items['it_model_no']?><input type="hidden" name="_it_model_no[]" value="<?php echo $items['it_model_no'] ?>"></td>
			<td><?php echo $items['it_desc']?></td>
			<td><input type="text" name="_it_booked_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format($items['otit_qty'],2)?>" readonly></td>
		</tr>
<?php
	$amount += $items['otit_qty'];
	$i++; 
}
?>
	  </tbody>
		<tr>
			<th align="right" colspan="3">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format($amount,2) ?>" readonly></th>
		</tr>
	</table><br />
	<strong class="info">DETAIL ITEM PER E/D</strong>
	<table width="50%" class="table_l">
	  <thead>
		<tr height="25px">
			<th width="15%">CODE</th>
			<th>ITEM NO</th>
			<th width="10%">SOURCE</th>
			<th width="20%">E/D</th>
			<th width="15%">QTY</th>
		</tr>
	  </thead>
	  <tbody id="EDPosition">
<?php while($items =& fetchRowAssoc($col[4])) { ?>
		<tr>
			<td><?php echo $items['it_code']?></td>
			<td><?php echo $items['it_model_no']?></td>
			<td align="center"><?php echo $cboFilter[3]['warehouse'][ZKP_FUNCTION][$items['oted_wh_location']-1][1] ?></td>
			<td><?php echo $items['exp_date'] ?></td>
			<td align="right"><?php echo number_format($items['oted_qty'],2) ?></td>
		</tr>
<?php } ?>
	  </tbody>
	</table><br />
	<strong class="info">OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">CONFIRMED BY</th>
			<td width="30%"><?php echo $col[0]["out_cfm_by_account"] ?></td>
			<th width="15%">CONFIRMED DATE</th>
			<td><?php echo date('d-M-Y',strtotime($col[0]["out_cfm_date"])) ?></td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="4" readonly><?php echo $col[0]["out_remark"] ?></textarea></td>
		</tr>
	</table><br />
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	window.document.all.btnList.onclick = function() {
		window.location.href = "daily_delivery_by_group.php?cboSource=<?php echo $col[0]["out_doc_type"]?>";
	}
</script>
<?php if($col[0]['book_is_revised'] == 't') {
	include_once "confirm_do_revised.php";
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