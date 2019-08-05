<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 29-May, 2007 23:50:32
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//Check PARAMETER
if(!isset($_GET['_bill_code']) && !isset($_GET['_cus_to']))
	die("<script language=\"javascript1.2\">window.close();</script>");

$_bill_code	 = $_GET['_bill_code'];
$_cus_to	 = $_GET['_cus_to'];
$_remain_amount = $_GET['_remain_billing'];

//Insert Payment
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . '/billing/p_detail_return.php', 'insert')) {

	$_dept			= $_POST['_dept'];
	$_cus_to		= $_POST['_cus_to'];
	$_date			= $_POST['_date'];
	$_bill_code		= $_POST['_bill_code'];
	$_amount		= $_POST['_amount'];
	$_inputed_by	= $S->getValue("ma_account");
	$_remark		= $_POST['_remark'];
	$_deposit_type	= $_POST['_deposit_type'];
	$_method		= $_POST['_method_type'];
	$_bank			= $_POST['_bank'];
/*
echo "select addNewPaymentByDeposit(<BR />".
		"$\${$_dept}$\$".", <BR />".
		"$\${$_cus_to}$\$".", <BR />".
		"$\${$_bill_code}$\$".", <BR />".
		"$\${$_date}$\$".", <BR />".
		$_amount.", <BR />".
		"$\${$_inputed_by}$\$".", <BR />".
		"$\${$_remark}$\$".", <BR />".
		"$\${$_deposit_type}$\$".", <BR />".
		"$\${$_method}$\$".", <BR />".
		"$\${$_bank}$\$);";exit;
*/
	//addNewPaymentByDeposit
	$result = executeSP(
		ZKP_SQL."_addNewPaymentByDeposit",
		"$\${$_dept}$\$",
		"$\${$_cus_to}$\$",
		"$\${$_bill_code}$\$",
		"$\${$_date}$\$",
		$_amount,
		"$\${$_inputed_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_deposit_type}$\$",
		"$\${$_method}$\$",
		"$\${$_bank}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "pharmaceutical/billing/p_detail_return.php?_bill_code=$_bill_code&_cus_to=$_cus_to&_remain_billing=$_remain_amount");
	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
}

//DEFAULT PROCESS
$sql = "
SELECT
  d.dep_idx,
  d.dep_issued_date AS origin_date,
  to_char(d.dep_issued_date, 'dd-Mon-YY') AS issued_date,
  to_char(d.dep_issued_date, 'dd-Mon-YYYY') AS date,
  d.cus_code,
  d.dep_cus_name,
  d.turn_code,
  d.pay_idx,
  d.dep_amount,
  d.dep_type,
  d.dep_method,
  CASE
	WHEN d.dep_bank = 'BNIS' THEN 'BNI Syariah'
	ELSE d.dep_bank
  END AS dep_bank_name,
  d.dep_bank,
  (SELECT bill_code FROM ".ZKP_SQL."_tb_payment WHERE pay_idx = d.pay_idx) AS bill_code,
  (SELECT SUBSTR(pay_note,9,1) FROM ".ZKP_SQL."_tb_payment WHERE pay_idx = d.pay_idx) AS note,
  CASE
	WHEN dep_type IN('deposit', 'return') THEN 'Incoming'
	WHEN dep_type IN('paymentA', 'paymentB') THEN 'Payment'
  END AS type_transaction
FROM ".ZKP_SQL."_tb_deposit AS d
WHERE d.cus_code = '$_cus_to'
ORDER BY origin_date, type_transaction, dep_idx
";

$dep_sql = "SELECT deg_amount AS amount FROM ".ZKP_SQL."_tb_deposit_group WHERE cus_code = '$_cus_to'";
$dep_res =& query($dep_sql);
$dep_col =& fetchRowAssoc($dep_res);

if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");
?>
<html>
<head>
<title>Payment using deposit</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
</head>
<body style="margin:8pt">
<!--START: BODY-->
<h5><strong>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] DETAIL DEPOSIT</strong></h5>
<table width="100%" class="table_box">
	<tr>
		<th width="5%">No</th>
		<th width="20%">DATE</th>
		<th>DESC</th>
		<th width="25%">AMOUNT</th>
	</tr>
