<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: list_account.php,v 1.3 2008/08/12 03:34:22 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_UPDATE, HTTP_DIR . "admin/account/index.php");

//GLOBAL
$left_loc = "list_account.php";

//DEFAULT PROCESS
$sql = "
SELECT
	ma_idx,
	ma_account,
	CASE
		WHEN ma_isvalidacc is true then 'check.jpg'
		WHEN ma_isvalidacc is false then 'delete_2.gif'
	END AS ma_isvalidacc,
	ma_displayname,
	to_char(ma_lastsignindate, 'dd-Mon-yyyy') AS lastsignin,
	to_char(ma_passwordblockdate, 'dd-Mon-yyyy') AS passwordblock,
	extract(DAY FROM CURRENT_DATE - ma_lastpasswdchangedate) || ' days ago' AS lastpasswdchange,
	to_char(ma_regdate, 'dd-Mon-yy') AS regdate
FROM tb_mbracc
ORDER BY ma_account";

if (isZKError($result = & query($sql))) {
	$M->goErrorPage($result, MAIN_PAGE);
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ACCOUNT LIST<br /><br /></strong>
<table width="100%" class="table_a">
	<tr>
		<th width="30">ID#</th>
		<th width="120">ACCOUNT</th>
		<th>USER NAME</th>
		<th width="50">VALID</th>
		<th width="80">Last Sign-In</th>
		<th width="80">BLOCK DATE</th>
		<th width="80">LAST PW CHANGE</th>
		<th width="80">Register</th>
	</tr>
	<?php $i=1; while ($data = fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo $i++ ?></td>
		<td><a href="<?php echo HTTP_DIR . "admin/account/" ?>detail_account.php?ma_idx=<?php echo $data['ma_idx']?>" class="bar"><?php echo ucfirst($data['ma_account']) ?></a></td>
		<td><?php echo ucfirst($data['ma_displayname']) ?></td>
		<td align="center"><img src="../../_images/icon/<?php echo $data['ma_isvalidacc']?>"></td>
		<td align="center"><?php echo $data['lastsignin']?></td>
		<td align="center"><?php echo $data['passwordblock']?></td>
		<td align="center"><?php echo $data['lastpasswdchange']?></td>
		<td align="center"><?php echo $data['regdate']?></td>
	</tr>
	<?php } ?>
</table>
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
