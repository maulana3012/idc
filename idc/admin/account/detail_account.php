<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: detail_account.php,v 1.4 2008/08/15 10:18:07 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "_access_local.php";
ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "list_account.php";
$ma_idx = $_REQUEST['ma_idx'];
$part = array('IDC', 'MED', 'MEP', 'ALL');

//PROCESS FORM
require_once "tpl_process_form.php";

// DEFAULT PROCESS ================================================================================
$sql = "
SELECT
	ma_idx,
	to_char(ma_regdate, 'DD-Mon-YYYY HH24:MI:SS') AS ma_regdate,
	ma_account, 
	ma_displayname,
	ma_password,
	to_char(ma_lastsignindate, 'DD-Mon-YYYY HH24:MI:SS') AS ma_lastsignindate,
	ma_remark,
	ma_isvalidacc,
	ma_numsigninfail,
	ma_numsignin,
	ma_signinfaildate,
	to_char(ma_lastpasswdchangedate, 'DD-Mon-YYYY HH24:MI:SS') AS ma_lastpasswdchangedate,
	ma_lastpasswdchangedate AS pwchangetime,
	to_char(ma_passwordblockdate, 'DD-Mon-YYYY HH24:MI:SS') AS ma_passwordblockdate,
	ma_lastpasswdissuetime,
	ma_display_as,
	ma_authority,
	ma_is_manager_all,
	ma_is_manager_idc,
	ma_is_manager_med,
	ma_is_manager_mep
FROM tb_mbracc 
WHERE ma_idx = " . $_GET['ma_idx'];

if(isZKError($result =& query($sql))) {
	$M->goErrorPage($result, HTTP_DIR . "admin/account/list_account.php");
}

if (numQueryRows($result) <= 0) {
	$o = new ZKError(
		"ACCOUNT NOT FOUND",
		"ACCOUNT NOT FOUND",
		"Cannot find the account. Account ID is " . $_GET['ma_idx']);
	$M->goErrorPage($o, HTTP_DIR . "admin/account/list_account.php");
}

$data = fetchRowAssoc($result);

//get password valid until
if (!empty($data['pwchangetime'])) {
	$result = executeSP(ZKP_SQL."_getValidPasswordPeriod", "$\${$data['ma_lastpasswdchangedate']}$\$");
} else {
	$data['ma_lastpasswdchangedate'] = "Waiting user login";
}

$sql = "SELECT gr_access, gr_idx, gr_name, 
		(select gm_perm from tb_gmember AS b where gr_access = a.gr_access AND ma_idx = $ma_idx and gr_idx = a.gr_idx) as gm_perm
		FROM tb_grade AS a
		ORDER BY gr_priority";
