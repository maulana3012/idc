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
$left_loc	= "input_warranty.php";

//========================================================================================== INSERT PROCESS
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_warranty.php", "insert")) {

	$_cus_name		= isset($_POST['_cus_name']) ? $_POST['_cus_name'] : '';
	$_cus_sex		= isset($_POST['_cus_sex']) ? $_POST['_cus_sex'] : '';
	$_cus_address	= isset($_POST['_cus_address']) ? $_POST['_cus_address'] : '';
	$_cus_city		= isset($_POST['_cus_city']) ? $_POST['_cus_city'] : '';
	$_cus_zip_code	= isset($_POST['_cus_zip_code']) ? $_POST['_cus_zip_code'] : '';
	$_cus_phone		= isset($_POST['_cus_phone']) ? $_POST['_cus_phone'] : '';
	$_cus_hphone	= isset($_POST['_cus_hphone']) ? $_POST['_cus_hphone'] : '';
	$_cus_email		= isset($_POST['_cus_email']) ? $_POST['_cus_email'] : '';

	$_it_product		= $_POST['_it_product'];
	$_it_code			= $_POST['_it_code'];
	$_it_model_no		= $_POST['_it_model_no'];
	$_warranty_no		= $_POST['_warranty_no'];
	$_serial_no			= $_POST['_serial_no'];
	$_purchase_date		= $_POST['_purchase_date'];
	$_purchase_store	= $_POST['_purchase_store'];
	$_suggest			= $_POST['_suggest'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_go_page 			= $_POST['_next'];

	$result = executeSP(
		ZKP_SQL."_insertWarranty",
		"$\${$_cus_name}$\$",
		"$\${$_cus_sex}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_cus_city}$\$",
		"$\${$_cus_zip_code}$\$",
		"$\${$_cus_phone}$\$",
		"$\${$_cus_hphone}$\$",
		"$\${$_cus_email}$\$",
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
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_warranty.php");
	}
	$M->goPage($_go_page);

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
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
function checkForm(o, next) {
	if (o._cus_email.value.length > 0 && !isEmailAddr(o._cus_email.value)) {
		alert("Please fill email column with valid email"); o._cus_email.focus(); return;
	}

	if(verify(o)){
		if(confirm("Are you sure to save the warranty card?")) {
			o._next.value = next;
			o.submit();
		}
	}
}

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

	if(f.icat_1.value == 0) {
		alert("Please choose product first");return;
	}

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
		if($i<=2) echo "\tfillOption(window.document.frmInsert.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function initPage() {
	fillOptionInit();
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
<div class="head-line">NEW WARRANTY</div>
<form name="frmInsert" method="POST">
<input type='hidden' name='p_mode' value="insert">
<input type='hidden' name='_next'>
<div class="i_line">Customer Info</div>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">NAME</th>
		<td width="35%"><input type="text" name="_cus_name" class="req" style="width:100%" maxlength="128"></td>
		<th width="12%">SEX</th>
		<td>
			<input type="radio" name="_cus_sex" value="M" checked> Man
			<input type="radio" name="_cus_sex" value="W"> Woman
		</td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt" style="width:100%"></td>
	</tr>
	<tr>
		<th>CITY</th>
		<td><input type="text" name="_cus_city" class="req" size="30"></td>
		<th>ZIP CODE</th>
		<td><input type="text" name="_cus_zip_code" class="fmt" size="15" maxlength="10"></td>
	</tr>
	<tr>
		<th>CONTACT</th>
		<td colspan="3">
			<i>
			Phone  <input type="text" name="_cus_phone" class="fmt" size="20" maxlength="32"><i> &nbsp;&nbsp;
			Handphone  <input type="text" name="_cus_hphone" class="req" size="20" maxlength="32"><i> &nbsp;&nbsp;
			E-mail  <input type="text" name="_cus_email" class="fmt" size="30" maxlength="32"><i>
			</i>
		</td>
	</tr>
</table><br />
<div class="i_line">Purchasing Info</div>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PRODUCT</th>
		<td>
			<input type="hidden" name="_it_product">
			<select name="icat_1">
				<option value="0">==ALL==</option>
			</select>
		</td>
		<th width="15%">MODEL</th>
		<td>
			<input type="text" name="_it_code" class="req" size="5" onclick="fillItem()" readonly>
			<input type="text" name="_it_model_no" class="fmt" size="25" onclick="fillItem()" readonly>
		</td>
		<th width="15%">WARRANTY NO</th>
		<td><input type="text" name="_warranty_no" class="req" size="18"></td>
	</tr>
	<tr>
		<th>PURCHASE DATE</th>
		<td><input type="text" name="_purchase_date" class="reqd" size="15"></td>
		<th>STORE</th>
		<td><input type="text" name="_purchase_store" class="fmt" maxlength="64" style="width:100%"></td>
		<th>SERIAL NO</th>
		<td><input type="text" name="_serial_no" class="fmt" size="18"></td>
	</tr>
	<tr>
		<th>SUGGESTION &amp; COMPLAIN</th>
		<td colspan="3"><textarea name="_suggest" style="width:100%" rows="4"></textarea></td>
	</td>
</table>
</form>
<p align="center">
	<button name='btnSave' class='input_btn' style='width:130px;' onclick="checkForm(window.document.frmInsert, 'input_warranty.php')"><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save Warranty"> &nbsp; Save &amp; Add</button>&nbsp;
	<button name='btnSave' class='input_btn' style='width:150px;' onclick="checkForm(window.document.frmInsert, 'summary_warranty_by_item.php')"><img src="../../_images/icon/btnSave-black.gif" width="15px" align="middle" alt="Save Warranty"> &nbsp; Save Warranty</button>&nbsp;
	<button name='btnList' class='input_btn' style='width:150px;' onclick="window.location.href='summary_warranty_by_personal_info.php'"><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list Warranty"> &nbsp; List Warranty</button>
</p>
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