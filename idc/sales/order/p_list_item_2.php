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

//========================================================================================= receive variable
if(ckperm(ZKP_INSERT, HTTP_DIR . "javascript:window.close();", 'item_info')) {
	//VARIABLE
	$_cus_code 		 = trim($_POST['_cus_code']);
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
$sql = "
SELECT
  b.it_code,		--0
  b.icat_midx,		--1
  b.it_model_no,	--2
  b.it_type,		--3
  b.it_desc,		--4
  ".ZKP_SQL."_getUserPrice(b.it_code, CURRENT_DATE) AS user_price	--5
FROM
  ".ZKP_SQL."_tb_set_item AS a
  JOIN ".ZKP_SQL."_tb_item AS b ON a.seit_code=b.it_code
WHERE a.it_code = '$_wh_it_code'";

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
	printf("it['%s'] = ['%s',%s,'%s','%s','%s',%s];\n",
		trim($rows[0]),	//cus_it_code		-idx
		addslashes($rows[0]),	//cus_it_code		-0
		addslashes($rows[1]),	//cus_it_icat_midx	-1
		addslashes($rows[2]),	//cus_it_model_no	-2
		addslashes($rows[3]),	//cus_it_type		-3
		addslashes($rows[4]),	//cus_it_desc		-4
		$rows[5]				//user price		-5
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
		f._cus_it_qty.value	 	 = numFormatval(f._wh_it_qty.value+'',0);;
		f._cus_it_price.value	 = addcomma(it[f._cus_it_code.value][5]);
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
		f._cus_it_code.className	= 'req';
		f._cus_it_qty.className		= 'reqn';
		f._cus_it_price.className	= 'reqn';
		f._cus_it_delivery.className= 'reqd';
		f._cus_it_qty.readOnly		= false;
		f._cus_it_price.readOnly	= false;
		f._cus_it_remark.readOnly	= false;
		f._cus_it_delivery.readOnly	= false;
		f._cus_it_code.value	 	= f._wh_it_code.value;
		f._cus_it_icat_midx.value	= f._wh_it_icat_midx.value;
		f._cus_it_model_no.value 	= f._wh_it_model_no.value;
		f._cus_it_type.value	 	= f._wh_it_type.value;
		f._cus_it_desc.value	 	= f._wh_it_desc.value;
		f._cus_it_qty.value	 	 	= numFormatval(f._wh_it_qty.value+'',0);
		f._cus_it_remark.value	 	= f._wh_it_remark.value;
		f._cus_it_price.focus();
	} else {
		f._cus_it_qty.value			= '';
		f._cus_it_price.value		= '';
		f._cus_it_remark.value		= '';
		f._cus_it_code.className	= 'fmt';
		f._cus_it_qty.className		= 'fmtn';
		f._cus_it_price.className	= 'fmtn';
		f._cus_it_delivery.className= 'fmt';
		f._cus_it_qty.readOnly		= 'readonly';
		f._cus_it_price.readOnly	= 'readonly';
		f._cus_it_remark.readOnly	= 'readonly';
		f._cus_it_delivery.readOnly	= 'readonly';
		f._cus_it_icat_midx.value	= '';
		f._cus_it_code.value		= '';
		f._cus_it_type.value		= '';
		f._cus_it_model_no.value	= '';
		f._cus_it_desc.value		= '';
		f._cus_it_qty.value			= '';
		f._cus_it_remark.value		= '';
		f._cus_it_delivery.value	= '';
	}
}

function fillItem(idx) {
	var f = window.document.frmCreateItem;

	if(f.chkIsPrintCus.checked == true) {
		f._cus_it_code.value 	 	= it[idx][0];
		f._cus_it_icat_midx.value 	= it[idx][1];
		f._cus_it_model_no.value 	= it[idx][2];
		f._cus_it_type.value	 	= it[idx][3];
		f._cus_it_desc.value	 	= it[idx][4];
		f._cus_it_price.value	 	= addcomma(it[idx][5]);
		if(f._wh_it_code.value == f._cus_it_code.value) {
			f.chkSameValue.checked	= true;
			f._cus_it_qty.value		= f._wh_it_qty.value; 
		} else {
			f.chkSameValue.checked	= false;
			f._cus_it_qty.value		= addcomma(parseFloat(removecomma(f._wh_it_qty.value))*f._wh_it_function.value);
		}
		f._cus_it_price.focus();
	}
}

