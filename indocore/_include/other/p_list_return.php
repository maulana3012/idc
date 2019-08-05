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

//Check PARAMETER
if(!isset($_GET['_cus_code']) || $_GET['_cus_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_cus_code	 = trim($_GET['_cus_code']);
$_cus_name	 = trim($_GET['_cus_name']);
$strGet		 = "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  turn_code,
  to_char(turn_return_date, 'dd-Mon-YYYY') AS return_date,
  turn_return_condition,
  CASE
	WHEN turn_paper = 0 AND turn_return_condition = 1 AND turn_cfm_wh_delivery_timestamp is not null THEN false
	ELSE true
  END AS lock_status,
  CASE 
	WHEN turn_vat > 0 THEN 0
	WHEN turn_vat <= 0 THEN 1
  END AS type_item,
  CASE
	WHEN turn_paper=0 AND turn_cfm_wh_delivery_timestamp is null THEN 'Unconfirmed'
	WHEN turn_paper=0 AND turn_cfm_wh_delivery_timestamp is not null THEN to_char(turn_cfm_wh_delivery_timestamp, 'dd/Mon/YY hh24:mi:ss')
	WHEN turn_paper=1 THEN '-'
  END AS cfm_date,
  CASE
	WHEN turn_paper = 0 THEN 'check_small.gif'
	WHEN turn_paper = 1 THEN 'cross_small.gif'
  END AS type_invoice
FROM
  ".ZKP_SQL."_tb_return");

$sqlQuery->whereCaluse = "turn_ship_to = '$_cus_code' AND turn_type_return = 'RR'";

//Search Option 1 : by bill_code
if(isset($_GET['txtKeyword']) && $_GET['txtKeyword'] != "") {
	$sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("turn_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=turn_code";
}

$strGet = "_cus_code=" . $_cus_code;

$sqlQuery->setOrderBy("turn_return_date, turn_code");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 40);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, "javascript:window.close();");

?>
<html>
<head>
<title>RETURN LIST</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var inv = new Array();\n";

while ($rows =& fetchRow($result,0)) {
	printf("inv['%s']=['%s', '%s', %s];\n",
		trim($rows[0]),	//code from query
		addslashes($rows[0]),	//turn code	0
		addslashes($rows[1]),	//turn date	1
		$rows[4]				//type item	2
	);
}
?>

function searchByKeyword() {
	var o = window.document.frmSrarchByKeyword;

	if(o.txtKeyword.value <=0 || o.txtKeyword.value != 'search . . .') {
		alert("Please insert the return number");
		o.txtKeyword.focus();
	} else {
		o.submit();
	}
}

function fillField(idx) {

	var f	= window.opener.document.frmInsert;
	var d1	= parseDate(inv[idx][1], 'prefer_euro_format');

	f._turn_date.value	= formatDate(d1, "d-NNN-yyyy");
	f._turn_code.value	= inv[idx][0];
	f._type_item.value	= inv[idx][2]+1;
	f._type_vat[inv[idx][2]].checked = true;
	f._type_vat[0].disabled = true;
	f._type_vat[1].disabled = true;
	window.close();
}
</script>
</head>
<body style="margin:8pt" onload="window.frmSrarchByKeyword.txtKeyword.focus()">
<!--START: BODY-->
<table width="100%" class="table_box">
	<tr>
		<td>
			<strong>
			<font color="black">
			[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT RETURN REPLACE<br />
			<small>* Recorder invoice(s) for customer ship to : [<?php echo $_cus_code ?>] <?php echo $_cus_name ?></small>
			</font>
			</strong>
		</td>
		<td align="right"><a href="javascript:window.location.href='p_information_return.php'"><img src="../../_images/icon/info.gif" alt="Help solution ;)"></a></td>
	</tr>
</table><hr>
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<form name="frmSrarchByKeyword" method="get">
			<input type="hidden" name="_cus_code" value="<?php echo $_cus_code?>">
			<input type="hidden" name="_cus_name" value="<?php echo $_cus_name?>">
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo (isset($_GET['txtKeyword']) && $_GET['txtKeyword']!='') ? $_GET['txtKeyword'] : ""?>" onblur="if(this.value=='') this.value='search . . .'" onfocus="if(this.value=='search . . .') this.value=''"> &nbsp;
			</form>
		</td>
		<th width="12%"> 
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="search return code"></a> &nbsp;
			<a href="javascript:window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>p_list_return.php?_cus_code=<?php echo $_cus_code?>&_cus_name=<?php echo $_cus_name ?>'"><img src="../../_images/icon/list_mini.gif" alt="view all return"></a>
		</th>
	</tr>
</table><br />
<table width="100%" class="table_box">
	<tr height="30px">
		<th width="4%">No</th>
		<th width="8%">Issue<br >Item</th>
		<th width="15%">Return No</th>
		<th width="13%">Return Date</th>
		<th width="22%">WH Confirm<br />Date</th>
		<th width="15%">VIEW</th>
	</tr>
</table>
<div style="height:420; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	  <tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td width="10%" align="center"><img src="../../_images/icon/<?php echo $column['type_invoice']?>"></td>
		<td width="20%">
			<?php if($column['lock_status']=='t') { ?>
			<?php echo "\t\t\t{$column['turn_code']}\n" ?>
			<?php } else if($column['lock_status']=='f') {?>
			<a href="javascript:fillField('<?php echo trim($column['turn_code']) ?>')"><b><?php echo $column['turn_code']?></b></a>
			<?php } ?>
		</td> 
		<td width="20%"><?php echo $column['return_date'] ?></td>
		<td width="30%"><?php echo $column['cfm_date']?></td>
		<td align="center"><a href="p_detail_return.php?_code=<?php echo $column['turn_code'] ?>&_cus_code=<?php echo $_cus_code ?>&_cus_name=<?php echo $_cus_name ?>">view</a></td>
	  </tr>
<?php } ?>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
<!--END: BODY-->
</body>
</html>