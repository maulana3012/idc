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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = 'daily_return_by_group.php';
if (!isset($_GET['_inc_idx']) || !isset($_GET['_std_idx'])) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_std_idx	= $_GET['_std_idx'];
	$_inc_idx	= $_GET['_inc_idx'];
}

$type[1]	= '[ref return billing : ';
$type[2]	= '[ref return order : ';

//========================================================================================= confirm return
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm')) {

	$_std_idx		 = $_POST["_std_idx"];
	$_inc_idx		 = $_POST["_inc_idx"];
	$_cus_code		 = $_POST["_cus_code"];
	$_type			 = $_POST["_std_type"];
	$_remark		 = $_POST["_remark"];
	$_revision_time	= $_POST["_revision_time"];
	$_cfm_by_account = $S->getValue("ma_account");
	$_doc_type		 = $_POST["_doc_type"];
	$_doc_ref		 = $_POST["_doc_ref"];
	$_doc_date		 = $_POST["_doc_date"];

	//	Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_ed'] as $val)			$_it_ed[]			= $val;
	foreach($_POST['_it_stock_qty'] as $val)	$_it_stock_qty[] 	= $val;
	foreach($_POST['_it_demo_qty'] as $val)		$_it_demo_qty[] 	= $val;
	foreach($_POST['_it_reject_qty'] as $val)	$_it_reject_qty[] 	= $val;

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_ed			= '$$' . implode('$$,$$', $_it_ed) . '$$';
	$_it_type		= '0';
	$_it_stock_qty	= implode(',', $_it_stock_qty);
	$_it_demo_qty	= implode(',', $_it_demo_qty);
	$_it_reject_qty	= implode(',', $_it_reject_qty);

	//stock item list
	if(isset($_POST['_ed_stk_it_code'])) {
		foreach($_POST['_ed_stk_it_code'] as $val) {$_ed_stk_it_code[]	 = $val;}
		$_ed_stk_it_code	= '$$'.implode('$$,$$', $_ed_stk_it_code).'$$';
	} else {
		$_ed_stk_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_stk_it_date'])) {
		foreach($_POST['_ed_stk_it_date'] as $val) {$_ed_stk_it_date[] 		 = $val;}
		$_ed_stk_it_date	= '$$'.implode('$$,$$', $_ed_stk_it_date).'$$';
	} else {
		$_ed_stk_it_date	= '$$$$';
	}

	if(isset($_POST['_ed_stk_it_location'])) {
		foreach($_POST['_ed_stk_it_location'] as $val) {$_ed_stk_it_location[]	= $val;}
		$_ed_stk_it_location	= implode(',', $_ed_stk_it_location);
	} else {
		$_ed_stk_it_location	= '0';
	}

	if(isset($_POST['_ed_stk_it_qty'])) {
		foreach($_POST['_ed_stk_it_qty'] as $val) {$_ed_stk_it_qty[]	= $val;}
		$_ed_stk_it_qty	= implode(',', $_ed_stk_it_qty);
	} else {
		$_ed_stk_it_qty	= '0';
	}

	//demo item list
	if(isset($_POST['_ed_demo_it_code'])) {
		foreach($_POST['_ed_demo_it_code'] as $val) {$_ed_demo_it_code[]	 = $val;}
		$_ed_demo_it_code	= '$$'.implode('$$,$$', $_ed_demo_it_code).'$$';
	} else {
		$_ed_demo_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_demo_it_date'])) {
		foreach($_POST['_ed_demo_it_date'] as $val) {$_ed_demo_it_date[] 		 = $val;}
		$_ed_demo_it_date	= '$$'.implode('$$,$$', $_ed_demo_it_date).'$$';
	} else {
		$_ed_demo_it_date	= '$$$$';
	}

	if(isset($_POST['_ed_demo_it_location'])) {
		foreach($_POST['_ed_demo_it_location'] as $val) {$_ed_demo_it_location[]	= $val;}
		$_ed_demo_it_location	= implode(',', $_ed_demo_it_location);
	} else {
		$_ed_demo_it_location	= '0';
	}

	if(isset($_POST['_ed_demo_it_qty'])) {
		foreach($_POST['_ed_demo_it_qty'] as $val) {$_ed_demo_it_qty[]	= $val;}
		$_ed_demo_it_qty	= implode(',', $_ed_demo_it_qty);
	} else {
		$_ed_demo_it_qty	= '0';
	}

	//reject item list
	if(isset($_POST['_reject_it_code'])) {
		foreach($_POST['_reject_it_code'] as $val) {$_reject_it_code[]	 = $val;}
		$_reject_it_code	= '$$'.implode('$$,$$', $_reject_it_code).'$$';
	} else {
		$_reject_it_code	= '$$$$';
	}

	if(isset($_POST['_reject_it_sn'])) {
		foreach($_POST['_reject_it_sn'] as $val) {$_reject_it_sn[] 		 = $val;}
		$_reject_it_sn	= '$$'.implode('$$,$$', $_reject_it_sn).'$$';
	} else {
		$_reject_it_sn	= '$$$$';
	}

	if(isset($_POST['_reject_it_warranty'])) {
		foreach($_POST['_reject_it_warranty'] as $val) {$_reject_it_warranty[] 		 = $val;}
		$_reject_it_warranty	= '$$'.implode('$$,$$', $_reject_it_warranty).'$$';
	} else {
		$_reject_it_warranty	= '$$$$';
	}

	if(isset($_POST['_reject_it_desc'])) {
		foreach($_POST['_reject_it_desc'] as $val) {$_reject_it_desc[] 		 = $val;}
		$_reject_it_desc	= '$$'.implode('$$,$$', $_reject_it_desc).'$$';
	} else {
		$_reject_it_desc	= '$$$$';
	}

	//confirmReturn
	$result = executeSP(
		ZKP_SQL."_confirmReturn",
		$_std_idx,
		$_inc_idx,
		$_type,
		"$\${$_remark}$\$",
		"$\${$_cfm_by_account}$\$",
		$_doc_type,
		"$\${$_doc_ref}$\$",
		"$\${$_doc_date}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_ed]",
		"ARRAY[$_it_type]",
		"ARRAY[$_it_stock_qty]",
		"ARRAY[$_it_demo_qty]",
		"ARRAY[$_it_reject_qty]",
		"ARRAY[$_ed_stk_it_code]",
		"ARRAY[$_ed_stk_it_date]",
		"ARRAY[$_ed_stk_it_location]",
		"ARRAY[$_ed_stk_it_qty]",
		"ARRAY[$_ed_demo_it_code]",
		"ARRAY[$_ed_demo_it_date]",
		"ARRAY[$_ed_demo_it_location]",
		"ARRAY[$_ed_demo_it_qty]",
		"ARRAY[$_reject_it_code]",
		"ARRAY[$_reject_it_sn]",
		"ARRAY[$_reject_it_warranty]",
		"ARRAY[$_reject_it_desc]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/confirm_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
	}
	//SAVE PDF FILE
	include "pdf/generate_return_pdf.php";
	$_out_idx = $result[0];
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
}

