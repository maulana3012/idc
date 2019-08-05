<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 29-May, 2007 23:50:32
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//RECEIVE DATA FROM p_list_item_return_1.php
if(ckperm(ZKP_INSERT, HTTP_DIR . "javascript:window.close();", 'item_info')) {
	//VARIABLE
	$_wh_it_code	 = $_POST['_wh_it_code'];
	$_wh_it_icat_midx= $_POST['_wh_it_icat_midx'];
	$_wh_it_model_no = $_POST['_wh_it_model_no'];
	$_wh_it_type	 = $_POST['_wh_it_type'];
	$_wh_it_desc	 = $_POST['_wh_it_desc'];
	$_wh_it_qty		 = $_POST['_wh_it_qty'];
	$_wh_it_function = $_POST['_wh_it_function'];
	$_wh_it_remark	 = $_POST['_wh_it_remark'];
}

//DEFAULT PROCESS
$sql = "SELECT b.it_code, b.icat_midx, b.it_model_no, b.it_type, b.it_desc, ".ZKP_SQL."_getUserPrice(b.it_code, CURRENT_DATE) AS user_price FROM ".ZKP_SQL."_tb_set_item AS a JOIN ".ZKP_SQL."_tb_item AS b ON a.seit_code=b.it_code WHERE a.it_code = '$_wh_it_code'";

if(isZKError($result =& query($sql)))
	$M->goErrorPage($result,  "javascript:window.close();");
?>
<html>
<head>
<title>ITEMS LIST</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='javascript' type='text/javascript'>
<?php
// Print Javascript Code
echo "var it = new Array();\n";
$i = 0;
while ($rows =& fetchRow($result, 0)) {
	printf("it['%s'] = ['%s',%s,'%s','%s','%s', %s];\n",
		addslashes($rows[0]),	//cus_it_code		-idx
		addslashes($rows[0]),	//cus_it_code		-0
		addslashes($rows[1]),	//cus_it_icat_midx	-1
		addslashes($rows[2]),	//cus_it_model_no	-2
		addslashes($rows[3]),	//cus_it_type		-3
		addslashes($rows[4]),	//cus_it_desc		-4
		$rows[5]				//user_price		-5
	);
}
?>

function copyRow() {
	var f = window.document.frmCreateItem;

	if(f.chkSameValue.checked == true) {
		f._cus_it_code.value	 = f._wh_it_code.value;
		f._cus_it_icat_midx.value= f._wh_it_icat_midx.value;
		f._cus_it_model_no.value = f._wh_it_model_no.value;
		f._cus_it_type.value	 = f._wh_it_type.value;
		f._cus_it_desc.value	 = f._wh_it_desc.value;
		f._cus_it_price.value	 = numFormatval(it[f._wh_it_code.value][5]+'',0);
		f._cus_it_qty.value	 	 = numFormatval(f._wh_it_qty.value+'',0);
		f._cus_it_remark.value	 = f._wh_it_remark.value;
	} else {
		f._cus_it_code.value	 = '';
		f._cus_it_icat_midx.value= '';
		f._cus_it_model_no.value = '';
		f._cus_it_type.value	 = '';
		f._cus_it_desc.value	 = '';
		f._cus_it_qty.value		 = '';
		f._cus_it_price.value	 = '';
		f._cus_it_remark.value	 = '';
	}
}

function isPrintedCus() {
	var f = window.document.frmCreateItem;

	if(f.chkIsPrintCus.checked == true) {
		f._cus_it_qty.className		= 'reqn';
		f._cus_it_price.className	= 'reqn';
		f._cus_it_qty.readOnly		= false;
		f._cus_it_price.readOnly	= false;
		f._cus_it_remark.readOnly	= false;
	} else {
		f._cus_it_qty.value			= '';
		f._cus_it_price.value		= '';
		f._cus_it_remark.value		= '';
		f._cus_it_qty.className		= 'fmtn';
		f._cus_it_price.className	= 'fmtn';
		f._cus_it_price.readOnly	= 'readonly';
		f._cus_it_remark.readOnly	= 'readonly';
	}
}

function fillItem(idx) {
	var f = window.document.frmCreateItem;

	f.chkIsPrintCus.checked 		= true;
	isPrintedCus();
	f._cus_it_code.value 	 	= it[idx][0];
	f._cus_it_icat_midx.value 	= it[idx][1];
	f._cus_it_model_no.value 	= it[idx][2];
	f._cus_it_type.value	 	= it[idx][3];
	f._cus_it_price.value	 	= numFormatval(it[idx][5]+'',0);
	f._cus_it_desc.value	 	= it[idx][4];
	if(f._wh_it_code.value == f._cus_it_code.value) {
		f.chkSameValue.checked	= true;
		f._cus_it_qty.value		= f._wh_it_qty.value; 
	} else {
		f.chkSameValue.checked	= false;
		f._cus_it_qty.value		= addcomma(parseFloat(removecomma(f._wh_it_qty.value))*f._wh_it_function.value);
	}
	f._cus_it_price.focus();
}

