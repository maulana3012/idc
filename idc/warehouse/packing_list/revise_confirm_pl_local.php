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
ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "summary_arrival_by_supplier.php";
if (!isset($_GET['_inlc_idx']) || $_GET['_inlc_idx'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_pl_by_supplier.php");
} else {
	$_inlc_idx	= $_GET['_inlc_idx'];
}

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_packing_list.php";

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
if($S->getValue("ma_authority") & 32)	{ $page_permission = false;}
else 									{ $page_permission = true;}

if($page_permission) {
	$result = new ZKError(
				"NOT_ENOUGH_AUTHORITY",
				"NOT_ENOUGH_AUTHORITY",
				"You don't have authority to update PL. Please contact the administrator");
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_confirm_pl_local.php?_inlc_idx=$_inlc_idx");
}

$sql	= "
SELECT * 
FROM
  ".ZKP_SQL."_tb_po_local AS po
  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
  JOIN ".ZKP_SQL."_tb_in_local_v2 AS inlc USING(po_code, pl_no)
WHERE inlc_idx = $_inlc_idx";
$result = query($sql);
$column = fetchRowAssoc($result);

$sql_item = "
SELECT
 it.it_code,
 it.it_model_no,
 it.it_desc,
 it.it_ed,
 init.init_qty,
 ".ZKP_SQL."_canUsedQty(3, '".$column["po_code"].'-'.$column["pl_no"]."', inlc_idx, it.it_code) AS max_qty,
 null,
 null
FROM
 ".ZKP_SQL."_tb_item AS it
 JOIN ".ZKP_SQL."_tb_in_local_item_v2 AS init USING(it_code)
WHERE inlc_idx = $_inlc_idx AND init.init_qty > 0
ORDER BY it.it_code
";
$res_item = query($sql_item);

$sql_ed = "
SELECT
 it_code,
 it_model_no,
 to_char(ined_expired_date,'Mon-YYYY') as expired_date,
 ined_qty
FROM ".ZKP_SQL."_tb_in_local_item_ed join ".ZKP_SQL."_tb_item using(it_code) 
WHERE inlc_idx = $_inlc_idx
ORDER BY it_code,ined_expired_date";
$res_ed =& query($sql_ed);
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
function checkMaxQty(value, idx) {
	var f			= window.document.frmInsert;
	var e			= window.document.frmInsert.elements;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 7;
	var idx_max		= 11;		/////
	var idx_default	= idx_max+1;	
	var idx_value	= idx_max+2;
	var qty_value	= parseFloat(removecomma(e(idx_value+idx*numInput).value));
	var max_qty		= parseFloat(removecomma(e(idx_max+idx*numInput).value)); 

	if(qty_value > max_qty) {
		alert("Maximum qty for this item is " + addcomma(max_qty) +" pcs.\n Please check the amount again");
		e(idx_value+idx*numInput).value = numFormatval(e(idx_default+idx*numInput).value+'',0);
	}
	updateAmount();
}

var wInputED;
function insertED(code, i) {

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var oRow		= window.rowPosition.rows(i);
	var item		= oRow.cells(1).innerText;

	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 200) / 2;

	wInputED = window.open(
		'./p_input_ed.php?_code='+code+'&_item='+item, 'wSearchED',
		'scrollbars,width=450,height=200,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

function createED() {
	var o	= window.document.frmConfirm;
	var f2	= wInputED.document.frmInsert;

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();
	var d	= parseDate(f2.elements[2].value, 'prefer_euro_format');

	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)+'-'+trim(f2.elements[2].value)) {
			alert("Item ["+trim(f2.elements[0].value)+"] "+ f2.elements[1].value +" for E/D "+ formatDate(d, 'NNN-yyyy') + " already exist!");
			return;
		}
	}

	for (var i=0; i<5; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // ed_it_code
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // ed_it_model_no
				oTD[i].innerText	= f2.elements[1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_model_no[]";
				oTextbox[i].value	= f2.elements[1].value;
				break;

			case 2: // ed_it_date
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_date[]";
				oTextbox[i].value	= formatDate(d, '1-NNN-yyyy');
				break;

			case 3: // ed_it_qty
				oTD[i].innerText	= numFormatval(f2.elements[3].value+'',0);
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_qty[]";
				oTextbox[i].value	= removecomma(numFormatval(f2.elements[3].value+'',0));
				break;

			case 4: // DELETE
				oTD[i].innerHTML	= "<a href=\"javascript:deleteED('"+f2.elements[0].value+'-'+trim(f2.elements[2].value)+"')\"><img src=\"../../_images/icon/delete.gif\" width=\"12px\"></a>";
				oTD[i].align		= "center";
				break;
		}
		if (i!=4) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value)+'-'+trim(f2.elements[2].value);
		oTR.appendChild(oTD[i]);
	}
	window.EDPosition.appendChild(oTR);
} 

