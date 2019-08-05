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
	$_inc_idx = $_GET['_inc_idx'];
	$_std_idx = $_GET['_std_idx'];
}

$type[1]	= '[ref return billing : ';
$type[2]	= '[ref return order : ';

//========================================================================================= unconfirm return
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'unconfirmed')) {

	$_inc_idx	= $_POST["_inc_idx"];
	$_std_idx	= $_POST["_std_idx"];
	$_doc_type	= $_POST["_doc_type"];
	$_doc_ref	= $_POST["_doc_ref"];
	$_admin_account	 = $S->getValue('ma_idx');
	$_admin_password = md5($_POST["_password"]);
	$_log_by		 = $S->getValue('ma_account');

	//unConfirmedDO
	$result = executeSP(
		ZKP_SQL."_unConfirmedReturn",
		$_inc_idx,
		$_std_idx,
		$_doc_type,
		"$\${$_doc_ref}$\$",
		$_admin_account,
		"$\${$_admin_password}$\$",
		"$\${$_log_by}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "FAIL_TO_AUTH")) {
			$result = new ZKError(
				"FAIL_TO_AUTHORITY",
				"FAIL_TO_AUTHORITY",
				"Your password wrong, please Try again. Also check [Caps Lock] Key");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
	}

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
}

//========================================================================================= DEFAULT PROCESS
$sql =	"
  SELECT *,
  ".ZKP_SQL."_isLockedConditionReturn(inc_idx) AS is_locked, 
  (SELECT std_revision_time FROM ".ZKP_SQL."_tb_outstanding WHERE std_idx = $_std_idx) AS revision_time,
  ".ZKP_SQL."_getReturnInvoiceReference(inc_doc_type, inc_doc_ref) AS inv_no,
  ".ZKP_SQL."_getReturnDateInvoiceReference(inc_doc_type, inc_doc_ref) AS inv_date
FROM ".ZKP_SQL."_tb_incoming join ".ZKP_SQL."_tb_customer using(cus_code) WHERE inc_idx = $_inc_idx";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0)
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
else if($column['inc_is_confirmed'] == 'f')
	goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
 else if($column['inc_doc_type'] == 3) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_return_dt.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
}

//[CUSTOMER] item
if($column['inc_doc_type'] == 1)  {
	$cus_sql = "SELECT a.it_code, a.it_model_no, a.it_desc, reit_qty, reit_remark FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING (turn_code) JOIN ".ZKP_SQL."_tb_item AS a USING(it_code) WHERE turn_code = '".trim($column['inc_doc_ref'])."'";	
} else if($column['inc_doc_type'] == 2)  {
	$cus_sql = "SELECT a.it_code, a.it_model_no, a.it_desc, roit_qty, roit_remark FROM ".ZKP_SQL."_tb_return_order JOIN ".ZKP_SQL."_tb_return_order_item USING (reor_code) JOIN ".ZKP_SQL."_tb_item AS a USING(it_code) WHERE reor_code = '".trim($column['inc_doc_ref'])."'";
} else if($column['inc_doc_type'] == 3)  {
	$cus_sql = "SELECT a.it_code, a.it_model_no, a.it_desc, rdtit_qty, rdtit_remark FROM ".ZKP_SQL."_tb_return_dt JOIN ".ZKP_SQL."_tb_return_dt_item USING (rdt_code) JOIN ".ZKP_SQL."_tb_item AS a USING(it_code) WHERE rdt_code = '".trim($column['inc_doc_ref'])."'";
}
$cus_res	=& query($cus_sql);

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
  a.it_code,
  a.it_model_no,
  a.it_desc,
  a.it_ed,
  b.init_qty AS qty,
  b.init_stock_qty,
  b.init_demo_qty,
  b.init_reject_qty
FROM ".ZKP_SQL."_tb_incoming_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE inc_idx = $_inc_idx
ORDER BY it_code";
$inc_res	 =& query($inc_sql);

//E/D Stock
$ed_stock_sql = "
SELECT
  a.it_code,
  a.it_model_no,
  ised_wh_location,
  to_char(ised_expired_date, 'Mon-YYYY') AS expired_date,
  ised_qty,
  ised_expired_date
FROM ".ZKP_SQL."_tb_incoming_stock_ed as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE inc_idx = $_inc_idx
ORDER BY it_code,ised_expired_date";
$ed_stk_res		=& query($ed_stock_sql);

