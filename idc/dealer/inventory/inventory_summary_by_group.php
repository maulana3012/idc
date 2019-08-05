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
$left_loc = "inventory_summary_by_group.php";
$_cug_name = isset($_GET['_cug_name']) ? urldecode($_GET['_cug_name']) : "all";
$_cus_code  = isset($_GET['_cus_code']) ? $_GET['_cus_code'] : "";

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
    $_last_category = 0;
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
    window.document.frmSearch.icat_2.options.length = 1;
    window.document.frmSearch.lastCategoryNo.value = "";
}

$(document).ready(function(){
	var dept	= $("input[name$=web_dept]").val();
	var ishideFilterGroupBy	= new Array("dealer","hospital","maketing", "pharmaceutical", "tender");
	if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] INVENTORY SUMMARY BY GROUP</h4>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<table width="100%" class="table_box">
	<tr>
		<td align="left"><div class="divGroupBy">PUSAT : 
<?php
	$sql = "SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group ORDER BY cug_name";
	isZKError($result = & query($sql)) ? $M->printMessage($result):0;

	if(numQueryRows($result) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the $arg first. you can find the [new ". ucfirst($arg) ."] under the BASIC DATA menu");
		$M->printMessage($result);
	} else {
		print "<select name=\"_cug_name\" class=\"req\">\n";
		print "\t<option value=\"all\">==ALL==</option>\n";
	
		while ($columns = fetchRow($result)) {
			print "\t<option value=\"".$columns[0]."\">".$columns[0]."</option>\n";
		}
		print "</select>\n";
	}
?>
		</div></td>
        <td align="left">
           CUSTOMER :
           <input type="checkbox" name="isFilledCus" onClick="fillCustomer('', this.checked)"<?php echo ($_cus_code!='') ? ' checked' : ''?>>
            <input type="text" name="_cus_code" size="8" class="fmt" value="<?php echo $_cus_code?>" readonly>
            <a href="javascript:changeCustomer()"><img src="../../_images/icon/go.png"></a>&nbsp; &nbsp;

        </td>

        <td align="right">CATEGORY :
            <input type="hidden" name="lastCategoryNo" value="0">
            <select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
                <option value="0">==ALL==</option>
            </select>&nbsp;
            <select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
                <option value="0">==ALL==</option>
            </select>&nbsp;
            <select name="icat_3">
                <option value="0">==ALL==</option>
            </select>
        </td>
	</tr>
</table><br />
</form>
<?php 
if(($currentDept == "apotik")) 
	 require_once APP_DIR . "_include/order/report/rpt_inventory_summary_by_group_pusat.php";
else require_once APP_DIR . "_include/order/report/rpt_inventory_summary_by_group.php";
?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	setSelect(f._cug_name, "<?php echo $_cug_name?>");

    var last_category = 0;
<?php 
if (isset($_GET['icat_3']) && $_GET['icat_3'] != 0)
    echo "\tlast_category = {$_GET['icat_3']};\n";
else if (isset($_GET['icat_2']) && $_GET['icat_2'] != 0)
    echo "\tlast_category = {$_GET['icat_2']};\n";
else if(isset($_GET['icat_1']) && $_GET['icat_1'] != 0)
    echo "\tlast_category = {$_GET['icat_1']};\n";
?>

	f._cug_name.onchange = function() {
        f.lastCategoryNo.value = last_category;
        f.submit();
	}

    f.icat_1.onchange     = function() {
        f.lastCategoryNo.value = this.value;
        f.submit();
    }

    f.icat_2.onchange  = f.icat_1.onchange;
    f.icat_3.onchange  = f.icat_1.onchange;

function changeCustomer() {
    if(window.document.frmSearch.isFilledCus.checked) {
        fillCustomer('', window.document.frmSearch.isFilledCus.checked);
    }
}

function fillCustomer(code, is_checked) {
    if(is_checked) {
        var x = (screen.availWidth - 400) / 2;
        var y = (screen.availHeight - 600) / 2;
        var win = window.open(
            'p_list_cus_code.php?_dept=<?php echo $department ?>&_check_code='+ code,
            'inventory',
            'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
    } else {
        var f = window.document.frmSearch;
        f._cus_code.value = ''; 
        f.lastCategoryNo.value = this.value;
        f.submit();
    }
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