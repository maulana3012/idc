<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : dskim
*
* $Id: zk_getmessage.php,v 1.1.1.1 2007/06/11 11:19:23 dskim Exp $
*/

if (isset($_POST['msg_contents'])) {
	$isfind			= true;
	$msg_code		= $_POST['msg_code'];
	$msg_title		= $_POST['msg_title'];
	$msg_contents	= $_POST['msg_contents'];
	$msg_button		= (int) $_POST['msg_button']; //1:Confirm, 2:Close
} else {
	$isfind = false;

	if(empty($_POST['msg_id'])){
		die ("<b>Dear, Programmer </b><br/><br/>I Cannot display error page because of wrong argument". __FILE__);
	}

	$msg_id = trim($_POST['msg_id']);

	// Constant ERROR_MESSAGE is defined "config.php"
	$handle = fopen (ERROR_MESSAGE, "r");
	while ($arrMsg = fgetcsv ($handle, 300, ",")) {
		if($arrMsg[0] == $msg_id){
		  $msg_title = $arrMsg[1];
		  $msg_contents = $arrMsg[2];
		  $isfind = true;
		  break;
		}
	}

	fclose ($handle);
}

if (!$isfind){
	$msg_title="Unknown Error";
	$msg_contents = "Sorry, Some Error occured but not defined.<br/>";
}
?>