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
$left_loc	= "summary_reg_by_customer.php";
$_code 		= $_GET['_code'];

//---------------------------------------------------------------------------------------------------- delete
if (ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'uncfm')) {

	$result =& query("UPDATE ".ZKP_SQL."_tb_service_reg SET sg_complete_service=false WHERE sg_code = '$_code'");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_registration.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_registration.php?_code=".$_code);
}

//========================================================================================= DEFAULT PROCESS
//service
$sql = "SELECT *, ".ZKP_SQL."_getServiceBill(sg_code,null,1) AS bill_no FROM ".ZKP_SQL."_tb_service_reg WHERE sg_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['sg_complete_service']=='f') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_registration.php?_code=".$_code);
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
	
	<?php if($column['bill_no']!='') { ?>
	window.document.all.btnUnCfm.disabled = true;
	<?php } ?>
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe">CUSTOMER SERVICE</font>] DETAIL SERVICE ITEM<br />
</strong>
<small class="comment">* Finished service item</small>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column['sg_code'] ?>">
<input type='hidden' name='_revision_time' value="<?php echo $column["sg_revesion_time"] ?>">
<input type='hidden' name='_num_item' value="<?php echo $numRow ?>">
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<td colspan="4" valign="bottom"><span class="bar_bl">SERVICE INFORMATION</span></td>
		<td colspan="3" align="right">
			<small><i>
			Last updated by : <?php echo $column['sg_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['sg_lastupdated_timestamp']))." Rev:".$column['sg_revesion_time']?><br />
			Confirm registration by : <?php echo $column['sg_complete_by_account'].date(', j-M-Y g:i:s', strtotime($column['sg_complete_timestamp'])) ?>
			</i></small>
		</td>
	</tr>
	<tr>
		<th width="15%">REG NO.</th>
		<td width="30%" colspan="2"><b><?php echo $column['sg_code'] ?></b></td>
		<th width="15%">RECEIVE DATE.</th>
		<td><?php echo date('j-M-Y', strtotime($column['sg_receive_date'])) ?></td>
		<th width="15%">SERVICE NO</th>
		<td width="15%"><a href="../service/revise_service.php?_code=<?php echo $column['bill_no'] ?>" target="_blank"><b><?php echo $column['bill_no'] ?></b></a></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER</th>
		<th width="12%"><u>C</u>ODE</th>
		<td width="10%"><?php echo $column['sg_cus_to'] ?></td>
		<th width="12%">NAME</th>
		<td><?php echo $column['sg_cus_to_name'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['sg_cus_to_address'] ?></td>
	</tr>
</table><br />
<span class="bar_bl">ITEM LIST</span>
<table width="100%" class="table_l">
	<tr>
		<th width="8%" rowspan="2">CODE</th>
		<th width="20%" rowspan="2">MODEL NO</th>
		<th width="15%" rowspan="2">SERIAL<br />NUMBER</th>
		<th width="15%" rowspan="2">GUARANTEE</th>
		<th colspan="2">REMARK</th>
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
			<td><?php echo $items[2] ?></td>
			<td><?php echo trim($items[3]) ?></td>
			<td><?php echo trim($items[6]) ?></td>
			<td align="center">
				<input type="hidden" name="_it_is_guarantee[]" value="<?php echo $items[4] ?>">
				<?php echo ($items[4]==1) ? 'Yes, '.date('j-M-Y', strtotime($items[5])) : 'No'?>
			</td>
			<td><?php echo $items[15] ?></td>
			<td><?php echo $items[16] ?></td>
		</tr>
	<?php } ?>
	</tbody>
	<tr>
		<th colspan="6" align="right">TOTAL ITEM(S) <input type="text" name="totalItem" class="fmtn" style="width:5%" readOnly></th>
	</tr>
</table><br />
<span class="bar_bl">SERVICE INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="15%">SIGN BY</th>
		<td><?php echo $column['sg_signature_by'] ?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td><textarea name="_remark" rows="4" cols="55" disabled><?php echo $column['sg_remark'] ?></textarea></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnUnCfm' class='input_red' style='width:100px;'><img src="../../_images/icon/update.gif" width="18px" align="middle" alt="Unconfirm this registration"> &nbsp; Unconfirm</button>&nbsp;
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<option value="f" selected>F</option>
			<?php
				for($counter = $column['sg_revesion_time']; $counter >= 0; $counter--) {
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
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnUnCfm.onclick = function() {
		if(confirm("Are you sure to delete unconfirm the registration?")) {
			oForm.p_mode.value = 'uncfm';
			oForm.submit();
		}
	}

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "<?php echo HTTP_DIR . "customer_service/service/pdf/" ?>download_pdf.php?_code=<?php echo trim($_code)."&_date=".date("Ym", strtotime($column['sg_receive_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = 'summary_registration_by_customer.php';
	}
</script>
<iframe id="rightFrame" src="i_detail_item.php?_code=<?php echo $_code?>" frameborder="0" width="100%" height="<?php echo ($numRow*350) ?>" name="iFrm"></iframe>
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