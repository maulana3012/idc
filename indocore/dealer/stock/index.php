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
$left_loc	= "list_stock.php";
$_dept 		= $department;

//DEFAULT SQLS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_item_cat WHERE icat_pidx = 0 AND icat_midx > 0 ORDER BY icat_name";
$result =& query($sql);
while ($col =& fetchRowAssoc($result)) {
	$product[] = array($col['icat_midx'],$col['icat_name']);
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

	<script type="text/javascript" src="../../_script/accordion/prototype.js"></script>
	<script type="text/javascript" src="../../_script/accordion/effects.js"></script>
	<script type="text/javascript" src="../../_script/accordion/accordion.js"></script>  
	<link href="../../_script/accordion/accordion.css" rel="stylesheet" type="text/css">
    
	<script type="text/javascript">
		Event.observe(window, 'load', loadAccordions, false);
	
		function loadAccordions() {
			var bottomAccordion = new accordion('vertical_container');
			bottomAccordion.activate($$('#vertical_container .accordion_toggle')[0]);
		}
		
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
<table width="100%" class="table_box">
	<tr>
		<td style="color:#000000"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CURRENT STOCK</h4></td>
	</tr>
</table>


<div id="vertical_container" >
   	
<?php 
for ($i=0; $i<count($product); $i++) { 
	echo "\t<h1 class=\"accordion_toggle\">{$product[$i][1]}</h1>\n";
	echo "\t\t<div class=\"accordion_content\">\n";


	$catList  = executeSP(ZKP_SQL."_getSubCategory", $product[$i][0]);
	$strWhere = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ") AND it_status = 0";
	$sql = "
		SELECT 
			it_code,
			it_model_no,
			it_desc,
			getReadyStock(it_code,'$_dept') AS real_stock,
			getReadyStock(it_code,'$_dept') - getBookedStock(NULL,it_code) AS est_stock
		FROM ".ZKP_SQL."_tb_item
		WHERE $strWhere
		ORDER BY it_code
		";
	$result =& query($sql);

print <<<END
<table width="80%" class="table_f">
	<tr>
		<th rowspan="2" width="10%">CODE</th>
		<th rowspan="2" width="20%">MODEL NO</th>
		<th rowspan="2">DESC</th>
		<th colspan="2">STOCK</th>
	</tr>
	<tr>
		<th width="10%">REAL</th>
		<th width="10%">EST</th>
	</tr>\n
END;
	while ($col =& fetchRow($result)) {
		echo "\t<tr>\n";
		cell($col[0]);
		cell($col[1]);
		cell($col[2]);
		cell(($col[0]=='2101  '||$col[0]=='2101NE')?number_format((double)$col[3],2):number_format((double)$col[3],0), ' align="right"');
		cell(($col[0]=='2101  '||$col[0]=='2101NE')?number_format((double)$col[4],2):number_format((double)$col[4],0), ' align="right"');
		echo "\t</tr>\n";
	}
print <<<END
</table><br />
END;

	echo "\t\t</div>\n";
}
?>

</div>



<script type="text/javascript" >

	var verticalAccordions = $$('.accordion_toggle');
	verticalAccordions.each(function(accordion) {
		$(accordion.next(0)).setStyle({
		  height: '0px'
		});
	});

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