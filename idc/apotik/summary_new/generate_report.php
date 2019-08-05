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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "generate_report.php";
$month    = array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$month_x  = array(0=>'January','February','March','April','May','June','July','August','September','October','November','December');
$current_year = date('Y');
$year_from  = array();
  for($i=$current_year; $i>$current_year-10 ; $i--) {
    $year_from[$i] = $i;
  }
$year_to  = array();
  for($i=$current_year+1; $i>$current_year-10 ; $i--) {
    $year_to[$i] = $i;
  }
$period_default = array();
  $period_default[0] = date('n');//(date('n')==12) ? 1 : date('n')+1;
  $period_default[1] = date('Y');//(date('n')<12) ? date('Y')-1 : date('Y');
  $period_default[2] = date('n');
  $period_default[3] = date('Y');
  $period_default[4] = date('n');
  $period_default[5] = date('Y');

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
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function fillOption(target, pidx) {
  target.options.length = 1;
  for(var i = 0; i<icat.length; i++) {
    if (icat[i][1] == pidx) {
      target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
    }
  }

  window.document.frmSearchBill.lastCategoryNo.value = pidx;
  window.document.frmSearchCon.lastCategoryNo.value = pidx;
}

function fillOptionInit() {
  fillOption(window.document.frmSearchBill.icat_1, 0);
  fillOption(window.document.frmSearchCon.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
  $count = count($path);
  for($i = 1; $i < $count; $i++) {
    echo "\twindow.document.frmSearchBill.icat_$i.value = \"{$path[$i][0]}\";\n";
    if($i<=2) echo "\tfillOption(window.document.frmSearchBill.icat_".($i+1).", \"{$path[$i][0]}\");\n";
  }
}

if(isset($path) && is_array($path)) {
  $count = count($path);
  for($i = 1; $i < $count; $i++) {
    echo "\twindow.document.frmSearchCon.icat_$i.value = \"{$path[$i][0]}\";\n";
    if($i<=2) echo "\tfillOption(window.document.frmSearchCon.icat_".($i+1).", \"{$path[$i][0]}\");\n";
  }
}
?>
}

function resetOption() {
  window.document.frmSearchBill.icat_3.options.length = 1;
  window.document.frmSearchBill.lastCategoryNo.value = "";

  window.document.frmSearchCon.icat_3.options.length = 1;
  window.document.frmSearchCon.lastCategoryNo.value = "";

}

$(document).ready(function(){
  setSelect(window.document.frmSearchBill.cboCus, "<?php echo ($currentDept == 'apotik') ? 'cug' : 'cus' ?>");
  setSelect(window.document.frmSearchCon.cboCus, "<?php echo ($currentDept == 'apotik') ? 'cug' : 'cus' ?>");
  fillOptionInit();

});
</script>
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
<!-- START : FORM 1 GENERATE BILLING -->
<form name="frmSearchBill" action="<?php echo HTTP_DIR  ."_include/billing/report/generate_excel/laporan_1_a_qty.php" ?>" method="GET">
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<input type="hidden" name="_order_by" value="<?php echo $cboFilter[1][ZKP_FUNCTION][0][0] ?>">
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] GENERATE REPORT</h3>
<table width="100%" class="table_layout">
  <tr>
    <td width="90%" rowspan="2"><h4>Generate Billing</h4></td>
    <td> SHOW </td>
    <td>CATEGORY</td>
  </tr>
  <tr>
    <td>
      <select name="cboShow">
        <option value="qty">QTY</option>
        <option value="amount">AMOUNT</option>
      </select> &nbsp; 
      <select name="cboCus">
        <option value="cug">CHAIN</option>
        <option value="cus">CUSTOMER</option>
      </select> &nbsp; 
    </td>
    <td>
      <input type="hidden" name="lastCategoryNo" value="0">
      <select name="icat_1" onChange="fillOption(window.document.frmSearchBill.icat_2, this.value)" onClick="resetOption()">
          <option value="0">==ALL==</option>
      </select>&nbsp;
      <select name="icat_2" onChange="fillOption(window.document.frmSearchBill.icat_3, this.value)">
          <option value="0">==ALL==</option>
      </select>&nbsp;
      <select name="icat_3">
          <option value="0">==ALL==</option>
      </select>
    </td>
  </tr>
