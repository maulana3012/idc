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
$left_loc = "daily_booking_demo_by_reference.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//========================================================================================= confirm request
if(ckperm(ZKP_INSERT, HTTP_DIR ."$currentDept/$moduleDept/index.php", 'cfm_request')) {

	$_code				= $_POST['_code'];
	$_confirm_by		= $_POST['_confirm_by'];
	$_log_by_account	= $S->getValue("ma_account");

	//make pgsql ARRAY String for many item
	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val)	$_ed_it_code[]			= $val;
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val)	$_ed_it_date[]			= $val;
		$_ed_it_date	= '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val)	$_ed_it_qty[]			= $val;
		$_ed_it_qty		= implode(',', $_ed_it_qty);		
	} else {
		$_ed_it_qty		= 0;
	}

	$result = executeSP(
		ZKP_SQL."_cfmrequestdemobymarketing",
		"$\${$_code}$\$",
		"$\${$_confirm_by}$\$",
		"$\${$_log_by_account}$\$",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_date]",
		"ARRAY[$_ed_it_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR ."$currentDept/$moduleDept/confirm_request.php?_code=".$_code);
	} else {
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_request.php?_code=".$_code);
	}
}

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
  usit_remark,
  it_ed
FROM ".ZKP_SQL."_tb_using_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '$_code'";
$res_item = query($sql_item);
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
<?php
//current demo
$stock_sql = "SELECT it_code, demo_qty FROM ".ZKP_SQL."_tb_demo join ".ZKP_SQL."_tb_item using(it_code)";
$stock_res	=& query($stock_sql);

echo "var demo = new Array();\n";
while ($rows =& fetchRow($stock_res, 0)) {
	printf("demo['%s'] = [%s];\n",
		trim($rows[0]), $rows[1]);	//it_code, demo_qty
}
?>

var wInputED;
function insertED(code,i) {
	var f			= window.document.frmConfirm;
	var numItem		= window.rowPosition.rows.length;
	var oRow		= window.rowPosition.rows(i);
	var item		= oRow.cells(1).innerText;

	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 220) / 2;

	wInputED = window.open(
		'./p_select_ed.php?_code='+code+'&_item='+item, 'wSearchED',
		'scrollbars,width=450,height=220,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

function createED() {
	var o	= window.document.frmConfirm;
	var f2	= wInputED.document.frmInsert;

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();
	var d	= parseDate(f2.elements[2].value, 'prefer_euro_format');

	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)+'-'+trim(f2.elements[2].value)) {
			alert("Item ["+trim(f2.elements[0].value)+"] "+ f2.elements[1].value +" for E/D "+ formatDate(d, 'NNN-yyyy') + " already exist!");
			return;
		}
	}

	for (var i=0; i<5; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // CODE
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // IT MODEL NO
				oTD[i].innerText	= f2.elements[1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_model_no[]";
				oTextbox[i].value	= f2.elements[1].value;
				break;

			case 2: // E/D
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_date[]";
				oTextbox[i].value	= f2.elements[2].value;
				break;

			case 3: // QTY
				oTD[i].innerText	= f2.elements[5].value;
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_qty[]";
				oTextbox[i].value	= numFormatval(f2.elements[5].value+'',2);
				break;

			case 4: // DELETE
				oTD[i].innerHTML	= "<a href=\"javascript:deleteED('"+f2.elements[0].value+'-'+trim(f2.elements[2].value)+"')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align		= "center";
				break;
		}
		if (i!=4) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value)+'-'+trim(f2.elements[2].value);
		oTR.appendChild(oTD[i]);
	}
	window.EDPosition.appendChild(oTR);
} 

function deleteED(idx) {
	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.EDPosition.removeChild(oRow);
			count = count - 1;
		}
	}
}