$result = & query($sql);
while($columns = fetchRowAssoc($result)) {
	$col["perm"][0][$columns["gr_idx"]] = $columns["gr_name"];
	$col["perm"][1][$columns["gr_idx"]][$columns["gr_access"]] = $columns["gm_perm"];
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_URL][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script type="text/javascript">
function chkEnabled(o, part) {
	var f = window.document.frmGroupPerm;
	var e = window.document.frmGroupPerm.elements;
	var idx	= 2 + (part*77);	/////

	if(e(idx).checked) {	// Manager 
		for(j=2; j<77; j++) {
			if(j<7) { e(idx+j).disabled = false; }
			else 	{ e(idx+j).disabled = true; e(idx+j).checked = false; }
		}
	} else {				// Admin
		for(j=2; j<77; j++) {
			if(j<7) { e(idx+j).disabled = true; e(idx+j).checked = false; }
			else 	{ e(idx+j).disabled = false; }
		}
	}
}

function defaultLoadChk() {
	var f = window.document.frmGroupPerm;
	var e = window.document.frmGroupPerm.elements;

	for(var i=0; i<4; i++) {
		var idx	= 2 + (i*77);	/////
		if(e(idx).checked) {	// Manager 
			for(j=2; j<77; j++) {
				if(j<7) { e(idx+j).disabled = false; }
				else 	{ e(idx+j).disabled = true; e(idx+j).checked = false; }
			}
		} else {				// Admin
			for(j=2; j<77; j++) {
				if(j<7) { 
				e(idx+j).disabled = true; e(idx+j).checked = false; 
				}
				else 	{ e(idx+j).disabled = false; }
			}
		}
	}
}

function initPage() {
	//defaultLoadChk();
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()"> 
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
<!--START BODY-->
<span class="bar"><font size="3em">I . Basic Information</font></span>
<hr style="color:#c0c0c0">
<form method="post" action="" name="frmAdd">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="ma_idx" value="<?php echo $_GET['ma_idx']?>">
<table class="table_box" width="100%">
	<tr>
		<th width="15%">ACCOUNT</th>
		<td width="45%"><?php echo trim($data['ma_account'])?></td>
		<th width="15%" rowspan="3">REMARK</th>
		<td rowspan="3"><textarea name="txtRemark" rows="3" cols="50"><?php echo $data['ma_remark']?></textarea></td>
	</tr>
	<tr>
		<th>USER NAME</th>
		<td><input type="text" name="txtUsrName" class="input_sky" maxlength="32" size="20" value="<?php echo $data['ma_displayname']?>" readonly> <span class="comment">*ReadOnly</span></td>
	</tr>
	<tr>
		<th>REG/ DATE</th>
		<td><?php echo $data['ma_regdate']?></td>
	</tr>
	<tr>
        <th>CASE</th>
		<td colspan="2">
			<input type="checkbox" name="chkDisplayAs[]" id="1" value="1" <?php echo ($data['ma_display_as'] & 1)? "checked":""?>><label for="1"> Marketing IDC &nbsp; </label>
            <input type="checkbox" name="chkDisplayAs[]" id="2" value="2" <?php echo ($data['ma_display_as'] & 2)? "checked":""?>><label for="2"> Marketing MED &nbsp; </label>
			<input type="checkbox" name="chkDisplayAs[]" id="3" value="4" <?php echo ($data['ma_display_as'] & 4)? "checked":""?>><label for="3"> See all marketing</label>
		</td>
		<td align="right" valign="bottom">
			<button name="btnSave" class="input_btn" style="width:150px;height:30px" onClick="if(verify(document.frmAdd)){document.frmAdd.submit()}"><img src="../../_images/icon/btnSave-blue.gif"> &nbsp; Update remark</button>&nbsp;
			<button name="btnDeleteAccount" class="input_red" style="width:150px;height:30px"><img src="../../_images/icon/trash.gif"> &nbsp; Delete account</button>
		</td>
	</tr>
</table><br /><br />
</form>
<script language="javascript" type="text/javascript">
	window.document.frmAdd.btnDeleteAccount.onclick = function() {
		window.document.frmAdd.p_mode.value = "deleteAccount";
		if(confirm("Are you sure to delete user account?")) {
			window.document.frmAdd.submit();
		}
	}
</script>
<span class="bar"><font size="3em">II . Password Management</font></span>
<hr style="color:#c0c0c0">
<?php if($data['ma_isvalidacc'] == "f") { //if account is not available (passowrd is blocked) ?>
<form name="frmActivate" method="post">
	<input type="hidden" name="p_mode" value="activate">
	<input type="hidden" name="ma_idx" value="<?php echo $ma_idx?>">
</form>
<table width="100%" class="table_box">
	<tr>
		<th width="150">PW BLOCK DATE</th>
		<td><?php echo empty($data['ma_passwordblockdate']) ? "Waiting user login" : $data['ma_passwordblockdate']?></td>
		<td align="center" width="5%">
			<button name="btnActivate" class="input_btn" style="width:150px;height:30px" onClick="if(confirm('Are you sure to activate?')){document.frmActivate.submit();}"><img src="../../_images/icon/user-visible.gif"> &nbsp; Activate password</button>
		</td>
	</tr>
</table><br /><br />
<?php } else { //if account is valid, show the account information ?>
<form name="frmBlock" method="post">
	<input type="hidden" name="p_mode" value="block">
	<input type="hidden" name="ma_idx" value="<?php echo $ma_idx?>">
</form>
<table width="50%" class="table_box">
	<tr>
		<th width="50%">LAST LOG-IN DATE</th>
		<td><?php echo empty($data['ma_lastsignindate'])?"Waiting user login":$data['ma_lastsignindate']?></td>
	</tr>
	<tr>
		<th>LAST PW CHANGE DATE</th>
		<td><?php echo empty($data['ma_lastpasswdchangedate'])?"Waiting user login":$data['ma_lastpasswdchangedate']?></td>
	</tr>
	<tr>
		<th>PASSWORD VALID UNTIL</th>
		<td valign="middle">
			<?php echo $data['ma_lastpasswdchangedate']?> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
			<button name="btnBlockPw" class="input_btn" style="width:150px;height:30px" onClick="if(confirm('Are you sure to block this account?')){document.frmBlock.submit();}"><img src="../../_images/icon/user-block.gif"> &nbsp; Block password</button>
		</td>
	</tr>
</table><br /><br />
<?php } ?>
<span class="bar"><font size="3em">III . Special Authority</font></span>
<hr style="color:#c0c0c0">
<form name="frmSpecialAuthority" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_idx" value="<?php echo $ma_idx?>">
<table width="100%" class="table_l" cellspacing="1">
	<tr>
		<th width="15%">DEPARTMENT</th>
		<td>
			<input type="checkbox" name="_auth[]" value="1"<?php echo ($data['ma_authority'] & 1) ? ' checked':'' ?>> See hidden invoice &nbsp;
			<input type="checkbox" name="_auth[]" value="2"<?php echo ($data['ma_authority'] & 2) ? ' checked':'' ?>> Change type invoice (on order &amp; billing) &nbsp;
			<input type="checkbox" name="_auth[]" value="4"<?php echo ($data['ma_authority'] & 4) ? ' checked':'' ?>> Delete IO billing &nbsp;
			<input type="checkbox" name="_auth[]" value="8"<?php echo ($data['ma_authority'] & 8) ? ' checked':'' ?>> Unconfirm delivery charge &nbsp;
			<input type="checkbox" name="_auth[]" value="16"<?php echo ($data['ma_authority'] & 16) ? ' checked':'' ?>> Revise billing price after warehouse confirm
		</td>
	</tr>
	<tr>
		<th>PURCHASING</th>
		<td>
			<input type="checkbox" name="_auth[]" value="2048"<?php echo ($data['ma_authority'] & 2048) ? ' checked':'' ?>> Unconfirm initial stock
		</td>
	</tr>
	<tr>
		<th>WAREHOUSE</th>
		<td>
			<input type="checkbox" name="_auth[]" value="32"<?php echo ($data['ma_authority'] & 32) ? ' checked':'' ?>> Revise arrival PL &nbsp;
			<input type="checkbox" name="_auth[]" value="64"<?php echo ($data['ma_authority'] & 64) ? ' checked':'' ?>> Unconfirm DO &nbsp;
			<input type="checkbox" name="_auth[]" value="128"<?php echo ($data['ma_authority'] & 128) ? ' checked':'' ?>> Unconfirm Return
		</td>
	</tr>
	<tr>
		<th>MARKETING</th>
		<td>
			<input type="checkbox" name="_auth[]" value="256"<?php echo ($data['ma_authority'] & 256) ? ' checked':'' ?>> Unconfirm receipt demo &nbsp;
			<input type="checkbox" name="_auth[]" value="512"<?php echo ($data['ma_authority'] & 512) ? ' checked':'' ?>> Unconfirm outgoing demo &nbsp;
			<input type="checkbox" name="_auth[]" value="1024"<?php echo ($data['ma_authority'] & 1024) ? ' checked':'' ?>> Unconfirm return demo &nbsp;
		</td>
	</tr>
</table><br />
<div align="right">
	<button name="btnUpdate" class="input_btn" style="width:150px;height:30px"><img src="../../_images/icon/setting_mini.gif"> &nbsp; Update authority</button>
</div><br />
</form>
<script language="javascript" type="text/javascript">
	window.document.frmSpecialAuthority.btnUpdate.onclick = function() {
		window.document.frmSpecialAuthority.p_mode.value = "updateAuth";
		if(confirm("Are you sure to update the authority?")) {
			window.document.frmSpecialAuthority.submit();
		}
	}
</script>
<span class="bar"><font size="3em">IV . Group Management</font></span>
<hr style="color:#c0c0c0">
<form name="frmGroupPerm" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_idx" value="<?php echo $ma_idx?>">
<table width="100%" class="table_box">

		<th width="15%" rowspan="4">GRADE</th>
		<td width="10%">ALL</td>
		<td>
			<input type="radio" name="_gr_grade_all" value="true"<?php echo ($data["ma_is_manager_all"]=="t") ? " checked":"" ?>> General Manager &nbsp;
			<input type="radio" name="_gr_grade_all" value="false"<?php echo ($data["ma_is_manager_all"]=="f") ? " checked":"" ?>> Admin
		</td>
	</tr>
	<tr>
		<td>IDC</td>
		<td>
			<input type="radio" name="_gr_grade_idc" value="true"<?php echo ($data["ma_is_manager_idc"]=="t") ? " checked":"" ?>> General Manager &nbsp;
			<input type="radio" name="_gr_grade_idc" value="false"<?php echo ($data["ma_is_manager_idc"]=="f") ? " checked":"" ?>> Admin
		</td>
	</tr>
	<tr>
		<td>MED</td>
		<td>
			<input type="radio" name="_gr_grade_med" value="true"<?php echo ($data["ma_is_manager_med"]=="t") ? " checked":"" ?>> General Manager &nbsp;
			<input type="radio" name="_gr_grade_med" value="false"<?php echo ($data["ma_is_manager_med"]=="f") ? " checked":"" ?>> Admin
		</td>
	</tr>
	<tr>
		<td>MEP</td>
		<td>
			<input type="radio" name="_gr_grade_mep" value="true"<?php echo ($data["ma_is_manager_mep"]=="t") ? " checked":"" ?>> General Manager &nbsp;
			<input type="radio" name="_gr_grade_mep" value="false"<?php echo ($data["ma_is_manager_mep"]=="f") ? " checked":"" ?>> Admin
		</td>
	</tr>
</table>
<table width="100%" class="table_l">
	<tr height="20px">
		<th rowspan="2">GROUP PERMISSION</th>
		<th colspan="4">IDC</th><th width="5%">&nbsp;</th>
		<th colspan="4">MED</th><th width="5%">&nbsp;</th>
		<th colspan="4">MEP</th><th width="5%">&nbsp;</th>
		<th colspan="4">ALL</th><th width="1%">&nbsp;</th>
	</tr>
		<td width="3%"></td><td width="3%"></td><td width="3%"></td><td width="3%"></td><td></td>
		<td width="3%"></td><td width="3%"></td><td width="3%"></td><td width="3%"></td><td></td>
		<td width="3%"></td><td width="3%"></td><td width="3%"></td><td width="3%"></td><td></td>
		<td width="3%"></td><td width="3%"></td><td width="3%"></td><td width="3%"></td><td></td>
	<tr>
	</tr>
<?php 
/*
echo "<pre>";
var_dump($col["perm"][1]);
echo "</pre>";
*/
?>
<?php foreach($col["perm"][0] AS $key => $val) { ?>
<?php if($key == 130) { ?>
	<tr height="10px">
		<th colspan="21"></th>
	</tr>
<?php } ?>
	<tr>
		<td><?php echo $val ?></td>
		<?php for($i=0; $i<count($part); $i++) { ?>
		<td><?php if(isset($col["perm"][1][$key][$part[$i]])) { ?><input type="checkbox" name="chkPerm_<?php echo $part[$i]."_".$key ?>[]" value="1"<?php echo (($col["perm"][1][$key][$part[$i]] & 1)? " checked":"")?>><?php } ?></td>
		<td><?php if(isset($col["perm"][1][$key][$part[$i]])) { ?><input type="checkbox" name="chkPerm_<?php echo $part[$i]."_".$key ?>[]" value="2"<?php echo (($col["perm"][1][$key][$part[$i]] & 2)? " checked":"")?>><?php } ?></td>
		<td><?php if(isset($col["perm"][1][$key][$part[$i]])) { ?><input type="checkbox" name="chkPerm_<?php echo $part[$i]."_".$key ?>[]" value="4"<?php echo (($col["perm"][1][$key][$part[$i]] & 4)? " checked":"")?>><?php } ?></td>
		<td><?php if(isset($col["perm"][1][$key][$part[$i]])) { ?><input type="checkbox" name="chkPerm_<?php echo $part[$i]."_".$key ?>[]" value="8"<?php echo (($col["perm"][1][$key][$part[$i]] & 8)? " checked":"")?>><?php } ?></td>
		<th><input type="hidden" name="_gr_idx_<?php echo $part[$i] ?>[]" value="<?php echo $key ?>"></th>		
		<?php } ?>
	</tr>
<?php } ?>
</table>
<!--START Button-->
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="right">
			<button name="btnUpdate" class="input_btn" style="width:150px;height:30px"><img src="../../_images/icon/setting_mini.gif"> &nbsp; Update permission</button>
		</td>
	</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm1 = window.document.frmGroupPerm;

	//Update permission
	oForm1.btnUpdate.onclick = function() {
		if (confirm("Are you sure to update this permission?")) {
			if(verify(oForm1)){
				oForm1.p_mode.value = 'updatePerm';
				oForm1.submit();
			}
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