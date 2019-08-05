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
$left_loc = 'daily_delivery_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
else $_code = urldecode($_GET['_code']);

$type[6]	= '[ref marketing request : ';

//========================================================================================= unconfirm DO
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'unconfirmed')) {

	$_out_idx		 = $_POST["_out_idx"]; 
	$_book_idx		 = $_POST["_book_idx"];
	$_ref_type		 = $_POST["_ref_type"];
	$_ref_doc		 = $_POST["_ref_doc"];
	$_admin_account	 = $S->getValue('ma_idx');
	$_admin_password = md5($_POST["_password"]);
	$_log_by		 = $S->getValue('ma_account');

	//unConfirmedDO
	$result = executeSP(
		ZKP_SQL."_unConfirmedDeliveryStock",
		$_out_idx,
		$_book_idx,
		$_ref_type,
		"$\${$_ref_doc}$\$",
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
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_do.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_do.php?_code=$_book_idx");
}

//========================================================================================= DEFAULT PROCESS
$sql = "SELECT *,".ZKP_SQL."_isLockedCondition(book_doc_type,book_doc_ref) AS is_locked FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_booking using(cus_code) JOIN ".ZKP_SQL."_tb_outgoing on book_idx=out_book_idx WHERE out_idx = $_code";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['book_doc_type']!=6) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_do.php?_code=$_code");
}

//first summary
$whitem_sql = "
SELECT
  a.it_code,			--0
  a.icat_midx,			--1
  a.it_model_no,		--2
  a.it_type,			--3
  a.it_desc,			--4
  (select it_model_no from ".ZKP_SQL."_tb_item where it_code=b.boit_it_code_for) AS it_used_for,	--5
  b.boit_qty,			--6
  b.boit_function,		--7
  b.boit_remark, 		--8
  b.boit_type,			--9
  a.it_ed				--10
FROM
  ".ZKP_SQL."_tb_booking AS c
  JOIN ".ZKP_SQL."_tb_booking_item AS b USING(book_idx)
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE c.book_doc_ref = '".trim($column["book_doc_ref"])."'
ORDER BY a.it_code";

//second summary
$summary_sql = "
SELECT 
  a.it_code,
  a.it_model_no,
  a.it_desc,
  b.otit_qty
FROM
  ".ZKP_SQL."_tb_outgoing AS c
  JOIN ".ZKP_SQL."_tb_outgoing_item AS b USING(out_idx)
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE c.out_idx = $_code
ORDER BY a.it_code
";

//ed
$ed_sql = "
SELECT 
  a.it_code,			--0
  a.it_model_no,		--1
  b.oted_wh_location,	--2
  b.oted_type,			--3
  to_char(b.oted_date,'Mon-YYYY') AS exp_date,	--4
  b.oted_qty			--5
FROM
  ".ZKP_SQL."_tb_outgoing AS c
  JOIN ".ZKP_SQL."_tb_outgoing_ed AS b USING(out_idx)
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE c.out_idx = $_code
ORDER BY a.it_code, b.oted_date
";
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL REQUEST DEMO UNIT &nbsp; <small class="comment"><?php echo $type[$column['out_doc_type']] . trim($column['out_doc_ref']).']'?></small></h4>
	<table width="100%" class="table_box">
		<tr>
			<td><strong class="info">DO INFORMATION</strong></td>
			<td colspan="5" align="right"><span class="comment"><i>Confirmed at: <?php echo date('d-M-Y g:i:s',strtotime($column["out_cfm_timestamp"])) ?></i></span></td>
		</tr>
		<tr>
			<th width="15%">REQUEST NO</th>
			<td><b><?php echo $column['book_code'] ?></b></td>
			<th width="15%">ISSUED BY</th>
			<td width="15%"><?php echo $column['book_received_by'] ?></td>
			<th width="15%">DATE</th>
			<td><?php echo date('d-M-Y', strtotime($column['book_date'])) ?></td>
		</tr>
	</table><br />
	<strong class="info">SUMMARY ITEM</strong>
	<table width="100%" class="table_box" cellspacing="1">
	  <thead>
		<tr>
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">QTY</th>
		</tr>
	  </thead>
	  <tbody id="itemStockPosition">
