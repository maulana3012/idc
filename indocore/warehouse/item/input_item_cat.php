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

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc	= "input_item_cat.php";
$_pidx		= isset($_REQUEST['_pidx']) ? $_REQUEST['_pidx'] : 0;

//PROCESS FORM
require_once "tpl_process_form.php";

//========================================================================================== DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_item_cat WHERE icat_pidx = $_pidx AND icat_midx > 0 ORDER BY icat_code";
if(isZKError($result =& query($sql))) {
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

//Make Link
if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_pidx))) {
	$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/input_item_cat.php");
} else {

	eval(html_entity_decode($path[0]));
	$path = array_reverse($path);

	$aLink = array();
	$aLink[] = "<a href=\"".$_SERVER['PHP_SELF']."?_pidx=0\">Category</a>";
	$count = count($path);

	for($i = 1; $i < $count; $i++) { //Excluding
		if($i == ($count - 1)) {
			$aLink[] = "[". $path[$i][3] . "] " . $path[$i][4];
		} else {
			$aLink[] = sprintf(
				"<a href=\"".$_SERVER['PHP_SELF']."?_pidx=%d\">[%s] %s</a>",
				$path[$i][0], //icat_midx
				$path[$i][3], //icat_code
				$path[$i][4]); //icat_name
		}
	}

	$current_depth = $path[$count-1][2];
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

<script language='text/javascript' type='text/javascript'>
function checkform(o) {
	if (verify(o)) {
		o.submit();
	}
}

function deleteCategroy(_midx) {
	if(confirm("Are you sure to delete? if you delete, will delete sub category also")) {
		window.document.frmDelete._midx.value =_midx;
		window.document.frmDelete.submit();
	}
}
var _GET = get_GET();
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
			<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ITEM'S CATEGORY LIST</strong><br /><br />
			<table width="60%" class="table_no_02">
				<tr>
					<td><?php echo implode("&nbsp;&gt;&nbsp;", $aLink);?></td>
				</tr>
			</table>
            <table width="60%" class="table_aa">
              <tr>
                <th width="13%">Code</th>
                <th width="76%">Name</th>
                <th width="11%">DELETE</th>
              </tr>
<?php
while ($column =& fetchRowAssoc($result)) {
?>
              <tr>
				<td><?php echo $column['icat_code']?></td>
                <td>
<?php
if ($column['icat_depth'] <= 2) {
	echo "<a href=\"./input_item_cat.php?_pidx=".$column['icat_midx']."\">".$column['icat_name']."</a>";
	} else {
		echo $column['icat_name'];
	}
?>				</td>
                <td align="center"><a href="javascript:deleteCategroy(<?php echo $column['icat_midx']?>)"><strong>X</strong></a></td>
              </tr>
<?php } ?>
			</table><br><span class="comment">* Pls Click the name to check the sub-category</span><br><br><br>

<?php
if($current_depth <= 2) {
?>
			<table width="60%" class="table_no_02">
				<tr>
					<td>Add Category</td>
				</tr>
			</table>
            <form name='frmAdd' method='POST'>
              <input type='hidden' name='p_mode' value='insert_item_cat'>
			  <input type="hidden" name="_pidx" value="<?php echo $_pidx?>">
			  <input type="hidden" name="_depth" value="<?php echo $current_depth + 1?>">
              <table width="60%" class="table_a">
                <tr>
                  <th width="15%">CODE</th>
                  <td colspan="2"><input name="_code" type="text" class="req" size="3" maxlength="2"><span class="comment">* 2 character only</span>
                  </td>
                </tr>
                <tr>
                  <th>NAME</th>
                  <td>
                  	<input name="_name" type="text" class="req" size="55" maxlength="32"> &nbsp;
					<button name='btnSave' class='input_btn' style='width:100px;' onclick='checkform(window.document.frmAdd)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save item categry"> &nbsp; Save</button>
                  </td>
                </tr>
              </table>
            </form>
<?php } ?>
			<form method="post" name="frmDelete">
				<input type="hidden" name="p_mode" value="delete_item_cat">
				<input type="hidden" name="_pidx" value="<?php echo $_pidx;?>">
				<input type="hidden" name="_midx">
			</form>
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