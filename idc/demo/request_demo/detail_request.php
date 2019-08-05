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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "daily_request_demo_by_reference.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS FORM
require_once APP_DIR . "_include/demo/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
//request
$sql = "
SELECT *, (SELECT out_idx FROM ".ZKP_SQL."_tb_outgoing_v2 WHERE out_doc_ref='$_code' AND out_doc_type='DM') AS out_idx
FROM ".ZKP_SQL."_tb_request WHERE req_code = '$_code'";

$result =& query($sql);
$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_request_demo_by_reference.php");
} else if($column['req_cfm_wh_delivery_by_account'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".urlencode($column['req_code']));
}

//[WAREHOUSE] request item
$sql_item = "
SELECT
  it_code,			--0
  it_model_no,		--1
  it_desc,			--2
  rqit_qty,			--3
  rqit_remark,		--4
  rqit_type			--5
FROM
  ".ZKP_SQL."_tb_request_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE req_code = '$_code'
ORDER BY it_code";
$whitem_res	=& query($sql_item);

$sql_ed	= "
SELECT it_code,it_model_no,oted_expired_date,oted_qty
FROM ".ZKP_SQL."_tb_outgoing_stock_ed JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE out_idx = {$column["out_idx"]}";
$res_ed	=& query($sql_ed);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
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
		sumOfQty = sumOfQty + parseFloat(removecomma(oRow.cells(3).innerText));
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL REQUEST</h3>
<strong class="info">REQUEST INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REQUEST NO</th>
		<td width="20%"><span class="bar"><?php echo $column["req_code"] ?></span></td>
		<th width="15%">ISSUED BY</th>
		<td width="20%"><?php echo $column["req_issued_by"] ?></td>
		<th width="15%">ISSUED DATE</th>
		<td><?php echo date('d-M-y', strtotime($column["req_issued_date"])) ?></td>
	</tr>
</table><br />
<strong class="info">[<font color="#315c87">WAREHOUSE</font>] ITEM LIST</strong>  &nbsp; <i>( <a href="revise_request_2.php?_code=<?php echo $_code ?>">Revised item</a> )</i>
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
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format($items[3],2)?></td>
			<td><?php echo $items[4]?></td>
		</tr>
<?php } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="10%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%">&nbsp;</th>
	</tr>
</table><br />
<?php if(numQueryRows($res_ed) > 0) { ?>
<strong class="info">[<font color="#315c87">DEMO UNIT</font>] DETAIL ITEM PER E/D</strong>
<table width="45%" class="table_l">
	<tr height="25px">
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
</table><br />
<?php } ?>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" rows="5" style="width:100%" readonly><?php echo $column["req_remark"] ?></textarea></td>
	</tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><span class="comment"><i>
			Warehouse confirmation by : <?php echo $column['req_cfm_wh_delivery_by_account'].date(', j-M-Y g:i:s', strtotime($column['req_cfm_wh_delivery_timestamp']))?><br />
			<?php if($column['req_cfm_marketing_timestamp'] != '') { ?>
			Marketing confirmation by : <?php echo $column['req_cfm_marketing_by_account'].date(', j-M-Y g:i:s', strtotime($column['req_cfm_marketing_timestamp']))?><br />
			<?php } ?>
		</i></span></td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['req_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/demo/pdf/download_pdf.php?_code=<?php echo trim($_code)."&_date=".date("Ym", strtotime($column['req_issued_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = 'daily_request_demo_by_reference.php" ?>';
	}
</script>
<?php if($column["req_cfm_marketing_timestamp"]=='') { ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>CONFIRMATION</strong></th>
    </tr>
</table>
<form name="frmCfmReceived" method="post">
<input type="hidden" name="p_mode" value="cfm_received">
<input type="hidden" name="_code" value="<?php echo $column['req_code']?>">
<input type="hidden" name="_wh_idx" value="<?php echo $column['out_idx']?>">
<input type="hidden" name="_type" value="1">
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
<input type="hidden" name="_code" value="<?php echo $column['req_code']?>">
<input type="hidden" name="_wh_idx" value="<?php echo $column['out_idx']?>">
<input type="hidden" name="_type" value="1">
<table width="100%" cellpadding="0">
    <tr>
    	<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
        <td colspan="2"><strong>UnConfirm Request</strong></td>
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