</table>
<div style="height:365; overflow-y:scroll">
<table width="100%" class="table_l">
<?php
$amount = array();
$date	= array();
$total  = array(0,0,0); //0.total depA, 1.total depB, 2.total deposit

pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td width="7%" align="center"><?php echo ++$oPage->serial ;?></td>
		<td width="18%"><?php echo $column['issued_date']?></td>
		<td align="left">
		<?php
		if(trim($column['dep_type']) == 'deposit') {
			echo "Deposit [method > {$column['dep_method']} {$column['dep_bank_name']}] ";
		}
		else if(trim($column['dep_type']) == 'return') echo "Return number ".$column['turn_code'];
		else if(substr($column['dep_type'],0,7) == 'payment') 
			echo "[{$column['note']}] Payment to {$column['bill_code']}";
		?>
		</td>
		<td align="right">Rp. <?php echo number_format((double)$column['dep_amount'],2)?></td>
	</tr>
<?php
	if(trim($column['dep_type']) == 'deposit' || trim($column['dep_type']) == 'paymentA') {
		if(!isset($amount[trim($column['dep_method'])][trim($column['dep_bank'])]))
			$amount[trim($column['dep_method'])][trim($column['dep_bank'])] = $column['dep_amount'];
		else
			$amount[trim($column['dep_method'])][trim($column['dep_bank'])] += $column['dep_amount'];

		if(trim($column['dep_type']) == 'deposit')	$date[trim($column['dep_method'])][trim($column['dep_bank'])] = $column['date'];
	} else if(trim($column['dep_type']) == 'return' || trim($column['dep_type']) == 'paymentB') {
		if(!isset($amount['return']['']))
			$amount['return'][''] = $column['dep_amount'];
		else
			$amount['return'][''] += $column['dep_amount'];

		if(trim($column['dep_type']) == 'return')	$date['return'][''] = $column['date'];
	}
	$total[2] += $column['dep_amount'];
}
?>
</table>
</div>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL &nbsp;</th>
		<th width="30%">Rp. <input type="text" name="total" style="width:70%" class="fmtn" value="<?php echo number_format((double)$total[2],2) ?>"></th>
	</tr>
</table><br />
<script language='javascript' type='text/javascript'>
<?php
echo "var amount = new Array();\n";
foreach($amount as $method => $method1) {
	foreach($method1 as $bank => $amt) {
	printf("amount['%s'] = %s;\n",
		$method.$bank,$amt);
	}
}

echo "\nvar date   = new Array();\n";
foreach($date as $method => $method1) {
	foreach($method1 as $bank => $amt) {
	printf("date['%s'] = '%s';\n",
		$method.$bank,$amt); 
	}
}
?>
</script>
<form name="frmInsertAmount" method="POST">
<input type="hidden" name="p_mode" value="insert">
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<input type="hidden" name="_cus_to" value="<?php echo $_cus_to ?>">
<input type="hidden" name="_bill_code" value="<?php echo $_bill_code ?>">
<input type="hidden" name="_remain_amount" value="<?php echo $_remain_amount ?>">
<input type="hidden" name="_deposit_type" value="">
<input type="hidden" name="_method_type" value="">
<input type="hidden" name="_bank" value="">
<input type="hidden" name="_max_amount" value="">
<input type="hidden" name="_min_date" value="">
<table width="100%" class="table_box">
	<tr>
		<th width="15%">TYPE</th>
		<th width="20%">DATE</th>
		<th>REMARK</th>
		<th width="25%">AMOUNT</th>
	</tr>
	<tr>
		<td>
			<input type="radio" name="_type" value="A" onclick="setType('A')">A 
			<input type="radio" name="_type" value="B" onclick="setType('B')">B
		</td>
		<td><input type="text" name="_date" class="reqd" style="width:100%" value="<?php echo date('j-M-Y') ?>"></td>
		<td><input type="text" name="_remark" class="fmt" style="width:100%"></td>
		<td>Rp. <input type="text" name="_amount" style="width:80%" class="reqn" value="0" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode == 13) checkform(window.document.frmInsertAmount)"></td>
	</tr>
