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
$left_loc	= "input_do_step_1.php";

//PROCESS FORM
require_once APP_DIR . "_include/other/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
if($_do_type == 'dr' && $_turn_code != '') {

	$res = query("SELECT 
				  (SELECT std_idx FROM ".ZKP_SQL."_tb_outstanding WHERE std_doc_ref='$_turn_code') AS std_idx, 
				  ".ZKP_SQL."_getDRCode('$_turn_code') AS dr_code,
				  (SELECT turn_ordered_by from ".ZKP_SQL."_tb_return WHERE turn_code='$_turn_code') AS turn_ordered_by
				FROM ".ZKP_SQL."_tb_return");
	$col = fetchRow($res);
	$std_idx = ($col[0]=='')?0:$col[0];

	//[WAREHOUSE] return item
	$whitem_sql = "
	SELECT
	  a.it_code,
	  b.istd_it_code_for,
	  a.it_model_no,
	  a.it_desc,
	  b.istd_qty,
	  b.istd_function,
	  b.istd_remark
	FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
	WHERE std_idx = $std_idx
	ORDER BY it_code,std_idx";
	$whitem_res	=& query($whitem_sql);

	$wh_items = array();
	while($items =& fetchRow($whitem_res)) {
		$wh_items[] = $items[0];
	}
	$wh_item = "'" . implode("','", $wh_items) . "'";


	$rr_sql = "
	SELECT
	  it_code,
	  sum(b.istd_qty)
	FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
	WHERE std_idx = $std_idx
	GROUP BY it_code";
	$rr_res	=& query($rr_sql);

	//[CUSTOMER] return item
	$cusitem_sql = "
	SELECT
	 a.it_code,			--0
	 a.it_model_no,		--1
	 a.it_desc,			--2
	 b.reit_unit_price,	--3
	 b.reit_qty,		--4
	 b.reit_unit_price * b.reit_qty AS amount,	--5	
	 b.reit_remark,		--6
	 b.reit_idx			--7		
	FROM ".ZKP_SQL."_tb_return_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
	WHERE turn_code = '$_turn_code'
	ORDER BY it_code, reit_idx";
	$cusitem_res	=& query($cusitem_sql);
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
<?php if ($_do_type == 'dr') { ?>
<?php
$stock_sql =" 
SELECT
 it_code, 
 (".ZKP_SQL."_getReadyStock(it_code,'$department') - ".ZKP_SQL."_getBookedStock(NULL,it_code)) AS stock
FROM ".ZKP_SQL."_tb_item WHERE it_code IN ($wh_item)
";
$stock_res	=& query($stock_sql);

echo "var stock = new Array();\n";
while ($rows =& fetchRow($stock_res, 0)) {
	printf("stock['%s'] = [%s];\n",
		trim($rows[0]),			//item
		$rows[1]				//total est vat + non
	);
}

if($_do_type == 'dr') {
	echo "\n\nvar dr_wh = new Array();\n";
	while ($rows =& fetchRow($rr_res, 0)) {
		printf("dr_wh['%s'] = [%s];\n",
			trim($rows[0]),			//item
			$rows[1]				//total est vat + non
		);
	}
}
?>
<?php } ?>

function checkform(o) {

	if(o._do_type.value == 'dr') {

		var e 			= window.document.frmInsert.elements;
		var count		= window.itemWHPosition.rows.length;
		var numInput	= 7;
		var idx_code	= 18;				/////
		var idx_item	= idx_code+2;
		var idx_qty		= idx_code+4;

		for (var i=0; i<count; i++) {
			var code		= e(idx_code+i*numInput).value;
			var item		= e(idx_item+i*numInput).value;
			var wh_stock	= stock[code][0];
			var dr_stock	= dr_wh[code][0];

			if(removecomma(e(idx_qty+i*numInput).value) == 0 || removecomma(e(idx_qty+i*numInput).value) == null) {
				alert(
					"You haven't input qty for item : ["+ trim(code) +"] " + item +
					"\nPlease check again!");
				return;
			} else if(wh_stock < removecomma(e(idx_qty+i*numInput).value)){
				alert(
					"Please check item : ["+ trim(code) +"] " + item +
					"\nCurrent estimated stock : "+ numFormatval(wh_stock+'',2) +
					"\nCurrent input qty            : " + numFormatval(e(idx_qty+i*numInput).value+'',2) +
					"\n\nPlease check again!");
				return;
			} else if(dr_stock < e(idx_qty+i*numInput).value){
				alert(
					"Please check item : ["+ trim(code) +"] " + item +
					"\nRR stock qty         : "+ numFormatval(dr_stock+'',2) +
					"\nCurrent input qty  : " + numFormatval(e(idx_qty+i*numInput).value+'',2) +
					"\n\nYou may not input qty more than DR warehouse qty" +
					"\nPlease check again!");
				return;
			}
		}
	}

	if (window.itemWHPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (window.itemCusPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save DO?")) {
			o.submit();
		}
	}
}

function seeCurrentStock() {
	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'../../_include/other/p_list_stock.php', '',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function seedetailreturn() {
	var x = (screen.availWidth - 500) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open("<?php APP_DIR . "_include/order/p_detail_return.php?_code=" . $_turn_code ?>",'',
		'scrollbars,width=500,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 350) / 2;
	var type = window.document.frmInsert._type_item.value;

	wSearchItem = window.open("p_list_item_1.php?_type="+type,'wSearchItem',
		'scrollbars,width=550,height=350,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
function createItem() {

	var f2	  = wSearchItem.document.frmCreateItem;
	var oTR_1 = window.document.createElement("TR");
	var oTR_2 = window.document.createElement("TR");
	var oTD_1 = new Array();
	var oTD_2 = new Array();
	var oTextbox_1 = new Array();
	var oTextbox_2 = new Array();

	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == trim(f2.elements[12].value)) {
			alert("[" + trim(f2.elements[12].value) + "] " + f2.elements[13].value + " already exist in customer item list");
			return;
		}
	}

	//Print cell for WH
	for (var i=0; i<8; i++) {
		oTD_1[i] = window.document.createElement("TD");
		oTextbox_1[i] = window.document.createElement("INPUT");
		oTextbox_1[i].type = "text";

		switch (i) {
			case 0: // _wh_it_code
				oTD_1[i].innerText	= trim(f2.elements[0].value);
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_code[]";
				oTextbox_1[i].value	= f2.elements[0].value;
				break;

			case 1: // _wh_it_code_for
				oTD_1[i].innerText	= trim(f2.elements[12].value);
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_code_for[]";
				oTextbox_1[i].value	= f2.elements[12].value;
				break;

			case 2: // _wh_it_model_no
				oTD_1[i].innerText	= f2.elements[3].value;
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_model_no[]";
				oTextbox_1[i].value	= f2.elements[3].value;
				break;

			case 3: // _wh_it_desc
				oTD_1[i].innerText	= f2.elements[4].value;
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_desc[]";
				oTextbox_1[i].value	= f2.elements[4].value;
				break;

			case 4: // _wh_it_qty
				oTD_1[i].innerText	= numFormatval(f2.elements[5].value+'',2);
				oTD_1[i].align		= "right";
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_qty[]";
				oTextbox_1[i].value	= parseFloat(f2.elements[5].value);
				break;

			case 5: // _wh_it_function
				oTD_1[i].innerText	= numFormatval(f2.elements[6].value+'',2);
				oTD_1[i].align		= "right";
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_function[]";
				oTextbox_1[i].value	= numFormatval(f2.elements[6].value+'',2);
				break;

			case 6: // _wh_it_remark
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmt";
				oTextbox_1[i].name			= "_wh_it_remark[]";
				oTextbox_1[i].value			= f2.elements[7].value;
				break;

			case 7: // DELETE
				oTD_1[i].innerHTML	= "<a href=\"javascript:deleteWHItem('" + trim(f2.elements[0].value) +'-'+ trim(f2.elements[12].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_1[i].align		= "center";
				break;
		}

		if (i!=7) oTD_1[i].appendChild(oTextbox_1[i]);
		oTR_1.id = trim(f2.elements[0].value)+'-'+trim(f2.elements[12].value);
		oTR_1.appendChild(oTD_1[i]);
	}
	window.itemWHPosition.appendChild(oTR_1);

	if(f2.elements[9].checked==true) {var i = 8;}
	else {var i = 14;}

	//Print cell for Customer
	for (var i=i; i<14; i++) {
		oTD_2[i] = window.document.createElement("TD");
		oTextbox_2[i] = window.document.createElement("INPUT");
		oTextbox_2[i].type = "text";

		switch (i) {
			case 8: // _cus_it_code
				oTD_2[i].innerText	= trim(f2.elements[12].value);
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_code[]";
				oTextbox_2[i].value	= f2.elements[12].value;
				break;

			case 9: // _cus_it_model_no
				oTD_2[i].innerText	= f2.elements[13].value;
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_model_no[]";
				oTextbox_2[i].value	= f2.elements[13].value;
				break;

			case 10: // _cus_it_desc
				oTD_2[i].innerText	= f2.elements[14].value;
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_desc[]";
				oTextbox_2[i].value	= f2.elements[14].value;
				break;

			case 11: // _cus_it_qty
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "reqn";
				oTextbox_2[i].name			= "_cus_it_qty[]";
				oTextbox_2[i].value			= numFormatval(removecomma(f2.elements[15].value)+'',0);
				oTextbox_2[i].onblur		= function() {updateAmount();}
				oTextbox_2[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 12: // _cus_it_remark
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].name			= "_cus_it_remark[]";
				oTextbox_2[i].value			= f2.elements[16].value;
				break;

			case 13: // DELETE
				oTD_2[i].innerHTML	= "<a href=\"javascript:deleteCusItem('" + trim(f2.elements[12].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_2[i].align		= "center";
				break;
		}
		if (i!=13) oTD_2[i].appendChild(oTextbox_2[i]);
		oTR_2.id = trim(f2.elements[12].value);
		oTR_2.appendChild(oTD_2[i]);
	}
	if(f2.elements[9].checked==true) {window.itemCusPosition.appendChild(oTR_2);}
	updateAmount();
}

//Delete Item rows collection
function deleteWHItem(idx) {

	var count = window.itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemWHPosition.rows(i);
		if (oRow.id == idx) {
			var code_ref = trim(oRow.cells(1).innerText);
			var n = window.itemWHPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	deleteCusItem(code_ref);
	updateAmount();
}

function deleteCusItem(idx) {
	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemCusPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	updateAmount();
}

//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countWH		= window.itemWHPosition.rows.length;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputWH	= 7;
	var numInputCus	= 5;

	var idx_qty1	= 22;	/////
	var idx_qty2	= idx_qty1+(numInputWH*countWH);

	var sumOfQty1	= 0;
	var sumOfQty2	= 0;

	for (var i=0; i<countWH; i++) {
		var qty = parseFloat(removecomma(e(idx_qty1+i*numInputWH).value));
		sumOfQty1	+= qty;
	}

	for (var i=0; i<countCus; i++) {
		var qty	  = parseFloat(removecomma(e(idx_qty2+i*numInputCus).value));
		sumOfQty2	+= qty;
	}

	f.totalWhQty.value	  = numFormatval(sumOfQty1 + '', 2);
	f.totalCusQty.value	  = addcomma(sumOfQty2);
}

<?php if ($_do_type == 'dr' && $col[1] != '') { ?>
function seedetailreturn() {
	var winforPrint = window.open('','','');
	winforPrint.document.location.href = "<?php echo HTTP_DIR . $currentDept . "/_billing/revise_return.php?_code=$_turn_code" ?>";
}
<?php } ?>

function initPage() {
	setSelect(window.document.frmInsert._type, "<?php echo $_do_type ?>");
	updateAmount();
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW DELIVERY REQUEST STEP (2 / 2)</strong><br /><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type='hidden' name='_dept' value="<?php echo $department ?>">
<input type='hidden' name='_do_type' value="<?php echo $_do_type ?>">
<input type='hidden' name='_do_date' value="<?php echo $_do_date ?>">
<input type='hidden' name='_issued_by' value="<?php echo $_issued_by ?>">
<input type='hidden' name='_issued_date' value="<?php echo $_issued_date ?>">
<input type='hidden' name='_received_by' value="<?php echo $_received_by  ?>">
<input type='hidden' name='_type_item' value="<?php echo $_type_item  ?>">
<input type='hidden' name='_cus_to' value="<?php echo $_cus_to ?>">
<input type='hidden' name='_cus_name' value="<?php echo $_cus_name ?>">
<input type='hidden' name='_cus_address' value="<?php echo $_cus_address ?>">
<input type='hidden' name='_ship_to' value="<?php echo $_ship_to ?>">
<input type='hidden' name='_ship_name' value="<?php echo $_ship_name ?>">
<input type='hidden' name='_turn_code' value="<?php echo $_turn_code ?>">
<input type='hidden' name='_turn_date' value="<?php echo $_turn_date ?>">
<strong class="info">DO INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="13%">DO TYPE</th>
		<td width="35%">
			<select name="_type" disabled>
				<option value="">==SELECT==</option>
				<option value="df">FREE</option>
				<option value="dr">REPLACE</option>
				<option value="dt">TEMPORARY</option>
			</select>
		</td>
		<th width="13%">DO DATE</th>
		<td><?php echo $_do_date ?></td>
	</tr>
	<tr>
		<th>ISSUED BY</th>
		<td><?php echo $_received_by ?></td>
		<th>ISSUED DATE</th>
		<td><?php echo $_issued_date ?></td>
	</tr>
	<tr>
		<th>RECEIVED BY</th>
		<td><?php echo $_issued_by ?></td>
		<th>TYPE ITEM</th>
		<td>
			<input type="radio" name="_type_vat" value="1" disabled <?php echo ($_type_item==1)?'checked':'' ?>> Vat &nbsp;
			<input type="radio" name="_type_vat" value="2" disabled <?php echo ($_type_item==2)?'checked':'' ?>> Non Vat &nbsp;
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="13%">CUSTOMER</th>
		<th width="10%">CODE</th>
		<td width="25%"><?php echo $_cus_to ?></td>
		<th width="13%">NAME</th>
		<td colspan="2" width="43%"><?php echo $_cus_name ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="4"><?php echo $_cus_address ?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th>CODE</th>
		<td><?php echo $_ship_to ?></td>
		<th>NAME</th>
		<td colspan="2"><?php echo $_ship_name ?></td>
	</tr>
	<?php if($_do_type == 'dr') { ?>
	<tr>
		<th rowspan="2">RETURN REF.</th>
		<th>CODE</th>
		<td><a href="../_billing/revise_return.php?_code=<?php echo $_turn_code ?>" target="_blank"><b><?php echo $_turn_code ?></b></a></td>
		<th>DATE</th>
		<td><?php echo $_turn_date ?></td>
	</tr>
	<?php if ($col[1] != '') { ?>
	<tr>
		<th><img src="../../_images/icon/hint.gif"> &nbsp; <span style="font-family:Courier;color:blue;font-weight:bold">HINT</span></th>
		<th colspan="4" align="left">
			<span style="font-family:Courier;font-size:12px">
			This return replace (RR) already has DR. Please check again in <a href="javascript:seedetailreturn()" style="color:#446FBE"><u>return detail</u>.</a>
			Current DR for this return : <b style="color:#000000"><?php echo $col[1] ?></b>
			</span>
		</th>
	</tr>
	<?php } ?>
	<?php } ?>
</table><br />
<table width="100%" class="table_box">
	<tr>
		<?php if($_do_type=='dr'){ ?>
		<td width="50%"><strong class="info">[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:seeCurrentStock()">( see current stock <img src="../../_images/icon/search_mini.gif"> )</a></i></small></td>
		<td align="right"><a href="javascript:window.location.reload()"><img src="../../_images/icon/reload.gif" alt="Reload page"></a></td>
		<?php } else { ?>
		<td width="18%"><strong class="info">[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong></td>
		<td><small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small></td>
		<?php } ?>
	</tr>
</table>
<table width="100%" class="table_box">
	<thead>
		<tr height="30px">
			<th width="7%">CODE</th>
			<th width="7%">FOR</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="8%">(x)</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php 
if($_do_type == 'dr'){
	pg_result_seek($whitem_res,0);
	while($items =& fetchRow($whitem_res)) {
?>
			<tr id="<?php echo trim($items[0]).'-'.trim($items[1])?>">
				<td><input type="hidden" name="_wh_it_code[]" value="<?php echo trim($items[0])?>"><?php echo $items[0]?></td>
				<td><input type="hidden" name="_wh_it_code_for[]" value="<?php echo trim($items[1])?>"><?php echo $items[1]?></td>
				<td><input type="hidden" name="_wh_it_model_no[]" value="<?php echo $items[2]?>"><?php echo $items[2]?></td>
				<td><input type="hidden" name="_wh_it_desc[]" value="<?php echo $items[3]?>"><?php echo $items[3]?></td>
				<td align="right"><input type="text" class="reqn" name="_wh_it_qty[]" value="<?php echo number_format((double)$items[4],2)?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
				<td align="right"><input type="hidden" name="_wh_it_function[]" value="<?php echo number_format((double)$items[5],2)?>"><?php echo number_format((double)$items[5],2)?></td>
				<td><input type="text" class="fmt" style="width:100%" name="_wh_it_remark[]" value="" style="width:100%"></td>
				<td align="center">
					<a href="javascript:deleteWHItem('<?php echo trim($items[0]).'-'.trim($items[1])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
				</td>
			</tr>
<?php
	}
} 
?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="27%">&nbsp;</th>
	</tr>
</table><br />
<strong>[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_box">
	<thead>
		<tr height="30px">
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="15%">REMARK</th>
			<th width="5%" colspan="3">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php
if($_do_type == 'dr'){
	while($items =& fetchRow($cusitem_res)) {
?>
			<tr id="<?php echo trim($items[0])?>">
				<td><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
				<td><input type="hidden" name="_cus_it_model_no[]" value="<?php echo $items[1]?>"><?php echo $items[1]?></td>
				<td><input type="hidden" name="_cus_it_desc[]" value="<?php echo $items[2]?>"><?php echo $items[2]?></td>
				<td align="right"><input type="text" class="reqn" name="_cus_it_qty[]" value="<?php echo $items[4]?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
				<td><input type="text" class="fmt" name="_cus_it_remark[]" value="" style="width:100%"></td>
				<td align="center"><a href="javascript:deleteCusItem('<?php echo trim($items[0])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
			</tr>
<?php
	}
}
?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalCusQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%">&nbsp;</th>
	</tr>
</table><br />
<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2">ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="D">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" value="Courier" size="6" class="fmt"></td>
		<td>Freight charge : Rp <input type="text" name="_delivery_freight_charge" class="fmtn" onKeyUp="formatNumber(this,'dot')" size="8" onBlur="updateAmount()"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="4"><textarea name="_remark" style="width:100%" rows="4"></textarea></td>
	</tr>
</table><br/>
<input type='hidden' name='_ordered_by' value="<?php echo isset($col[2]) ? $col[2] : "" ?>">
</form>
<p align="center">
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save DO"> &nbsp; Save DO</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_do_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel DO"> &nbsp; Cancel DO</button>
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