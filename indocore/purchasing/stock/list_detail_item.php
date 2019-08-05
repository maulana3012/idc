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
$left_loc	= "list_detail_item.php";
$strGet 	= "";
$_location	= isset($_GET['cboLocation'])? $_GET['cboLocation'] : "0";
$_type		= isset($_GET['cboType'])? $_GET['cboType'] : "0";

//DEFAULT PROCESS
$sqlQuery = new strSelect(
"SELECT 
  it_code,											/*0.	*/
  it_model_no,										/*1.	*/

  ".ZKP_SQL."_getDetailStock(it_code,11,$_location,$_type) as in_initial,		/*2.	init stock*/
  ".ZKP_SQL."_getDetailStock(it_code,12,$_location,$_type) as in_pl,			/*3.	PL*/
  ".ZKP_SQL."_getDetailStock(it_code,13,$_location,$_type) as in_return,		/*4.	good return*/
  ".ZKP_SQL."_getDetailStock(it_code,14,$_location,$_type) as in_claim_pl,		/*5.	claim service*/
  ".ZKP_SQL."_getDetailStock(it_code,17,$_location,$_type) as in_claim_setup,	/*6.	claim service lewat setup stock*/
  ".ZKP_SQL."_getDetailStock(it_code,15,$_location,$_type) as in_rt,			/*7.	RT*/

  ".ZKP_SQL."_getDetailStock(it_code,21,$_location,$_type) as out_do,			/*8.	outgoing DO*/
  ".ZKP_SQL."_getDetailStock(it_code,22,$_location,$_type) as out_dt,			/*9.			DT*/
  ".ZKP_SQL."_getDetailStock(it_code,23,$_location,$_type) as out_df,			/*10.			DF*/
  ".ZKP_SQL."_getDetailStock(it_code,24,$_location,$_type) as out_dr,			/*11.			DR*/
  ".ZKP_SQL."_getDetailStock(it_code,26,$_location,$_type) as out_dm,			/*12.	move stock to demo*/
  ".ZKP_SQL."_getDetailStock(it_code,27,$_location,$_type) as out_reject,		/*13.	move stock to reject*/
  ".ZKP_SQL."_getDetailStock(it_code,29,$_location,$_type) as out_expired,		/*14.	delete expired stock*/
  (".ZKP_SQL."_getDetailStock(it_code,16,$_location,$_type)*-1) as in_enter,			/*15.	enter*/
  (".ZKP_SQL."_getDetailStock(it_code,25,$_location,$_type)*-1) as out_borrow,		/*16.	borrow*/
  ".ZKP_SQL."_getDetailStock(it_code,19,$_location,$_type) as in_move_loc,		/*17.	In - Move location*/
  ".ZKP_SQL."_getDetailStock(it_code,30,$_location,$_type) as out_move_loc,		/*18.	Out - Move location*/

  ".ZKP_SQL."_getDetailStock(it_code,31,$_location,$_type) as req_do,			/*19.	request	DO*/
  ".ZKP_SQL."_getDetailStock(it_code,32,$_location,$_type) as req_dt,			/*20.			DT*/
  ".ZKP_SQL."_getDetailStock(it_code,33,$_location,$_type) as req_df,			/*21.			DF*/
  ".ZKP_SQL."_getDetailStock(it_code,34,$_location,$_type) as req_dr,			/*22.			DR*/
  ".ZKP_SQL."_getDetailStock(it_code,35,$_location,$_type) as req_dm,			/*23.			DM*/

  ".ZKP_SQL."_getDetailStock(it_code,1,$_location,$_type) as demo,				/*24.	demo unit*/
  ".ZKP_SQL."_getDetailStock(it_code,2,$_location,$_type) as reject,			/*25.	reject*/
  ".ZKP_SQL."_getDetailStock(it_code,0,$_location,$_type) as stock,				/*26.	available stock*/

  CASE
  	WHEN ".ZKP_SQL."_statusQtyLevel(it_code) is true THEN 'background-color:#FFFFFF;'
  	WHEN ".ZKP_SQL."_statusQtyLevel(it_code) is false THEN 'background-color:#E6E4E6;'
  END AS it_color									/*27. */
FROM ".ZKP_SQL."_tb_item");

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
$strGet		= $findStrGet . "&cboType=$_type&cboLocation=$_location";

//$sqlQuery->setGroupBy("it_code, it_type, it_model_no, it_desc");
$sqlQuery->setOrderBy("it_code");

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 25);
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
<table border="0" cellpadding="0" cellspacing="0" width="110%" bgcolor="#9CBECC">
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
			<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<table width="100%" class="table_box">
	<tr>
		<td colspan="2" style="color:#000000"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL STOCK</h4></td>
		<td align="right" colspan="2">
			<form name="frmSearch" method="GET">
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
			</form>
		</td>
		<th width="5%">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		</th>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr height="25px">
		<td width="25%">
			<span class="bar">Warehouse Location :</span>
			<select name="cboLocation">
			<?php 
			$wh = array($cboFilter[3]['purchasing'][ZKP_FUNCTION], count($cboFilter[3]['purchasing'][ZKP_FUNCTION]));
			for($i=0; $i<$wh[1]; $i++) {
				$v = (intval($_location)==intval($wh[0][$i][0]))?' selected':'';
				echo "\t\t\t<option value=\"".$wh[0][$i][0]."\" ".$v.">".substr($wh[0][$i][1],0,3)."</option>\n";
			}
			?>
			</select>
		</td>
		<td width="20%">
			<span class="bar">Item type :</span>
			<select name="cboType">
				<option value="0">==ALL==</option>
				<option value="1">VAT</option>
				<option value="2">NON VAT</option>
			</select>
		</td>
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
			<a href="javascript:document.location.href='list_detail_item.php?_location=<?php echo $_location ?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
	function searchByCat() {
		var loc		= window.document.all.cboLocation.value;
		var type	= window.document.all.cboType.value;
		// take the last category number
		var o = window.document.frmSearch.all.tags('SELECT');
		for (var i = 2; i >=0; i--) {
			if (o[i].value != 0) {
				window.location.href = "?lastCategoryNo=" + o[i].value + '&cboLocation='+loc+'&cboType='+type;
				break;
			}
		}
	}

	setSelect(window.document.all.cboLocation, "<?php echo isset($_GET['cboLocation']) ? $_GET['cboLocation'] : "0"?>");
	setSelect(window.document.all.cboType, "<?php echo isset($_GET['cboType']) ? $_GET['cboType'] : "0"?>");

	window.document.all.cboLocation.onchange = function() {
		var loc		= window.document.all.cboLocation.value;
		var type	= window.document.all.cboType.value;
		window.location.href = './list_detail_item.php?<?php echo getQueryString() ?>&cboLocation='+loc+'&cboType='+type;
	}
	window.document.all.cboType.onchange = window.document.all.cboLocation.onchange;