//========================================================================================= DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_outstanding join ".ZKP_SQL."_tb_customer using(cus_code) WHERE std_idx = '$_std_idx'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['std_is_confirmed'] == 't') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
} else if($column['std_doc_type'] == 3) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_return_dt.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
}

//[OUTSTANDING] item
$std_sql = "
SELECT
  a.it_code,
  b.istd_it_code_for,
  a.it_model_no,
  a.it_desc,
  b.istd_qty,
  b.istd_function,
  b.istd_remark
FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE std_idx = $_std_idx
ORDER BY it_code,istd_idx";
$std_sql	=& query($std_sql);

//[INCOMING] item
$inc_sql = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  a.it_ed,				--3
  b.init_qty AS qty		--4
FROM ".ZKP_SQL."_tb_incoming_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE inc_idx = $_inc_idx
ORDER BY it_code";
$inc_res	 =& query($inc_sql);
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkQty(i,type) {
	var f			= window.document.frmInsert;
	var e			= window.document.frmInsert.elements;
	var numItem		= window.itemStockPosition.rows.length;
	var numInput	= 7;
	var idx_qty		= 16;				//////
	var idx_stock	= idx_qty+1;
	var idx_demo	= idx_qty+2;
	var idx_reject	= idx_qty+3;

	var idx_code	= idx_qty-3;
	var idx_model	= idx_qty-1;

	var it_code		= trim(e(idx_code+i*numInput).value);
	var it_model_no	= e(idx_model+i*numInput).value;
	var default_qty	= parseFloat(removecomma(e(idx_qty+i*numInput).value));

	if(e(idx_stock+i*numInput).value=='') {
		var stock_qty	= 0;
		e(idx_stock+i*numInput).value	= 0;
	} else {var stock_qty	= parseFloat(removecomma(e(idx_stock+i*numInput).value));}

	if(e(idx_demo+i*numInput).value=='') {
		var demo_qty	= 0;
		e(idx_demo+i*numInput).value	= 0;
	} else {var demo_qty	= parseFloat(removecomma(e(idx_demo+i*numInput).value));}

	if(e(idx_reject+i*numInput).value=='') {
		var reject_qty	= 0;
		e(idx_reject+i*numInput).value	= 0;
	} else{var reject_qty	= parseFloat(removecomma(e(idx_reject+i*numInput).value));}

	var total_qty	= stock_qty+demo_qty+reject_qty;

	if(total_qty > default_qty) {
		alert("You can't input qty more than return qty.\n Please check item : ["+ it_code + "] "+ it_model_no);
		e(idx_stock+i*numInput).value	= 0;
		e(idx_demo+i*numInput).value	= 0;
		e(idx_reject+i*numInput).value	= 0;
	}
}

