<?php
/**
http://localhost:8080/v3_test/customer_service/service/revise_registration.php?_code=S090401
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
$left_loc	= "input_registration.php";
$_code 		= $_GET['_code'];

//---------------------------------------------------------------------------------------------------- delete
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$_rev = (int) $_POST['_revision_time'];
	$_reg_date = date("Ym", strtotime($_POST['_reg_date']));

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_service_reg WHERE sg_code = '$_code'");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_registration.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/input_registration.php");
}

//---------------------------------------------------------------------------------------------------- update
if(ckperm(ZKP_UPDATE,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code				= $_POST['_code'];
	$_reg_date			= $_POST['_reg_date'];
	$_cus_to			= (isset($_POST['_cus_to'])) ? $_POST['_cus_to'] : '';
	$_cus_name			= (isset($_POST['_cus_name'])) ? $_POST['_cus_name'] : '';
	$_cus_address		= (isset($_POST['_cus_address'])) ? $_POST['_cus_address'] : '';
	$_remark			= $_POST['_remark'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time		= (int) $_POST['_revision_time'];

	//Item 
	foreach($_POST['_it_code'] as $val)					$_it_code[]				= $val;
	foreach($_POST['_it_model_no'] as $val)				$_it_model_no[] 		= $val;
	foreach($_POST['_it_sn'] as $val)					$_it_sn[]				= $val;
	foreach($_POST['_it_is_guarantee'] as $val)			$_it_is_guarantee[]		= $val;
	foreach($_POST['_it_guarantee_period'] as $val)		$_it_guarantee_period[] = $val;
	foreach($_POST['_it_cus_complain'] as $val)			$_it_cus_complain[]		= $val;
	foreach($_POST['_it_tech_analyze'] as $val)			$_it_tech_analyze[]		= $val;

	$_it_code				= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_model_no			= '$$' . implode('$$,$$', $_it_model_no) . '$$';
	$_it_sn					= '$$' . implode('$$,$$', $_it_sn) . '$$';
	$_it_is_guarantee		= implode(',', $_it_is_guarantee);
	$_it_guarantee_period	= 'DATE $$' . implode('$$,$$', $_it_guarantee_period) . '$$';
	$_it_cus_complain		= '$$' . implode('$$,$$', $_it_cus_complain) . '$$';
	$_it_tech_analyze		= '$$' . implode('$$,$$', $_it_tech_analyze) . '$$';

	$result = executeSP(
		ZKP_SQL."_reviseServiceReg",
		"$\${$_code}$\$",
		"$\${$_reg_date}$\$",
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_model_no]",
		"ARRAY[$_it_sn]",
		"ARRAY[$_it_is_guarantee]",
		"ARRAY[$_it_guarantee_period]",
		"ARRAY[$_it_cus_complain]",
		"ARRAY[$_it_tech_analyze]"
	);

	if(isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_registration.php?_code=$_code");
	}
	//SAVE PDF FILE
	//include "./pdf/generate_service_pdf.php";
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_registration.php?_code=$_code");
}

//========================================================================================= DEFAULT PROCESS
//service
$sql = "SELECT * FROM ".ZKP_SQL."_tb_service_reg WHERE sg_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

$item_sql	= "SELECT * FROM ".ZKP_SQL."_tb_service_reg_item WHERE sg_code = '$_code' ORDER BY it_code";
$item_res	= query($item_sql);
$numRow		= numQueryRows($item_res);
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
function setItemStatus() {
	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;

}

function setItemTotalTime() { }

function enabledDateStatus(idx, it_idx, val) {
	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var idx1		= 9;	///// increse when input type added
	var idx2		= 10;
	var idx3		= 12;
	var numInput	= 22;

	if(val.value == 0 && val.checked) {
		oCheck[idx1+(idx*numInput)].readOnly		= false;
		oCheck[idx1+(idx*numInput)].className		= 'reqd';
		oCheck[idx2+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx2+(idx*numInput)].className		= 'fmtd';
		oCheck[idx3+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx3+(idx*numInput)].className		= 'fmtd';
	} else if(val.value == 1 && val.checked) {	
		oCheck[idx1+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx1+(idx*numInput)].className		= 'fmtd';
		oCheck[idx2+(idx*numInput)].readOnly		= false;
		oCheck[idx2+(idx*numInput)].className		= 'reqd';
		oCheck[idx3+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx3+(idx*numInput)].className		= 'fmtd';
	} else if(val.value == 2 && val.checked) {
		oCheck[idx1+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx1+(idx*numInput)].className		= 'fmtd';
		oCheck[idx2+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx2+(idx*numInput)].className		= 'fmtd';
		oCheck[idx3+(idx*numInput)].readOnly		= false;
		oCheck[idx3+(idx*numInput)].className		= 'reqd';
	}

}

function fillCustomer(target) {
	var f		 = window.document.frmInsert;

	keyword = window.document.frmInsert._cus_to.value;

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'./p_list_cus_code.php?_check_code='+ keyword, '',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;

	wSearchItem = window.open("./p_list_item.php",'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem() {

	var f2 = wSearchItem.document.frmCreateItem;
	var oTR = window.document.createElement("TR");

	var oTD = new Array();
	var oTextbox = new Array();

	for (var i=0; i<7; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText		= f2.elements[0].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_code[]";
				oTextbox[i].value		= f2.elements[0].value;
				break;

			case 1: // MODEL NO
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "req";
				oTextbox[i].name		= "_it_model_no[]";
				oTextbox[i].value		= f2.elements[1].value;
				break;

			case 2: // SERIAL NUMBER
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "req";
				oTextbox[i].name		= "_it_sn[]";
				oTextbox[i].value		= f2.elements[5].value;
				break;

			case 3: // GUARANTEE PERIOD
				if(f2.elements[2].checked) {
					var nilaiText	= 'Yes, '+ f2.elements[3].value;
					var nilaiInput	= 1;
				} else {
					var nilaiText	= 'No';
					var nilaiInput	= 0;
				}
				oTD[i].align		= 'center';
				oTD[i].innerText	= nilaiText;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_it_is_guarantee[]";
				oTextbox[i].value	= nilaiInput;
				break;

			case 4: // CUSTOMER COMPLAIN
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_cus_complain[]";
				oTextbox[i].value		= f2.elements[6].value;
				break;

			case 5: // TECHNICAL ANALYZE
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_tech_analyze[]";
				oTextbox[i].value		= f2.elements[7].value;
				break;

			case 6: // DELETE
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + f2.elements[1].value+'||'+f2.elements[5].value + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align			= "center";
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_guarantee_period[]";
				if(f2.elements[2].checked) {
					var nilaiInput	= f2.elements[3].value;
				} else {
					var nilaiInput	= '1-Jan-1970';
				}
				oTextbox[i].value		= nilaiInput;
				break;
		}

		oTD[i].appendChild(oTextbox[i]);
		oTR.id = f2.elements[1].value+'||'+f2.elements[5].value;
		oTR.appendChild(oTD[i]);
	}

	window.rowPosition.appendChild(oTR);
	for (var i=0; i<8; i++) {f2.elements[i].value = '';}
	updateAmount();
}

function deleteItem(idx) {
	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
		}
	}
	updateAmount();
}

function updateAmount() {
	var f		= window.document.frmInsert;
	var count	= window.rowPosition.rows.length;
	f.totalItem.value	= numFormatval(count + '', 0);
}

function initPage() {
	updateAmount();
	setItemStatus();
	setItemTotalTime();
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="initPage()">
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
<h3>[<font color="#446fbe">CUSTOMER SERVICE</font>] REVISE SERVICE ITEM</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column['sg_code'] ?>">
<input type='hidden' name='_revision_time' value="<?php echo $column["sg_revesion_time"] ?>">
<input type='hidden' name='_num_item' value="<?php echo $numRow ?>">
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<td colspan="2"><span class="bar_bl">SERVICE INFORMATION</span></td>
		<td colspan="3" align="right"><i>Last updated by : <?php echo $column['sg_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['sg_lastupdated_timestamp']))." Rev:".$column['sg_revesion_time']?></i></td>
	</tr>
	<tr>
		<th width="15%">REG NO.</th>
		<td width="30%" colspan="2"><b><?php echo $column['sg_code'] ?></b></td>
		<th width="15%">RECEIVE DATE.</th>
		<td><input type="text" name="_reg_date" class="reqd" size="12" value="<?php echo date('j-M-Y', strtotime($column['sg_receive_date'])) ?>"></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER</th>
		<th width="12%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td width="15%"><input type="text" name="_cus_to" class="req" size="10" maxlength="7" value="<?php echo $column['sg_cus_to'] ?>"></td>
		<th width="12%">NAME</th>
		<td><input type="text" name="_cus_name" class="fmt" style="width:100%" maxlength="128" value="<?php echo $column['sg_cus_to_name'] ?>"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt" style="width:100%" maxlength="128" value="<?php echo $column['sg_cus_to_address'] ?>"></td>
	</tr>
</table><br />
<span class="bar_bl">ITEM LIST</span> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="8%" rowspan="2">CODE</th>
		<th width="20%" rowspan="2">MODEL NO</th>
		<th width="15%" rowspan="2">SERIAL<br />NUMBER</th>
		<th width="15%" rowspan="2">GUARANTEE</th>
		<th colspan="2">REMARK</th>
		<th width="3%" rowspan="2">DEL</th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<th>TECHNICIAN</th>
	</tr>
	<tbody id="rowPosition">
	<?php
	pg_result_seek($item_res, 0);
	while($items =& fetchRow($item_res)) {
	?>
		<tr id="<?php echo $items[3].'||'.$items[6] ?>">
			<td><input type="hidden" name="_it_code[]" value="<?php echo $items[2] ?>"><?php echo $items[2] ?></td>
			<td><input type="text" name="_it_model_no[]" class="req" style="width:100%" value="<?php echo trim($items[3]) ?>"></td>
			<td><input type="text" name="_it_sn[]" class="req" style="width:100%" value="<?php echo trim($items[6]) ?>"></td>
			<td align="center">
				<input type="hidden" name="_it_is_guarantee[]" value="<?php echo $items[4] ?>">
				<?php echo ($items[4]==1) ? 'Yes, '.date('j-M-Y', strtotime($items[5])) : 'No'?>
			</td>
			<td><input type="text" name="_it_cus_complain[]" class="fmt" style="width:100%" value="<?php echo $items[15] ?>"></td>
			<td><input type="text" name="_it_tech_analyze[]" class="fmt" style="width:100%" value="<?php echo $items[16] ?>"></td>
			<td align="center">
				<input type="hidden" name="_it_guarantee_period[]" value="<?php echo $items[5] ?>">
				<a href="javascript:deleteItem('<?php echo $items[3].'||'.$items[6] ?>',2)"><img src="../../_images/icon/delete.gif" width='15px'></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
	<tr>
		<th colspan="6" align="right">TOTAL ITEM <input type="text" name="totalItem" class="fmtn" style="width:5%" readOnly></th>
		<th></th>
	</tr>
</table><br />
<span class="bar_bl">SERVICE INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" rows="4" cols="55"><?php echo $column['sg_remark'] ?></textarea></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete registration"> &nbsp; Delete reg</button>
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['sg_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update registration"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete registration?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}
	
	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "<?php echo HTTP_DIR . "customer_service/service/pdf/" ?>download_pdf.php?_code=<?php echo trim($_code)."&_date=".date("Ym", strtotime($column['sg_receive_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if (window.rowPosition.rows.length <= 0) {
			alert("You need to fill at least one item");
			return;
		}

		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = 'summary_registration_by_customer.php';
	}
</script>
<!--
0	sgit_idx
1	sg_code
2	it_code
3	sg_model_no
4	sg_is_guarantee
5	sg_guarantee
6	sg_serial_number
7	sg_incoming_start_timestamp
8	sg_incoming_end_timestamp
9	sg_repaired_start_timestamp
10	sg_repaired_end_timestamp
11	sg_finishing_start_timestamp
12	sg_finishing_end_timestamp
13	sg_service_action_chk
14	sg_replacement_item
15	sg_cus_complain
16	sg_tech_analyze
-->
<br /><br />
<form name="frmUpdateItem">
<?php 
$i = 1;
$j = 0;
pg_result_seek($item_res, 0);
while($items =& fetchRowAssoc($item_res)) { 
?>
<input type="hidden" name="_sgit_idx[]" value="<?php echo $items['sgit_idx']?>">
<div style="border:#016fa1 1px solid;padding:5pt 0 5pt 0;">
<table width="100%" class="table_box">
  <tr>
    <th width="5%" rowspan="4" style="border-right:#016fa1 1px solid"><font style="font-size:35px;"><?php echo $i ?></font></th>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Description</span></td>
    <td>
      <table width="100%" class="table_box">
        <tr>
          <th width="15%">Item</th>
          <td width="30%"><b><?php echo $items['sgit_model_no'].', '.$items['sgit_serial_number'] ?></b></td>
          <th width="15%">Guarantee</th>
          <td>
            <input type="radio" name="_it_is_guarantee_<?php echo $items['sgit_idx']?>" value="1" disabled<?php echo ($items['sgit_is_guarantee']==1) ? ' checked':''?>> Yes, <input type="text" name="_it_guarantee_period[]" class="fmtd" size="10" value="<?php echo ($items['sgit_is_guarantee']==1) ? date('j-M-Y', strtotime($items['sgit_guarantee'])) : '' ?>"  disabled>
            <input type="radio" name="_it_is_guarantee_<?php echo $items['sgit_idx']?>" value="0" disabled<?php echo ($items['sgit_is_guarantee']==0) ? ' checked':''?>> No
          </td>
        </tr>
        <tr>
          <th>Cus Complain</th>
          <td colspan="3"><input type="text" name="_it_cus_complain[]" class="fmt" style="width:100%" value="<?php echo $items['sgit_cus_complain'] ?>"></td>
        </tr>
        <tr>
          <th>Tech Analyze</th>
          <td colspan="3"><input type="text" name="_it_tech_analyze[]" class="fmt" style="width:100%" value="<?php echo $items['sgit_cus_complain'] ?>"></td>
        </tr>
      </table>
	</td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Status</span></td>
	<td>
	  <table width="50%" class="table_box">
       <tr>
        <th align="left" width="27%"><input type="radio" name="_it_status_<?php echo $items['sgit_idx']?>" value="0" onclick="enabledDateStatus(<?php echo $j.','.$items['sgit_idx']?>, this)"<?php echo ($items['sgit_status']==0) ? ' checked':''?>> Incoming</th>
        <th align="left" width="27%"><input type="radio" name="_it_status_<?php echo $items['sgit_idx']?>" value="1" onclick="enabledDateStatus(<?php echo $j.','.$items['sgit_idx']?>, this)"<?php echo ($items['sgit_status']==1) ? ' checked':''?>> Finish</th>
        <th width="15%">time (days)</th>
        <td></td>
        <th align="left" width="27%"><input type="radio" name="_it_status_<?php echo $items['sgit_idx']?>" value="2" onclick="enabledDateStatus(<?php echo $j.','.$items['sgit_idx']?>, this)"<?php echo ($items['sgit_status']==2) ? ' checked':''?>> Delivery</th>
      </tr>
      <tr>
          <td><input type="text" class="fmtd" style="width:100%" name="_it_incoming[]" readonly></td>
          <td><input type="text" class="fmtd" style="width:100%" name="_it_finish[]" readonly></td>
          <td><input type="text" class="fmtd" style="width:100%" name="_it_total_time[]" disabled></td>
          <td></td>
          <td><input type="text" class="fmtd" style="width:100%" name="_it_delivery[]" readonly></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Action</span></td>
	<td>
	  <table width="100%" class="table_box">
        <tr>
          <td><input type="checkbox" name="chk_<?php echo $items['sgit_idx']?>[]" value="1"<?php echo ($items['sgit_service_action_chk'] & 1)? ' checked':''?>> Service</td>
          <td><input type="checkbox" name="chk_<?php echo $items['sgit_idx']?>[]" value="8"<?php echo ($items['sgit_service_action_chk'] & 8)? ' checked':''?>> Replacement product &nbsp; <input type="text" name="_it_replace_product[]" class="fmt" size="30" value="<?php echo $items['sgit_replacement_product'] ?>" disabled></td>
        </tr>
        <tr>
          <td><input type="checkbox" name="chk_<?php echo $items['sgit_idx']?>[]" value="2"<?php echo ($items['sgit_service_action_chk'] & 2)? ' checked':''?>> Return back to Customer</td>
          <td><input type="checkbox" name="chk_<?php echo $items['sgit_idx']?>[]" value="16"<?php echo ($items['sgit_service_action_chk'] & 16)? ' checked':''?>> Replacement part &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="_it_replace_part[]" class="fmt" size="30" value="<?php echo $items['sgit_replacement_part'] ?>" disabled></td>
        </tr>
        <tr>
          <td><input type="checkbox" name="chk_<?php echo $items['sgit_idx']?>[]" value="4"<?php echo ($items['sgit_service_action_chk'] & 4)? ' checked':''?>> Calibation</td>
        </tr>
      </table>
	</td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Cost</span></td>
    <td>
      <table width="100%" class="table_box">
        <tr>
          <td>
            <input type="radio" name="_it_cost_<?php echo $items['sgit_idx']?>" value="0"<?php echo ($items['sgit_cost']==0)? ' checked':''?>> Free charge &nbsp;
            <input type="radio" name="_it_cost_<?php echo $items['sgit_idx']?>" value="1"<?php echo ($items['sgit_cost']==1)? ' checked':''?>> Service charge
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</div><br />
<?php 
	$i++;
	$j++;
}
?>
<div align="right">
  <button name='btnUpdate' class='input_btn'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update registration"> &nbsp; Update item status</button>&nbsp;
</div>
</form>
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
<!--
/*
<?php
// Print Javascript Code
echo "\tvar item     = new Array();\n";
$i = 0;
while($rows =& fetchRowAssoc($item_res)) {
	printf("\t    item[%s] = [%s, '%s', '%s', '%s', %s];\n",
		$i,
		$rows['sgit_idx'],					// 0
		trim($rows['it_code']),				// 1
		trim($rows['sgit_model_no']),		// 2
		trim($rows['sgit_serial_number']),	// 3
		$rows['sgit_status']				// 4
	);
	$i++;
}
?>
*/