//E/D Demo
$ed_demo_sql = "
SELECT
  a.it_code,
  a.it_model_no,
  to_char(ided_expired_date, 'Mon-YYYY') AS expired_date,
  ided_qty,
  ided_expired_date
FROM ".ZKP_SQL."_tb_incoming_ed_demo as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE inc_idx = $_inc_idx
ORDER BY it_code,ided_expired_date";
$ed_demo_res	=& query($ed_demo_sql);

//Reject Detail
$reject_sql = "
SELECT
  it_code, rjit_serial_number, to_char(rjit_warranty,'Mon-YYYY') AS warranty, rjit_desc
FROM ".ZKP_SQL."_tb_reject JOIN ".ZKP_SQL."_tb_reject_item USING(rjt_idx) WHERE rjt_doc_idx = $_inc_idx AND rjt_doc_type = 1 ORDER BY it_code";
$rjt_res	=& query($reject_sql);
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL RETURN &nbsp; <small class="comment"><?php echo $type[$column['inc_doc_type']] . trim($column['inc_doc_ref']).']'?></small></h4>
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><strong class="info">RETURN INFORMATION</strong></td>
		<td colspan="5" align="right"><span class="comment"><i>Confirm by : <?php echo $column['inc_confirmed_by_account'].date(', d-M-Y g:i:s',strtotime($column['inc_confirmed_timestamp'])) ?></i></span></td>
	</tr>
	<tr>
		<th width="15%">RETURN NO</th>
		<td width="25%" colspan="2"><b><?php echo $column['inc_doc_ref'] ?></b></td>
		<th width="15%">RETURN DATE</th>
		<td width="15%"><?php echo date('d-M-Y', strtotime($column['inc_date'])) ?></td>
		<th width="15%">RECEIVED BY</th>
		<td><?php echo $column['inc_received_by'] ?></td>
	</tr>
	<tr>
		<th>INV NO</th>
		<td colspan="2"><?php echo $column['inv_no'] ?></td>
		<th>INV DATE</th>
		<td><?php echo ($column['inv_date'] != '') ? date('d-M-Y', strtotime($column['inv_date'])) : "" ?></td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<th rowspan="3">CUSTOMER<br />SHIP TO</th>
		<th width="10%">CODE</th>
		<td colspan="6"><?php echo '[' . trim($column['cus_code']) . '] ' . $column['cus_full_name'] ?></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="6"><?php echo $column['cus_address'] ?></td>
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
<?php
$amount		= 0;
while($items =& fetchRow($cus_res)) {
?>
			<tr>
				<td><?php echo $items[0]?></td>
				<td><?php echo $items[1]?></td>
				<td><?php echo $items[2]?></td>
				<td align="right"><?php echo number_format((double)$items[3],2)?></td>
				<td><?php echo $items[4]?></td>
			</tr>
<?php 
	$amount +=  $items[3];
}
?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="7%" align="right"><?php echo number_format((double)$amount,2) ?></th>
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
<table width="100%" class="table_nn" cellspacing="1">
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
while($items =& fetchRow($inc_res)) {
?>
	<tr>
		<td><?php echo $items[0] ?></td>
		<td><?php echo $items[1]?></td>
		<td><?php echo $items[2]?></td>
		<td align="right"><?php echo number_format($items[4],2)?></td>
		<td align="right"><?php echo number_format($items[5],2)?></td>
		<td align="right"><?php echo number_format($items[6],2)?></td>
		<td align="right"><?php echo number_format($items[7],2)?></td>
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
		<th colspan="3"></th>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="54%" valign="top">
			<strong class="info">[<font color="#315c87">STOCK</font>] DETAIL ITEM PER E/D</strong>
			<table width="100%" class="table_l">
			  <thead>
				<tr>
					<th width="15%">CODE</th>
					<th>ITEM NO</th>
					<th width="10%">LOCATION</th>
					<th width="25%">E/D</th>
					<th width="15%">QTY</th>
				</tr>
			  </thead>
			  <tbody id="stockPosition">
<?php while($items =& fetchRow($ed_stk_res)) { ?>
	<tr>
		<td><?php echo $items[0] ?></td>
		<td><?php echo $items[1]?></td>
		<td align="center"><?php echo $cboFilter[3]['warehouse'][ZKP_FUNCTION][$items[2]-1][1] ?></td>
		<td align="center"><?php echo $items[3]?></td>
		<td align="right"><?php echo number_format($items[4],2)?></td>
	</tr>
<?php } ?>
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
					<th width="25%">E/D</th>
					<th width="15%">QTY</th>
				</tr>
			  </thead>
			  <tbody id="demoPosition">
<?php while($items =& fetchRow($ed_demo_res)) { ?>
	<tr>
		<td><?php echo $items[0] ?></td>
		<td><?php echo $items[1]?></td>
		<td align="center"><?php echo $items[2]?></td>
		<td align="right"><?php echo number_format($items[3],2)?></td>
	</tr>
<?php } ?>
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
		<th colspan="2">DESCRIPTION</th>
	</tr>
  </thead>
  <tbody id="rejectPosition">
<?php while($items =& fetchRow($rjt_res)) { ?>
	<tr>
		<td><?php echo $items[0] ?></td>
		<td align="center"><?php echo $items[1]?></td>
		<td align="center"><?php echo $items[2]?></td>
		<td width="8%"></td>
		<td><?php echo $items[3]?></td>
	</tr>
<?php } ?>
  </tbody>
</table><br />
<strong class="info">OTHERS</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="4" readonly><?php echo $column['inc_remark'] ?></textarea></td>
	</tr>
</table><br />
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['revision_time']; $counter >= 0; $counter--) {
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
		winforPrint.document.location.href = "../../_include/warehouse/pdf/download_do_pdf.php?_type=return&_code=<?php echo trim($column['inc_doc_ref'])."&_date=".date("Ym", strtotime($column['inc_date'])) ?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = "daily_delivery_by_group.php?cboSource=<?php echo $column["inc_doc_type"]?>";
	}