</table><br />
<table width="100%" class="table_box">
	<tr>
		<th width="20%">METHOD</th>
		<td colspan="4">
			<input type="radio" name="_method" value="cash" onClick="enabledBankPayment(this, 'cash')" disabled>Cash &nbsp;
			<input type="radio" name="_method" value="check" onClick="enabledBankPayment(this, 'check')" disabled>Check &nbsp;
			<input type="radio" name="_method" value="transfer" onClick="enabledBankPayment(this, 'transfer')" disabled>Transfer &nbsp;
			<input type="radio" name="_method" value="giro" onClick="enabledBankPayment(this, 'giro')" disabled>Giro &nbsp;
		</td>
	</tr>
	<tr>
		<th valign="middle">BANK</th>
		<td>
			<input type="radio" name="_bank" value="BCA1" onClick="setValue()" disabled>BCA 1<br />
			<input type="radio" name="_bank" value="BCA2" onClick="setValue()" disabled>BCA 2
		</td>
		<td>
			<input type="radio" name="_bank" value="MANDIRI" onClick="setValue()" disabled>Mandiri<br />
			<input type="radio" name="_bank" value="BII1" onClick="setValue()" disabled>BII 1
		</td>
		<td>
			<input type="radio" name="_bank" value="BII2" onClick="setValue()" disabled>BII 2<br />
			<input type="radio" name="_bank" value="DANAMON" onClick="setValue()" disabled>Danamon
		</td>
		<td valign="top">
			<input type="radio" name="_bank" value="BNIS" onClick="setValue()" disabled>BNI Syariah<br />
		</td>
		<td colspan="5" align="right" valign="bottom">
			
		</td>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr>
		<td width="65%">
			<small class="comment"><i>
				type A : deposit input manually at <span style="color:black">new deposit</span><br />
				type B : deposit from return billing
			</i></small>
		</td>
		<td align="right">
			<button name='btnSave' class='input_btn' style='width:60px;' onclick="checkform(window.document.frmInsertAmount)"> &nbsp; Save </button>&nbsp;
			<button name='btnClose' class='input_btn' style='width:60px;' onClick="window.close();"> &nbsp; Close</button>
		</td>
	</tr>
<table>
</form>
<script language="javascript" type="text/javascript">
function enabledBankPayment(o, method){
	var f = window.document.frmInsertAmount;

	if (o.checked == true) {
		if(method == 'transfer') {
			for(i=0; i<8; i++) { f._bank[i].disabled = false; }
		} else if(method == 'check' || method == 'giro') {
			for(i=0; i<8; i++) { if(i<5) f._bank[i].disabled = true; else f._bank[i].disabled = false; }
			f._bank[7].checked = true;
		} else {
			for(i=0; i<8; i++) { f._bank[i].disabled = true; f._bank[i].checked = false; }
		}
	}
	setValue();
}

function setType(value) {

	var f = window.document.frmInsertAmount;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var idx_bank	= 15;	/////

	if(value == 'A') {
		for(i=idx_bank; i<idx_bank+11; i++) { oCheck[i].disabled = false; }
		f._deposit_type.value = 'DEPOSIT-A';
		f._amount.value = '0';
	} else if(value == 'B') {
		for(i=idx_bank; i<idx_bank+11; i++) { oCheck[i].disabled = true; oCheck[i].checked = false; }

		if(amount['return'] != null) {
			f._deposit_type.value = 'DEPOSIT-B';
			f._method_type.value  = 'deposit';
			if(amount['return'] > f._remain_amount.value) {
				f._amount.value		= numFormatval(f._remain_amount.value+'',0);
				f._max_amount.value	= numFormatval(f._remain_amount.value+'',0);
			} else {
				f._amount.value		= numFormatval(amount['return']+'',0);
				f._max_amount.value	= numFormatval(amount['return']+'',0);
			}
			f._min_date.value	  = date['return'];
			f._bank.value		  = '';
		} else {
			f._amount.value		= '0';
			f._max_amount.value	= '0';
			f._min_date.value	 = '<?php echo date('j-M-Y') ?>';
		}
	}

}