function updateAmount() {

	var count	 = window.rowPosition.rows.length;
	var sumOfQty = 0;

	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseFloat(removecomma(oRow.cells(3).innerText));
	}
	
	window.document.all.totalWhQty.value = numFormatval(sumOfQty.toString(), 2);
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CONFIRM REQUEST</h3>
<form name="frmConfirm" method="post">
<input type="hidden" name="p_mode" value="cfm_request">
<input type="hidden" name="_code" value="<?php echo $column['use_code'] ?>">
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="4"><strong class="info">REQUEST INFORMATION</strong></td>
		<td colspan="3" align="right"><i>Last updated by : <?php echo $column['use_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['use_lastupdated_timestamp']))?></i></td>
	</tr>
	<tr>
		<th width="15%">REQUEST NO</th>
		<td colspan="2"><strong><?php echo $column['use_code'] ?></strong></td>
		<th width="15%">REQUEST BY</th>
		<td width="20%"><?php echo $column['use_request_by'] ?></td>
		<th width="15%">REQUEST DATE</th>
		<td width="15%"><?php echo date('d-M-Y', strtotime($column['use_request_date'])) ?></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER/<br />EVENT</th>
		<th width="12%">CODE</th>
		<td><?php echo $column['use_cus_to'] ?></td>
		<th>NAME</th>
		<td colspan="3"><?php echo $column['use_cus_name'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="5"><?php echo $column['use_cus_address'] ?></td>
	</tr>
</table><br />
<strong class="info">ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr height="40px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">QTY</th>
			<th width="7%">RETURNABLE<br />Yes | No</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php
$i = 0;
while($items =& fetchRow($res_item)) {
?>
		<tr id="<?php echo trim($items[0])?>">
			<td>
				<input type="hidden" name="_it_code" value="<?php echo trim($items[0])?>">
				<input type="hidden" name="_it_item" value="<?php echo $items[1]?>">
				<input type="hidden" name="_it_ed" value="<?php echo $items[7]?>">
				<input type="hidden" name="_it_qty" value="<?php echo $items[5]?>">
				<?php if($items[7] == 't') { ?>
				<a href="javascript:insertED(<?php echo "'".trim($items[0])."',".$i ?>)"><b><?php echo $items[0]?></b></a>
				<?php } else if($items[7] == 'f') { ?>
				<?php echo $items[0]?>
				<?php } ?>
			</td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format($items[5],2)?></td>
			<td align="center"><?php echo $items[3]?></td>
			<td><?php echo $items[6]?></td>
		</tr>
<?php $i++; } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="10%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="3" width="20%">&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="55%" valign="top">
			<strong class="info">OTHERS</strong>
			<table width="100%" class="table_box" cellspacing="1">
				<tr>
					<th width="20%">SIGN BY</th>
					<td><?php echo $column["use_signature_by"] ?></td>
				</tr>
				<tr>
					<th>REMARK</th>
					<td><textarea name="_remark" rows="5" style="width:95%" readonly><?php echo $column["use_remark"] ?></textarea></td>
				</tr>
			</table>
		</td>
		<td width="45%" valign="top">
			<strong class="info">DETAIL ITEM PER E/D</strong>
			<table width="100%" class="table_l">
				<thead>
					<tr height="25px">
						<th width="15%">CODE</th>
						<th>ITEM NO</th>
						<th width="25%">E/D</th>
						<th width="15%">QTY</th>
						<th width="5%">DEL</th>
					</tr>
				</thead>
				<tbody id="EDPosition">
				
				</tbody>
			</table><br />
		</td>
	</tr>
	<tr height="40px" valign="bottom">
		<td>
			Confirm by  : <input type="text" name="_confirm_by" class="req"> &nbsp; &nbsp;
			<button name='btnConfirm' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle" alt="Confirm"> &nbsp; Confirm</button>&nbsp;
		</td>
	</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
window.document.frmConfirm.btnConfirm.onclick = function() {
	var f = window.document.frmConfirm;

	//variable
	var countI		= window.rowPosition.rows.length;
	var countII		= window.EDPosition.rows.length;
	var e 			= window.document.frmConfirm.elements;
	var numInput	= 4;
	var idx_code	= 2;				/////
	var idx_item	= idx_code+1;
	var idx_ed		= idx_code+2;
	var idx_qty		= idx_code+3;
	var idx_codeII	= idx_code+(numInput*countI)+2;
	var idx_qtyII	= idx_code+(numInput*countI)+5;

	//checking there is stock or not
	for (var i=0; i<countI; i++) {
		var code		= e(idx_code+i*numInput).value;
		var item		= e(idx_item+i*numInput).value;
		var qty			= parseFloat(removecomma(e(idx_qty+i*numInput).value));
		var demo_stock	= demo[code][0];

		if(demo_stock == '') {demo_stock = 0;}

		if(qty > demo_stock){
			alert(
				"Check request qty for:\n\n" +
				"Code : "+ code +"\nItem  : "+ item + "\n" +
				"Current demo qty     : "+numFormatval(demo_stock+'',2)+"\n" +
				"Current request qty : "+numFormatval(qty+'',2));
			return;
		}
	}

	//checking E/D
	for (var i=0; i<countI; i++) {
		if(e(idx_ed+i*numInput).value=='t') {
			var istrue	= false;
			var code	= trim(e(idx_code+i*numInput).value);
			var item	= e(idx_item+i*numInput).value;
			var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

			if(countII<=0) { alert("Please complete data for outgoing Expired Date");return;}

			var temp_qty = 0;
			for (var j=0; j<countII; j++) {
				if(e(idx_codeII+j*numInput).value==code) {
					if(parseFloat(removecomma(e(idx_qtyII+j*numInput).value))=='') {
						var value = 0;
					} else {
						var value = parseFloat(removecomma(e(idx_qtyII+j*numInput).value));
					}
					temp_qty = temp_qty + value;
				}
			}

			if(temp_qty != qty) {
				alert(
					"Check outgoing expired date for:\n\n" +
					"Code : "+ code +"\nItem  : "+ item + "\n" +
					"Current request qty       : "+addcomma(qty)+"\n" +
					"Current inputed E/D qty : "+addcomma(temp_qty));
				return;
			}
 		}
	}


	if(f._confirm_by.value.length <= 0){
		alert("Confirm by have to filled");
		f._confirm_by.focus();
		return;
	}

	if(confirm("Are you sure to confirm request?")) {
		window.document.frmConfirm.submit();
	}
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