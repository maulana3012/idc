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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "revise_po.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php");
} else {
	$_code = urldecode($_GET['_code']); 
}
$title = array(1=>"Issue PO &amp; Order Item","Invoice only");

//PROCESS FORM
require_once APP_DIR . "_include/purchasing/tpl_process_form.php";

//DEFAULT PROCESS =====================================================================================================
$sql	= "SELECT *, ".ZKP_SQL."_getPLCode('$_code') AS pl_idx FROM ".ZKP_SQL."_tb_po WHERE po_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if($column["po_confirmed_timestamp"] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
}

$sql_item = "
SELECT 
 poit.icat_midx, 		--0
 poit.it_code,			--1
 poit.poit_item,		--2
 poit.poit_desc,		--3
 poit.poit_unit_price,	--4
 poit.poit_qty,			--5
 CASE
 	WHEN po.po_layout_type = 3 THEN poit.poit_unit_price * poit.poit_qty/100
 	ELSE poit.poit_unit_price * poit.poit_qty 
 END AS amount,			--6
 poit.poit_remark,		--7
 poit.poit_attribute	--8
FROM ".ZKP_SQL."_tb_po AS po JOIN ".ZKP_SQL."_tb_po_item AS poit USING (po_code)
WHERE po.po_code = '$_code'
ORDER BY poit.it_code";
$res_item	=& query($sql_item);

$sql_pl =
"SELECT
  a.inpl_idx,
  to_char(a.inpl_checked_date,'dd-Mon-YY') AS checked_date,
  a.inpl_inv_no,
  c.it_code,
  c.it_model_no,
  c.it_desc,
  b.init_qty
FROM
  ".ZKP_SQL."_tb_in_pl AS a
  JOIN ".ZKP_SQL."_tb_in_pl_item AS b USING(inpl_idx)
  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE a.po_code = '$_code'
ORDER BY a.inpl_idx, a.inpl_checked_date, c.it_code 
";

$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res_pl = query($sql_pl);

