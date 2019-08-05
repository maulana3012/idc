<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: p_group_member_list.php,v 1.1 2008/04/28 06:52:12 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "javascript:window.close();");

$_access = $_GET['_access'];
$_idx = $_GET['_idx'];
$_name = urldecode($_GET['_name']);

//PROCESS FORM
require_once "tpl_process_form.php";

// DEFAULT PROCESS ================================================================================
$sql = "
SELECT 
	a.gr_idx, 
	a.gm_perm, 
	b.ma_idx, 
	b.ma_account, 
	b.ma_displayname, 
	b.ma_isvalidacc
FROM 
	tb_gmember AS a 
	INNER JOIN tb_mbracc AS b ON (a.ma_idx = b.ma_idx)
WHERE gr_access='$_access' AND a.gr_idx = $_idx AND gm_perm>0 ORDER BY b.ma_account";

if(isZKError($result1 =& query($sql))) {
	$M->goErrorPage($result1, "#", 2);
}

$arrMemberList = array();
while ($columns = fetchRow($result1)) {
	$arrMemberList[] = $columns[2];
}
?>
<html>
<head>
<title>MEMBER LIST IN GROUP</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc_kr">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body style="margin:8px;">
<h4>[GROUP CODE : <?php echo $_idx;?>] <?php echo $_name;?> : Member List</h4>
<fieldset>
	<legend> <img src="../../_images/icon/group.gif"> ADD NEW MEMBER IN GROUP <u><?php echo $_name;?></u></legend>
	<form name="frmAddMember" method="post">
	<input type="hidden" name="p_mode">
	<input type="hidden" name="_idx" value="<?php echo $_idx?>">
	<input type="hidden" name="_name" value="<?php echo $_name?>">
	<input type="hidden" name="_access" value="<?php echo $_access?>">
	<table width="100%" class="table_no" align="center">
		<tr>
			<td>MEMBER : <select name="lstNewMember">
<?php
if(count($arrMemberList)>0) {
	$sql = "SELECT ma_idx, ma_account, ma_displayname FROM tb_mbracc WHERE ma_idx NOT IN(".implode(", ",$arrMemberList).") AND ma_isvalidacc = 't' ORDER BY ma_displayname";
} else {
	$sql = "SELECT ma_idx, ma_account, ma_displayname FROM tb_mbracc ORDER BY ma_displayname";
}
$result =& query($sql);
print "\t\t<option value=\"\">==SELECT==</option>\n";
while ($columns = fetchRow($result)) {
	print "\t\t<option value=\"$columns[0]\">$columns[2] ($columns[1])</option>\n";
}
?>
			</select> &nbsp;
			<input type="checkbox" name="chkNewPerm[]" value="2">INPUT,&nbsp;
			<input type="checkbox" name="chkNewPerm[]" value="4">UPDATE,&nbsp;
			<input type="checkbox" name="chkNewPerm[]" value="8">DELETE &nbsp;
			<button name='btnAdd' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Add new member"></button>&nbsp;	
			</td>
		</tr>
	</table>
	</form>
</fieldset>
<br/>
<form name="frmMemberPermission" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_idx" value="<?php echo $_idx?>">
<input type="hidden" name="_name" value="<?php echo $_name?>">
<input type="hidden" name="_access" value="<?php echo $_access?>">
<table width="100%" class="table_f" align="center">
	<tr>
	  <th width="5%" rowspan="2" align="center"><input type="checkbox" name="chkAll"></th>
		<th width="5%" rowspan="2">NO</th>
		<th width="17%" rowspan="2">ACCOUNT</th>
		<th width="37%" rowspan="2">USER NAME</th>
		<th colspan="4">PERSONAL PERMISSION</th>
	</tr>
	<tr>
		<th width="9%">INQUIRY</th>
		<th width="9%">INPUT</th>
		<th width="9%">UPDATE</th>
		<th width="9%">DELETE</th>
	</tr>
<?php
$i = 1;
pg_result_seek($result1, 0);
while($columns = fetchRow($result1)) {
?>
	<tr>
		<td align="center"><input type="checkbox" name="chk[]" value="<?php echo $columns[2]?>"></td>
		<td align="center"><?php echo $i++?></td>
		<td><?php echo $columns[3]?></td><!--account-->
		<td><?php echo $columns[4]?></td><!--User Name-->
		<td align="center">
			<input type="checkbox" name="chkPerm_<?php echo $columns[2]?>[]" value="1" checked disabled></td>
		<td align="center">
			<input type="checkbox" name="chkPerm_<?php echo $columns[2]?>[]" value="2"<?php echo ($columns[1] & 2)? " checked":""?>></td>
		<td align="center">
			<input type="checkbox" name="chkPerm_<?php echo $columns[2]?>[]" value="4"<?php echo ($columns[1] & 4)? " checked":""?>></td>
		<td align="center">
			<input type="checkbox" name="chkPerm_<?php echo $columns[2]?>[]" value="8"<?php echo ($columns[1] & 8)? " checked":""?>></td>
	</tr>
<?php } ?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td><button name='btnDeleteGroup' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete selected member"></button></td>
		<td align="right">
			<button name='btnUpdate' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/update.gif" align="middle" alt="Update permission of selected member"></button>
			<button name='btnClose' class='input_sky' style='width:60px;height:30px' onClick="window.close();"><img src="../../_images/icon/close.gif" align="middle" alt="Close pop-up"></button>
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2">
			<span class="comment"><i>* Please check the member first before you update the permission</i></span>
		</td>
	</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm1 = window.document.frmMemberPermission;
	var oForm2 = window.document.frmAddMember;

	//Add new group
	oForm2.btnAdd.onclick = function() {
		if (oForm2.lstNewMember.value == "") {
			alert("Please select the member first");
		} else {
			oForm2.p_mode.value = "insertMember";
			oForm2.submit();
		}
	}

	//Check all rows
	oForm1.chkAll.onclick = function() {
		var oCheck = oForm1.all.tags("INPUT");

		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck[i].name == "chk[]") {
				oCheck[i].checked = this.checked;
			}
		}
	}
	
	//Remove group
	oForm1.btnDeleteGroup.onclick = function() {
		var oCheck = oForm1.all.tags("INPUT");
		var selectedItem = new Array();
		var counter = 0;
		
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chk[]" && oCheck(i).checked) {
				selectedItem[counter++] = oCheck[i].value;
			}
		}

		if(selectedItem.length > 0) {
			if (confirm("Are you sure to remove this member")) {
				oForm1.p_mode.value = "deleteMember";
				oForm1.submit();
			}
		} else {
			alert("Please select the member that you want to delete");
		}
	}
	
	//Update permission
	oForm1.btnUpdate.onclick = function() {
		var oCheck = oForm1.all.tags("INPUT");
		var selectedItem = new Array();
		var counter = 0;
		
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chk[]" && oCheck(i).checked) {
				selectedItem[counter++] = oCheck[i].value;
			}
		}

		if(selectedItem.length > 0) {
			if (confirm("Are you sure to update this permission?")) {
				if(verify(oForm1)){
					oForm1.p_mode.value = 'updatePermPopUp';
					oForm1.submit();
				}
			}
		} else {
			alert("Please select the group that you want to delete");
		}
	}
</script>
</body>
</html>