//Wrapper function. It call opener's function.
function createNewItem() {
	var f = window.document.frmCreateItem;

	if(f.chkIsPrintCus.checked==true) {
		if (f._cus_it_code.value.length <= 0) {
			alert("Please select the code first");
			f._cus_it_code.focus();
			return;
		} else if (f._cus_it_qty.value.length <= 0) {
			alert("Please fill the qty");
			f._cus_it_qty.focus();
			return;
		} else if (f._cus_it_price.value.length <= 0) {
			alert("Please fill the unit price");
			f._cus_it_price.focus();
			return;
		}
	}

	window.opener.createItem();
	window.location.href = './p_list_item_return_1.php';
}

function initLoad() {
	copyRow();
	window.document.frmCreateItem._cus_it_price.focus();
}
</script>
</head>
<body style="margin:8pt" onLoad="initLoad()">
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT STOCK (STEP 2 / 2)<br />
<small>* Printed for customer item list</small>
</strong>
<hr>
<form name="frmCreateItem">
<input type="hidden" name="_wh_it_code" value="<?php echo $_wh_it_code ?>">
<input type="hidden" name="_wh_it_icat_midx" value="<?php echo $_wh_it_icat_midx ?>">
<input type="hidden" name="_wh_it_type" value="<?php echo $_wh_it_type ?>">
<table width="100%" class="table_box">
	<tr>
		<th width="8%">CODE</th>
		<th width="12%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="12%">QTY</th>
		<th width="8%">(x)</th>
		<th width="25%">REMARK</th>
	</tr>
	<tr>
		<td><?php echo $_wh_it_code ?></td>
		<td><input type="text" class="fmt" style="width:100%" name="_wh_it_model_no" value="<?php echo $_wh_it_model_no ?>" readonly></td>
		<td><input type="text" class="fmt" size="35%" name="_wh_it_desc" value="<?php echo $_wh_it_desc ?>" readonly></td>
		<td><input type="text" class="fmtn" size="5" name="_wh_it_qty" value="<?php echo number_format((double)$_wh_it_qty,2) ?>" readonly></td>
		<td><input type="text" class="fmtn" size="4" name="_wh_it_function" value="<?php echo number_format((double)$_wh_it_function,2) ?>" readonly></td>
		<td><input type="text" class="fmt" style="width:100%" name="_wh_it_remark" value="<?php echo $_wh_it_remark ?>"></td>
	</tr>
</table>
<div align="right"><input type="checkbox" name="chkIsPrintWH" checked> <small class="comment">Print in warehouse item list</small></div><br />
<table width="100%" class="table_box">
	<tr>
		<th width="4%">No</th>
		<th width="6%">CODE</th>
		<th width="12%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="25%">@ PRICE</th>
	</tr>
</table>
<div style="height:300; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><a href="javascript:fillItem('<?php echo $column['it_code']?>')"><?php echo $column['it_code']?></a></td>
		<td><?php echo substr($column['it_model_no'], 0, 15)?></td>
		<td><?php echo cut_string($column['it_desc'],70);?></td>
		<td align="right">Rp. <?php echo number_format((double)$column['user_price'])?></td>
	</tr>
<?php } ?>
</table>
</div>
<table width="100%" class="table_layout">
	<tr>
		<td></td>
		<td width="34%"><input type="checkbox" name="chkSameValue" onclick="copyRow()" checked> <small class="comment">Same as above</small></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="checkbox" name="chkIsPrintCus" onclick="isPrintedCus()" checked> <small class="comment">Print in customer item list</small></td>
	</tr>
</table>
<input type="hidden" name="_cus_it_icat_midx">
<input type="hidden" name="_cus_it_type">
<table width="100%" class="table_box">
	<tr>
		<th width="8%">CODE</th>
		<th width="12%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="12%">QTY</th>
		<th width="15%">UNIT<br />PRICE</th>
		<th width="20%">REMARK</th>
	</tr>
	<tr>
		<td><input type="text" name="_cus_it_code" style="width:100%" class="req" readonly></td>
		<td><input type="text" name="_cus_it_model_no" style="width:100%" class="fmt" readonly></td>
		<td><input type="text" name="_cus_it_desc" style="width:100%" class="fmt" readonly></td>
		<td><input type="text" name="_cus_it_qty" style="width:100%" class="reqn" readonly></td>
		<td><input type="text" name="_cus_it_price" style="width:100%" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
		<td><input type="text" name="_cus_it_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
	</tr>
	<tr>
		<td>
			<button name='btnBack' class='input_sky' style='width:30px;height:25px' onclick="window.location.href='./p_list_item_return_1.php'"><img src="../../_images/icon/back.gif" align="middle" alt="Back"></button>
		</td>
		<td colspan="5" align="right">
			<button name='btnAdd' class='input_sky' style='width:50px;height:25px' onclick='createNewItem()'><img src="../../_images/icon/add.gif" width="15px" align="middle" alt="Add"></button>&nbsp;
			<button name='btnClose' class='input_sky' style='width:50px;height:25px' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"></button>
		</td>
	</tr>
</table>
</form>
</body>
</html>