while($col =& fetchRowAssoc($res_pl)) {

	$rd[] = array(
		$col['inpl_idx'],		//0
		$col['checked_date'],	//1
		$col['inpl_inv_no'],	//2
		$col['it_code'], 		//3
		$col['it_model_no'],	//4
		$col['it_desc'],		//5
		$col['init_qty'] 		//6
	);

	//1st grouping
	if($cache[0] != $col['inpl_idx']) {
		$cache[0] = $col['inpl_idx'];
		$group0[$col['inpl_idx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['inpl_idx']][$col['it_code']] = 1;
}
$g_total = 0;
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
function reCalculationTotal() {
	var count = window.rowPosition.rows.length;
	var sumOfQty	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseInt(removecomma(oRow.cells(5).innerText));
		sumOfTotal = sumOfTotal + parseFloat(removecomma(oRow.cells(6).innerText));
	}

	window.document.all.totalQty.value = addcomma(sumOfQty);
	window.document.all.totalAmount.value = numFormatval(sumOfTotal + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="reCalculationTotal()">
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
<table width="100%">
  <tr>
	<td>
		<strong style="font-size:18px;font-weight:bold">
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL PO<br />
		</strong>
	</td>
  </tr>
  <tr>
	<td colspan="2"><small class="comment">* <?php echo $title[$column['po_type_invoice']] ?></small></td>
  </tr>
</table>
<hr><br />
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><span class="bar_bl">PO INFORMATION</span></td>
		<td colspan="2" align="right">
			<I>Confirmed by : <?php echo $column['po_confirmed_by_account'].date(', j-M-Y g:i:s', strtotime($column['po_confirmed_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th width="15%">PO NO</th>
		<td><span class="bar"><?php echo $column['po_code'] ?></span></td>
		<th width="15%">PO DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column['po_date'])) ?></td>
	</tr>
	<tr>
		<th width="15%">PO TYPE</th>
		<td>
			<input type="radio" name="_type" value="1" disabled <?php echo ($column['po_type'] == 1) ? "checked" : "" ?>>NORMAL &nbsp;
			<input type="radio" name="_type" value="2" disabled <?php echo ($column['po_type'] == 2) ? "checked" : "" ?>>DOOR TO DOOR
		</td>
		<th>SHIPMENT MODE</th>
		<td>
			<input type="radio" name="_shipment_mode" value="sea" <?php echo (trim($column['po_shipment_mode']) == 'sea') ? "checked" : "" ?> disabled>SEA &nbsp; 
			<input type="radio" name="_shipment_mode" value="air" <?php echo (trim($column['po_shipment_mode']) == 'air') ? "checked" : "" ?> disabled>AIR &nbsp; 
			<input type="radio" name="_shipment_mode" value="other" <?php echo ($column['po_shipment_mode'] == 'other') ? "checked" : "" ?> disabled>OTHER &nbsp; 
			<input type="text" name="_mode_desc" class="fmt" size="10" maxlength="15" value="<?php echo $column["po_shipment_desc"] ?>" disabled>
		</td>
	</tr>
	<tr>
		<th width="15%">RECEIVED BY</th>
		<td width="34%"><?php echo $column['po_received_by']?></td>
		<th>LAYOUT TYPE</th>
		<td>
			<input type="radio" name="_layout_type" value="1" <?php echo ($column['po_layout_type'] == 1) ? "checked" : "" ?> disabled>1 &nbsp; 
			<input type="radio" name="_layout_type" value="2" <?php echo ($column['po_layout_type'] == 2) ? "checked" : "" ?> disabled>2 &nbsp;
			<input type="radio" name="_layout_type" value="3" <?php echo ($column['po_layout_type'] == 3) ? "checked" : "" ?> disabled>3 &nbsp;
			<input type="radio" name="_layout_type" value="4" <?php echo ($column['po_layout_type'] == 4) ? "checked" : "" ?> disabled>4
		</td>
	</tr>
	<tr>
		<th>CURRENCY TYPE</th>
		<td>
			<input type="radio" name="_currency_type" value="1" <?php echo ($column['po_currency_type'] == 1) ? "checked" : "" ?> disabled>USD &nbsp;
			<input type="radio" name="_currency_type" value="2" <?php echo ($column['po_currency_type'] == 2) ? "checked" : "" ?> disabled>RUPIAH &nbsp;
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="12%">SUPPLIER</th>
		<th width="12%">CODE</th>
		<td width="25%"><?php echo $column['po_sp_code']?></td>
		<th width="15%">NAME</th>
		<td width="43%"><?php echo $column['po_sp_name']?></td>
	</tr>
</table><br />
<span class="bar_bl">ITEM LIST</span>
<table width="100%" class="table_nn">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="17%">ITEM</th>
			<th width="25%">DESC</th>
			<th width="5%">ATT</th>
			<th width="12%">UNIT PRICE<br />(US$)</th>
			<th width="8%">QTY</th>
			<th width="12%">AMOUNT<br />(US$)</th>
			<th width="11%">REMARK</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php while($items =& fetchRow($res_item)) { ?>
		<tr id="<?php echo trim($items[1])?>">
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td><?php echo substr($items[3],0,32)?></td>
			<td><?php echo trim($items[8])?></td>
			<td align="right"><?php echo number_format($items[4],2)?></td>
			<td align="right"><?php echo number_format($items[5])?></td>
			<td align="right"><?php echo number_format($items[6],2)?></td>
			<td> &nbsp; &nbsp; <?php echo $items[7]?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th width="64%" align="right">GRAND TOTAL</th>
		<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="1%">&nbsp;</th>
		<th width="11%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="11%">&nbsp;</th>
	</tr>
</table><br>
<span class="bar_bl">OTHERS</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PREPARED BY</th>
		<td width="40%"><?php echo $column['po_prepared_by']?></td>
		<th width="15%">CONFIRMED BY</th>
		<td><?php echo $column['po_confirmed_by']?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="3" readonly><?php echo $column['po_remark']?></textarea></td>
	</tr>
	<tr>
		<th>PO PRINT<br />REMARK</th>
		<td colspan="3"><textarea name="_print_remark" style="width:100%" rows="4" readonly><?php echo $column['po_doc_remark']?></textarea></td>
	</tr>
</table><br /><br />
<?php if($column['pl_idx'] != '') { 
	$part = "detail_pl_po";
	include "../../_include/purchasing/tpl_detail_po.php";  
}
?>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['po_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
</table><br /><br />
<script language="javascript" type="text/javascript">

	window.document.all.btnPrint.onclick = function() {
		<?php
		if(ZKP_SQL == 'IDC') {
			if($column['po_type'] == 1) 	 $code = substr($_code,0,2)."-". substr($_code,3,2)."-".substr($_code,6,2);
			else if($column['po_type'] == 2) $code = substr($_code,0,2)."-". substr($_code,3,3)."-".substr($_code,7,2);
		} else if(ZKP_SQL == 'MED') {
			$no = explode('/', $_code);
			if ($no[0] < 100) {
				if($column['po_type'] == 1)		$code = substr($_code,0,2)."-". substr($_code,3,4)."-".substr($_code,8,2);
				else if($column['po_type'] == 2)	$code = substr($_code,0,2)."-". substr($_code,3,5)."-".substr($_code,9,2);
			} else {
				if($column['po_type'] == 1)		$code = substr($_code,0,3)."-". substr($_code,4,4)."-".substr($_code,9,2);
				else if($column['po_type'] == 2)	$code = substr($_code,0,3)."-". substr($_code,4,5)."-".substr($_code,10,2);
			}
		}
		?>
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/purchasing/pdf/download_pdf.php?_code=<?php echo $code ?>&_po_date=<?php echo $column['po_date'] ?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php" ?>';
	}
</script>
<?php
if($column['pl_idx'] == '') {
	$part = "detail_uncfm";			include "../../_include/purchasing/tpl_detail_po.php"; 
} else {
	$part = "detail_uncfm_disabled";	include "../../_include/purchasing/tpl_detail_po.php"; 
}
?>
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