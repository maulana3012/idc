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

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//GLOBAL
$_code		= trim($_GET['_code']);
$_model		= $_GET['_model'];
$_loc		= $_GET['_loc'];
$_date		= $_GET['_year'].'-'.$_GET['_month']."-1";
$_filter_by		= isset($_GET['cboTypeActivity']) ? $_GET['cboTypeActivity'] : '0';
$_filter_act	= array();

if($_filter_by == 0) {
} else if($_filter_by == 1) {
	$_filter_act = array(8=>' AND oted_idx is null', 
						 9=>' AND bed_idx is null', 
						 10=>' AND rebed_idx is null', 
						 11=>' AND mved_idx is null',
						 12=>' AND rjed_idx is null');
} else if($_filter_by == 2) {
	$_filter_act = array(0=>' AND eni_idx is null', 
						 1=>' AND epl_idx is null', 
						 2=>' AND ecl_idx is null', 
						 3=>' AND elc_idx is null', 
						 4=>' AND ised_idx is null', 
						 5=>' AND eed_idx is null', 
						 6=>' AND rebed_idx is null',
						 7=>' AND mved_idx is null');
} else if($_filter_by == 12) {
	$_filter_act = array(0=>' AND eni_idx is null', 
						 2=>' AND ecl_idx is null', 
						 4=>' AND ised_idx is null', 
						 5=>' AND eed_idx is null', 
						 6=>' AND rebed_idx is null', 
						 7=>' AND mved_idx is null', 
						 8=>' AND oted_idx is null', 
						 9=>' AND bed_idx is null', 
						 10=>' AND rebed_idx is null', 
						 11=>' AND mved_idx is null',
						 12=>' AND rjed_idx is null');
} else {
	$act		= array(0,	1,	2,	3,	4,	5,	6,	7,	8,	9,	10,	11, 12);
	$act_II		= array('eni_idx','epl_idx','ecl_idx','elc_idx','ised_idx','eed_idx','rebed_idx','mved_idx',
						'oted_idx','bed_idx','rebed_idx','mved_idx','rjed_idx');
	$act_III	= array(11=>0, 12=>1, 14=>2, 13=>4, 15=>4, 16=>4, 18=>5, 19=>6, 
						21=>8, 22=>8, 23=>8, 24=>8, 26=>8, 25=>8, 28=>9, 30=>10, 29=>11);

	for ($i=0; $i<count($act); $i++) {
		if($i != $act_III[$_filter_by]) {
			$_filter_act[$i]	= ' AND '.$act_II[$i].' is null';
		} else  {
			if($_filter_by == 13)		$_filter_act[$i] .= ' AND inc_doc_type in (1,2)';
			else if($_filter_by == 15)	$_filter_act[$i] .= ' AND inc_doc_type in (3)';
			else if($_filter_by == 21)	$_filter_act[$i] .= ' AND out_doc_type in (1,2)';
			else if($_filter_by == 22)	$_filter_act[$i] .= ' AND out_doc_type in (3)';
			else if($_filter_by == 23)	$_filter_act[$i] .= ' AND out_doc_type in (4)';
			else if($_filter_by == 24)	$_filter_act[$i] .= ' AND out_doc_type in (5)';
			else if($_filter_by == 26)	$_filter_act[$i] .= ' AND out_doc_type in (6)';
		}
	}
}