/*
alert(idx +' - '+ it_idx +' - '+ val.value);

alert(
	"0.  type: "+oCheck[0].type + ', name: ' + oCheck[0].name + ', value: ' + oCheck[0].value +"\n"+
	"1.  type: "+oCheck[1].type + ', name: ' + oCheck[1].name + ', value: ' + oCheck[1].value +"\n"+
	"2.  type: "+oCheck[2].type + ', name: ' + oCheck[2].name + ', value: ' + oCheck[2].value +"\n"+
	"3.  type: "+oCheck[3].type + ', name: ' + oCheck[3].name + ', value: ' + oCheck[3].value +"\n"+
	"4.  type: "+oCheck[4].type + ', name: ' + oCheck[4].name + ', value: ' + oCheck[4].value +"\n"+
	"5.  type: "+oCheck[5].type + ', name: ' + oCheck[5].name + ', value: ' + oCheck[5].value +"\n"+
	"6.  type: "+oCheck[6].type + ', name: ' + oCheck[6].name + ', value: ' + oCheck[6].value +"\n"+
	"7.  type: "+oCheck[7].type + ', name: ' + oCheck[7].name + ', value: ' + oCheck[7].value +"\n"+
	"8.  type: "+oCheck[8].type + ', name: ' + oCheck[8].name + ', value: ' + oCheck[8].value +"\n"+
	"9.  type: "+oCheck[9].type + ', name: ' + oCheck[9].name + ', value: ' + oCheck[9].value +"\n"+
	"10. type: "+oCheck[10].type + ', name: ' + oCheck[10].name + ', value: ' + oCheck[10].value +"\n"+
	"11. type: "+oCheck[11].type + ', name: ' + oCheck[11].name + ', value: ' + oCheck[11].value +"\n"+
	"12. type: "+oCheck[12].type + ', name: ' + oCheck[12].name + ', value: ' + oCheck[12].value +"\n"+
	"13. type: "+oCheck[13].type + ', name: ' + oCheck[13].name + ', value: ' + oCheck[13].value +"\n"+
	"14. type: "+oCheck[14].type + ', name: ' + oCheck[14].name + ', value: ' + oCheck[14].value +"\n"+
	"15. type: "+oCheck[15].type + ', name: ' + oCheck[15].name + ', value: ' + oCheck[15].value +"\n"+
	"16. type: "+oCheck[16].type + ', name: ' + oCheck[16].name + ', value: ' + oCheck[16].value +"\n"+
	"17. type: "+oCheck[17].type + ', name: ' + oCheck[17].name + ', value: ' + oCheck[17].value +"\n"+
	"18. type: "+oCheck[18].type + ', name: ' + oCheck[18].name + ', value: ' + oCheck[18].value +"\n"+
	"19. type: "+oCheck[19].type + ', name: ' + oCheck[19].name + ', value: ' + oCheck[19].value +"\n"+
	"20. type: "+oCheck[20].type + ', name: ' + oCheck[20].name + ', value: ' + oCheck[20].value +"\n"+
	"21. type: "+oCheck[21].type + ', name: ' + oCheck[21].name + ', value: ' + oCheck[21].value +"\n\n\n"+
	"22. type: "+oCheck[22].type + ', name: ' + oCheck[22].name + ', value: ' + oCheck[22].value +""
);
*/
-->
