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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc	= "summary_warranty_by_item.php";
$_code = $_GET['_code'];

//========================================================================================== DELETE PROCESS
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/detail_warranty.php?_code=$_code", 'delete')) {
	if(isZKError($result =& query("DELETE FROM ".ZKP_SQL."_tb_warranty WHERE wr_idx = $_code"))) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_warranty.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_warranty_by_item.php");
}

//========================================================================================== UPDATE PROCESS
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/input_warranty.php", "update")) {

	$_idx				= $_POST['_idx'];
	$_name				= $_POST['_name'];
	$_sex				= $_POST['_sex'];
	$_address			= $_POST['_address'];
	$_city				= $_POST['_city'];
	$_zip_code			= $_POST['_zip_code'];
	$_contact_phone		= $_POST['_contact_phone'];
	$_contact_hphone	= $_POST['_contact_hphone'];
	$_contact_email		= $_POST['_contact_email'];
	$_it_product		= $_POST['_it_product'];
	$_it_code			= $_POST['_it_code'];
	$_it_model_no		= $_POST['_it_model_no'];
	$_warranty_no		= $_POST['_warranty_no'];
	$_serial_no			= $_POST['_serial_no'];
	$_purchase_date		= $_POST['_purchase_date'];
	$_purchase_store	= $_POST['_purchase_store'];
	$_suggest			= $_POST['_suggest'];
	$_lastupdated_by_account = $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_updateWarranty",
		$_idx,
		"$\${$_name}$\$",
		"$\${$_sex}$\$",
		"$\${$_address}$\$",
		"$\${$_city}$\$",
		"$\${$_zip_code}$\$",
		"$\${$_contact_phone}$\$",
		"$\${$_contact_hphone}$\$",
		"$\${$_contact_email}$\$",
		$_it_product,
		"$\${$_it_code}$\$",
		"$\${$_it_model_no}$\$",
		"$\${$_warranty_no}$\$",
		"$\${$_serial_no}$\$",
		"$\${$_purchase_date}$\$",
		"$\${$_purchase_store}$\$",
		"$\${$_suggest}$\$",
		"$\${$_lastupdated_by_account}$\$"
	);

	if (isZKError($result)) {
		$M->goPage(HTTP_DIR ."$currentDept/$moduleDept/detail_warranty.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR ."$currentDept/$moduleDept/detail_warranty.php?_code=$_code");

}

//========================================================================================== DEFAULT PROCESS
$sql = "
	SELECT *
	FROM ".ZKP_SQL."_tb_warranty AS a 
	LEFT JOIN ".ZKP_SQL."_tb_item AS b ON(a.it_code = b.it_code) JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
	WHERE wr_idx=$_code";
$result =& query($sql);
$column =& fetchRowAssoc($result);
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
function fillOption(target, pidx) {
	target.options.length = 1;
	for(var i = 0; i<icat.length; i++) {
		if (icat[i][1] == pidx) {
			target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
		}
	}
}

function fillItem() {
	var f = window.document.frmInsert;
	f._it_product.value = f.icat_1.value;
	f.icat_1.disabled = true;

	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 640) / 2;
	wSearchItem = window.open(
		'p_list_item.php?lastCategoryNo='+window.document.frmInsert.icat_1.value,
		'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function fillOptionInit() {
	fillOption(window.document.frmInsert.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
	$count = count($path);
	for($i = 1; $i < $count; $i++) {
		echo "\twindow.document.frmInsert.icat_$i.value = \"{$path[$i][0]}\";\n";
		if($i<=1) echo "\tfillOption(window.document.frmInsert.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function initPage() {
	fillOptionInit();
	setSelect(window.document.frmInsert.icat_1, window.document.frmInsert._it_product.value);
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
<div class="head-line">DETAIL WARRANTY</div>
<form name="frmInsert" method="POST">
<input type='hidden' name='p_mode'>
<input type="hidden" name="_idx" value="<?php echo $column["wr_idx"] ?>">
<input type="hidden" name="_it_code" value="<?php echo $column["it_code"] ?>">
<input type="hidden" name="_it_model_no" value="<?php echo $column["it_model_no"] ?>">
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><div width="50%" class="i_line">Customer Info</div></td>
		<td colspan="2" align="right" valign="bottom"><span><i><?php echo "Lastupdated by ". $column["wr_lastupdated_by_account"] . ", " . date("d-M-Y H:i:s", strtotime($column["wr_lastupdated_timestamp"])) ?></i></span></td>
	</tr>
	<tr>
		<th width="15%">CUSTOMER NAME</th>
		<td width="35%"><input type="text" name="_name" class="req" maxlength="64" style="width:100%" value="<?php echo $column["wr_cus_name"] ?>"></td>
		<th width="15%">SEX</th>
		<td>
			<input type="radio" name="_sex" value="M"<?php echo ($column["wr_cus_sex"]=='M') ? ' checked':''  ?>> Man
			<input type="radio" name="_sex" value="W"<?php echo ($column["wr_cus_sex"]=='W') ? ' checked':''  ?>> Woman
		</td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_address" class="fmt" style="width:100%" value="<?php echo $column["wr_cus_address"] ?>"></td>
	<tr>
		<th>CITY</th>
		<td><input type="text" name="_city" class="req" size="30" value="<?php echo $column["wr_cus_city"] ?>"></td>
		<th>ZIP CODE</th>
		<td><input type="text" name="_zip_code" class="fmt" size="15" maxlength="10" value="<?php echo $column["wr_cus_zip_code"] ?>"></td>
	</tr>
	<tr>
		<th>CONTACT</th>
		<td colspan="3">
			<i>
			Phone  <input type="text" name="_contact_phone" class="fmt" size="20" maxlength="32" value="<?php echo $column["wr_cus_phone"] ?>"> &nbsp;&nbsp;
			Handphone  <input type="text" name="_contact_hphone" class="req" size="20" maxlength="32" value="<?php echo $column["wr_cus_hphone"] ?>"> &nbsp;&nbsp;
			E-mail  <input type="text" name="_contact_email" class="fmt" size="30" maxlength="32" value="<?php echo $column["wr_cus_email"] ?>">
			</i>
		</td>
	</tr>
</table><br />
<div class="i_line">Purchasing Info</div>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PRODUCT</th>
		<td>
			<input type="hidden" name="_it_product" value="<?php echo $column["wr_product"] ?>">
			<select name="icat_1" disabled>
				<option value="0">==ALL==</option>
			</select>
		</td>
		<th width="15%">MODEL</th>
		<td>
			<input type="text" name="_item_no" class="req" size="5" value="<?php echo $column["it_code"] ?>" disabled>
			<input type="text" name="_model_no" class="req" size="25" value="<?php echo $column["it_model_no"] ?>" disabled>
		</td>
		<th width="15%">WARRANTY NO</th>
		<td><input type="text" name="_warranty_no" class="req" size="18" value="<?php echo $column["wr_warranty_no"] ?>"></td>
	</tr>
	<tr>
		<th>PURCHASE DATE</th>
		<td><input type="text" name="_purchase_date" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column["wr_purchase_date"])) ?>"></td>
		<th>STORE</th>
		<td><input type="text" name="_purchase_store" class="fmt" maxlength="64" style="width:100%" value="<?php echo $column["wr_purchase_store"] ?>"></td>
		<th>SERIAL NO</th>
		<td><input type="text" name="_serial_no" class="fmt" style="width:100%" value="<?php echo $column["wr_serial_no"] ?>"></td>
	</tr>
	<tr>
		<th>SUGGESTION &amp; COMPLAIN</th>
		<td colspan="3"><textarea name="_suggest" style="width:100%" rows="4"><?php echo $column["wr_suggest"] ?></textarea></td>
	</td>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:80px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete"> &nbsp; Delete</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:100px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Summary</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;
	
	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}
	
	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
			/*
				if (oForm._contact_email.value.length > 0 && !isEmailAddr(oForm._contact_email.value)) {
					alert("Please fill email column with valid email"); oForm._contact_email.focus(); return;
				} */
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}
	
	window.document.all.btnList.onclick = function() {
		window.location.href = 'summary_warranty_by_item.php';
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