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
$left_loc	= "daily_request_demo_by_reference.php";
$_code		= urldecode($_GET['_code']);

//PROCESS FORM
require_once APP_DIR . "_include/request_demo/tpl_process_form.php";

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	 = "SELECT * FROM ".ZKP_SQL."_tb_using_demo WHERE use_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['use_cfm_marketing_timestamp'] != '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_request.php?_code=".urlencode($column['use_code']));
}

$sql_item = "
SELECT
  it_code,
  it_model_no,
  it_desc,
  CASE 
	WHEN usit_returnable is true THEN 'Yes'
	WHEN usit_returnable is false THEN 'No'
  END AS it_returnable,
  CASE 
	WHEN usit_returnable is true THEN 0
	WHEN usit_returnable is false THEN 1
  END AS it_return,
  usit_qty,
  usit_remark
FROM ".ZKP_SQL."_tb_using_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '$_code' ORDER BY it_code";
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
function fillCustomer(target) {

	keyword = window.document.frmInsert._cus_to.value;

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'../../_include/request_demo/p_list_cus_code.php?_dept=<?php echo $department ?>&_check_code='+ keyword,
		'',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 600) / 2;
	wSearchItem = window.open(
		'../../_include/request_demo/p_list_item.php','wSearchItem',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
function createItem() {

	var f2		 = wSearchItem.document.frmCreateItem;
	var oTR		 = window.document.createElement("TR");
	var oTD		 = new Array();
	var oTextbox = new Array();

	//check same item in WAREHOUSE list
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.rowPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)) {
			alert(
				"Please check item list"+
				"\nItem ["+ trim(f2.elements[0].value) +"] " + f2.elements[1].value +  " already exist!");
			for (var i=0; i<8; i++) {f2.elements[i].value = '';}
			return false;
		}
	}

	//Print cell for WH
	for (var i=0; i<7; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i]		 = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // it_code
				oTD[i].innerText			= trim(f2.elements[0].value);
				oTextbox[i].type			= "hidden";
				oTextbox[i].name			= "_it_code[]";
				oTextbox[i].value			= f2.elements[0].value;
				break;

			case 1: // it_model_no
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_it_model_no[]";
				oTextbox[i].value			= f2.elements[1].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 2: //  it_desc
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_it_desc[]";
				oTextbox[i].value			= f2.elements[2].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 3: //  it_qty
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "reqn";
				oTextbox[i].name			= "_it_qty[]";
				oTextbox[i].value			= numFormatval(f2.elements[3].value+'',2);
				oTextbox[i].onblur			= function() {updateAmount();}
				oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
				break;

			case 4: //  it_returned
				oTD[i].align				= "center";
				if(f2.elements[6].value == "0") {
					oTD[i].innerText		= 'Yes';
				} else if(f2.elements[6].value == "1") {
					oTD[i].innerText		= 'No';
				}
				oTextbox[i].type			= "hidden";
				oTextbox[i].name			= "_it_returnable[]";
				oTextbox[i].value			= f2.elements[6].value;
				break;

			case 5: //  it_remark
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_it_remark[]";
				oTextbox[i].value			= f2.elements[7].value;
				break;

			case 6: // DELETE
				oTD[i].innerHTML			= "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value)+ "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align				= "center";
				break;
		}

		if (i!=6) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);
	}
	for (var i=0; i<8; i++) {f2.elements[i].value = '';}
	window.rowPosition.appendChild(oTR);
	updateAmount();

}

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
	var numInputWH	= 6;
	var idx_qty		= 11;		/////
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE REQUEST</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column['use_code'] ?>">
<input type='hidden' name='_revision_time' value="<?php echo $column['use_revesion_time'] ?>">
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="4"><strong class="info">REQUEST INFORMATION</strong></td>
		<td colspan="3" align="right"><i>Last updated by : <?php echo $column['use_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['use_lastupdated_timestamp']))?></i></td>
	</tr>
	<tr>
		<th width="15%">REQUEST NO</th>
		<td width="35%" colspan="2"><strong><?php echo $column['use_code'] ?></strong></td>
		<th width="15%">REQUEST BY</th>
		<td width="20%"><input name="_request_by" type="text" class="req" size="15" maxlength="32" value="<?php echo $column['use_request_by'] ?>"></td>
		<th width="15%">REQUEST DATE</th>
		<td><input name="_request_date" type="text" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column['use_request_date'])) ?>"></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER/<br />EVENT</th>
		<th width="12%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td><input name="_cus_to" type="text" class="req" size="10" maxlength="7" value="<?php echo $column['use_cus_to'] ?>"></td>
		<th>NAME</th>
		<td colspan="3"><input type="text" name="_cus_name" class="req" style="width:100%" maxlength="128" value="<?php echo $column['use_cus_name'] ?>"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="5"><input type="text" name="_cus_address" class="fmt"  style="width:100%" maxlength="255" value="<?php echo $column['use_cus_address'] ?>"></td>
	</tr>
</table><br />
<strong class="info">ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr height="40px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="7%">RETURNABLE<br />Yes | No</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php while($items =& fetchRow($res_item)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><input type="text" name="_it_model_no[]" class="fmt" style="width:100%" value="<?php echo $items[1]?>"></td>
			<td><input type="text" name="_it_desc[]" class="fmt" style="width:100%" value="<?php echo $items[2]?>"></td>
			<td><input type="text" name="_it_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format((double)$items[5],2)?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
			<td align="center"><?php echo $items[3]?><input type="hidden" name="_it_returnable[]" value="<?php echo $items[4]?>"></td>
			<td><input type="text" name="_it_remark[]" class="fmt" style="wifth:100%" value="<?php echo $items[6]?>"></td>
			<td align="center"><a href="javascript:deleteItem('<?php echo trim($items[0]) ?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
		</tr>
<?php } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="3" width="20%">&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th>SIGN BY</th>
		<td><input type="text" name="_sign_by" class="req" size="15" maxlength="32" value="<?php echo $column["use_signature_by"] ?>"></td>
	</tr>
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" rows="5" style="width:100%"><?php echo $column["use_remark"] ?></textarea></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete request"> &nbsp; Delete request</button>
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['use_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update request"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete request demo unit?")) {
			oForm.p_mode.value = 'delete_request';
			oForm.submit();
		}
	}

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/request_demo/pdf/download_pdf.php?_code=<?php echo trim($_code)."&_dept=".$currentDept."&_date=".date("Ym", strtotime($column['use_request_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if (window.rowPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}

		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_request';
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