function deleteED(idx) {
	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.EDPosition.removeChild(oRow);
			count = count - 1;
		}
	}
}

function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 7;
	var idx_value	= 13;				/////
	var sumOfQty	= 0;
	var e			= window.document.frmInsert.elements;

	for (var i=0; i<numItem; i++) {
		var qty		= parseFloat(removecomma(e(idx_value+i*numInput).value));
		sumOfQty	+= qty;
	}
	f.totalQty.value	= addcomma(sumOfQty);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="updateAmount()">
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] RE-CONFIRM INCOMING PL<br />
</strong>
<small class="comment">* Source by PO Local</small>
<hr><br />
<table width="100%" class="table_box">
	<tr>
		<td colspan="4"><span class="bar_bl">PL INFORMATION</span></td>
	<tr>
		<th width="15%">PL NO</th>
		<td width="22%"><?php echo $column["po_code"] ?>&nbsp; &nbsp;#<?php echo $column["pl_no"] ?></td>
		<th width="7%">
			<a href="../purchasing/revise_po.php?_code=<?php echo $column["po_code"] ?>" target="_blank"><img src="../../_images/icon/list_mini.gif" alt="View detail PO"></a>
		</th>
		<td width="5%"></td>
		<th width="15%">PL DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column["pl_date"])) ?></td>
	</tr>
	<tr>
		<th width="15%">ISSUED BY</th>
		<td colspan="3"><?php echo $column["pl_issued_by"] ?></td>
		<th>DELIVERY DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column["pl_delivery_date"])) ?></td>
	</tr>
	<tr>
		<th width="15%">SUPPLIER</th>
		<td colspan="5"><?php echo '['.$column["sp_code"].'] '.$column["po_sp_name"] ?></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
</table><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type="hidden" name="_inlc_idx" value="<?php echo $column['inlc_idx']?>">
<input type="hidden" name="_po_code" value="<?php echo $column['po_code']?>">
<input type="hidden" name="_pl_no" value="<?php echo $column['pl_no']?>">
<input type="hidden" name="_inlc_type" value="<?php echo $column['inlc_type']?>">
<input type="hidden" name="_wh_location" value="<?php echo $column['inlc_warehouse']?>">
<input type="hidden" name="_arrival_date" value="<?php echo $column['inlc_checked_date']?>">
<span class="bar_bl">CONFIRM ARRIVAL ITEMS</span>
<table width="75%" class="table_box" cellspacing="1">
	<thead>
		<tr height="35px">
			<th width="10%">CODE</th>
			<th width="17%">ITEM</th>
			<th>DESC</th>
			<th width="10%">QTY</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php
