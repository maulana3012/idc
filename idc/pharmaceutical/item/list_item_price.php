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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "list_item_price.php";
$_disc_1 = 0.3;
$_disc_2 = 0.325;

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT it.it_code, it.it_type, substr(it.it_model_no,1,14) as it_model_no, substr(it.it_desc,1,40) AS it_desc,
to_char(ip.ip_date_from, 'dd-Mon-YYYY') as date_from,
COALESCE(to_char(ip.ip_date_to, 'dd-Mon-YYYY'), '-') AS date_to, ip.ip_user_price
FROM ".ZKP_SQL."_tb_item AS it JOIN ".ZKP_SQL."_tb_item_price AS ip ON(it.it_code = ip.it_code)");

$strGet = "";
//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];
	
	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	
	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_lastCategoryNo))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}

	$strGet = "&lastCategoryNo=" . $_lastCategoryNo;
}

//Search Option 2 : by model no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") {
	$sqlQuery->setWhere("%s ILIKE '%s%%'", array("it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere("%s ILIKE '%s%%'", array("it.it_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=code_no";
}

$sqlQuery->setOrderBy("it.it_code, ip.ip_idx DESC");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(),25);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$dup = "";
$rowSpan = array();
$td = array();
$rowSpanIdx = -1; //it will start 0

while($col =& fetchRowAssoc($result)) {
	$td[] = array(
		$col['it_code'],
		$col['it_type'],
		$col['it_model_no'],
		$col['it_desc'],
		$col['date_from'],
		$col['date_to'],
		$col['ip_user_price']);

	if($dup == $col['it_code']) {
		$rowSpan[$rowSpanIdx] += 1;
	} else {
		$dup = $col['it_code'];
		$rowSpanIdx += 1; // check how many item now duplicate
		$rowSpan[$rowSpanIdx] = 1; //rowspan = 1
	}
}
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
function checkform(o) {
	if (verify(o)) {
		o.submit();
	}
}

function fillOption(target, pidx) {
	target.options.length = 1;
	for(var i = 0; i<icat.length; i++) {
		if (icat[i][1] == pidx) {
			target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
		}
	}

	window.document.frmSearch.lastCategoryNo.value = pidx;
}

function fillOptionInit() {
	fillOption(window.document.frmSearch.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
	$count = count($path);
	for($i = 1; $i < $count; $i++) {
		echo "\twindow.document.frmSearch.icat_$i.value = \"{$path[$i][0]}\";\n";
		if($i<=2) echo "\tfillOption(window.document.frmSearch.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function resetOption() {
	window.document.frmSearch.icat_3.options.length = 1;
}

function searchByKeyword() {
	var o = window.document.frmSrarchByKeyword;
	if(o.txtKeyword.value <=0 ) {
		alert("Please insert the model no or Keyword");
		o.txtKeyword.focus();
	} else {
		o.submit();
	}
}

function calcDisc(o) {
	var obj = o;
	if(window.event.keyCode == 13) {
		if(isNaN(obj.value)) {
			alert("Please Enter a number only");
			return;
		}

		var disc = parseFloat(obj.value)/100;
		var oSpan = window.document.all.tags('span');

		if(obj.name == "_disc_1") {
			for (var i = 2; i < oSpan.length; i = i + 3) {
				oSpan.item(i).innerText =
					numFormatval(
						Math.round(
							(parseInt(removecomma(oSpan.item(i-1).innerText)) - parseInt(removecomma(oSpan.item(i-1).innerText))*disc)/1.1)
					+ '');
			}
		} else {
			for (var i = 3; i < oSpan.length; i = i + 3) {
				oSpan.item(i).innerText =
					numFormatval(
						Math.round(
							(parseInt(removecomma(oSpan.item(i-2).innerText)) - parseInt(removecomma(oSpan.item(i-2).innerText))*disc)/1.1)
					+ '');
			}
		}
	}
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="fillOptionInit()">
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
<table width="100%" class="table_box">
	<tr>
		<td style="color:#000000"><strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ITEMS PRICE HISTORY</strong></td>
		<td align="right" colspan="2">
			<form name="frmSearch" method="GET">
			  Category:
			  <input type="hidden" name="lastCategoryNo" value="0">
			  <input type="hidden" name="_location" value="<?php echo $_location ?>">
				<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
					<option value="0">==ALL==</option>
				</select>&nbsp;
				<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
					<option value="0">==ALL==</option>
				</select>&nbsp;
				<select name="icat_3">
					<option value="0">==ALL==</option>
				</select> &nbsp;
			</form>
		</td>
		<th width="5%">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		</th>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr height="35px">
		<td align="right">
			<form name="frmSrarchByKeyword" method="get">
			<input type="hidden" name="_location" value="<?php echo $_location ?>">
			Search: 
			<select name="searchBy">
				<option value="code_no">CODE</option>
				<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>&nbsp;
			<input type="text" name="txtKeyword" size="15" class="fmt"
			value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>">
			</form>
		</td>
		<th width="8%">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='list_item_price.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
function searchByCat() {
	// take the last category number
	var o = window.document.frmSearch.all.tags('SELECT');
	for (var i = 2; i >=0; i--) {
		if (o[i].value != 0) {
			window.location.href = "?lastCategoryNo=" + o[i].value;
			break;
		}
	}
}
</script>
            <table width="100%" class="table_c">
              <tr>
                <th width="6%">CODE</th>
                <th width="12%">TYPE</th>
                <th width="15%">ITEM NO</th>
                <th>DESCRIPTION</th>
				<th width="11%">FROM</th>
				<th width="11%">TO</th>
				<th width="11%">USER PRICE</th>
              </tr>
<?php
$rowIdx = 0;
$numItem = count($rowSpan);
for ($i = 0; $i < $numItem; $i++) {
	print "<tr>\n";	
	cell_link("<span class=\"bar\">".$td[$rowIdx][0]."</span>", ' valign="top" rowspan="'.$rowSpan[$i].'"', ' href="./detail_item.php?_code='.$td[$rowIdx][0].'"');
	cell($td[$rowIdx][1], ' valign="top" rowspan="'.$rowSpan[$i].'"');
	cell($td[$rowIdx][2], ' valign="top" rowspan="'.$rowSpan[$i].'"');
	cell($td[$rowIdx][3], ' valign="top" rowspan="'.$rowSpan[$i].'"');

	for ($o = 0; $o < $rowSpan[$i]; $o++) {
		if($o > 0) print "<tr>\n";
		cell($td[$rowIdx][4], ' align="center"');
		cell($td[$rowIdx][5], ' align="center"');
		cell(number_format((double)$td[$rowIdx][6]), ' align="right"');
		print "</tr>\n";
		$rowIdx++;
	}
}
?>
            </table>
            <table width="100%" cellpadding="0" cellspacing="2" border="0">
              <tr>
                <td align="center"><?php echo $oPage->putPaging();?></td>
              </tr>
            </table>
            <!--END: html_body.tpl-->
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