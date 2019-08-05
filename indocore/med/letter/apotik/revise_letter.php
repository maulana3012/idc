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
ckperm(ZKP_SELECT, "index.php");

//GLOBAL
$left_loc	= "list_letter.php";
$_code = $_GET["_code"];

//PROCESS FORM
require_once "tpl_process_form.php"; 

//========================================================================================== DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_letter WHERE lt_reg_no = '$_code'";
$result =& query($sql);
$column =& fetchRowAssoc($result);
$file_sql = "SELECT * FROM ".ZKP_SQL."_tb_letter_file WHERE lt_reg_no = '$_code' ORDER BY ltf_file_name";
$file_res =& query($file_sql);

if(numQueryRows($result) <= 0) {
	goPage("list_letter.php");
} else if($column['lt_status_of_letter'] != "1"){
	goPage("detail_letter.php?_code=$_code");
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script type="text/javascript">
function submitForm(type, file, idx, path) {
	if(type=='A'){
		if (window.row_copy_letter.rows.length == 1) {
			alert("You need to leave at least 1 file");
			f.btnAddLetter.focus();
			return;
		}
	}

	var o = window.document.frmUpdate;
	if(confirm("Are you sure to delete file "+file+" ?")) {
		if(verify(o)){
			o._del_file_idx.value = idx;
			o._del_file_path.value = path;
			o.p_mode.value = 'deleteFile';
			o.submit();
		}
	}
}

function enabledText(val) {
	if(val == '2') {
		window.document.frmUpdate._reg_confirmed_date.disabled = false;
		window.document.frmUpdate._reg_confirmed_date.className = 'reqd';
		window.document.frmUpdate._reg_confirmed_date.value = '<?php echo date('d-M-Y') ?>';
		window.document.frmUpdate._reg_cancelled_reason.disabled = true;
		window.document.frmUpdate._reg_cancelled_reason.className = 'fmt';
		window.document.frmUpdate._reg_cancelled_reason.value = '';
	} else if (val == '3') {
		window.document.frmUpdate._reg_cancelled_reason.disabled = false;	
		window.document.frmUpdate._reg_cancelled_reason.className = 'req';
		window.document.frmUpdate._reg_confirmed_date.disabled = true;
		window.document.frmUpdate._reg_confirmed_date.className = 'fmt';
		window.document.frmUpdate._reg_confirmed_date.value = '';
	} else {
		window.document.frmUpdate._reg_confirmed_date.disabled = true;
		window.document.frmUpdate._reg_confirmed_date.className = 'fmtd';
		window.document.frmUpdate._reg_confirmed_date.value = '';
		window.document.frmUpdate._reg_cancelled_reason.disabled = true;	
		window.document.frmUpdate._reg_cancelled_reason.className = 'fmt';
		window.document.frmUpdate._reg_cancelled_reason.value = '';
	}
}

function initPage() {
	setSelect(window.document.all.cboRegType, "<?php echo $column['lt_type_of_letter'] ?>");	
	setSelect(window.document.all.cboRegStatus, "<?php echo $column['lt_status_of_letter'] ?>");	
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
<form name="frmUpdate" method="POST" enctype="multipart/form-data">
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $_code ?>">
<input type='hidden' name='_del_file_idx'>
<input type='hidden' name='_del_file_path'>
<div class="head-line">[ <font color="#446fbe"><?php echo strtoupper($currentDept) ?></font> ] Revise Official Letter</div>
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><div class="i_line">Letter Info</div></td>
		<td colspan="4" align="right" valign="bottom"><span class="comment"><i><?php echo "Lastupdated by ". $column["lt_lastupdated_by_account"] . ", " . date("d-M-Y H:i:s", strtotime($column["lt_lastupdated_timestamp"])) ?></i></span></td>
	</tr>
	<tr>
		<th width="15%">REG. NO</th>
		<td width="20%"><b><?php echo $_code ?><b></td>
		<th width="15%">REG. DATE</th>
		<td width="20%"><input type="text" name="_reg_date" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column["lt_reg_date"])) ?>"></td>
		<th width="15%">ISSUED BY</th>
		<td><input type="text" name="_reg_issued_by" class="req" size="15" value="<?php echo $column["lt_issued_by"] ?>"></td>
	</tr>
		<th>TYPE OF LETTER</th>
		<td>
			<select name="cboRegType" disabled>
				<option value="T">TENDER</option>
				<option value="Q">QUOTATION</option>
				<option value="B">BUSINESS</option>
				<option value="O">OTHERS</option>
			</select>
		</td>
		<th>SEND TO</th>
		<td colspan="3"><input type="text" name="_reg_send_to" class="fmt" style="width:100%" value="<?php echo $column["lt_send_to"] ?>"></td>
	</tr>
	<tr>
		<th>COPY OF LETTER</th>
		<td colspan="5">
			<table width="100%" class="table_box">
			  <tr>
				<td width="5%" valign="top"><button name="btnAddLetter" class="input_sky" style="color:#003d78;width:100%;">+</button></td>
				<td>
				  <table width="100%" class="table_box">
				    <tr>
					 <td></td>
					 <td width="5%"></td>
					</tr>
					<tbody id="copy_letter">
					</tbody>
					<tfoot id="row_copy_letter">
					<?php 
					while($rows =& fetchRowAssoc($file_res)) { 
						if($rows["ltf_type"]=='A') {
					?>
					  <tr id="<?php echo $rows["ltf_idx"] ?>">
						<td colspan="2">
							<span class="<?php echo $rows["ltf_file_type"] ?>"><?php echo $rows["ltf_file_name"] ?></span> &nbsp;&nbsp; 
							<a href="javascript:submitForm(<?php echo "'A','".$rows["ltf_file_name"]."', ".$rows["ltf_idx"].", '".$rows["ltf_file_path"]."'" ?>)"><img src="../../_images/icon/delete.gif"></a>
						</td>
					  </tr>
					<?php }} ?>
					</tfoot>
				  </table>
				</td>
			  </tr>
			</table>
		</td>
	</tr>
	<tr>
		<th>ATTACHMENT</th>
		<td colspan="5">
			<table width="100%" class="table_box">
			  <tr>
				<td width="5%" valign="top" ><button name="btnAddAttachment" class="input_sky" style="color:#003d78;width:100%">+</button></td>
				<td>
				  <table width="100%" class="table_box">
				    <tr>
					 <td width="25%"></td>
					 <td></td>
					 <td width="5%"></td>
					</tr>
					<tbody id="attachment">
					</tbody>
					<tfoot id="row_attachment">
					<?php 
					pg_result_seek($file_res,0);
					while($rows =& fetchRowAssoc($file_res)) { 
						if($rows["ltf_type"]=='B') {
					?>
					  <tr id="<?php echo $rows["ltf_idx"] ?>">
						<td colspan="2">
							<span class="<?php echo $rows["ltf_file_type"] ?>"><?php echo $rows["ltf_file_name"] ?></span> &nbsp;&nbsp; 
							<a href="javascript:submitForm(<?php echo "'B','".$rows["ltf_file_name"]."', ".$rows["ltf_idx"].", '".$rows["ltf_file_path"]."'" ?>)"><img src="../../_images/icon/delete.gif"></a>
						</td>
					  </tr>
					<?php }} ?>
					</tfoot>
				  </table>
				</td>
			  </tr>
			</table>
		</td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><input type="text" name="_reg_remark" class="fmt" style="width:100%" value="<?php echo $column["lt_remark"] ?>"></td>
	</tr>
	<tr>
		<th>BRIEF SUMMARY</th>
		<td colspan="5"><textarea name="_reg_brief_summary" rows="4" class="req" style="width:100%"><?php echo $column["lt_brief_summary"] ?></textarea></td>
	</tr>
	<tr>
		<th>STATUS</th>
		<td>
			<select name="cboRegStatus" onchange="enabledText(this.value)">
				<option value="1">ON PROCESS</option>
				<option value="2">CONFIRMED</option>
				<option value="3">CANCELLED</option>
			</select>
		</td>
		<th>CONFIRM DATE</th>
		<td><input type="text" name="_reg_confirmed_date" class="fmtd" size="15" disabled value="<?php echo ($column["lt_confirm_date"] != "") ? date('d-M-Y', strtotime($column["lt_confirm_date"])):"" ?>"></td>
	</tr>
	<tr>
		<th>REASON</th>
		<td colspan="5"><input type="text" name="_reg_cancelled_reason" class="fmt" style="width:100%" disabled></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:90px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete"> &nbsp; Delete</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update order"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<div class="i_line">Download Letter &amp; Attachment</div>
