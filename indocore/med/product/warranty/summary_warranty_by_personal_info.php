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

//Global
$left_loc		= "summary_warranty_by_personal_info.php";
$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time() - 2592000);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {

	if(isset($_GET['some_date'])) {
		$some_date = $_GET['some_date'];
	} else {
		$some_date = date('j-M-Y');
		$_GET['cboDate'] = "0";
	}

	$period_from 		= "";
	$period_to 			= "";
}

if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_last_category = $_GET['lastCategoryNo'];

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_last_category))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
} else {
	$_last_category	= 0;
}

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
	icat_pidx,
	icat_midx,
	a.it_code,
	it_model_no,
	wr_warranty_no,
	wr_serial_no,
	wr_purchase_date,
	wr_purchase_store,
	wr_cus_name,
	wr_cus_phone,
	wr_cus_hphone,
	wr_cus_email,
	wr_cus_address,
	wr_cus_city,
	wr_cus_zip_code,
	'detail_warranty.php?_code='||wr_idx AS go_page
FROM
	".ZKP_SQL."_tb_warranty AS a JOIN ".ZKP_SQL."_tb_item AS b ON(a.it_code = b.it_code) JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)");

//STRWHERE
$tmp = array();
$get = array();

if($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$get[] = "lastCategoryNo=$_last_category";
}
if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"wr_cus_city", "byAddress"=>"wr_cus_address", "byModelNo"=>"it_model_no", "byWarrantyNo"=>"wr_warranty_no", "byStore"=>"wr_purchase_store");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$get[] = "$cboSearchType=$txtSearch";
}

if ($some_date != "") {
	$tmp[] = "wr_purchase_date = DATE '$some_date'";
	$get[] = "period_from=&period_to=&some_date=$some_date";
} else {
	$tmp[] = "wr_purchase_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$get[] = "period_from=$period_from&period_to=$period_to&some_date=";
}



$strWhere 		= implode(" AND ", $tmp);
$strWherePaging = implode("&", $get);

$sqlQuery->whereCaluse = "$strWhere";
$sqlQuery->setOrderBy("wr_purchase_date DESC, wr_cus_name");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$strGet = $sqlQuery->getQueryString() . $strWherePaging;
$oPage = new strPaging($sqlQuery->getSQL(), 100);
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
		if($i ==1 ) echo "\tfillOption(window.document.frmSearch.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function resetOption() {
	window.document.frmSearch.icat_2.options.length = 1;
	window.document.frmSearch.lastCategoryNo.value = "";
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="fillOptionInit()">
<table border="0" cellpadding="0" cellspacing="0" width="140%" bgcolor="#9CBECC">
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
						<?php //require_once "_left_menu.php";?>
						<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] LIST WARRANTY CARD by Personal Info</h3>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr height="5px"><td></td></tr>
	<tr>
		<td> PRODUCT </td>
		<td> SEARCH </td>
		<td> PURCHASE DATE </td>
		<td> PURCHASE PERIOD </td>
		<td width="50%"></td>
	</tr>
	<tr>
		<td>
			<input type="hidden" name="lastCategoryNo" value="<?php echo $_last_category ?>">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
				<option value="0">==ALL==</option>
			</select>
			<select name="icat_2">
				<option value="0">==ALL==</option>
			</select>
		</td>
		<td>
			<select name="cboSearchType">
				<option value=""></option>
				<option value="byCity">CITY</option>
				<option value="byAddress">ADDRESS</option>
				<option value="byModelNo">MODEL NO</option>
				<option value="byWarrantyNo">WARRANTY NO</option>
				<option value="byStore">STORE</option>
			</select>
			<input type="text" name="txtSearch" size="25" class="fmt" value="<?php echo $txtSearch; ?>">
		</td>
		<td>
			<input type="hidden" name="s_mode">
				<select name="cboDate">
				<option value=""></option>
					<option value="-1">YESTERDAY</option>
					<option value="0">TODAY</option>
					<option value="1">TOMORROW</option>
				</select>
			<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
		</td>
		<td>
			<select name="cboPeriod">
				<option value=""></option>
				<option value="lastWeek">LAST WEEK</option>
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisWeek">THIS WEEK</option>
				<option value="thisMonth">THIS MONTH</option>
			</select>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
		<td></td>
</table><br />
</form>
<table width="100%" class="table_f">
	<tr height="30px">
		<th width="2%">No</th>
		<th width="7%">WARRANTY NO</th>
		<th width="7%">SERIAL NO</th>
		<th width="10%">MODEL</th>
		<th width="7%">PURCHASE<BR />DATE</th>
		<th width="10%">CUSTOMER NAME</th>
		<th width="8%">HP</th>
		<th width="8%">PHONE.</th>
		<th width="8%">EMAIL</th>
		<th width="13%">ADDRESS</th>
		<th width="6%">CITY</th>
		<th width="5%">ZIP CODE</th>
		<th width="10%">STORE</th>
	</tr>
	<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><a href="<?php echo $column["go_page"] ?>"><span style="color:blue"><?php echo $column["wr_warranty_no"] ?></span></a></td>
		<td><?php echo $column["wr_serial_no"] ?></td>
		<td><?php echo $column["it_model_no"] ?></td>
		<td align="center"><?php echo date("d-M-y", strtotime($column["wr_purchase_date"])) ?></td>
		<td><?php echo $column['wr_cus_name']?></td>
		<td><?php echo $column["wr_cus_hphone"] ?></td>
		<td><?php echo $column["wr_cus_phone"] ?></td>
		<td><?php echo $column["wr_cus_email"] ?></td>
		<td><?php echo $column["wr_cus_address"] ?></td>
		<td><?php echo $column["wr_cus_city"] ?></td>
		<td><?php echo $column["wr_cus_zip_code"] ?></td>
		<td><?php echo $column["wr_purchase_store"] ?></td>
	</tr>
	<?php } ?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	var last_category = 0;
<?php 
if (isset($_GET['icat_2']) && $_GET['icat_2'] != 0)
	echo "\tlast_category = {$_GET['icat_2']};\n";
else if(isset($_GET['icat_1']) && $_GET['icat_1'] != 0)
	echo "\tlast_category = {$_GET['icat_1']};\n";
?>

	setSelect(f.cboSearchType, "<?php echo isset($_GET['cboSearchType']) ? $_GET['cboSearchType'] : ""?>");
	setSelect(f.cboDate, "<?php echo isset($_GET['cboDate']) ? $_GET['cboDate'] : "default"?>");
	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : ""?>");

	f.txtSearch.onkeypress = function() {
		if(window.event.keyCode == 13) {
			if(f.cboDate.value != '' || f.some_date.value != '') {
				f.period_from.value = '';
				f.period_to.value = '';
				f.cboPeriod.value = '';
				f.s_mode.value = 'date';
			} else {
				f.some_date.value = '';
				f.s_mode.value = 'period';
				f.cboDate.value = '';
			}
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.cboDate.onchange = function() {
		setDate(ts, this.value, f.some_date);

		if (f.some_date.value != "" || f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.cboPeriod.onchange = function() {
		if (this.value != "") {
			setPeriod(ts, this.value, f.period_from, f.period_to);
			f.some_date.value = '';
			f.s_mode.value = 'period';
			f.cboDate.value = '';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.cboDate.value = '';
			f.cboPeriod.value = '';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboDate.value = '';
			f.cboPeriod.value = '';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboDate.value = '';
			f.cboPeriod.value = '';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.icat_1.onchange	  = function() {
		f.lastCategoryNo.value = this.value;
		f.submit();
	}
	f.icat_2.onchange  = f.icat_1.onchange;
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