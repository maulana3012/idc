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
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "index.php");

//GLOBAL
$left_loc	= "list_letter.php";

//GLOBAL
$type	= array("T"=>"TENDER", "Q"=>"QUOTATION", "B"=>"BUSINESS", "O"=>"OTHERS");
$status	= array(1=>"ON PROCESS", "CONFIRMED", "CANCELLED");
$search_by = array("letter_number"=>"lt_reg_no", "ship_to"=>"lt_send_to", "brief_summary"=>"lt_brief_summary");
$_dept		 	= isset($_GET['cboDept'])? $_GET['cboDept'] : "";
$_status	 	= isset($_GET['cboStatus'])? $_GET['cboStatus'] : "";
$_type	 		= isset($_GET['cboType'])? $_GET['cboType'] : "";
$_is_file_exist	= isset($_GET['cboFileExist'])? $_GET['cboFileExist'] : "";
$cboSearchBy	= isset($_GET['cboSearchBy'])? $_GET['cboSearchBy'] : "";
$txtSearchBy	= isset($_GET['txtSearchBy'])? $_GET['txtSearchBy'] : "";
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());

//DEFAULT PROCESS
$sqlQuery = new strSelect("
	SELECT * , 
		".ZKP_SQL."_isFileExist(lt_reg_no) AS is_file_exist, 
		CASE
			WHEN lt_dept = 'A' THEN '../apotik/revise_letter.php?_code='||lt_reg_no
			WHEN lt_dept = 'D' THEN '../dealer/revise_letter.php?_code='||lt_reg_no
			WHEN lt_dept = 'H' THEN '../hospital/revise_letter.php?_code='||lt_reg_no
			WHEN lt_dept = 'P' THEN '../pharmaceutical/revise_letter.php?_code='||lt_reg_no
			WHEN lt_dept = 'G' THEN '../general/revise_letter.php?_code='||lt_reg_no
			WHEN lt_dept = 'M' THEN '../management/revise_letter.php?_code='||lt_reg_no
		END AS go_page
	FROM ".ZKP_SQL."_tb_letter");
$tmp = array();
$get = array();
$strGet	= "";

if($_status != "") {
	$tmp[] = "lt_status_of_letter = '$_status'";
	$get[] = "cboStatus=$_status";
}
if($_type != "") {
	$tmp[] = "lt_type_of_letter = '$_type'";
	$get[] = "cboType=$_type";
}

if($_is_file_exist != "") {
	$tmp[] = ZKP_SQL."_isFileExist(lt_reg_no) = $_is_file_exist";
	$get[] = "cboFileExist=$_is_file_exist";
}

if($cboSearchBy != "" && $txtSearchBy != "") {
	$tmp[] = $search_by[$cboSearchBy] . " ILIKE '%$txtSearchBy%'";
	$get[] = "cboSearchBy=$cboSearchBy&txtSearchBy=$txtSearchBy";
}

if($moduleDept != "summary") {
	$tmp[] = "lt_dept = '".strtoupper(substr($moduleDept,0,1))."'";	
} else {
	if($_dept != "") {
		$tmp[] = "lt_dept = '$_dept'";
		$get[] = "cboDept=$_dept";	
	}
}
$tmp[] = "lt_reg_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
$get[] = "period_from=$period_from&period_to=$period_to";

$strWhere		= implode(" AND ", $tmp);
$strWherePaging = implode("&", $get);

$sqlQuery->whereCaluse = $strWhere;
$sqlQuery->setOrderBy("lt_reg_date DESC");

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$strGet = $sqlQuery->getQueryString() . $strWherePaging;
$oPage = new strPaging($sqlQuery->getSQL(), 50);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");s
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
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
<h4>SUMMARY LETTER REGISTRATION</h4>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET" id="form_id">
<input type="hidden" name="_module" value="<?php echo $moduleDept ?>">
<table width="100%" class="table_layout">
	<tr height="5px"><td></td></tr>
	<tr>
		<td width="40%"> </td>
		<?php if($moduleDept == "summary") { echo "<td> DEPT </td>"; } ?>
		<td> STATUS </td>
		<td> TYPE </td>
		<td> FILE EXIST </td>
		<td> DOCUMENT PERIOD </td>
		<td> SEARCH BY </td>
	</tr>
	<tr>
		<td></td>
		<?php if($moduleDept == "summary") { echo "<td>
		<select name=\"cboDept\">
				<option value=\"\">==ALL==</option>
				<option value=\"A\">APOTIK</option>
				<option value=\"D\">DEALER</option>
				<option value=\"H\">HOSPITAL</option>
				<option value=\"P\">PHARMACEUTICAL</option>
				<option value=\"G\">GENERAL</option>
				<option value=\"M\">MANAGEMENT</option>
			</select>
		</td>"; } ?>
		<td>	
			<select name="cboStatus">
				<option value="">==ALL==</option>
				<option value="1">ON PROCESS</option>
				<option value="2">CONFIRMED</option>
				<option value="3">CANCELLED</option>
			</select>
		</td>
		<td>
			<select name="cboType">
				<option value="">==ALL==</option>
				<option value="T">TENDER</option>
				<option value="Q">QUOTATION</option>
				<option value="B">BUSINESS</option>
				<option value="O">OTHERS</option>
			</select>
		</td>
		<td>
			<select name="cboFileExist">
				<option value="">==ALL==</option>
				<option value="true">YES</option>
				<option value="false">NO</option>
			</select>
		</td>
		<td>
			<a href="javascript:setFilterDate('period',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous month"> </a>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
			<a href="javascript:setFilterDate('period',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next month"> </a>
		</td>
		<td>
			<select name="cboSearchBy">
				<option value=""></option>
				<option value="letter_number">Letter Number</option>
				<option value="ship_to">Ship to</option>
				<option value="brief_summary">Brief Summary</option>
			</select>
			<input type="text" name="txtSearchBy" size="25" class="fmt"  value="<?php echo $txtSearchBy ?>">
		</td>
	</tr>
	<tr height="5px">
		<td colspan="6" align="right"><button name="btnGenerate" class="input_sky">Download Excel File</button></td>
	</tr>
</table><br />
<table width="100%" class="table_f">
	<tr height="30px">
		<th width="2%">No</th>
		<th width="10%">Reg No</th>
		<th width="7%">Reg Date</th>
		<th width="15%">Ship to</th>
		<th width="8%">Type Letter</th>
		<th width="10%">Item</th>
		<th>Brief Summary</th>
		<th width="2%"><img src="../../_images/icon/attach.gif"></th>
		<th width="8%">Status</th>
	</tr>
	<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td align="center"><?php echo ++$oPage->serial ;?></td>
		<td align="center"><a href="<?php echo $column["go_page"] ?>"><span style="color:blue"><?php echo $column["lt_reg_no"] ?></span></a></td>
		<td align="center"><?php echo date("d-M-y", strtotime($column["lt_reg_date"])) ?></td>
		<td><?php echo $column["lt_send_to"] ?></td>
		<td align="center"><?php echo $type[$column["lt_type_of_letter"]] ?></td>
		<td><?php echo $column["lt_item"] ?></td>
		<td><?php echo $column["lt_brief_summary"] ?></td>
		<td align="center"><?php echo ($column["is_file_exist"]=='t') ? '<img src="../../_images/icon/check.jpg">':'-' ?></td>
		<td align="center"><?php echo $status[$column["lt_status_of_letter"]] ?></td>
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

	setSelect(f.cboStatus, "<?php echo isset($_GET['cboStatus']) ? $_GET['cboStatus'] : ""?>");
	setSelect(f.cboType, "<?php echo isset($_GET['cboType']) ? $_GET['cboType'] : ""?>");
	setSelect(f.cboSearchBy, "<?php echo isset($_GET['cboSearchBy']) ? $_GET['cboSearchBy'] : ""?>");
	setSelect(f.cboFileExist, "<?php echo isset($_GET['cboFileExist']) ? $_GET['cboFileExist'] : ""?>");

	function setFilterDate(status, value){
		var d = new Date(ts);
		setFilterPeriodCalc(d, value, f.period_from, f.period_to);
		f.submit();
	}

	f.cboStatus.onchange = function() {
		f.submit();
	}

	f.cboType.onchange = f.cboStatus.onchange;
	f.cboFileExist.onchange = f.cboStatus.onchange;
	<?php if($moduleDept == "summary") { ?>
	setSelect(f.cboDept, "<?php echo isset($_GET['cboDept']) ? $_GET['cboDept'] : ""?>");
	f.cboDept.onchange = f.cboStatus.onchange;
	<?php } ?>


	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.submit();
		}
	}

	f.txtSearchBy.onkeypress = function() {
		if(window.event.keyCode == 13) {
			f.submit();
		}
	}

	f.btnGenerate.onclick = function() {
		document.getElementById("form_id").action = "<?php echo HTTP_DIR  ."_include/letter/generate_list_letter_excel.php" ?>";
	    f.submit();
  	}

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