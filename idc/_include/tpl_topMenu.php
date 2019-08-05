<?php
/*
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: tpl_topMenu.php,v 1.4 2008/07/15 09:07:19 neki Exp $
*/
//=====================================================================================================================
// DEFINE MENU STRUCTURE
//=====================================================================================================================

require_once LIB_DIR . "zk_dbconn.php";

$top_menu	= array();
if(ZKP_URL == 'ALL') {
	include APP_DIR . "_system/util_tabmenu_all.php";
} else if(ZKP_URL == 'IDC') {
	include APP_DIR . "_system/util_tabmenu_idc.php";
} else if(ZKP_URL == 'MED') {
	include APP_DIR . "_system/util_tabmenu_med.php";
} else if(ZKP_URL == 'MEP') {
	include APP_DIR . "_system/util_tabmenu_mep.php";
}

//=====================================================================================================================

$path = str_replace(HTTP_DIR, "/", pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME));
$init = array("admin"=>"admin", "accounting"=>"accounting", "report"=>"report", "report_all"=>"report", 
			  "apotik"=>"apotik", "dealer"=>"dealer", "marketing"=>"marketing", "hospital"=>"hospital", 
			  "pharmaceutical"=>"pharmaceutical", "sales"=>"sales support", "tender"=>"tender", "customer_service"=>"CS", "incentive"=>"incentive",
			  "purchasing"=>"purchasing", "warehouse"=>"warehouse", "demo"=>"demo",
			   "event"=>"event", "product"=>"warranty", "complain"=>"complain",
			   "management"=>"management","general"=>"general","summary"=>"summary"
			);
$availTab = array(
			 "management_all"=>array("admin","report_all","incentive"),
			 "management"=>array("admin","report","accounting","incentive"),
			 "department"=>array("admin","apotik","dealer","hospital","marketing","pharmaceutical","sales","tender","customer_service","incentive"),
			 "warehouse"=>array("admin","purchasing", "demo", "warehouse"),
			 "others"=>array("admin","event","product","complain"),
			 "letter"=>array("apotik","dealer","hospital","pharmaceutical","general","management","summary")
			);
if($S->getValue("ma_workgroup") != '') $numAvailTab = count($availTab[$S->getValue("ma_workgroup")]);
else $numAvailTab=0;
preg_match("/\/(\w+)\/(\w+)/i",$path, $loc);

echo "<div id=\"main_nav\" class=\"TabbedPanels\">\n";
echo "  <ul class=\"TabbedPanelsTabGroup\">\n";

$i = 0;
if($S->getValue("ma_workgroup") != "letter") {
foreach($top_menu as $key=>$val) {
	for($j=0; $j<$numAvailTab; $j++) {
		if($availTab[$S->getValue("ma_workgroup")][$j] == $key) {
			if($key!='incentive') {
				echo "    <li class=\"TabbedPanelsTab\">" . strtoupper($init[$key]) ."</li>\n";
			} else if($key=='incentive') {
				if($S->getValue("ma_is_marketing") || $S->getValue("ma_see_all")) {
					echo "    <li class=\"TabbedPanelsTab\">" . strtoupper($init[$key]) ."</li>\n";
				} else {
					//
				}
			} 
			if ($key == $loc[1]) $module_index = $i;
			$i++;
		}
	}
} } else {
for($j=0; $j<$numAvailTab; $j++) {
foreach($top_menu["letter"] as $key=>$val) {
	if($availTab[$S->getValue("ma_workgroup")][$j] == $key) {
		echo "    <li class=\"TabbedPanelsTab\">" . strtoupper($init[$key]) ."</li>\n";
		if ($key == $loc[2]) $module_index = $i;
		$i++;
	}
} }
}

echo "  </ul>\n";
echo "  <div class=\"TabbedPanelsContentGroup\">\n";
if($S->getValue("ma_workgroup") != "letter") {
foreach($top_menu as $module => $folder) {
	for($j=0; $j<$numAvailTab; $j++) {
		if($availTab[$S->getValue("ma_workgroup")][$j] == $module) {
			$numFolder = count($folder);
			echo "    <div class=\"TabbedPanelsContent\">\n";
			for ($i=0; $i < $numFolder; $i++) {
				echo "      <a href=\"".HTTP_DIR.$module."/".$top_menu[$module][$i][0]."/".$top_menu[$module][$i][1]."\">".$top_menu[$module][$i][2]."</a> &nbsp; &nbsp;\n";
			}
			echo "		</div>\n";
		}
	}
} } else {
foreach($top_menu["letter"] as $module => $folder) {
	for($j=0; $j<$numAvailTab; $j++) {
		if($availTab[$S->getValue("ma_workgroup")][$j] == $module) {
			$numFolder = count($folder);
			echo "    <div class=\"TabbedPanelsContent\">\n";
			for ($i=0; $i < $numFolder; $i++) {
				echo "      <a href=\"".HTTP_DIR."letter/".$top_menu["letter"][$module][$i][0]."/".$top_menu["letter"][$module][$i][1]."\">".$top_menu["letter"][$module][$i][2]."</a> &nbsp; &nbsp;\n";
			}
			echo "		</div>\n";
		}
	}
}
}

echo "  </div>\n";
echo "</div>\n";
if ($S->getValue("ma_workgroup") != '') {
	echo "<script type=\"text/javascript\">\n";
	echo "var main_nav = new Spry.Widget.TabbedPanels('main_nav');\n";
	echo "main_nav.showPanel(".$module_index.");\n";
	echo "</script>\n";
}
?>