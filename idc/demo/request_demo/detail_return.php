<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR .$currentDept. "/request_demo/index.php");

//GLOBAL
$left_loc = "daily_request_demo_by_reference.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . $currentDept . '/request_demo/index.php');
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS FORM
require_once APP_DIR . "_include/demo/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
//request
$sql		= "SELECT * FROM ".ZKP_SQL."_tb_incoming_marketing WHERE inc_idx = $_code";
$sql_item	= "SELECT * FROM ".ZKP_SQL."_tb_incoming_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE inc_idx = $_code";
$sql_ed		= "SELECT it_code,it_model_no,ided_expired_date,ided_qty FROM ".ZKP_SQL."_tb_incoming_ed_demo JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE inc_idx = $_code";

$result 	= query($sql);
$res_item	= query($sql_item);
$res_ed		= query($sql_ed);

$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . $currentDept . '/request_demo/daily_request_demo_by_reference.php');
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
<script language="javascript" type="text/javascript">
function reCalculationTotal() {
	var count	 = window.itemWHPosition.rows.length;
	var sumOfQty = 0;
	
	for (var i=0; i<count; i++) {
		var oRow = window.itemWHPosition.rows(i);
		sumOfQty = sumOfQty + parseFloat(oRow.cells(3).innerText);
	}

	window.document.all.totalWhQty.value = numFormatval(sumOfQty + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="reCalculationTotal()">
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL RETURN</h3>
<strong class="info">RETURN INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">RETURN NO</th>
		<td width="20%"><span class="bar"><?php echo $column["inm_doc_no"] ?></span></td>
		<th width="15%">RETURN BY</th>
		<td width="20%"><?php echo $column["inm_issued_by"] ?></td>
		<th width="15%">RETURN DATE</th>
		<td><?php echo date('d-M-y', strtotime($column["inm_doc_date"])) ?></td>
	</tr>
</table><br />
<strong class="info">[<font color="#315c87">WAREHOUSE</font>] ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr height="35px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php
while($items =& fetchRowAssoc($res_item)) {
	if($items['init_demo_qty'] > 0) {
?>
		<tr id="<?php echo trim($items['it_code'])?>">
			<td><?php echo $items['it_code']?></td>
			<td><?php echo $items['it_model_no']?></td>
			<td><?php echo $items['it_desc']?></td>
			<td align="right"><?php echo number_format($items['init_demo_qty'],2)?></td>
			<td></td>
		</tr>
<?php } } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%">&nbsp;</th>
	</tr>
</table><br />
<?php if(numQueryRows($res_ed) > 0) { ?>
<strong class="info">[<font color="#315c87">DEMO UNIT</font>] DETAIL ITEM PER E/D</strong>
<table width="45%" class="table_l">
	<tr height="35px">
		<th width="15%">CODE</th>
		<th>ITEM NO</th>
		<th width="25%">E/D</th>
		<th width="15%">QTY</th>
	</tr>
<?php while($items =& fetchRow($res_ed)) { ?>
	<tr>
		<td><?php echo $items[0] ?></td>
		<td><?php echo $items[1]?></td>
		<td align="center"><?php echo date('M-Y', strtotime($items[2]))?></td>
		<td align="right"><?php echo number_format($items[3],2)?></td>
	</tr>
<?php } ?>
</table>
<?php } ?>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><span class="comment"><i>
			Warehouse confirmation by : <?php echo $column['inm_cfm_wh_delivery_by_account'].date(', j-M-Y g:i:s', strtotime($column['inm_cfm_wh_delivery_timestamp']))?><br />
			<?php if($column['inm_received_date'] != '') { ?>
			Marketing confirmation by : <?php echo $column['inm_received_by_account'].date(', j-M-Y g:i:s', strtotime($column['inm_received_date']))?><br />
			<?php } ?>
		</i></span></td>
		<td align="right">
			<button name='btnList' class='input_btn' style='width:130px;' onClick="window.location.href='daily_request_demo_by_reference.php'"><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<?php if($column["inm_cfm_marketing_timestamp"]=='') { ?>
<form name="frmCfmReceived" method="post">
<input type="hidden" name="p_mode" value="cfm_received">
<input type="hidden" name="_code" value="<?php echo $column['inm_idx']?>">
<input type="hidden" name="_doc_ref" value="<?php echo $column['inm_doc_no']?>">
<input type="hidden" name="_wh_idx" value="<?php echo $column['inc_idx']?>">
<input type="hidden" name="_type" value="2">
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>CONFIRMATION</strong></th>
    </tr>
</table>
<table width="100%" cellpadding="0">
    <tr>
    	<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
        <td colspan="2"><strong>Marketing Confirmation</strong></td>
    </tr>
    <tr height="20px" valign="bottom">
    	<td></td>
		<td>Received By &nbsp; : &nbsp; <input type="text" name="_received_by" class="req" size="15" maxlength="32" value="<?php echo $S->getValue("ma_account"); ?>"></td>
		<td>Received Date &nbsp; : &nbsp; <input type="text" name="_received_date" class="reqd" size="15" value="<?php echo date('d-M-Y') ?>"></td>
		<td align="right">
			<button name='btnConfirm' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
		</td>
    </tr>
	<tr>
		<td></td>
		<td colspan="3"><span class="comment" style="color:red"><i>* Make sure you accept qty as much as the qty in the list. Pay attention for the E/D qty too.</i></span></td>
	</tr>
</table><br /><br />
</form>
<script language="javascript" type="text/javascript">
	window.document.frmCfmReceived.btnConfirm.onclick = function() {
		if(confirm("Are you sure to confirm incoming stock to marketing staff?")) {
			if(verify(window.document.frmCfmReceived)){
				window.document.frmCfmReceived.submit();
			}
		}
	}
</script>
<?php } else if($S->getValue("ma_authority") & 256) { ?>
<form name="frmUnCfmReceived" method="post">
<input type="hidden" name="p_mode" value="uncfm_received">
<input type="hidden" name="_code" value="<?php echo $column['inm_idx']?>">
<input type="hidden" name="_doc_ref" value="<?php echo $column['inm_doc_no']?>">
<input type="hidden" name="_wh_idx" value="<?php echo $column['inc_idx']?>">
<input type="hidden" name="_type" value="2">
<table width="100%" cellpadding="0">
    <tr>
    	<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
        <td colspan="2"><strong>Unconfirm Return</strong></td>
		<td align="right">
			<button name='btnUnConfirm' class='input_btn' style='width:120px;'> <img src="../../_images/icon/clean.gif" align="middle"> &nbsp; Unconfirm</button>
		</td>
    </tr>
	<tr>
		<td></td>
		<td colspan="2"><span class="comment" style="color:red"><i>* Be carefull when you unconfirm this document! Make sure the demo unit in this document has not been deleted.</i></span></td>
	</tr>
</table><br /><br />
</form>
<script language="javascript" type="text/javascript">
	window.document.frmUnCfmReceived.btnUnConfirm.onclick = function() {
		if(confirm("Are you sure to unconfirm incoming stock to marketing staff?")) {
			if(verify(window.document.frmUnCfmReceived)){
				window.document.frmUnCfmReceived.submit();
			}
		}
	}
</script>
<?php } else { ?>
<table width="100%" class="table_box">
	<tr>
		<td width="10%" valign="top"><img src="../../_images/icon/warning.gif"></td>
		<td>
			<span>
			This is a locked document. To modify document, please ask to the Administrator.<br /><br />
			</span>
		</td>
	</tr>
</table>
<?php } ?>
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