</script>
<?php if(numQueryRows($result)<=0) { ?>
<span class="comment"><i>(No recorder stock)</i></span>
<?php } else { ?>
<table width="100%" class="table_f">
	<tr height="18px">
		<th rowspan="2" width="5%">CODE</th>
		<th rowspan="2" width="10%">ITEM NO</th>
		<th rowspan="2" style="background-color:#9CBECC"></th>
		<th colspan="6">INCOMING ITEMS</th>
		<th rowspan="2" style="background-color:#9CBECC"></th>
		<th colspan="6">REQUEST ITEMS</th>
		<th rowspan="2" style="background-color:#9CBECC"></th>
		<th colspan="9">WAREHOUSE CONFIRM</th>
		<th rowspan="2" style="background-color:#9CBECC"></th>
		<th rowspan="2" width="5%">DEMO<br />UNIT</th>
		<th rowspan="2" width="5%">REJECT</th>
		<th rowspan="2" width="5%">STOCK<br />BAL</th>
	</tr>
	<tr>
		<th width="5%">Initial</th>
		<th width="5%">P/L</th>
		<th width="5%">Return</th>
		<th width="5%">Replace<br />Claim</th>
		<th width="5%">RT</th>
		<th width="5%">Total</th>

		<th width="5%">DO</th>
		<th width="5%">DT</th>
		<th width="5%">DF</th>
		<th width="5%">DR</th>
		<th width="5%">Demo<br />Stock</th>
		<th width="5%">Total</th>

		<th width="5%">DO</th>
		<th width="5%">DT</th>
		<th width="5%">DF</th>
		<th width="5%">DR</th>
		<th width="5%">Demo<br />Stock</th>
		<th width="5%">Move to Rjct</th>
		<th width="5%">Borrow/<br />Lend</th>
		<th width="5%">Move<br />Loc</th>
		<th width="5%">Total</th>
	</tr>
<?php
while ($column =& fetchRow($result)) {
	$total_incoming	= $column[2]+$column[3]+$column[4]+$column[5]+$column[6]+$column[7];
	$total_outgoing	= (($column[8]+$column[9]+$column[10]+$column[11]+$column[12]+$column[13]+$column[14])*-1)+$column[15]+$column[16]+$column[17]+$column[18];
	$total_request	= $column[19]+$column[20]+$column[21]+$column[22]+$column[23];
	$total_stock	= $total_incoming-$total_outgoing;
?>
	<tr>
		<td style="<?php echo $column[27] ?>"><?php echo $column[0]?></td>
		<td style="<?php echo $column[27] ?>"><?php echo substr($column[1],0,15)?></td>
		<!-- incoming -->
		<td style="background-color:#9CBECC"></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[2]!=0)?number_format($column[2],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[3]!=0)?number_format($column[3],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[4]!=0)?number_format($column[4],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[5]+$column[6]!=0)?number_format($column[5]+$column[6],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[7]!=0)?number_format($column[7],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><b><?php echo ($total_incoming!=0)?number_format($total_incoming,2):''?></b></td>
		<!-- request -->
		<td style="background-color:#9CBECC"></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[19]!=0)?number_format($column[19],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[20]!=0)?number_format($column[20],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[21]!=0)?number_format($column[21],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[22]!=0)?number_format($column[22],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[23]!=0)?number_format($column[23],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><b><?php echo ($total_request!=0)?number_format($total_request,2):''?></b></td>
		<!-- outgoing -->
		<td style="background-color:#9CBECC"></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[8]!=0)?number_format($column[8]*-1,2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[9]!=0)?number_format($column[9]*-1,2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[10]!=0)?number_format($column[10]*-1,2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[11]!=0)?number_format($column[11]*-1,2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[12]!=0)?number_format($column[12]*-1,2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[13]+$column[14]!=0)?number_format(($column[13]+$column[14])*-1,2):''?></td>
		<?php if($column[15]+$column[16] == 0) { ?>
			<td style="<?php echo $column[27] ?>" align="right"></td>
		<?php } else { ?>
			<td style="<?php echo $column[27] ?>" align="right"><?php echo number_format($column[15]+$column[16],2)?></td>
		<?php } ?>
		<?php if($column[17]+$column[18] == 0) { ?>
			<td style="<?php echo $column[27] ?>" align="right"></td>
		<?php } else { ?>
			<td style="<?php echo $column[27] ?>" align="right"><?php echo number_format($column[17]+$column[18],2)?></td>
		<?php } ?>
		<td style="<?php echo $column[27] ?>" align="right"><b><?php echo ($total_outgoing!=0)?number_format($total_outgoing,2):''?></b></td>
		<td style="background-color:#9CBECC"></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[24]!=0)?number_format($column[24],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><?php echo ($column[25]!=0)?number_format($column[25],2):''?></td>
		<td style="<?php echo $column[27] ?>" align="right"><b><?php echo ($total_stock!=0)?number_format($total_stock,2):''?></b></td>
	</tr>
	<?php } ?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
<?php } ?>
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