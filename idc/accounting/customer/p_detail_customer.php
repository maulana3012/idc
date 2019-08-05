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
require_once "../../_system/util_html.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "javascript:window.close();");

//GLOBAL
$_code		= $_GET['_code'];

//UPDATE CUSTOMER =====================================================================================================
if(ckperm(ZKP_UPDATE, "javascript:window.close();", 'update')) {
	$_code = $_POST['_code'];
	$_name = $_POST['_name'];
	$_type_of_biz = $_POST['_type_of_biz'];
	$_tax_code_status = $_POST['_tax_code_status'];

	$sql = "UPDATE ".ZKP_SQL."_tb_customer SET
				cus_full_name = '$_name',
				cus_type_of_biz = '$_type_of_biz',
				cus_tax_code_status = '$_tax_code_status'
			WHERE cus_code = '$_code'";
	if (isZKError($result =& query($sql))) {
		$M->goErrorPage($result, "p_detail_customer.php?_code=$_code");
	}
	$M->goPage("p_detail_customer.php?_code=$_code");
}


//========================================================================================== DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_code'";

if (isZkError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);
$column['cus_since'] = empty($column['cus_since']) ? "" : date("j-M-Y", strtotime($column['cus_since']));
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>DETAIL CUSTOMER</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="javascript1.2" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<table width="100%" cellpadding="0">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting_mini.gif">&nbsp;&nbsp;<strong>Detail Customer</strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table><br />
<form name="frmUpdate" method="post">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['cus_code']?>">
<table width="100%" class="table_l">
	<tr>
		<th width="25%">CODE</th>
		<td colspan="3"><b><?php echo trim($column['cus_code'])?></b></td>
	</tr>
	<tr>
		<th>NAME</th>
		<td><input type="text" name="_name" class="req" style="width:100%" value="<?php echo $column['cus_full_name']?>"></td>
	</tr>
	<tr>
		<th>NPWP</th>
		<td>
			<input type="text" name="_type_of_biz" value="<?php echo $column['cus_type_of_biz'];?>" class="fmt" size="30">
			<input type="radio" name="_tax_code_status" value="1"<?php echo ($column['cus_tax_code_status']=='1')?" checked":"" ?>>1 &nbsp;
			<input type="radio" name="_tax_code_status" value="2"<?php echo ($column['cus_tax_code_status']=='2')?" checked":"" ?>>2 &nbsp;
			<input type="radio" name="_tax_code_status" value="3"<?php echo ($column['cus_tax_code_status']=='3')?" checked":"" ?>>3 &nbsp;
			<input type="radio" name="_tax_code_status" value="7"<?php echo ($column['cus_tax_code_status']=='7')?" checked":"" ?>>7
		</td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name='btnUpdate' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/update.gif" align="middle"></button>&nbsp;
			<button name='btnClose' class='input_sky' style='width:60px;height:30px' onClick="window.close();"><img src="../../_images/icon/delete_2.gif" style="width:17px" align="middle"></button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;

	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnClose.onclick = function() {
		window.opener.location.reload();
		window.close();
	}
</script>
</body>
</html>