$i = 0;
while($items =& fetchRow($res_item)) {
?>
		<tr id="<?php echo trim($items[0]) ?>">
			<td>
				<?php
				if($items[3]=='f') {echo $items[0]."\n";}
				else {echo "<a href=\"javascript:insertED('".trim($items[0])."',$i)\"><span class=\"bar\">".trim($items[0])."</span></a>\n";}
				?>
				<input type="hidden" name="_rcp_idx[]" value="<?php echo $items[7] ?>">
				<input type="hidden" name="_it_code[]" value="<?php echo trim($items[0]) ?>">
				<input type="hidden" name="_it_model_no[]" value="<?php echo $items[1] ?>">
				<input type="hidden" name="_it_ed[]" value="<?php echo ($items[3] == 't') ? 'true' : 'false'?>">
			</td>
			<td><?php echo $items[1]?></td>
			<td><?php echo cut_string($items[2],70)?></td>
			<td align="right">
				<input type="hidden" name="_max_qty[]" value="<?php echo $items[5] ?>">
				<input type="hidden" name="_default_qty[]" value="<?php echo $items[4] ?>">
				<input type="text" name="_it_qty[]" class="reqn" style="width:100%" value="<?php echo number_format($items[4])?>" onblur="checkMaxQty(<?php echo "'".trim($items[0])."',".$i++ ?>)" onKeyUp="formatNumber(this,'dot')">
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="75%" class="table_box">
	<tr>
		<th align="right">GRAND TOTAL</th>
		<th width="10%" align="right"><input type="text" name="totalQty" class="reqn" style="width:100%" readonly></th>
	</tr>
</table><br />
<span class="bar_bl">DETAIL ITEM PER E/D</span>
<table width="50%" class="table_l">
	<thead>
		<tr height="25px">
			<th width="15%">CODE</th>
			<th>ITEM NO</th>
			<th width="25%">E/D</th>
			<th width="15%">QTY</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="EDPosition">
<?php while($col =& fetchRow($res_ed)) { ?>
		<tr id="<?php echo trim($col[0]).'-1-'.$col[2] ?>">
			<td><input type="hidden" name="_ed_it_code[]" value="<?php echo trim($col[0]) ?>"><?php echo $col[0]?></td>
			<td><input type="hidden" name="_ed_it_model_no[]" value="<?php echo $col[1] ?>"><?php echo $col[1] ?></td>
			<td><input type="hidden" name="_ed_it_date[]" value="<?php echo "1-".$col[2] ?>"><?php echo $col[2] ?></td>
			<td align="right"><input type="hidden" name="_ed_it_qty[]" value="<?php echo number_format($col[3]) ?>"><?php echo number_format($col[3]) ?></td>
			<td align="center"><a href="javascript:deleteED('<?php echo trim($col[0]).'-1-'.$col[2] ?>')"><img src="../../_images/icon/delete.gif" width="12px"></a></td>
		</tr>
<?php } ?>
	</tbody>
</table><br /><br />
<span class="bar_bl">CONFIRM INFORMATION</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">ARRIVAL DATE</th>
		<td width="40%"><?php echo date("j-M-Y", strtotime($column["inlc_checked_date"])) ?></td>
		<th width="15%">CHECKED BY</th>
		<td><?php echo $column["inlc_checked_by"] ?></td>
	</tr>
	<tr>
		<th>WAREHOUSE</th>
		<td colspan="3">
			<input type="radio" name="_warehouse_name" value="1" disabled <?php echo ($column["inlc_warehouse"] == 1) ? 'checked' : '' ?>> INDOCORE &nbsp;
			<input type="radio" name="_warehouse_name" value="2" disabled <?php echo ($column["inlc_warehouse"] == 2) ? 'checked' : '' ?>> DNR
		</td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="3"><?php echo $column["inlc_remark"] ?></textarea></td>
	</tr>
</table>
<?php
//[WAREHOUSE] Incoming item
$init_sql = "
SELECT trim(it_code), init_qty
FROM
  ".ZKP_SQL."_tb_in_local_v2
  JOIN ".ZKP_SQL."_tb_in_local_item_v2 USING(inlc_idx)
WHERE inlc_idx = $_inlc_idx
ORDER BY it_code";
$init_res =& query($init_sql);
//echo $init_sql;
while($items =& fetchRow($init_res)) {
	$in_item[0][] = $items[0];
	$in_item[1][] = $items[1];
	echo "<input type=\"hidden\" name=\"_in_it_code[]\" value=\"". $items[0]. "\">";
	echo "<input type=\"hidden\" name=\"_in_it_qty[]\" value=\"". $items[1]. "\">\n";
}
?>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_btn' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete incoming PL"> &nbsp; Delete PL</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update incoming PL"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">

	var oForm = window.document.frmInsert;

	//Define the form that you want to handle
	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete_PL_local';
			oForm.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {
		var e			= window.document.frmInsert.elements;
		var countI		= window.rowPosition.rows.length;
		var countII		= window.EDPosition.rows.length;
		var numInput	= 7;
		var numInputII	= 4;
		var idx_code	= 8;				/////
		var idx_item	= idx_code+1;
		var idx_ed		= idx_code+2;
		var idx_qty		= idx_code+5;
		var idx_codeII	= idx_code+(numInput*countI)+0;
		var idx_qtyII	= idx_code+(numInput*countI)+3;

		//checking E/D
		for (var i=0; i<countI; i++) {
			if(e(idx_ed+i*numInput).value == 'true') {
				var istrue	= false;
				var code	= trim(e(idx_code+i*numInput).value);
				var item	= e(idx_item+i*numInput).value;
				var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

				if(countII<=0) { alert("Please complete data for outgoing Expired Date");return;}

				var temp_qty = 0;
				for (var j=0; j<countII; j++) {
					if(e(idx_codeII+j*numInputII).value==code) {
						if(parseFloat(removecomma(e(idx_qtyII+j*numInputII).value))=='') {
							var value = 0;
						} else {
							var value = parseFloat(removecomma(e(idx_qtyII+j*numInputII).value));
						}
						temp_qty = temp_qty + value;
					}
				}

				if(temp_qty != qty) {
					alert(
						"Check incoming expired date for:\n\n" +
						"Code : "+ code +"\nItem  : "+ item + "\n" +
						"Current arrived PL qty    : "+addcomma(qty)+"\n" +
						"Current inputed E/D qty : "+addcomma(temp_qty));
					return;
				}
	 		}
		}

		if(verify(oForm)){
			if(confirm("Are you sure to change incoming PL?")) {
				oForm.p_mode.value = 'update_PL_local';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/summary_arrival_by_supplier.php" ?>';
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