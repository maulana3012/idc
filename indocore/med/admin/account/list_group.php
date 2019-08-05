<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*
* $Id: list_group.php,v 1.3 2008/08/12 03:34:22 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_UPDATE, HTTP_DIR . "admin/account/index.php");

//GLOBAL
$left_loc = "list_group.php";

// DEFAULT PROCESS ================================================================================
$sql = "SELECT 
	gr_access,
	CASE
	  WHEN gr_access='ALL' THEN 'Report Management System'
	  WHEN gr_access='IDC' THEN 'Indocore Management System'
	  WHEN gr_access='MED' THEN 'Medisindo Management System'
	  WHEN gr_access='MEP' THEN 'Medikus Management System'
	END AS gr_access_desc, 
	gr_idx, 
	gr_name, 
	getTotalMember(gr_access, gr_idx) AS gr_total_member, 
	gr_desc 
  FROM tb_grade ORDER BY gr_access, gr_priority";
if(isZKError($result =& query($sql))) {
	$M->goErrorPage($result, MAIN_PAGE);
}

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['gr_access'],			//0
		$col['gr_idx'],				//1
		$col['gr_name'],			//2
		$col['gr_total_member'],	//3
		$col['gr_desc'],			//4
		$col['gr_access_desc']		//5
	);

	//1st grouping
	if($cache[0] != $col['gr_access']) {
		$cache[0] = $col['gr_access'];
		$group0[$col['gr_access']] = array();
	}

	if($cache[1] != $col['gr_idx']) {
		$cache[1] = $col['gr_idx'];
	}

	$group0[$col['gr_access']][$col['gr_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_URL][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] GROUP MANAGEMENT<br /><br /></strong>
<?php
//GROUP
foreach ($group0 as $total1 => $group1) {
	
	echo "<span class=\"comment\"><b> [". $total1. "] ".$rd[$rdIdx][5]. "</b></span>\n";
print <<<END
<table width="100%" class="table_a">
	<tr>
		<th width="7%">ID</th>
		<th width="30%">GROUP NAME</th>
		<th width="15%">TOTAL MEMBER</th>
		<th>DESCRIPTION</th>
	</tr>\n
END;

	foreach($group1 as $total2) {
		print "<tr>\n";
		cell($rd[$rdIdx][1], ' align="center"');
		cell_link('<span style="color:blue">'.$rd[$rdIdx][2] . '</span>', '', 
			' href="javascript:openWindow(\'p_group_member_list.php?_access='.$rd[$rdIdx][0].'&_idx='.$rd[$rdIdx][1].'&_name='.$rd[$rdIdx][2].'\', 670,600)"');
		cell($rd[$rdIdx][3] . " member(s)", ' align="center"');
		cell($rd[$rdIdx][4]);
		print "</tr>\n";
		$rdIdx++;
	}
	print "</table><br />";
}
?>

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