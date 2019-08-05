<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*
* $Id: page_error.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
require_once "../../zk_config.php";
require_once APP_DIR . "../_lib/zk_getmessage.php";
?>
<html>
<head>
<title>INFORMATION</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="stylesheet" href="../../_script/aden.css" type="text/css">
<script language="JavaScript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
function setFocus() {
	var btn = window.document.all.tags("BUTTON");
	btn[0].focus();
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="setFocus();" class="main">
<br /><br /><br /><br />
<table class="table_g" width="60%" align="center">
	<tr>
		<th align="center" colspan="2" height="22"><b>INFORMATION</b></th>
	</tr>
	<tr>
		<td width="20%" align="center" height="18">Code</td>
		<td><?php echo base64_decode($msg_code)?></td>
	</tr>
	<tr>
		<td width="20%" align="center" height="18">Subject</td>
		<td><?php echo base64_decode($msg_title)?></td>
	</tr>
	<tr>
		<th align="center" colspan="2" height="18"><b>MESSAGE</b></th>
	</tr>
	<tr>
		<td colspan="2" height="40"><?php echo base64_decode($msg_contents)?></td>
	</tr>
</table><br />
<table class="table_no" width="60%" align="center">
	<tr>
		<td align="center">
<?php
	$btn[1] = "<button onClick=\"window.location.href='" . (empty($_REQUEST['_next'])? MAIN_PAGE : urldecode($_REQUEST['_next'])) .
				"'\" class=\"input_sky\" accesskey=\"c\">CONFIRM</button>";
	$btn[2] = "<button onClick=\"javascript:window.close();\" class=\"input_sky\">CLOSE</BUTTON>";
	echo $btn[$msg_button];
?>
		</td>
	</tr>
</table><br />
<?php require_once APP_DIR . "_include/tpl_footer.php"?>
</body>
</html>