</script>
<!---------------------------------------- start print unconfirm DO ---------------------------------------->
<?php if($S->getValue("ma_authority") & 128 && $column['is_locked']=='f') { ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>UNCONFIRM RETURN</strong></th>
    </tr>
</table><br />
<form name="unconfirmReturn" method="post">
<input type="hidden" name="p_mode" value="unconfirmed">
<input type="hidden" name="_inc_idx" value="<?php echo $_inc_idx ?>">
<input type="hidden" name="_std_idx" value="<?php echo $_std_idx ?>">
<input type="hidden" name="_doc_type" value="<?php echo $column['inc_doc_type'] ?>">
<input type="hidden" name="_doc_ref" value="<?php echo trim($column['inc_doc_ref']) ?>">
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Account Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
			<table width="100%" class="table_box">
				<tr>
					<td width="15%">Account Name</td>
					<td width="2%">:</td>
					<td><b><?php echo ucfirst($S->getValue('ma_account')) ?></b></td>
					<td width="15%">Account Password</td>
					<td width="2%">:</td>
					<td width="15%"><input type="password" name="_password" class="reqd" size="15" value=""></td>
					<td align="right">
						<button name='btnUnConfirm' class='input_btn' style='width:130px;'><img src="../../_images/icon/clean.gif" align="middle"> &nbsp; Unconfirm</button>
					</td>
				</tr>
			</table>
    	</td>
    </tr>
</table>
</form>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.unconfirmReturn;

	window.document.unconfirmReturn.btnUnConfirm.onclick = function() {
		var f = window.document.unconfirmReturn;

		if(f._password.value.length <= 0) {
			alert('PASSWORD must be entered');
			return;
		}

		if(confirm("Are you sure to unconfirmed Return?")) {
			f.submit();
		}
	}
</script>
<?php } else { ?>
<table width="100%" class="table_box">
	<tr>
		<td width="10%" valign="top"><img src="../../_images/icon/warning.gif"></td>
		<td>
			<span>
			This is a locked document. To modify document, see the hierarchy process.<br /><br />
			Here are the possibility(es) :
			</span>
			<ul>
			<?php if($column['inc_doc_type']=='1') {?>
				<li> If this is a Return Replace (RR), probably already has DR</li>
			<?php } ?>
				<li> Qty in this return move to demo stock &amp; already confirmed by marketing</li>
				<li> Reject qty already modify</li>
			</ul>
		</td>
	</tr>
</table>
<?php } ?>
<!---------------------------------------- end print unconfirm DO ---------------------------------------->
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