$strWhere = array(
/*0*/	0=>	"it_code='$_code' AND eni_wh_location=$_loc AND eni_expired_date=date '$_date'".	(isset($_filter_act[0]) ? $_filter_act[0] : ''),
/*1*/	1=>	"it_code='$_code' AND epl_wh_location=$_loc AND epl_expired_date=date '$_date'".	(isset($_filter_act[1]) ? $_filter_act[1] : ''),
/*2*/	2=>	"it_code='$_code' AND ecl_wh_location=$_loc AND ecl_expired_date=date '$_date'".	(isset($_filter_act[2]) ? $_filter_act[2] : ''),
/*3*/	3=>	"it_code='$_code' AND elc_wh_location=$_loc AND elc_expired_date=date '$_date'".	(isset($_filter_act[3]) ? $_filter_act[3] : ''),
/*4*/	4=>	"it_code='$_code' AND ised_wh_location=$_loc AND ised_expired_date=date '$_date'".	(isset($_filter_act[4]) ? $_filter_act[4] : ''),
/*5*/	5=>	"eed.it_code='$_code' AND eed_wh_location=$_loc AND eed_expired_date=date '$_date'".(isset($_filter_act[5]) ? $_filter_act[5] : ''),
/*6*/	6=>	"it_code='$_code' AND rebed_to_wh=$_loc AND rebed_expired_date=date '$_date'".		(isset($_filter_act[6]) ? $_filter_act[6] : ''),
/*7*/	7=>	"it_code='$_code' AND mved_to_wh=$_loc AND mved_expired_date=date '$_date'".		(isset($_filter_act[7]) ? $_filter_act[7] : ''),
/*8*/	8=>	"it_code='$_code' AND oted_wh_location=$_loc AND oted_date=date '$_date'".			(isset($_filter_act[8]) ? $_filter_act[8] : ''),
/*9*/	9=>	"it_code='$_code' AND bed_from_wh=$_loc AND bed_expired_date=date '$_date'".		(isset($_filter_act[9]) ? $_filter_act[9] : ''),
/*10*/	10=>"it_code='$_code' AND rebed_from_wh=$_loc AND rebed_expired_date=date '$_date'".	(isset($_filter_act[10]) ? $_filter_act[10] : ''),
/*11*/	11=>"it_code='$_code' AND mved_from_wh=$_loc AND mved_expired_date=date '$_date'".		(isset($_filter_act[11]) ? $_filter_act[11] : ''),
/*12*/	12=>"it_code='$_code' AND rjed_wh_location=$_loc AND rjed_expired_date=date '$_date'".	(isset($_filter_act[12]) ? $_filter_act[12] : '')
);