var wInputED;
function insertED(code,i, value) {
	var f			= window.document.frmInsert;
	var e			= window.document.frmInsert.elements;
	var numItem		= window.itemStockPosition.rows.length;
	var numInput	= 7;
	var idx_qty		= 16;				//////
	var idx_code	= idx_qty-3;
	var idx_model	= idx_qty-1;
	var it_code		= trim(e(idx_code+i*numInput).value);
	var it_model_no	= e(idx_model+i*numInput).value;

	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 260) / 2;

	wInputED = window.open(
		'./p_input_return_ed.php?_code='+it_code+'&_item='+it_model_no+'&_value='+value,
		'wSearchED',
		'scrollbars,width=450,height=260,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

function createED() {
	var o	= window.document.frmInsert;
	var f2	= wInputED.document.frmInsert;

	var oTR 	 = window.document.createElement("TR");
	var oTD 	 = new Array();
	var oTextbox = new Array();
	var d		 = parseDate(f2.elements[7].value, 'prefer_euro_format');

	//check has same E/D in same item
	if(f2.elements[3].value==1) {
		var count = stockPosition.rows.length;
		var name  = "_ed_stk" ;
	} else if(f2.elements[3].value==2) {
		var count = demoPosition.rows.length;
		var name  = "_ed_demo" ;
	}

	for (var i=0; i<count; i++) {
		if(f2.elements[3].value==1) { var oRow = window.stockPosition.rows(i); var list = "Stock E/D list" }
		else if(f2.elements[3].value==2) { var oRow = window.demoPosition.rows(i); var list = "Demo E/D list" }

		var comparing = formatDate(d, 'NNN-yyyy')+'-'+trim(f2.elements[0].value)
		if (oRow.id == comparing) {
			alert(
				"Please check "+ list +
				"\nE/D " + formatDate(d, 'NNN-yyyy') + " in item ["+ f2.elements[0].value +"] " + f2.elements[1].value+ " already exist!" +
				"\nPlease check again");
			return false;
		}
	}

	for (var i=0; i<6; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // _ed_it_code
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= name + "_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // _ed_it_model_no
				oTD[i].innerText	= f2.elements[1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= name + "_it_model_no[]";
				oTextbox[i].value	= f2.elements[1].value;
				break;

			case 2: // _ed_it_location
				if(f2.elements[3].value == 1) {
					if(f2.elements[2].value==1) { oTD[i].innerText	= 'IDC'; }
					else if(f2.elements[2].value==2) { oTD[i].innerText	= 'DNR'; }
					oTD[i].align		= "center";
				}
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= name + "_it_location[]";
				oTextbox[i].value	= f2.elements[2].value;
				break;

			case 3: // _ed_it_date
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= name + "_it_date[]";
				oTextbox[i].value	= formatDate(d, '1-NNN-yyyy');
				break;

			case 4: // QTY
				oTD[i].innerText	= f2.elements[8].value;
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= name + "_it_qty[]";
				oTextbox[i].value	= f2.elements[8].value;
				break;

			case 5: // DELETE
				if(f2.elements[3].value==1) {
					oTD[i].innerHTML	= "<a href=\"javascript:deleteStockItem('"+formatDate(d, 'NNN-yyyy')+'-'+trim(f2.elements[0].value)+"')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				} else if(f2.elements[3].value==2) {
					oTD[i].innerHTML	= "<a href=\"javascript:deleteDemoitem('"+formatDate(d, 'NNN-yyyy')+'-'+trim(f2.elements[0].value)+"')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				}
				oTD[i].align			= "right";
				break;
		}
		if (i!=5) oTD[i].appendChild(oTextbox[i]);
		oTR.id = formatDate(d, 'NNN-yyyy')+'-'+trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);
	}
	if(f2.elements[3].value==1) {
		window.stockPosition.appendChild(oTR);
	} else if(f2.elements[3].value==2) {
		window.demoPosition.appendChild(oTR);
	}
}

function createRejectDesc() {
	var o	= window.document.frmInsert;
	var f2	= wInputED.document.frmInsert;

	var oTR 	 = window.document.createElement("TR");
	var oTD 	 = new Array();
	var oTextbox = new Array();
	var d		 = parseDate(f2.elements[12].value, 'prefer_euro_format');

	//check has same SN in same item
	var count = rejectPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rejectPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value) + '-' +f2.elements[11].value) {
			alert(
				"Please check Reject List"+
				"\nSN " + f2.elements[11].value + " in item ["+ f2.elements[0].value +"] " + f2.elements[1].value+ " already exist!" +
				"\nPlease check again");
			return false;
		}
	}

	for (var i=0; i<5; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // _reject_it_code
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_reject_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // _reject_it_sn
				oTD[i].innerText	= f2.elements[11].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_reject_it_sn[]";
				oTextbox[i].value	= f2.elements[11].value;
				break;

			case 2: // _reject_it_warranty
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_reject_it_warranty[]";
				oTextbox[i].value	= formatDate(d, 'd-NNN-yyyy');
				break;

			case 3: // _reject_it_desc
				oTD[i].innerText	= f2.elements[13].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_reject_it_desc[]";
				oTextbox[i].value	= f2.elements[13].value;
				break;

			case 4: // DELETE
				oTD[i].innerHTML	= "<a href=\"javascript:deleteRejectitem('"+trim(f2.elements[0].value)+'-'+trim(f2.elements[11].value)+"')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align		= "right";
				break;
		}
		if (i!=4) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value) + '-' +trim(f2.elements[11].value);
		oTR.appendChild(oTD[i]);
	}
	window.rejectPosition.appendChild(oTR);
} 