function setValue() {

	var f = window.document.frmInsertAmount;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var idx_method	= 15;	/////
 	var idx_bank	= 19;	/////
	var bank = '';

	for(i=idx_method; i<idx_method+4; i++) {if(oCheck[i].checked) {var method = oCheck[i].value; break;}}	// what is method checked
	for(i=idx_bank; i<idx_bank+7; i++) {if(oCheck[i].checked) {bank = oCheck[i].value; break;}}				// what is bank checked

	var amt		 = amount[method+bank];
	var min_date = date[method+bank];

	if(amt != null) {
		if(amt > f._remain_amount.value) {
			f._amount.value		= numFormatval(f._remain_amount.value+'',0);
			f._max_amount.value	= numFormatval(f._remain_amount.value+'',0);
		} else {
			f._amount.value		= numFormatval(amt+'',0);
			f._max_amount.value	= numFormatval(amt+'',0);
		}
		f._method_type.value = method;
		f._bank.value		 = bank;
		f._min_date.value	 = min_date;
	} else {
		f._amount.value		= '0';
		f._max_amount.value	= '0';
		f._min_date.value	 = '<?php echo date('j-M-Y') ?>';
	}

}

function checkform(o) {

	var f = window.document.frmInsertAmount;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var idx_method	= 15;	/////
 	var idx_bank	= 19;	/////
	var f_amount		= parseInt(removecomma(o._amount.value));
	var f_max_amount	= parseInt(removecomma(o._max_amount.value));
	var f_remain_amount	= parseInt(removecomma(o._remain_amount.value));
	var d	= parseDate(new Date(), 'prefer_euro_format');
	var d1	= parseDate(o._min_date.value, 'prefer_euro_format');
	var d2	= parseDate(o._date.value, 'prefer_euro_format');
	var method=''; var bank='';

	//checking radio button
	for(i=idx_method; i<idx_method+4; i++) {if(oCheck[i].checked) {var method = oCheck[i].value;break;}}	// is method checkeded
	for(i=idx_bank; i<idx_bank+7; i++) {if(oCheck[i].checked) {bank = oCheck[i].value; break;}}				// is bank checked

	if(o._type[0].checked == false && o._type[1].checked == false) {alert("You have to choose [TYPE] of deposited you will use");return;}
	if(o._type[0].checked) {
		if(method=='') {alert("You have to choose [METHOD] of deposit you will used");return;}
		if(method=='trasfer' || method=='giro' || method=='check') {if(bank=='') {alert("You have to choose [BANK] you will used");return;}}
	}

	//checking value
	if(d2 == null) { alert("Paid date must be entered with proper date format"); o._date.value='<?php echo date('j-M-Y') ?>'; o._date.focus(); return; }
	if(isNaN(removecomma(o._amount.value))) { alert("You can enter only number"); o._amount.focus(); return; }
	if(f_max_amount == 0) {alert("Please choose another method.\nBalance in this method is Rp. 0");return;}
	if(f_amount <= 0) {alert("Amount have to more than 0");o._amount.focus();return;}
	if(f_amount > f_remain_amount) {alert("Remain amount in this invoice only Rp. "+numFormatval(f_remain_amount+'',0));o._amount.value=numFormatval(f_remain_amount+'',0);o._amount.focus();return;}
	if(f_amount > f_max_amount) { alert("Balance amount in this deposit method only Rp. "+numFormatval(f_max_amount+'',0)+".\nYou input too much.");o._amount.value=numFormatval(f_max_amount+'',0);o._amount.focus();return;}
	if(d2 < d) { alert("Paid date cannot earlier than today "+formatDate(d, "d-NNN-yyyy")); o._date.value=formatDate(d, "d-NNN-yyyy");o._date.focus(); return; }	
	if(d2 < d1) { alert("Paid date must be later from deposit date, "+formatDate(d1, "d-NNN-yyyy")); o._date.value=formatDate(d, "d-NNN-yyyy");o._date.focus(); return; }	

	if(confirm("Are you sure to save?")) {
		o._amount.value = removecomma(o._amount.value);
		o._date.value	= formatDate(d2, "d-NNN-yyyy");
		o.submit();
	}

}
</script>
<!--END: BODY-->
</body>
</html>