//Wrapper function. It call opener's function.
function createNewItem() {
	var f = window.document.frmCreateItem;
	var d = parseDate(f._cus_it_delivery.value, 'prefer_euro_format');

	if(f.chkIsPrintCus.checked == true) {
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
		}  else if (d == null) {
			alert("You must be input date with proper format")
			f._cus_it_delivery.value = "";
			f._cus_it_delivery.focus();
			return;
		}
		f._cus_it_delivery.value = formatDate(d, "d-NNN-yyyy");
	}

	f._wh_it_qty.value	= removecomma(numFormatval(f._wh_it_qty.value+'',2));
	f._cus_it_qty.value	= removecomma(numFormatval(f._cus_it_qty.value+'',2));

	window.opener.createItem();
	window.location.href = './p_list_item_1.php?_cus_code=<?php echo $_cus_code ?>';
}

function initLoad() {
	var f = window.document.frmCreateItem; 
	copyRow();
	f._cus_it_price.value = addcomma(it[f._cus_it_code.value][5]);
	f._cus_it_delivery.focus();
}
</script>
</head>
<body style="margin:8pt" onLoad="initLoad()">
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT STOCK (STEP 2 / 2)<br />
<small>* Printed for customer item list</small>
</strong><hr>
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
</table><br />
<table width="100%" class="table_box">
	<tr>
		<th width="4%">No</th>
		<th width="6%">CODE</th>
		<th width="12%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="25%">@ PRICE</th>
	</tr>
</table>
<div style="height:80; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><a href="javascript:fillItem('<?php echo trim($column['it_code']) ?>')"><?php echo $column['it_code']?></a></td>
		<td><?php echo substr($column['it_model_no'], 0, 15)?></td>
		<td><?php echo cut_string($column['it_desc'],70);?></td>
		<td align="right">Rp. <?php echo number_format((double)$column['user_price']);?></td>
	</tr>
<?php } ?>
</table>
</div>
<div align="right">
	<input type="checkbox" name="chkSameValue" onclick="copyRow()" checked> <small class="comment">Same as above</small> &nbsp;
	<input type="checkbox" name="chkIsPrintCus" onclick="isPrintedCus()" checked> <small class="comment">Print in customer item list</small>
</div>
<input type="hidden" name="_cus_it_icat_midx">
<input type="hidden" name="_cus_it_type">
<table width="100%" class="table_box">
	<tr>
		<th width="7%">CODE</th>
		<th width="10%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="12%">QTY</th>
		<th width="15%">UNIT<br />PRICE</th>
		<th width="15%">DELIVERY</th>
		<th width="13%">REMARK</th>
	</tr>
	<tr>
		<td><input type="text" name="_cus_it_code" style="width:100%" class="req" readonly></td>
		<td><input type="text" name="_cus_it_model_no" style="width:100%" class="fmt" readonly></td>
		<td><input type="text" name="_cus_it_desc" style="width:100%" class="fmt" readonly></td>
		<td><input type="text" name="_cus_it_qty" style="width:100%" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewItem()" readonly></td>
		<td><input type="text" name="_cus_it_price" style="width:100%" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
		<td><input type="text" name="_cus_it_delivery" class="reqd" style="width:100%" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
		<td><input type="text" name="_cus_it_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
	</tr>
	<tr>
		<td>
			<button name='btnBack' class='input_sky' style='width:50px;height:25px' onclick="window.location.href='p_list_item_1.php?_cus_code=<?php echo $_cus_code ?>'"><img src="../../_images/icon/back.gif" align="middle" alt="Back"></button>
		</td>
		<td colspan="6" align="right">
			<button name='btnAdd' class='input_sky' style='width:50px;height:25px' onclick='createNewItem()'><img src="../../_images/icon/add.gif" width="18px" align="middle" alt="Add item"></button>&nbsp;
			<button name='btnClose' class='input_sky' style='width:50px;height:25px' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"></button>
		</td>
	</tr>
</table>
</form>
</body>
</html>