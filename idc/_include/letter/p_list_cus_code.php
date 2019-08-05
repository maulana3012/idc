<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 25-May, 2007 16:16:33
* @author    : daesung kim
*/
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//CHECK PARAMETER
$_dept			= isset($_REQUEST['_dept']) ? $_REQUEST['_dept'] : "D";
$_check_code	= isset($_REQUEST['_check_code']) ? $_REQUEST['_check_code'] : "";
$_cus_channel	= isset($_REQUEST['_cus_channel'])? $_REQUEST['_cus_channel'] : $cus_channel[$_dept];
$strwhere	= array();

$sqlQuery = new strSelect("
SELECT 
  cus_code, 
  cus_full_name, 
  cus_address, 
  cus_is_blocked,
  CASE
	WHEN cus_responsibility_to IS NULL then 0
	ELSE cus_responsibility_to
  END AS cus_responsibility_to
FROM ".ZKP_SQL."_tb_customer");

$strwhere[] = "cus_channel = '".$_cus_channel."'";

if(!isset($_GET['cboSearchBy']) && $_check_code != "") {
	$strwhere[] = "cus_code ILIKE '%%".$_check_code."%%'";
}

if(isset($_GET['cboSearchBy']) && $_GET['cboSearchBy'] == "code") {
	$strwhere[] = "cus_code ILIKE '%%".$_check_code."%%'";
}

if(isset($_GET['cboSearchBy']) && $_GET['cboSearchBy'] == "name") {
	$strwhere[] = "cus_full_name ILIKE '%%".$_check_code."%%'";
}

if(isset($_GET['cboSearchBy']) && $_GET['cboSearchBy'] == "address") {
	$strwhere[] = "cus_address ILIKE '%%".$_check_code."%%'";
}

$sqlQuery->whereCaluse = implode($strwhere, " AND ");
$sqlQuery->setOrderBy("cus_code");
$findStrGet = str_replace('curpage=','',getQueryString());
$strGet	 = $findStrGet . "&_cus_channel=$_cus_channel&_check_code=$_check_code&cboSearchBy=".$_GET['cboSearchBy'];

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 100);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $sqlQuery->getQueryString();

if(isZKError($result =& query($oPage->getListQuery())))
	$M->goErrorPage($result, "../basic_data/index.php");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Customer Code List</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var cus = new Array();\n";
$i = 0;
$ptn = array("/(['\"])/", "/[\r\n][\s]+/");
$rpm = array("\\1", " ");

while ($rows =& fetchRow($result,0)) {
	printf("cus['%s']=['%s','%s',\"%s\",'%s','%s'];\n",
		addslashes($rows[0]), 				//code from query
		addslashes($rows[0]),
		addslashes($rows[1]), 				//full name (for attn)
		preg_replace($ptn, $rpm, $rows[2]), //address (for attn)
		$rows[3],							// active cus or not
		$rows[4]							// responsible by
	);
}
?>

function searchRow(frmObj) {
	var isValid = false;
	for(i=0; i<frmObj.elements.length; ++i) {
		if(frmObj.elements[i].type == "text" && frmObj.elements[i].value.length > 0) {
			isValid = true;
		}
	}
	
	if (isValid) {
		frmObj.submit();
	} else {
		alert("Please Input keyword for search data");
		window.frmSearch._check_code.focus();
	}
}

function fillField(idx) {
	if(cus[idx][3] == 't') {
		if(confirm(cus[idx][1]+" is a deactive customer.\nAre you sure still want to choose "+cus[idx][1]+"?")) {
			confirmValue(idx);
		}
	} else {
		confirmValue(idx);
	}	
}

function confirmValue(idx) {
	var target = window.name;	

	if(target == 'cus_letter') {
		var f = window.opener.document.frmInsert;
		f._cus_code.value	= cus[idx][0];
		f._cus_name.value	= cus[idx][1];
	} else if(target == 'cus_letter_detail') {
		var f = window.opener.document.frmUpdate;
		f._cus_code.value	= cus[idx][0];
		f._cus_name.value	= cus[idx][1];
	}

	window.close();
}

function initPage() {
	window.frmSearch._check_code.focus();
	if(window.document.frmSearch._dept.value == 'A') {
		window.document.frmSearch._cus_channel.disabled = true;
	}
	setSelect(window.document.frmSearch._cus_channel, "<?php echo $_cus_channel ?>");
	setSelect(window.document.frmSearch.cboSearchBy, '<?php echo $_GET['cboSearchBy'] ?>');;
}
</script>
</head>
<body style="margin:8pt" onLoad="initPage()">
<form method="get" name="frmSearch">
<input type="hidden" name="p_mode" value="search">
<input type="hidden" name="_dept" value="<?php echo $_dept ?>">
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2" width="50%"><h4>CUSTOMER<br />CODE</h4></td>
		<td colspan="2" align="center">
			<select name="_cus_channel" style="width:100%">
				<option value="000">Medical Dealer</option>
            	<option value="001">Medicine Dist</option>
            	<option value="002">Pharmacy Chain</option>
            	<option value="003">Gen/ Specialty</option>
            	<option value="004">Pharmaceutical</option>
            	<option value="005">Hospital</option>
            	<option value="6.1">M/L Marketing</option>
            	<option value="6.2">Mail Order</option>
            	<option value="6.3">Internet Business</option>
            	<option value="007">Promotion &amp; Other</option>
            	<option value="008">Individual</option>
            	<option value="009">Private use</option>
				<option value="00S">Service</option>
			</select>
		</td>
		<th rowspan="2" width="15%">
			<a href="javascript:searchRow(window.document.frmSearch)"><img src="../../_images/icon/search_mini.gif" alt="search"></a>
			<a href="javascript:document.location.href='p_list_cus_code.php?_cus_channel=<?php echo $_cus_channel ?>'"><img src="../../_images/icon/list_mini.gif" alt="see all"></a>
		</th>
	</tr>
	<tr>
		<td width align="center">
			<select name="cboSearchBy">
				<option value="code">Code</option>
            	<option value="name">Name</option>
            	<option value="address">Adrress</option>
			</select>
		</td>
		<td align="center"><input type="text" name="_check_code" size="15" class="fmt" value="<?php echo $_check_code?>"></td>
	</tr>
</table><br />
</form>
<script language="javascript" type="text/javascript">
	window.document.frmSearch._cus_channel.onchange = function() {
		window.location.href = "?_cus_channel=" + window.document.frmSearch._cus_channel.value ;
	}
</script>
<table width="100%" class="table_box">
	<tr height="30px">
		<th width="8%">No</th>
		<th width="15%">CODE</th>
		<th>NAME</th>
	</tr>
</table>
<div style="height:440; overflow-y:scroll">
<table width="100%" class="table_box">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td width="8%"><?php echo ++$oPage->serial ;?></td>
		<td width="15%"><a href="javascript:fillField('<?php echo addslashes(html_entity_decode($column['cus_code']))?>')"><?php echo $column['cus_code']?></a></td>
		<td><?php echo $column['cus_full_name']?></td>
	</tr>
<?php } ?>
</table>
</div><br />
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
</body>
</html>