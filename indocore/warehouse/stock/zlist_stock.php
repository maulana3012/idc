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
$left_loc	= "list_stock.php";
$strGet 	= "";
$_location	= isset($_GET['_location'])? $_GET['_location'] : $cboFilter[3]['warehouse'][ZKP_FUNCTION][0][0];

//DEFAULT PROCESS
$sqlQuery = new strSelect(
"SELECT 
  it_code,
  it_model_no,
  it_type,
  it_desc,
  ".ZKP_SQL."_getStock(it_code,1,$_location) AS vat_stock,
  ".ZKP_SQL."_getStock(it_code,2,$_location) AS non_stock,
  ".ZKP_SQL."_getStock(it_code,1,$_location) + ".ZKP_SQL."_getStock(it_code,2,$_location) AS total_stock,
  CASE
  	WHEN ".ZKP_SQL."_statusQtyLevel(it_code) is true THEN '#333333'
  	WHEN ".ZKP_SQL."_statusQtyLevel(it_code) is false THEN '#DA25IF'
  END AS it_color
FROM ".ZKP_SQL."_tb_item AS it
");

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];

	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "".ZKP_SQL."_getStock(it_code,1,$_location) + ".ZKP_SQL."_getStock(it_code,2,$_location) > 0 AND icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_lastCategoryNo))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
	$strGet = "&lastCategoryNo=" . $_lastCategoryNo . "_location=$_location";
}

//Search Option 2 : by model no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=code_no";
}

$findStrGet = str_replace('curpage=','',getQueryString());
$strGet		= $findStrGet . "&_location=$_location";	//show just parent item

$sqlQuery->setGroupBy("it_code, it_type, it_model_no, it_desc");
$sqlQuery->setOrderBy("it_code");

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 1000);
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
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
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
	window.document.frmSearch.lastCategoryNo.value = "";
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
<table width="100%">
	<tr>
		<td valign="top">
			<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CURRENT STOCK</h4>
		</td>
		<td>
<table width="100%" class="table_box">
	<form name="frmSearch" method="GET">
	<tr>
		<td align="right" colspan="2">
		<?php 
		$wh = array($cboFilter[3]['warehouse'][ZKP_FUNCTION], count($cboFilter[3]['warehouse'][ZKP_FUNCTION]));
		for($i=0; $i<$wh[1]; $i++) {
			$v = (intval($_location)==intval($wh[0][$i][0]))?' checked':'';
			echo "\t\t\t<input type=\"radio\" name=\"_location\" value=\"".$wh[0][$i][0]."\" id=\"".$wh[0][$i][1]."\" onclick=\"searchByCat()\"".$v."><label for=\"".$wh[0][$i][1]."\"> ".$wh[0][$i][1]." </label>\n";
		}
		?>
		</td>
		<th width="5%" rowspan="2">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		</th>
	</tr>
	<tr height="35px">
		<td align="right" colspan="2">
		  Category:
		  <input type="hidden" name="lastCategoryNo" value="0">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_3">
				<option value="0">==ALL==</option>
			</select> &nbsp;
		</td>
	</tr>
	</form>
	<tr>
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
		<th width="10%" colspan="2">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='list_initial_stock.php?_location=<?php echo $_location ?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
	</tr>
</table>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
function searchByCat() {
	var o = window.document.frmSearch.all.tags('SELECT');
	var loc = 0;
	var icat_midx = 0;
	if(document.frmSearch._location[0]){ 
		for (var i=0; i<document.frmSearch._location.length; i++) {
			if (document.frmSearch._location[i].checked) loc = document.frmSearch._location[i].value;
		}
	} else {
		loc = document.frmSearch._location.value;
	}
	for (i=2; i>=0; i--) {
		if (o[i].value != 0) { icat_midx = o[i].value; break; }
	}
	window.location.href = "?lastCategoryNo=" + icat_midx + '&_location='+loc;
}
</script>
<?php if(numQueryRows($result)<=0) { ?>
<span class="comment"><i>(No recorder stock)</i></span>
<?php } else { ?>
			<table width="100%" class="table_c">
				<tr>
					<th width="4%">No</th>
					<th width="6%">CODE</th>
					<th width="12%">TYPE</th>
					<th width="20%">ITEM NO</th>
					<th>DESCRIPTION</th>
					<th width="7%">TOTAL</th>
			<?php while ($column =& fetchRowAssoc($result)) { ?>
				<tr style="font-color:<?php echo $column['it_color'] ?>">
					<td><font color="<?php echo $column['it_color'] ?>"><?php echo ++$oPage->serial ;?></font></td>
					<td><font color="<?php echo $column['it_color'] ?>"><?php echo $column['it_code']?></font></td>
					<td><font color="<?php echo $column['it_color'] ?>"><?php echo $column['it_type']?></font></td>
					<td><font color="<?php echo $column['it_color'] ?>"><?php echo substr($column['it_model_no'], 0, 25)?></font></td>
					<td><font color="<?php echo $column['it_color'] ?>"><?php echo cut_string($column['it_desc'],65);?></font></td>
					<td align="right">
						<a href="<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>history_stock.php?_code=<?php echo urlencode(html_entity_decode($column['it_code'], ENT_QUOTES));?>&cboLocation=<?php echo $_location ?>">
						<font color="<?php echo $column['it_color'] ?>"><?php echo ($column['it_code']=='2101  ' || $column['it_code']=='2101NE') ? number_format($column['total_stock'],2) : number_format($column['total_stock'])?></font>
						</a>
					</td>
				</tr>
			<?php } ?>
			</table>
			<table width="100%" cellpadding="0" cellspacing="2" border="0">
				<tr>
					<td align="center"><?php echo $oPage->putPaging();?></td>
				</tr>
			</table>
<?php } ?>
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