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
ckperm(ZKP_INSERT, "javascript:window.close();");

if(ckperm(ZKP_INSERT, "javascript:window.close();", 'ed_info')) {
	$_pl_idx 	= $_POST['_pl_idx'];
	$_idx 		= $_POST['_idx'];
	$_it_code	= $_POST['_it_code'];
	$_it_name	= $_POST['_it_name'];
	$_it_desc	= $_POST['_it_desc'];

	foreach($_POST['_layout_date'] as $val)	$_layout_date[]	= $val;
	foreach($_POST['_exp_qty'] as $val)		$_qty[]			= str_replace(',','',$val);
	foreach($_POST['_ed_it_code'] as $val)	$_ed_it_code[]	= $val;
	foreach($_POST['_ed_date'] as $val)		$_ed_date[]		= $val;

	$_layout_date	= implode(', ', $_layout_date);
	$_qty			= implode(', ', $_qty);
	$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	$_ed_date		= '$$' . implode('$$,$$', $_ed_date) . '$$';
}

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_pl_item WHERE it_code = '$_it_code' AND pl_idx = $_pl_idx";
if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");
$column =& fetchRowAssoc($result);	
?>
<html>
<head>
<title>SET EXPIRED DATE</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">

</script>
</head>
<body style="margin:8pt">
<!--START: BODY-->
<table width="100%" class="table_box">
	<tr>
		<th width="20%">ITEM</th>
		<td><font color="#446fbe" style="font-weight:bold">[<?php echo trim($column["it_code"]) ?>]</font> <?php echo $column["plit_item"] ?></td>
	</tr>
	<tr>
		<th>DESC</th>
		<td><?php echo $column["plit_desc"] ?></td>
	</tr>
</table><br />
<form name="frmCreateED" method="POST">
<input type='hidden' name='_it_code' value='<?php echo $_it_code?>'>
<input type='hidden' name='_ed_it_code' value='<?php echo $_ed_it_code?>'>
<input type='hidden' name='_ed_date' value='<?php echo $_ed_date?>'>
<input type='hidden' name='_idx' value='<?php echo $_idx?>'>
<table width="100%" class="table_box">
	<tr>
		<th width="20%">QTY</th>
		<td><input type="text" name="_ed_qty" style="width:100%" class="fmt" value="<?php echo $_qty ?>" readonly></td>
	</tr>
	<tr>
		<th width="20%">DATE</th>
		<td><input type="text" name="_date_layout" style="width:100%" class="fmt" value="<?php echo $_layout_date ?>" readonly></td>
	</tr>
</table><br />
</form>
<table width="100%" class="table_box">
	<tr>
		<td><button name="btnBack" class="input_sky">BACK</button></td>
		<td align="right">
			<button name="btnAdd" class="input_sky">FINISH</button> &nbsp; &nbsp;
			<button name="btnCancel" class="input_sky">CANCEL</button>		
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	window.document.all.btnBack.onclick = function() {
		window.history.go(-1);
	}

	window.document.all.btnAdd.onclick = function() {
		var f	= window.document.frmCreateED;
		var o	= window.opener.document.frmInsert;

		window.opener.createED();
		window.close();
	}

	window.document.all.btnCancel.onclick = function() {
		window.close();
	}
</script>
<!--END: BODY-->
</body>
</html>