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
$_search_by	= isset($_REQUEST['_search_by']) ? $_REQUEST['_search_by'] : "sp_full_name";
$_search_txt	= isset($_REQUEST['_search_txt']) ? $_REQUEST['_search_txt'] : "";

$sqlQuery = new strSelect("SELECT sp_code, sp_full_name, sp_phone, sp_fax, sp_contact_attn, sp_contact_cc  FROM ".ZKP_SQL."_tb_supplier");
$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array($_search_by => "_search_txt"), "AND");
$sqlQuery->setOrderBy("sp_full_name");

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
	$M->goErrorPage($result, "javascript:window.close();");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>SUPPLIER LIST</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var sp = new Array();\n";
$i = 0;
$ptn = array("/(['\"])/", "/[\r\n][\s]+/");
$rpm = array("\\1", " ");

while ($rows =& fetchRow($result,0)) {
	printf("sp['%s']=['%s','%s','%s','%s','%s','%s'];\n",
		addslashes($rows[0]),	//code from query
		addslashes($rows[0]),	//
		addslashes($rows[1]),	//full name
		addslashes($rows[2]),	//phone
		addslashes($rows[3]),	//fax
		addslashes($rows[4]),	//attn
		addslashes($rows[5])	//cc)
	);
}
?>

function searchRow(frmObj) {
	var isValid = false;
	for(i=0; i<frmObj.elements.length; ++i) {
		if(frmObj.elements[i].tagName == "INPUT" && frmObj.elements[i].value.length > 0) {
			isValid = true;
		}
	}
	
	if (isValid) {
		frmObj.submit();
	} else {
		alert("Please Input keyword for search data");
	}
}

function fillField(idx) {
	var target = window.name;
	var f = window.opener.document.frmInsert;

	f._sp_code.value	= sp[idx][0];
	f._sp_name.value	= sp[idx][1];
	f._sp_phone.value 	= sp[idx][2];
	f._sp_fax.value		= sp[idx][3];
	f._sp_attn.value	= sp[idx][4];
	f._sp_cc.value		= sp[idx][5];

	window.close();
}

function initPage() {
	window.frmSearch._search_txt.focus();
	setSelect(window.frmSearch._search_by, '<?php echo $_search_by ?>');
}
</script>
</head>
<body style="margin:8pt" onLoad="initPage()">
<form method="get" name="frmSearch">
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2" width="50%"><h4>SUPPLIER LIST</h4></td>
		<th>SEARCH</th>
		<th width="40%">CODE</th>
		<th rowspan="2" width="10%">
			<a href="javascript:searchRow(window.document.frmSearch)"><img src="../../_images/icon/search_mini.gif" alt="search"></a>
			<a href="javascript:document.location.href='p_list_supplier.php'"><img src="../../_images/icon/list_mini.gif" alt="see all"></a>
		</th>
	</tr>
	<tr>
		<td>
			<select name="_search_by">
				<option value="sp_code">CODE</option>
				<option value="sp_full_name">NAME</option>
			</select>
		</td>
		<td align="center"><input type="text" name="_search_txt" style="width:100%" class="fmt" value="<?php echo $_search_txt ?>"></td>
	</tr>
</table><br />
</form>
<script language="javascript" type="text/javascript">
	window.document.frmSearch._search_by.onchange = function() {
		var search_by = window.document.frmSearch._search_by.value;
		var search_txt = window.document.frmSearch._search_txt.value;
		window.location.href = "?_search_by=" + search_by +'&_search_txt='+ search_txt;
	}
</script>
<!--START: LIST-->
<table width="100%" class="table_box">
  <tr>
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
		<td width="15%"><a href="javascript:fillField('<?php echo addslashes(html_entity_decode($column['sp_code']))?>')"><?php echo $column['sp_code']?></a></td>
		<td><?php echo $column['sp_full_name']?></td>
	  </tr>
	  <?php
	}//end repeat rows
	?>
	</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
    <td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
<!--END: html_body.tpl-->
</body>
</html>