function deleteStockItem(idx) {
	var count = window.stockPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.stockPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.stockPosition.removeChild(oRow);
			count = count - 1;
		}
	}
}

function deleteDemoitem(idx) {
	var count = window.demoPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.demoPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.demoPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
}

function deleteRejectitem(idx) {
	var count = window.rejectPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rejectPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rejectPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
}
</script>
</head>
<body topmargin="0" leftmargin="0">
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CONFIRM RETURN &nbsp; <small class="comment"><?php echo $type[$column['std_doc_type']] . trim($column['std_doc_ref']).']'?></small></h4>
<form name="frmInsert" method="post">
<input type="hidden" name="p_mode" value="confirm">
<input type="hidden" name="_std_idx" value="<?php echo $_std_idx?>">
<input type="hidden" name="_inc_idx" value="<?php echo $_inc_idx?>">
<input type="hidden" name="_std_type" value="<?php echo $column['std_type']?>">
<input type="hidden" name="_doc_type" value="<?php echo $column['std_doc_type']?>">
<input type="hidden" name="_doc_ref" value="<?php echo trim($column['std_doc_ref'])?>">
<input type="hidden" name="_doc_date" value="<?php echo trim($column['std_date'])?>">
<input type="hidden" name="_cus_code" value="<?php echo trim($column['cus_code'])?>">
<input type="hidden" name="_revision_time" value="<?php echo $column['std_revision_time']?>">
	<table width="100%" class="table_box">
		<tr>
			<td colspan="3"><span class="bar_bl">RETURN INFORMATION</span></td>
			<?php if($column['std_revision_time']>0) {?>
			<td colspan="2" align="right">
				<span class="comment"><i>
				Last unconfirmed by : <?php echo $column['std_last_cancelled_by'].', '.date('d-M-Y g:i:s', strtotime($column['std_last_cancelled_timestamp'])).' <b>['.$column['std_revision_time'].']</b>' ?>
				</i></span>
			</td>
			<?php } ?>
		</tr>
		<tr>
			<th>RETURN NO</th>
			<td colspan="2"><b><?php echo $column['std_doc_ref'] ?></b></td>
			<th>RETURN DATE</th>
			<td><?php echo date('d-M-Y', strtotime($column['std_date'])) ?></td>
		</tr>
		<tr>
			<th width="15%">RECEIVED BY</th>
			<td><?php echo $column['std_received_by'] ?></td>
			<td width="22%"></td>
			<th width="15%">TYPE INVOICE</th>
			<td>
				<input type="radio" name="_format" value="1" disabled <?php echo ($column['std_type']=='1')?'checked':'' ?>> Vat &nbsp;
				<input type="radio" name="_format" value="2" disabled <?php echo ($column['std_type']=='2')?'checked':'' ?>> Non Vat &nbsp;
			</td>
		</tr>
		<tr>
			<th rowspan="3">CUSTOMER<br />SHIP TO</th>
			<th width="12%">CODE</th>
			<td><?php echo $column['cus_code'] ?></td>
			<th>NAME</th>
			<td colspan="3"><?php echo $column['cus_full_name'] ?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="5"><?php echo $column['cus_address'] ?></td>
		</tr>
	</table><br />
	<strong class="info">ITEM LIST</strong>
	<table width="100%" class="table_nn">
		<thead>
			<tr height="30px">
				<th width="7%">CODE</th>
				<th width="7%">FOR</th>
				<th width="15%">ITEM NO</th>
				<th>DESCRIPTION</th>
				<th width="7%">QTY</th>
				<th width="7%">(x)</th>
				<th width="15%">REMARK</th>
			</tr>
		</thead>
		<tbody id="itemWHPosition">