<?php
$i = 0;
$amount = 0;
$result	=& query($summary_sql);
while($items =& fetchRow($result)) {
?>
		<tr>
			<td>
				<input type="hidden" name="_it_code[]" value="<?php echo $items[0] ?>">
				<input type="hidden" name="_it_ed[]" value="<?php echo $items[3] ?>">
				<?php echo $items[0] ?>
			</td>
			<td><?php echo $items[1]?><input type="hidden" name="_it_model_no[]" value="<?php echo $items[1] ?>"></td>
			<td><?php echo $items[2]?></td>
			<td><input type="text" name="_it_booked_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format($items[3],2)?>" readonly></td>			
		</tr>
<?php
	$amount += $items[3];
	$i++; 
}
?>
	  </tbody>
		<tr>
			<th align="right" colspan="3">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format($amount,2) ?>" readonly></th>
</tr>

	</table><br />
	<strong class="info">DETAIL ITEM PER E/D</strong>
	<table width="75%" class="table_l">
	  <thead>
		<tr>
			<th width="15%">CODE</th>
			<th>ITEM NO</th>
			<th width="10%">SOURCE</th>
			<th width="10%">TYPE</th>
			<th width="20%">E/D</th>
			<th width="15%">QTY</th>
		</tr>
	  </thead>
	  <tbody id="EDPosition">
<?php
$result	=& query($ed_sql);
while($items =& fetchRow($result)) {
?>
		<tr>
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[1]?></td>
			<td align="center"><?php echo $cboFilter[3]['warehouse'][ZKP_FUNCTION][$items[2]-1][1] ?></td>
			<td align="center"><?php echo ($items[3]==1)?'VAT':'NON' ?></td>
			<td><?php echo $items[4] ?></td>
			<td align="right"><?php echo number_format($items[5],2) ?></td>
		</tr>
<?php } ?>
	  </tbody>
	</table><br />
	<strong class="info">OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">CONFIRMED BY</th>
			<td width="30%"><?php echo $column["out_cfm_by_account"] ?></td>
			<th width="15%">CONFIRMED DATE</th>
			<td><?php echo date('d-M-Y',strtotime($column["out_cfm_date"])) ?></td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="4" readonly><?php echo $column["out_remark"] ?></textarea></td>
		</tr>
	</table><br />
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['book_revision_time']; $counter >= 0; $counter--) {
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
		winforPrint.document.location.href = "../../_include/warehouse/pdf/download_do_pdf.php?_type=do&_code=<?php echo 'D'. substr(trim($column['out_doc_ref']),1)."&_date=".date("Ym", strtotime($column["out_cfm_timestamp"])) ?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = "daily_delivery_by_group.php?cboSource=<?php echo $column["out_doc_type"]?>";
	}
</script>
<!---------------------------------------- start print unconfirm ---------------------------------------->
<?php 
if($S->getValue("ma_authority") & 64) {
	if($column['is_locked']=='f') {
?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><strong>UNCONFIRM DO</strong></th>
    </tr>
</table><br />
<form name="frmUnconfirmed" method="post">
<input type="hidden" name="p_mode" value="unconfirmed">
<input type="hidden" name="_out_idx" value="<?php echo $column['out_idx']?>">
<input type="hidden" name="_book_idx" value="<?php echo $column['book_idx']?>">
<input type="hidden" name="_ref_type" value="<?php echo $column['out_doc_type']?>">
<input type="hidden" name="_ref_doc" value="<?php echo trim($column['out_doc_ref'])?>">
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
</table><br /><br />
</form>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUnconfirmed;

	window.document.frmUnconfirmed.btnUnConfirm.onclick = function() {
		var f = window.document.frmUnconfirmed;

		if(f._password.value.length <= 0) {
			alert('PASSWORD must be entered');
			return;
		}

		if(confirm("Are you sure to unconfirmed DO?")) {
			window.document.frmUnconfirmed.submit();
		}
	}
</script>
<?php  } else { ?>
<table width="100%" class="table_box">
	<tr>
		<td width="10%" valign="top"><img src="../../_images/icon/warning.gif"></td>
		<td>
			<span>
			This is a locked document. To modify document, see the hierarchy process.<br /><br />
			Here are the possibility(es) :
			</span>
			<ul>
				<li>This request demo already confirmed by Marketing staff</li>
			</ul>
		</td>
	</tr>
</table><br />
<?php
	}
} else { 
?>
<table width="100%" class="table_box">
	<tr>
		<td width="10%" valign="top"><img src="../../_images/icon/warning.gif"></td>
		<td>
			<span>This is a locked document. To modify document, contact the Administator.</span>
		</td>
	</tr>
</table><br />
<?php } ?>
<!---------------------------------------- end print unconfirm ---------------------------------------->
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