<?php if(numQueryRows($file_res) <= 0) { ?>
<span class="comment"><i>( No uploaded file )</i></span>
<?php } else { ?>
<table width="100%" class="table_f">
<?php
pg_result_seek($file_res,0);
while($rows =& fetchRowAssoc($file_res)) {
if($rows["ltf_type"]=='A') {
	$file_name = HTTP_DIR.'letter/letter'.$rows["ltf_file_path"];

	echo "<tr height='30px'>\n";
	cell_link('<span class="'.$rows["ltf_file_type"].'">'.$rows["ltf_file_name"].'</span>', ' colspan="2"', 
			  ' href="'.$file_name.'"');
	echo "</tr>\n";
}}

pg_result_seek($file_res,0);
while($rows =& fetchRowAssoc($file_res)) {
if($rows["ltf_type"]=='B') {
	$file_name = HTTP_DIR.'letter/letter'.$rows["ltf_file_path"];

	echo "<tr height='30px'>\n";
	cell($rows["ltf_file_desc"], ' width="20%"');
	cell_link('<span class="'.$rows["ltf_file_type"].'">'.$rows["ltf_file_name"].'</span>', '', 
			  ' href="'.$file_name.'"');
	echo "</tr>\n";
}}
?>
</table>
<div align="right"><small><i>*To download related file, please right click to the link &amp; choose Save Target As</i></small></div>
<?php } ?>
<script language="javascript" type="text/javascript">
	var f = window.document.frmUpdate;

	f.btnAddLetter.onclick = function() {
		//Define element will be used
		var oTd		= new Array();
		var oTr		= window.document.createElement("TR");
		var oText	= window.document.createElement("INPUT");
		var oButton = window.document.createElement("INPUT");
		oTd[0] =  window.document.createElement("TD");
		oTd[1] =  window.document.createElement("TD");

		//create textbox
		oText.style.width = "100%";
		oText.type = "file";
		oText.name = "_reg_letter[]";
		oText.className = "req";
		oText.readonly = "readOnly";

		//create button
		oTd[1].align = "center";
		oButton.style.width = "100%";
		oButton.type = "button";
		oButton.name = "btnDelLetter";
		oButton.value = " - ";
		oButton.className = "fmt";
		oButton.onclick = function () {
			var oRow = this.parentElement.parentElement;
			window.copy_letter.removeChild(oRow);
		}

		//Add 
		oTd[0].appendChild(oText);
		oTd[1].appendChild(oButton);
		oTr.appendChild(oTd[0]);
		oTr.appendChild(oTd[1]);
		window.copy_letter.appendChild(oTr);
	}

	f.btnAddAttachment.onclick = function() {
		//Define element will be used
		var oTd		= new Array();
		var oTr		= window.document.createElement("TR");
		var oText	= window.document.createElement("INPUT");
		var oText2	= window.document.createElement("INPUT");
		var oButton = window.document.createElement("INPUT");
		oTd[0] =  window.document.createElement("TD");
		oTd[1] =  window.document.createElement("TD");
		oTd[2] =  window.document.createElement("TD");

		//create textbox
		oText.style.width = "100%";
		oText.type = "text";
		oText.name = "_reg_desc_attachment[]";
		oText.className = "fmt";

		oText2.style.width = "100%";
		oText2.type = "file";
		oText2.name = "_reg_attachment[]";
		oText2.className = "req";
		
		//create button
		oTd[2].align = "center";
		oButton.style.width = "100%";
		oButton.type = "button";
		oButton.name = "btnDelAttachment";
		oButton.value = " - ";
		oButton.className = "fmt";
		oButton.onclick = function () {
			var oRow = this.parentElement.parentElement;
			window.attachment.removeChild(oRow);
		}

		oTd[0].appendChild(oText);
		oTd[1].appendChild(oText2);
		oTd[2].appendChild(oButton);
		oTr.appendChild(oTd[0]);
		oTr.appendChild(oTd[1]);
		oTr.appendChild(oTd[2]);
		window.attachment.appendChild(oTr);
	}

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete this Letter?")) {
			f.p_mode.value = 'delete';
			f.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {

		if(f.cboRegStatus.value == '2') {
			if (window.copy_letter.rows.length+window.row_copy_letter.rows.length <= 0) {
				alert("You need to submit at least 1 file");
				f.btnAddLetter.focus();
				return;
			}
		}	

		var e 			= window.document.frmUpdate.elements;
		var numLetter	= window.copy_letter.rows.length;
		var numAttach	= window.attachment.rows.length;
		var numInput1	= 2;	/////
		var numInput2	= 3;	/////
		var idx_letter	= 9;	/////
		var idx_attach	= idx_letter+(numInput1*numLetter)+2;

		for (var i = 0; i< numLetter; i++) {
			var str = e(idx_letter+i*numInput1).value;
			var ext = str.substring(str.lastIndexOf(".")+1);

			if(str <= 0)  { alert("Please complete your letter form"); return; }
			if (ext != "doc" && ext != "docx" && ext != "xls" && ext != "xlsx" && ext != "pdf") {
				alert ("You can only upload doc, xls, or pdf type.\nPlease check your submit file in copy of letter column.");
				return;
			} 
		}

		if(numAttach>0) {
			for (var i = 0; i< numAttach; i++) {
				var str = e(idx_attach+i*numInput2).value;
				var ext = str.substring(str.lastIndexOf(".")+1);

				if(e(idx_attach+i*numInput2).value <= 0)  { alert("Please complete your attachment form"); return; }
				if (ext != "doc" && ext != "docx" && ext != "xls" && ext != "xlsx" && ext != "pdf") {
					alert ("You can only upload doc, xls, or pdf type.\nPlease check your submit file in attachment column.");
					return;
				} 
			}
		}

		if(confirm("Are you sure to update?")) {
			if(verify(f)){
				f.p_mode.value = 'update';
				f.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = 'list_letter.php';
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