//DEFAULT PROCESS
$sql = "
SELECT
	eni_timestamp AS timestamp,
  	to_char(eni_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	11 AS type_activity,
	'Initial Stock' AS name_activity,
	null AS idx_activity,
	null AS doc_activity,
	null AS date_activity,
	true AS qty_value,
	eni_qty AS qty
	FROM ".ZKP_SQL."_tb_expired_initial WHERE $strWhere[0] UNION
SELECT 
	epl_timestamp AS timestamp,
  	to_char(epl_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	12 AS type_activity,
	'P/L' AS name_activity,
	inpl_idx AS idx_activity,
	(SELECT inpl_inv_no FROM ".ZKP_SQL."_tb_in_pl WHERE inpl_idx = epl.inpl_idx) AS doc_activity,
	(SELECT inpl_checked_date FROM ".ZKP_SQL."_tb_in_pl WHERE inpl_idx = epl.inpl_idx) AS date_activity,
	true AS qty_value,
	epl_qty AS qty 
	FROM ".ZKP_SQL."_tb_expired_pl AS epl WHERE $strWhere[1] UNION
SELECT 
	ecl_timestamp AS timestamp,
  	to_char(ecl_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	14 AS type_activity,
	'Replace Claim' AS name_activity,
	incl_idx AS idx_activity,
	(SELECT incl_inv_no FROM ".ZKP_SQL."_tb_in_claim WHERE incl_idx = ecl.incl_idx) AS doc_activity,
	(SELECT incl_checked_date FROM ".ZKP_SQL."_tb_in_claim WHERE incl_idx = ecl.incl_idx) AS date_activity,
	true AS qty_value,
	ecl_qty AS qty 
	FROM ".ZKP_SQL."_tb_expired_claim AS ecl WHERE $strWhere[2] UNION
SELECT 
	elc_timestamp AS timestamp,
  	to_char(elc_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	12 AS type_activity,
	'P/L Local' AS name_activity,
	inlc_idx AS idx_activity,
	(SELECT po_code||' #'||pl_no FROM ".ZKP_SQL."_tb_in_local WHERE inlc_idx = elc.inlc_idx) AS doc_activity,
	(SELECT inlc_checked_date FROM ".ZKP_SQL."_tb_in_local WHERE inlc_idx = elc.inlc_idx) AS date_activity,
	true AS qty_value,
	elc_qty AS qty 
	FROM ".ZKP_SQL."_tb_expired_local AS elc WHERE $strWhere[3] UNION
SELECT 
	ised_timestamp AS timestamp,
  	to_char(ised_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	CASE
		WHEN inc_doc_type IN(1,2) THEN 13
		WHEN inc_doc_type =3 THEN 15
	END AS type_activity,
	CASE
		WHEN inc_doc_type IN(1,2) THEN 'Return (Good Condition)'
		WHEN inc_doc_type =3 THEN 'RT'
	END AS name_activity,
	inc_idx AS idx_activity,
	inc_doc_ref AS doc_activity,
	inc_date AS date_activity,
	true AS qty_value,
	ised_qty AS qty 
	FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_stock_ed USING(inc_idx) WHERE $strWhere[4] UNION
/*SELECT 
	eed_timestamp AS timestamp,
  	to_char(eed_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	16 AS type_activity,
	'Borrow' AS name_activity,
	out_idx AS idx_activity,
	out_doc_ref AS doc_activity,
	out_issued_date AS date_activity,
	true AS qty_value,
	eed_qty AS qty 
	FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_borrow_ed using(out_idx) JOIN ".ZKP_SQL."_tb_enter_ed as eed using(bed_idx) WHERE $strWhere[5] UNION*/
/*SELECT
	rebed_timestamp AS timestamp, 
  	to_char(rebed_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	18 AS type_activity,
	'Incoming (Move type)' AS name_activity,
	null AS idx_activity,
	null AS doc_activity,
	null AS date_activity,
	true AS qty_value,
	rebed_qty AS qty 
	FROM ".ZKP_SQL."_tb_return_borrow_ed WHERE $strWhere[6] UNION*/
SELECT
	mved_timestamp AS timestamp, 
  	to_char(mved_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	19 AS type_activity,
	'Incoming (Move location)' AS name_activity,
	null AS idx_activity,
	null AS doc_activity,
	null AS date_activity,
	true AS qty_value,
	mved_qty AS qty 
	FROM ".ZKP_SQL."_tb_move_stock_ed WHERE $strWhere[7] UNION

SELECT 
	oted_timestamp AS timestamp,	
  	to_char(oted_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	CASE
		WHEN out_doc_type =1 THEN 21
		WHEN out_doc_type =2 THEN 21
		WHEN out_doc_type =3 THEN 22
		WHEN out_doc_type =4 THEN 23
		WHEN out_doc_type =5 THEN 24
		WHEN out_doc_type =6 THEN 26
	END AS type_activity,
	CASE
		WHEN out_doc_type =1 THEN 'Billing'
		WHEN out_doc_type =2 THEN 'Order'
		WHEN out_doc_type =3 THEN 'DT'
		WHEN out_doc_type =4 THEN 'DF'
		WHEN out_doc_type =5 THEN 'DR'
		WHEN out_doc_type =6 THEN 'Move to Demo'
	END AS name_activity,
	out_idx AS idx_activity,
	out_doc_ref AS doc_activity,
	out_issued_date AS date_activity,
	false AS qty_value,
	oted_qty AS qty 
	FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_ed USING(out_idx) WHERE $strWhere[8] UNION
/*SELECT
	bed_timestamp AS timestamp, 
  	to_char(bed_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	25 AS type_activity,
	'Lend' AS name_activity,
	out_idx AS idx_activity,
	out_doc_ref AS doc_activity,
	out_issued_date AS date_activity,
	false AS qty_value,
	bed_qty AS qty 
	FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_borrow_ed using(out_idx) WHERE $strWhere[9] UNION*/
/*SELECT
	rebed_timestamp AS timestamp, 
  	to_char(rebed_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	28 AS type_activity,
	'Outgoing (Move type)' AS name_activity,
	null AS idx_activity,
	null AS doc_activity,
	null AS date_activity,
	false AS qty_value,
	rebed_qty AS qty 
	FROM ".ZKP_SQL."_tb_return_borrow_ed WHERE $strWhere[10] UNION*/
SELECT
	mved_timestamp AS timestamp, 
  	to_char(mved_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	30 AS type_activity,
	'Outgoing (Move location)' AS name_activity,
	null AS idx_activity,
	null AS doc_activity,
	null AS date_activity,
	false AS qty_value,
	mved_qty AS qty 
	FROM ".ZKP_SQL."_tb_move_stock_ed WHERE $strWhere[11] UNION
SELECT
	rjed_timestamp AS timestamp, 
  	to_char(rjed_timestamp, 'dd / Mon / yy hh24:mi:ss') AS cfm_timestamp,
	29 AS type_activity,
	'Delete expired stock' AS name_activity,
	null AS idx_activity,
	null AS doc_activity,
	null AS date_activity,
	false AS qty_value,
	rjed_qty AS qty 
	FROM ".ZKP_SQL."_tb_reject_ed WHERE $strWhere[12]
ORDER BY timestamp
";

$result =& query($sql);
if(isZKError($result =& query($sql))) {
	die("<script language=\"javascript1.2\">window.close();</script>");
}
$numRow = numQueryRows($result);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function findEDLog() {
	var f = window.document.frmEDStock;
	if(f._year.value.length < 4) {
		alert("Please input year in complete digit");
		f._year.focus();
		return;
	}

	var code	= f._code.value;
	var model	= f._model.value;
	var type	= f._type.value;
	var loc		= f._loc.value;
	var month	= f._month.value;
	var year	= f._year.value;
	var filter_by = f.cboTypeActivity.value;

	window.location.href = "p_list_ed.php?_code="+code+"&_model="+model+"&_type="+type+"&_loc="+loc+"&_month="+month+"&_year="+year+"&cboTypeActivity="+filter_by;
}

function initPage() {
	setSelect(window.document.frmEDStock._month, "<?php echo $_GET['_month'] ?>");
	setSelect(window.document.frmEDStock.cboTypeActivity, "<?php echo $_filter_by ?>");
}
</script>
</head>
<body style="margin:8px" onLoad="initPage()">
<form name="frmEDStock" class="GET" action="p_list_ed.php">
<input type="hidden" name="p_mode" value="search">
<input type="hidden" name="_code" value="<?php echo $_code ?>">
<input type="hidden" name="_model" value="<?php echo $_model ?>">
<input type="hidden" name="_type" value="<?php echo $_type ?>">
<input type="hidden" name="_loc" value="<?php echo $_loc ?>">
<table width="100%" class="table_box">
	<tr>
		<td>
			<strong>
			<font color="black">
			[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] COMPLETE E/D HISTORY<br />
			* Item <span class="bar_bu">[<?php echo trim($_code) ?>] <?php echo $_model ?></span>
			</font>
			</strong>
		</td>
	</tr>
</table><hr>
<table class="table_box" width="100%">
	<tr>
		<td align="right">
			Type Activity :
			<select name="cboTypeActivity">
				<option value="0" style="background-color:#FFFFFF;color:darkblue"> == ALL CONDITION== </option>
				<optgroup label="-In Stock-" style="background-color:#FFFF99;color:darkblue;">
					<option value="1">ALL IN STOCK</option>
					<option value="11">Initial Stock</option>
					<option value="12">P/L</option>
					<option value="13">Return (Good Condition)</option>
					<option value="14">Replace Claim</option>
					<option value="15">Return Temporarry</option>
					<option value="19">Incoming (Move Location)</option>
				</optgroup>
				<optgroup label="-Out Stock-" style="background-color:#FFCC99;color:darkblue">
					<option value="2">ALL OUT STOCK</option>
					<option value="21">DO</option>
					<option value="22">DT</option>
					<option value="23">DF</option>
					<option value="24">DR</option>
					<option value="26">DM</option>
					<option value="29">Delete expired stock</option>
					<option value="30">Outgoing (Move Location)</option>
				</optgroup>
			</select>
			Period :
			<select name="_month">
				<option value="1">January</option>
				<option value="2">February</option>
				<option value="3">March</option>
				<option value="4">April</option>
				<option value="5">May</option>
				<option value="6">June</option>
				<option value="7">July</option>
				<option value="8">August</option>
				<option value="9">September</option>
				<option value="10">October</option>
				<option value="11">November</option>
				<option value="12">December</option>
			</select>&nbsp;
			<input type="text" name="_year" class="fmtn" style="width:40px" maxlength="4" value="<?php echo $_GET['_year'] ?>" onKeyPress="if(window.event.keyCode==13) {findEDLog()}">&nbsp;
		</td>
		<th width="10%">
			<a href="javascript:findEDLog()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:window.close()"><img src="../../_images/icon/close.gif" alt="Close pop-up"></a>		
		</th>
	</tr>
</table><br />
</form>
<table width="100%" class="table_box">
	<tr>
		<th width="25%">CONFIRM DATE</th>
		<th>TYPE</th>
		<th width="20%">DOCUMENT NO.</th>
		<th width="15%">DOCUMENT<br />DATE</th>
		<th width="10%">QTY<br />(Pcs)</th>
	</tr>
</table>
<div style="height:460; overflow-y:scroll">
<?php if($numRow <= 0) { ?>
<br /><span class="comment"><i>(No recorder stock)</i></span>
<?php } else { ?>
<table width="100%" class="table_c">
	<?php
	$qty = array(0,0,0);
	while ($column =& fetchRowAssoc($result)) {
	?>
	<tr>
		<td width="25%"><?php echo $column['cfm_timestamp'] ?></td>
		<td><?php echo $column['name_activity'] ?></td>
		<td width="25%"><?php echo $column['doc_activity'] ?></td>
		<td width="15%"><?php echo ($column['date_activity']=='') ? '' : date('d-M-y',strtotime($column['date_activity'])) ?></td>
		<?php if($column['qty_value']=='t') { ?>
		<td width="7%" align="right"><?php echo number_format($column['qty'],2) ?></td>
		<?php } else { ?>
		<td width="7%" align="right" style="color:red"><?php echo number_format($column['qty'],2) ?></td>
		<?php } ?>
	</tr>
	<?php
		if($column['qty_value']=='t')		$qty[0] += $column['qty'];
		else if($column['qty_value']=='f')	$qty[1] += $column['qty'];		

		if($column['qty_value']=='t')		$qty[2] += $column['qty'];
		else if($column['qty_value']=='f')	$qty[2] += $column['qty']*-1;
	}
	?>
</table>
<?php } ?>
</div>
<table width="100%" class="table_box">
	<tr height="25px">
		<th align="right">TOTAL</th>
		<th width="15%">
			<?php if(isset($qty[2]) && $qty[2] > 0) { ?>
			<input type="text" class="fmtn" style="width:100%;" value="<?php echo isset($qty[2]) ? number_format($qty[2],2) : "" ?>" readonly>
			<?php } else if(isset($qty[2]) && $qty[2] <= 0)  { ?>
			<input type="text" class="fmtn" style="width:100%;color:red" value="<?php echo isset($qty[2]) ? number_format($qty[2]*-1,2) : "" ?>" readonly>
			<?php } ?>
		</th>
		<th width="2%"></th>
	</tr>
</table>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmEDStock;

	f.cboTypeActivity.onchange	  = function() {
		findEDLog();
	}

	f._month.onchange	  = function() {
		findEDLog();
	}
</script>
</body>
</html>