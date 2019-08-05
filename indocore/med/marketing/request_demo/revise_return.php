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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "daily_return_demo_by_reference.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
	$_show_item	= isset($_GET['_show_item']) ? $_GET['_show_item'] : 'dont';
}

//PROCESS FORM
require_once APP_DIR . "_include/request_demo/tpl_process_form.php";

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	 = "SELECT * FROM ".ZKP_SQL."_tb_return_demo JOIN ".ZKP_SQL."_tb_using_demo USING(use_code) WHERE red_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['red_cfm_marketing_timestamp'] != '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_code=".urlencode($column['red_code']));
}

if($_show_item == 'all') {
	$sql_item = "
	SELECT
	  it_code,
	  it_model_no,
	  it_desc,
	  usit_qty
	FROM ".ZKP_SQL."_tb_using_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '{$column["use_code"]}' ORDER BY it_code";
} else {
	$sql_item = "
	SELECT
	  it_code,
	  it_model_no,
	  it_desc,
	  rdit_qty
	FROM ".ZKP_SQL."_tb_return_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE red_code = '$_code' ORDER BY it_code";
}

$res_item = query($sql_item);
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
//Delete Item rows collection
function deleteItem(idx) {
	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	updateAmount();
}

function updateAmount() {

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countWH		= window.rowPosition.rows.length;;
	var numInputWH	= 2;
	var idx_qty		= 7;		/////
	var sumOfQty	= 0;

	for (var i=0; i<countWH; i++) {
		var qty = parseFloat(removecomma(e((idx_qty)+i*numInputWH).value));
		sumOfQty	+= qty;
	}
	f.totalWhQty.value	  = numFormatval(sumOfQty + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="updateAmount()">
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE RETURN</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column['red_code'] ?>">
<input type='hidden' name='_use_code' value="<?php echo $column['use_code'] ?>">
<input type='hidden' name='_revision_time' value="<?php echo $column['red_revesion_time'] ?>">
<strong class="info">REQUEST INFORMATION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="15%">REQUEST NO</th>
		<td colspan="2"><strong><a href="detail_request.php?_code=<?php echo $column['use_code'] ?>" target="_blank"><?php echo $column['use_code'] ?><a/></strong></td>
		<th width="15%">REQUEST BY</th>
		<td width="20%"><?php echo $column['use_request_by'] ?></td>
		<th width="15%">REQUEST DATE</th>
		<td><?php echo date('d-M-Y', strtotime($column['use_request_date'])) ?></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER/<br />EVENT</th>
		<th width="12%">CODE</th>
		<td width="10%"><?php echo $column['use_cus_to'] ?></td>
		<th>NAME</th>
		<td colspan="3"><?php echo $column['use_cus_name'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="5"><?php echo $column['use_cus_address'] ?></td>
	</tr>
</table><br />
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="4"><strong class="info">RETURN INFORMATION</strong></td>
		<td colspan="3" align="right"><i>Last updated by : <?php echo $column['red_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['red_lastupdated_timestamp']))?></i></td>
	</tr>
	<tr>
		<th width="15%">RETURN NO</th>
		<td width="22%" colspan="2"><strong><?php echo $column['red_code'] ?></strong></td>
		<th width="15%">RETURN BY</th>
		<td width="20%"><input name="_return_by" type="text" class="req" size="15" maxlength="32" value="<?php echo $column['red_return_by'] ?>"></td>
		<th width="15%">RETURN DATE</th>
		<td><input name="_return_date" type="text" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column['red_return_date'])) ?>"></td>
	</tr>
</table><br />
<strong class="info">ITEM LIST</strong>
<table width="80%" class="table_box">
	<thead>
		<tr height="40px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="5%">DEL</th>
			<?php if($_show_item != 'all') { ?>
			<th><a href="revise_return.php?_code=<?php echo $_code ?>&_show_item=all"><img src="../../_images/icon/reload.gif" alt="Re-print all item in this request"></a></th>
			<?php } else { ?>
			<th><a href="revise_return.php?_code=<?php echo $_code ?>&_show_item=dont"><img src="../../_images/icon/reload.gif" alt="Print current item in this return"></a></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php while($items =& fetchRow($res_item)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td><input type="text" name="_it_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format((double)$items[3],2)?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
			<td align="center"><a href="javascript:deleteItem('<?php echo trim($items[0]) ?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
		</tr>
<?php } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th>&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th>SIGN BY</th>
		<td><input type="text" name="_sign_by" class="req" size="15" maxlength="32" value="<?php echo $column["red_signature_by"] ?>"></td>
	</tr>
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" rows="5" style="width:100%"><?php echo $column["red_remark"] ?></textarea></td>
	</tr>
</table>
<input type='hidden' name='_cus_to' value="<?php echo $column['use_cus_to'] ?>">
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete return request"> &nbsp; Delete return</button>
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['red_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update return request"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete request demo unit?")) {
			oForm.p_mode.value = 'delete_return';
			oForm.submit();
		}
	}

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/request_demo/pdf/download_pdf.php?_code=<?php echo trim($_code)."&_dept=".$currentDept."&_date=".date("Ym", strtotime($column['red_return_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if (window.rowPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}

		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_return';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_request_demo_by_reference.php" ?>';
	}
</script>
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