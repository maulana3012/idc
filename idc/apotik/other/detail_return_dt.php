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
$left_loc = 'daily_summary_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//========================================================================================= DEFAULT PROCESS
//return dt
$sql	= "
SELECT *, 
  (SELECT std_idx FROM ".ZKP_SQL."_tb_outstanding WHERE std_doc_ref='$_code' AND std_doc_type='Return DT') AS std_idx,
  (SELECT inc_idx FROM ".ZKP_SQL."_tb_incoming WHERE inc_doc_ref='$_code' AND inc_doc_type='Return DT') AS inc_idx
FROM ".ZKP_SQL."_tb_return_dt WHERE rdt_code = '$_code'";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['rdt_cfm_wh_delivery_by_account'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return_dt.php?_code=".urlencode($column['rdt_code']));
}

//[WAREHOUSE] dt item
$whitem_sql = "
SELECT
  it_code,			--0
  it_model_no,		--1
  it_desc,			--2
  istd_type,		--3
  istd_qty,			--4
  istd_remark
FROM
  ".ZKP_SQL."_tb_outstanding_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE std_idx = {$column['std_idx']}
ORDER BY it_code";
$whitem_res	=& query($whitem_sql);

//[CUSTOMER] dt item
$cusitem_sql = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.rdtit_qty,			--3
  b.rdtit_remark 		--4
FROM
  ".ZKP_SQL."_tb_return_dt_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE rdt_code = '$_code'
ORDER BY it_code";
$cusitem_res	=& query($cusitem_sql);
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
function checkform(o) {
	if (o.totalComingQty.value <= 0) {
		alert("Return DT should be has at least 1 return qty");
		return;
	}

	if (window.itemCusPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save Return DT?")) {
			o.submit();
		}
	}
}

function fillCustomer(target) {
	if (target == 'customer') {
		keyword = window.document.frmInsert._cus_to.value;
	} else if (target == 'ship') {
		keyword = window.document.frmInsert._ship_to.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'../../_include/other/p_list_cus_code.php?_dept=<?php echo $department ?>&_check_code='+ keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Reculate Amount base on the form element
function reCalculationTotal(){
	var countI	= window.itemWHPosition.rows.length;
	var countII	= window.itemCusPosition.rows.length;
	var sumOfQtyI	= 0;
	var sumOfQtyII	= 0;
	
	for (var i=0; i<countI; i++) {
		var oRow = window.itemWHPosition.rows(i);
		sumOfQtyI = sumOfQtyI + parseFloat(removecomma(oRow.cells(3).innerText));
	}

	for (var i=0; i<countII; i++) {
		var oRow = window.itemCusPosition.rows(i);
		sumOfQtyII = sumOfQtyII + parseFloat(removecomma(oRow.cells(3).innerText));
	}

	window.document.all.totalQty.value		= numFormatval(sumOfQtyI+'',2);
	window.document.all.totalCusQty.value	= addcomma(sumOfQtyII);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="reCalculationTotal()">
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL RETURN DT</strong><br /><br />
<strong class="info">RETURN INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">RETURN NO</th>
		<td width="30%"><b><?php echo $column["rdt_code"] ?></b></td>
		<th width="12%">RETURN DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column['rdt_date'])) ?></td>
	</tr>
	<tr>
		<th>ISSUED BY</th>
		<td><?php echo $column["rdt_issued_by"] ?></td>
		<th>TYPE INVOICE</th>
		<td>
			<input type="radio" name="_vat" value="1" disabled <?php echo ($column["rdt_type_item"]==1)?'checked':''?>> Vat &nbsp;
			<input type="radio" name="_vat" value="2" disabled <?php echo ($column["rdt_type_item"]==2)?'checked':''?>> Non Vat &nbsp;
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="12%">CUSTOMER</th>
		<th width="10%">CODE</th>
		<td width="20%"><?php echo $column['rdt_cus_to'] ?></td>
		<th width="12%">NAME</th>
		<td><?php echo $column['rdt_cus_name'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['rdt_cus_address'] ?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th>CODE</th>
		<td><?php echo $column['rdt_ship_to'] ?></td>
		<th>NAME</th>
		<td><?php echo $column['rdt_ship_name'] ?></td>
	</tr>
	<tr>
		<th>DT NO</th>
		<th>CODE</th>
		<td><a href="detail_dt.php?_code=<?php echo $column['dt_code'] ?>" target="_blank"><b><?php echo $column['dt_code'] ?></b></a></td>
		<th>DT DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column['rdt_dt_date'])) ?></td>
	</tr>
</table><br />
<strong class="info">[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong>
<table width="100%" class="table_l" cellspacing="1">
	<thead>
		<tr height="30px">
			<th width="8%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr>
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format((double)$items[4],2)?></td>
			<td><?php echo $items[5]?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="15%">&nbsp;</th>
</table><br />
<strong class="info">[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr height="30px">
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr>
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format((double)$items[3])?></td>
			<td><?php echo $items[4]?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalCusQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="15%">&nbsp;</th>
	</tr>
</table><br />
<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2" value="<?php echo $column["rdt_delivery_warehouse"] ?>" disabled>ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="<?php echo $column["rdt_delivery_franco"] ?>" disabled>Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" size="6" class="fmt" value="<?php echo $column["rdt_delivery_by"] ?>" disabled></td>
		<td>Freight charge : Rp <?php echo number_format((double)$column["rdt_delivery_freight_charge"]) ?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="4"><textarea name="_remark" style="width:100%" rows="4" readonly><?php echo $column["rdt_remark"] ?></textarea></td>
	</tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><span class="comment"><i>Confirm incoming item from warehouse by : <?php echo $column['rdt_cfm_wh_delivery_by_account'].date(', j-M-Y g:i:s', strtotime($column['rdt_cfm_wh_delivery_timestamp']))?></i></span></td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['rdt_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/other/pdf/download_pdf.php?_code=<?php echo trim($_code)."&_dept=".$currentDept."&_date=".date("Ym", strtotime($column['rdt_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_summary_by_group.php?cboSource=rdt" ?>';
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