<?php
$amount		= 0;
while($items =& fetchRow($std_sql)) {
?>
			<tr>
				<td><?php echo $items[0]?></td>
				<td><?php echo $items[1]?></td>
				<td><?php echo $items[2]?></td>
				<td><?php echo $items[3]?></td>
				<td align="right"><?php echo number_format($items[4],2)?></td>
				<td align="right"><?php echo number_format($items[5],2)?></td>
				<td><?php echo $items[6]?></td>
			</tr>
<?php 
	$amount +=  $items[4];
}
?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format($amount,2) ?>" readonly></th>
			<th width="21%">&nbsp;</th>
		</tr>
	</table><br />
	<strong class="info">SUMMARY ITEM</strong>
	<table width="100%" class="table_box" cellspacing="1">
	  <thead>
		<tr height="30px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
		</tr>
	  </thead>
	  <tbody id="itemStockPosition">
<?php
$i = 0;
$amount		 = 0;
while($items =& fetchRow($inc_res)) {
?>
		<tr>
			<td>
				<input type="hidden" name="_it_code[]" value="<?php echo trim($items[0]) ?>">
				<input type="hidden" name="_it_ed[]" value="<?php echo $items[3] ?>">
				<?php echo $items[0] ?>
			</td>
			<td><input type="hidden" name="_it_model_no[]" value="<?php echo $items[1] ?>"><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td><input type="text" name="_it_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format($items[4],2)?>" readonly></td>
		</tr>
<?php
	$amount += $items[4];
	$i++; 
}
?>
	  </tbody>
		<tr>
			<th align="right" colspan="3">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format($amount,2) ?>" readonly></th>
		</tr>
	</table><br />
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="center">
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_return_by_group.php?cboSource=".$column['std_doc_type']?>';
	}
</script>
<!--END Button-->
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