</table>
<table width="100%" class="table_layout">
  <tr>
    <td width="50%" rowspan="2"></td>
    <td> PERIOD <input type="text" name="_period_name_1" class="fmt" size="8"> </td>
    <td> PERIOD <input type="text" name="_period_name_2" class="fmt" size="8"> </td>
    <td> PERIOD <input type="text" name="_period_name_3" class="fmt" size="8"> </td>
  </tr>
  <tr>
    <td>
      <select name="cboMonthFrom1">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboMonthTo1">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboYear1">
<?php foreach($year_from as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> &nbsp;
    </td>

    <td>
      <select name="cboMonthFrom2">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboMonthTo2">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboYear2">
<?php foreach($year_from as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> &nbsp;
    </td>

    <td>
      <select name="cboMonthFrom3">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboMonthTo3">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboYear3">
<?php foreach($year_from as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> &nbsp;
  </tr>
  <tr>    
    <td colspan="4" align="right">
      <button name="btnGenerate1" class="input_sky">GENERATE</button> &nbsp;
      <button name="btnPreview" class="input_sky">Preview</button>
    </td>
  </tr>
</table>
</form>
<script language="javascript1.2" type="text/javascript">

  window.document.frmSearchBill.btnGenerate1.onclick = function() {

    var o = window.document.frmSearchBill.all.tags('SELECT');
    for (var i = 4; i >= 2; i--) {
      if (o[i].value != 0) {
        window.document.frmSearchBill.lastCategoryNo.value = o[i].value;
        break;
      }
    }
    window.document.frmSearchBill.submit();
  }

  window.document.frmSearchBill.btnPreview.onclick = function() {
      window.open("<?php echo HTTP_DIR ?>_images/laporan/laporan_1_a.png");
  }
</script>
<!-- END : FORM 1 GENERATE BILLING -->
<hr />
<!-- START : FORM 2 GENERATE CONSIGNMENT -->
<form name="frmSearchCon" action="<?php echo HTTP_DIR  ."_include/billing/report/generate_excel/laporan_2_a_qty.php" ?>" method="GET">
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<input type="hidden" name="_order_by" value="<?php echo $cboFilter[1][ZKP_FUNCTION][0][0] ?>">
<table width="100%" class="table_layout">
  <tr>
    <td width="90%" rowspan="2"><h4>Generate Consignment</h4></td>
    <td> SHOW </td>
    <td> CATEGORY </td>
  </tr>
  <tr>
    <td>
      <select name="show">
        <option value="item"> ITEM </option>
        <option value="chain"> CHAIN </option>
      </select>
      <select name="cboShow">
        <option value="qty">QTY</option>
        <option value="amount">AMOUNT</option>
      </select> &nbsp; 
      <select name="cboCus">
        <option value="cug">CHAIN</option>
        <option value="cus">CUSTOMER</option>
      </select> &nbsp; 
    </td>
    <td>
      <input type="hidden" name="lastCategoryNo" value="0">
      <select name="icat_1" onChange="fillOption(window.document.frmSearchCon.icat_2, this.value)" onClick="resetOption()">
          <option value="0">==ALL==</option>
      </select>&nbsp;
      <select name="icat_2" onChange="fillOption(window.document.frmSearchCon.icat_3, this.value)">
          <option value="0">==ALL==</option>
      </select>&nbsp;
      <select name="icat_3">
          <option value="0">==ALL==</option>
      </select>
    </td>
  </tr>
</table>
<table width="100%" class="table_layout">
  <tr>
    <td width="80%" rowspan="2"></td>
    <td> PERIOD </td>
  </tr>
  <tr>
        <td>
      <select name="cboMonth1">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboMonth2">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select> 
      <select name="cboYear">
<?php foreach($year_from as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
      </select>
    </td>
    <td colspan="4" align="right">
      <button name="btnGenerate1" class="input_sky">GENERATE</button> &nbsp;
      <button name="btnPreview" class="input_sky">Preview</button>
    </td>
  </tr>
</table>
</form>
<script language="javascript1.2" type="text/javascript">

  window.document.frmSearchCon.btnGenerate1.onclick = function() {

    var o = window.document.frmSearchCon.all.tags('SELECT');
    for (var i = 4; i >= 2; i--) {
      if (o[i].value != 0) {
        window.document.frmSearchCon.lastCategoryNo.value = o[i].value;
        break;
      }
    }
    window.document.frmSearchCon.submit();
  }

  window.document.frmSearchCon.btnPreview.onclick = function() {
      window.open("<?php echo HTTP_DIR ?>_images/laporan/laporan_2_a.png");
  }
</script>
<!-- END : FORM 2 GENERATE CONSIGNMENT -->
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