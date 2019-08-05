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
$left_loc	= "input_letter.php";

//PROCESS FORM
require_once "tpl_process_form.php"; 
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
function checkform(o) {

	if(f.cboRegStatus.value == '2') {
		if (window.copy_letter.rows.length <= 0) {
			alert("You need to submit at least 1 file");
			o.btnAddLetter.focus();
			return;
		} 
	}

	var e 			= window.document.frmInsert.elements;
	var numLetter	= window.copy_letter.rows.length;
	var numAttach	= window.attachment.rows.length;
	var numInput1	= 2;	/////
	var numInput2	= 3;	/////
	var idx_letter	= 6;	/////
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


	if (verify(o)) {
		if(confirm("Are you sure to save the letter registration?")) {
			o.submit();
		}
	}
}

function enabledText(val) {
	if(val == '2') {
		window.document.frmInsert._reg_confirmed_date.disabled = false;
		window.document.frmInsert._reg_confirmed_date.className = 'reqd';
		window.document.frmInsert._reg_confirmed_date.value = '<?php echo date('d-M-Y') ?>';
	} else {
		window.document.frmInsert._reg_confirmed_date.disabled = true;
		window.document.frmInsert._reg_confirmed_date.className = 'fmtd';
		window.document.frmInsert._reg_confirmed_date.value = '';
	}
}

function initPage() {
	setSelect(window.document.frmInsert.cboRegType, "<?php echo ($department=='G') ? "O":"T" ?>");
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
<form name="frmInsert" method="POST" enctype="multipart/form-data">
<input type='hidden' name='p_mode' value="insert">
<div class="head-line">[ <font color="#446fbe"><?php echo strtoupper($currentDept) ?></font> ] Registration Official Letter</div>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REG. DATE</th>
		<td width="35%"><input type="text" name="_reg_date" class="reqd" size="15" value="<?php echo date('d-M-Y'); ?>"></td>
		<th width="15%">ISSUED BY</th>
		<td><input type="text" name="_reg_issued_by" class="req" size="15" value="<?php echo ucfirst($S->getValue("ma_account"))?>"></td>
	</tr>
	<tr>
		<th>TYPE OF LETTER</th>
		<td>
			<select name="cboRegType">
				<option value="T">TENDER</option>
				<option value="Q">QUOTATION</option>
				<option value="B">BUSINESS</option>
				<option value="O">OTHERS</option>
			</select>
		</td>
		<th>SEND TO</th>
		<td><input type="text" name="_reg_send_to" class="req" style="width:100%"></td>
	</tr>
	<tr>
		<th>COPY OF LETTER</th>
		<td colspan="3">
			<table width="100%" class="table_box">
			  <tr>
				<td width="5%" valign="top" ><button name="btnAddLetter" class="input_sky" style="color:#003d78;width:100%;">+</button></td>
				<td>
				  <table width="100%" class="table_box">
				    <tr>
					 <td></td>
					 <td width="5%"></td>
					</tr>
					<tbody id="copy_letter">
					</tbody>
				  </table>
				</td>
			  </tr>
			</table>
		</td>
	</tr>
	<tr>
		<th>ATTACHMENT</th>
		<td colspan="3">
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
				  </table>
				</td>
			  </tr>
			</table>
		</td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="2"><input type="text" name="_reg_remark" class="fmt" style="width:100%"></td>
	</tr>
	<tr>
		<th>BRIEF SUMMARY</th>
		<td colspan="3"><textarea name="_reg_brief_summary" rows="4" class="req" style="width:100%"></textarea></td>
	</tr>
	<tr>
		<th>STATUS</th>
		<td>
			<select name="cboRegStatus" onchange="enabledText(this.value)">
				<option value="1">ON PROCESS</option>
				<option value="2">CONFIRMED</option>
			</select>
		</td>
		<th>CONFIRM DATE</th>
		<td><input type="text" name="_reg_confirmed_date" class="fmtd" size="15" disabled></td>
	</tr>
</table><br />
</form>
<p align="center">
	<button name='btnSave' class='input_btn' style='width:80px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save"> &nbsp; Save</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:90px;' onclick='window.location.href="list_letter.php"'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Cancel"> &nbsp; Cancel</button>
</p>
<script language="javascript" type="text/javascript">
	var f = window.document.frmInsert;

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