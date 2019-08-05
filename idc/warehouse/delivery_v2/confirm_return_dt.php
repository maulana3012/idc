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

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_do.php";
require_once "detail_do_m.php";

//DEFAULT PROCESS =======================================================================================
$col[0] = getReturnItem($_inc_idx, $_std_idx, 'info_confirm');
$col[1] = getReturnItem($_inc_idx, $_std_idx, 'cus_item', array('inc_doc_type'=> trim($col[0]['std_doc_type']), 'inc_doc_ref'=> trim($col[0]['std_doc_ref'])));
$col[2] = getReturnItem($_inc_idx, $_std_idx, 'std_item', array('inc_doc_type'=> trim($col[0]['std_doc_type'])));
$col[3] = getReturnItem($_inc_idx, $_std_idx, 'inc_item');

echo "<pre>";
//var_dump($col[0]['std_is_confirmed']);
echo "</pre>";

if(count($col[0]) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($col[0]['std_is_confirmed'] == 't') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
} else if($col[0]['std_doc_type'] != 'Return DT') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
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
<script language="javascript" type="text/javascript">
function checkQty(i,type) {
	var f			= window.document.frmInsert;
	var e			= window.document.frmInsert.elements;
	var numItem		= window.itemStockPosition.rows.length;
	var numInput	= 8;
	var idx_qty		= 14;				//////
	var idx_stock	= idx_qty+1;
	var idx_demo	= idx_qty+2;
	var idx_reject	= idx_qty+3;

	var idx_code	= idx_qty-4;
	var idx_model	= idx_qty-2;

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
	var numInput	= 8;
	var idx_qty		= 14;				//////
	var idx_code	= idx_qty-4;
	var idx_model	= idx_qty-2;
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
	var d		 = parseDate(f2.elements[8].value, 'prefer_euro_format');

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
				var loc = '';
				if (o._function.value == 'IDC') {
					if(f2.elements[2].value == 1) {
						loc = 'IDC';
					} else if(f2.elements[2].value == 2) {
						loc = 'DNR';
					}
				} else if (o._function.value == 'MED') {
					if(f2.elements[2].value == 1) {
						loc = 'MED';
					} 
				}
				oTD[i].innerText	= loc;
				oTD[i].align		= "center";
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
				oTD[i].innerText	= f2.elements[9].value;
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= name + "_it_qty[]";
				oTextbox[i].value	= f2.elements[9].value;
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
	var idx = parseFloat(12 + f2.elements[4].value);
	var d = parseDate(f2.elements[idx].value, 'prefer_euro_format');

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	//check has same SN in same item
	var count = rejectPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rejectPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value) + '-' +f2.elements[idx].value) {
			alert(
				"Please check Reject List"+
				"\nSN " + f2.elements[idx].value + " in item ["+ f2.elements[0].value +"] " + f2.elements[1].value+ " already exist!" +
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
				oTD[i].innerText	= f2.elements[idx-1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_reject_it_sn[]";
				oTextbox[i].value	= f2.elements[idx-1].value;
				break;

			case 2: // _reject_it_warranty
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_reject_it_warranty[]";
				oTextbox[i].value	= formatDate(d, 'd-NNN-yyyy');
				break;

			case 3: // _reject_it_desc
				oTD[i].innerText	= f2.elements[idx+1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_reject_it_desc[]";
				oTextbox[i].value	= f2.elements[idx+1].value;
				break;

			case 4: // DELETE
				oTD[i].innerHTML	= "<a href=\"javascript:deleteRejectitem('"+trim(f2.elements[0].value)+'-'+trim(f2.elements[idx].value)+"')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align		= "right";
				break;
		}
		if (i!=4) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value) + '-' +trim(f2.elements[idx-1].value);
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CONFIRM RETURN &nbsp; <small class="comment"><?php echo '[ref ' . $col[0]['std_doc_type'] .' no: '. trim($col[0]['std_doc_ref']).']'?></small></h4>
<form name="frmInsert" method="post">
<input type="hidden" name="p_mode" value="confirm_do_return">
<input type="hidden" name="_std_idx" value="<?php echo $_std_idx?>">
<input type="hidden" name="_inc_idx" value="<?php echo $_inc_idx?>">
<input type="hidden" name="_std_type" value="<?php echo $col[0]['std_type']?>">
<input type="hidden" name="_doc_type" value="<?php echo $col[0]['std_doc_type']?>">
<input type="hidden" name="_doc_ref" value="<?php echo trim($col[0]['std_doc_ref'])?>">
<input type="hidden" name="_doc_date" value="<?php echo trim($col[0]['std_date'])?>">
<input type="hidden" name="_cus_code" value="<?php echo trim($col[0]['cus_code'])?>">
<input type="hidden" name="_revision_time" value="<?php echo $col[0]['std_revision_time']?>">
	<table width="100%" class="table_box">
		<tr>
			<td colspan="4"><strong class="info">RETURN INFORMATION</strong></td>
			<?php if($col[0]['std_revision_time']>0) {?>
			<td colspan="3" align="right">
				<span class="comment"><i>
				Last unconfirmed by : <?php echo $col[0]['std_last_cancelled_by'].', '.date('d-M-Y g:i:s', strtotime($col[0]['std_last_cancelled_timestamp'])).' <b>['.$col[0]['std_revision_time'].']</b>' ?>
				</i></span>
			</td>
			<?php } ?>
		</tr>
		<tr>
			<th width="15%">RETURN NO</th>
			<td  width="25%" colspan="2"><b><?php echo $col[0]['std_doc_ref'] ?></b></td>
			<th width="15%">RETURN DATE</th>
			<td><?php echo date('d-M-Y', strtotime($col[0]['std_date'])) ?></td>
			<th width="15%">RECEIVED BY</th>
			<td><?php echo $col[0]['std_received_by'] ?></td>
		</tr>
		<tr>
			<th>DT NO</th>
			<td colspan="2"><?php echo $col[0]['inv_no'] ?></td>
			<th>DT DATE</th>
			<td><?php echo ($col[0]['inv_date'] != '') ? date('d-M-Y', strtotime($col[0]['inv_date'])) : "" ?></td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<th rowspan="3">CUSTOMER<br />SHIP TO</th>
			<th width="10%">NAME</th>
			<td colspan="5"><?php echo '['.trim($col[0]['cus_code']).'] '.$col[0]['cus_full_name'] ?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="5"><?php echo $col[0]['cus_address'] ?></td>
		</tr>
	</table><br />
	<strong class="info">CUSTOMER ITEM LIST</strong>
	<table width="100%" class="table_nn">
		<thead>
			<tr height="30px">
				<th width="7%">CODE</th>
				<th width="15%">ITEM NO</th>
				<th>DESCRIPTION</th>
				<th width="7%">QTY</th>
				<th width="15%">REMARK</th>
			</tr>
		</thead>
		<tbody id="itemWHPosition">
<?php $amount = 0; while($items =& fetchRowAssoc($col[1])) { ?>
			<tr>
				<td><?php echo $items['it_code']?></td>
				<td><?php echo $items['it_model_no']?></td>
				<td><?php echo $items['it_desc']?></td>
				<td align="right"><?php echo number_format((double)$items['qty'],0)?></td>
				<td><?php echo $items['remark']?></td>
			</tr>
<?php $amount +=  $items['qty']; } ?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">TOTAL QTY</th>
			<th width="7%" align="right"><?php echo number_format((double)$amount,0) ?></th>
			<th width="15%">&nbsp;</th>
		</tr>
	</table><br />
	<strong class="info">WAREHOUSE ITEM LIST</strong>
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
<?php $amount = 0; while($items =& fetchRowAssoc($col[2])) { ?>
			<tr>
				<td><?php echo $items['it_code']?></td>
				<td><?php echo $items['istd_it_code_for']?></td>
				<td><?php echo $items['it_model_no']?></td>
				<td><?php echo $items['it_desc']?></td>
				<td align="right"><?php echo number_format((double)$items['istd_qty'],1)?></td>
				<td align="right"><?php echo number_format((double)$items['istd_function'],1)?></td>
				<td><?php echo $items['istd_remark']?></td>
			</tr>
<?php $amount +=  $items['istd_qty']; } ?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format((double)$amount,2) ?>" readonly></th>
			<th width="21%">&nbsp;</th>
		</tr>
	</table><br />
	<strong class="info">SUMMARY ITEM</strong>
	<table width="100%" class="table_box" cellspacing="1">
	  <thead>
		<tr>
			<th rowspan="2" width="7%">CODE</th>
			<th rowspan="2" width="15%">ITEM NO</th>
			<th rowspan="2">DESCRIPTION</th>
			<th rowspan="2" width="7%">QTY</th>
			<th colspan="3" width="21%">SAVE TO (pcs)</th>
		</tr>
		<tr>
			<th width="7%">STOCK</th>
			<th width="7%">DEMO</th>
			<th width="7%">REJECT</th>
		</tr>
	  </thead>
	  <tbody id="itemStockPosition">
<?php
$i = 0;
$amount = 0;
while($items =& fetchRowAssoc($col[3])) {
?>
		<tr>
			<td>
				<input type="hidden" name="_it_code[]" value="<?php echo trim($items['it_code']) ?>">
				<input type="hidden" name="_it_ed[]" value="<?php echo $items['it_ed'] ?>">
				<?php
				if($items['it_ed']=='f') {echo $items['it_code']."\n";}
				else {echo "<a href=\"javascript:insertED('".trim($items['it_code'])."',$i,'true')\"><b>{$items['it_code']}</b></a>\n";}
				?>
			</td>
			<td><input type="hidden" name="_it_model_no[]" value="<?php echo $items['it_model_no'] ?>"><?php echo $items['it_model_no']?></td>
			<td>
				<input type="hidden" name="_it_type[]" value="<?php echo $items['init_stock_qty'] ?>">
				<?php echo $items['it_desc']?>
			</td>
			<td><input type="text" name="_it_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format($items['qty'],1)?>" readonly></td>
			<td><input type="text" name="_it_stock_qty[]" class="fmtn" style="width:100%" value="0" onKeyUp="formatNumber(this,'dot')" onBlur="checkQty(<?php echo "$i,1" ?>)"></td>
			<td><input type="text" name="_it_demo_qty[]" class="fmtn" style="width:100%" value="0" onKeyUp="formatNumber(this,'dot')" onBlur="checkQty(<?php echo "$i,2" ?>)"></td>
			<td><input type="text" name="_it_reject_qty[]" class="fmtn" style="width:70%" value="0" onKeyUp="formatNumber(this,'dot')" onBlur="checkQty(<?php echo "$i,3" ?>)"> <a href="javascript:insertED(<?php echo "'".trim($items['it_code'])."',".$i.",'false'" ?>)"><img src="../../_images/icon/add.gif" width="12"></a></td>
		</tr>
<?php $amount += $items['qty']; $i++; } ?>
	  </tbody>
		<tr>
			<th align="right" colspan="3">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format($amount,2) ?>" readonly></th>
			<th colspan="3"></th>
		</tr>
	</table><br />
<center>
</center>
	<table width="100%" class="table_layout">
		<tr>
			<td width="54%" valign="top">
				<strong class="info">[<font color="#315c87">STOCK</font>] DETAIL ITEM PER E/D</strong>
				<table width="100%" class="table_l">
				  <thead>
					<tr>
						<th width="15%">CODE</th>
						<th>ITEM NO</th>
						<th width="8%">LOCATION</th>
						<th width="25%">E/D</th>
						<th width="12%">QTY</th>
						<th width="10%"></th>
					</tr>
				  </thead>
				  <tbody id="stockPosition">
				  </tbody>
				</table>
			</td>
			<td width="2%"></td>
			<td width="44%" valign="top">
				<strong class="info">[<font color="#315c87">DEMO UNIT</font>] DETAIL ITEM PER E/D</strong>
				<table width="100%" class="table_l">
				  <thead>
					<tr>
						<th width="15%">CODE</th>
						<th>ITEM NO</th>
						<th width="8%">LOCATION</th>
						<th width="25%">E/D</th>
						<th width="12%">QTY</th>
						<th width="10%"></th>
					</tr>
				  </thead>
				  <tbody id="demoPosition">
				  </tbody>
				</table>
			</td>
		</tr>
	</table><br />
	<strong class="info">[<font color="#315c87">REJECT</font>] DETAIL PER ITEM</strong>
	<table width="75%" class="table_l">
	  <thead>
		<tr>
			<th width="15%">CODE</th>
			<th width="18%">SN</th>
			<th width="18%">WARRANTY</th>
			<th>DESCRIPTION</th>
			<th width="5%"></th>
		</tr>
	  </thead>
	  <tbody id="rejectPosition">
	  </tbody>
	</table><br />
	<strong class="info">OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">REMARK</th>
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="4"></textarea></td>
		</tr>
	</table>
<input type='hidden' name='_function' value='<?php echo ZKP_SQL ?>'>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name='btnConfirm' class='input_btn' style='width:150px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle" alt="Confirm return"> &nbsp; Confirm return</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnConfirm.onclick = function() {

		//Check qty value
		var e 			= window.document.frmInsert.elements;

		var row			= window.itemStockPosition.rows.length;
		var rowI		= window.stockPosition.rows.length;
		var rowII		= window.demoPosition.rows.length;
		var rowIII		= window.rejectPosition.rows.length;

		var numInput	= 8;
		var numInputI	= 5;
		var numInputII	= 5;
		var numInputIII	= 4;

		var idx_code	= 10;			//////
		var idx_codeI	= 0;
		var idx_codeII	= 0;
		var idx_codeIII	= 0;

		var idx_ed		= idx_code+1;
		var idx_item	= idx_code+2;
		var idx_type	= idx_code+3;
		var idx_qty		= idx_code+4;
		var idx_stock	= idx_code+5;
		var idx_demo	= idx_code+6;
		var idx_reject	= idx_code+7;

		if(rowI>0) {idx_codeI	  = idx_code+(numInput*row)+1;}
		if(rowII>0) {idx_codeII	  = idx_code+((numInput*row)+(numInputI*rowI))+1;}
		if(rowIII>0) {idx_codeIII = idx_code+((numInput*row)+(numInputI*rowI)+(numInputII*rowII))+1;}

		//check value in row
		for (var i=0; i<row; i++) {
			var code	= trim(e(idx_code+i*numInput).value);
			var item	= e(idx_item+i*numInput).value;
			var default_qty  = parseFloat(removecomma(e(idx_qty+i*numInput).value));
			var inputted_qty = parseFloat(removecomma(e(idx_stock+i*numInput).value))+parseFloat(removecomma(e(idx_demo+i*numInput).value))+parseFloat(removecomma(e(idx_reject+i*numInput).value));

			if(inputted_qty <= 0) {
				alert("INCLOMPLETE INPUT QTY.\n*for item [" + code + "] " + item + '\n\nPlease check again!');
				return;
			} else if(inputted_qty < default_qty) {
				alert(
					"Check item : [" + code + "] " + item +
					"\nDefault qty               : " + default_qty + 
					"\nCurrent inputed qty : " + inputted_qty
				);
				return;
			}
		}

		//checking item
		for (var i=0; i<row; i++) {
			var code	= trim(e(idx_code+i*numInput).value);
			var item	= e(idx_item+i*numInput).value;
			var default_qty = parseFloat(removecomma(e(idx_qty+i*numInput).value));
			var stock_qty	= parseFloat(removecomma(e(idx_stock+i*numInput).value));
			var demo_qty	= parseFloat(removecomma(e(idx_demo+i*numInput).value));
			var reject_qty	= parseFloat(removecomma(e(idx_reject+i*numInput).value));
			var qtyI	= 0;
			var qtyII	= 0;
			var qtyIII	= 0;

			//check qty item has E/D
			if(e(idx_ed+i*numInput).value=='t') {
				//check in [stock]
				for (var j=0; j<rowI; j++) {
					if(code == trim(e(idx_codeI+j*numInputI).value)){
						var qtyI	= qtyI + parseFloat(removecomma(e(idx_codeI+4+j*numInputI).value));
					}
				}
				if(qtyI != stock_qty) {
					alert(
						"Check E/D for : [" + code + "] " + item + 
						"\nin Stock Detail item per E/D"
					);
					return;
				}

				//check in [demo]
				for (var k=0; k<rowII; k++) {
					if(code == trim(e(idx_codeII+k*numInputII).value)){
						qtyII	= qtyII + parseFloat(removecomma(e(idx_codeII+4+k*numInputII).value));
					}
				}
				if(qtyII != demo_qty) {
					alert(
						"Check E/D for : [" + code + "] " + item + 
						"\nin Demo Unit Detail item per E/D"
					);
					return;
				}
			}

			//check qty reject item
			for (var l=0; l<rowIII; l++) {
				if(code == trim(e(idx_codeIII).value)) {
					qtyIII	= qtyIII + 1;
				}
			}
			if(qtyIII != reject_qty) {
				alert("Check Reject Item for : [" + code + "] " + item);
				return;
			}

		}

		if(confirm("Are you sure to confirm?\nAfter confirm, you can't change any data in this return.")) {
			if(verify(oForm)){
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_return_by_group.php?cboSource=".$col[0]['std_doc_type']?>';
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