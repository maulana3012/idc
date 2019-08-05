<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : dskim
*
* $Id: zk_messenger.php,v 1.2 2007/06/28 22:34:57 dskim Exp $
*/

class Messenger
{
  //Whether to use popup dialog or not.
  //If you mind open popup window, set false.You can't see the popup window anymore.
	var $_urlNext;
	var $msg_code = "";
	var $msg_title = "";
	var $msg_contents = "";
	var $msg_button = 1; //1:confirm, 2:Close
	var $target = MAIN_PAGE;

  //Constructor
    function Messenger() {}

    // User can go to the destination page directly with this method.
    function goPage($target = '') {

        $goPage = ($target == '') ? $this->_goPage : $target;

        if (empty($goPage)) {
            die("Error on developing : Wrong usage");
        }

        $loc = 'Location: http://';

        if (substr($goPage, 0, 1)=='/') {
            $loc .= $_SERVER['HTTP_HOST'] . $goPage;
        } else {
            $loc .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $goPage;
        }
		
		// force redirect
		header('HTTP/1.1 301 Moved Permanently');
        header($loc);
        exit;
    }

    function goErrorPage($ZKError, $afterErrorPage, $button = 1) {
        if (is_object($ZKError) && (strtolower(get_class($ZKError)) == "zkerror")) {
            $strHtml = "<html>
<head>
	<meta HTTP-EQUIV=\"Expires\" CONTENT=\"Fri, Jan 01 1900 00:00:00 GMT\">
</head>
<body>
	<form name=\"frmSendMessage\" method=\"POST\" action=\"".ERROR_PAGE."?msg_id=" . $ZKError->getCode() ."\">
		<input type=\"hidden\" name=\"_next\" value=\"".$afterErrorPage."\">
		<input type=\"hidden\" name=\"msg_code\" value=\"".base64_encode($ZKError->getCode())."\">
		<input type=\"hidden\" name=\"msg_title\" value=\"".base64_encode($ZKError->getTitle())."\">
		<input type=\"hidden\" name=\"msg_contents\" value=\"".base64_encode($ZKError->getMessage())."\">
		<input type=\"hidden\" name=\"msg_button\" value=\"".$button ."\">
	</form>
	<script language=\"javascript\">
		frmSendMessage.submit();
	</script>
</body>
</html>";
            die($strHtml);
        } else {
            die ("<b>Dear, Programmer </b><br/><br/>I Cannot display error page because of wrong argument. <br/> I'm here :". __FILE__);
        }
    }
    
    function alert() {
        $numArgs = func_num_args();
            if ($numArgs == 2) {
                $msg_id			= func_get_arg(0);
                $afterConfirm	= func_get_arg(1);
                $strHtml = "<html>
<head>
	<meta HTTP-EQUIV=\"Expires\" CONTENT=\"Fri, Jan 01 1900 00:00:00 GMT\">
	<title>alert</title>
	<script language=\"javascript\" type=\"text/javascript\">
		window.location=\"" . ERROR_PAGE . "?msg_id=".urlencode($msg_id)."&_next=".urlencode($afterConfirm)."\";
	</script>
</head>
</html>";
            } elseif ($numArgs == 0) {
                if ($this->msg_contents == "") {
                    die("Error : you must specify to \$M->setMessage(\"title\", \"message\", \"target\")");
                } else {
                    $strHtml = "
					<html>
						<head>
							<meta HTTP-EQUIV='Expires' CONTENT='Fri, Jan 01 1900 00:00:00 GMT'>
							<title>alert</title>
						</head>
						<body>
							<form name='frmSendMessage' method='POST' action='".ERROR_PAGE."'>
								<input type='hidden' name='_next' value='".urlencode($this->target)."'>
								<input type='hidden' name='msg_title' value='".$this->msg_title."'>
								<input type='hidden' name='msg_contents' value='".$this->msg_contents."'>
							</form>
							<script language='Javascript'>
								frmSendMessage.submit();
							</script>
						</body>
					</html>";
                }
            }
        die($strHtml);
    }

    function printMessage($ZKError) {
        if (is_object($ZKError) && (strtolower(get_class($ZKError)) == "zkerror")) {
            echo "<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" width=\"70%\" align=\"center\" bgcolor=\"red\">\n";
            echo "<tr><td align=\"center\" bgcolor=\"#FFFF99\"><b>" . $ZKError->getTitle() . "</b></td></tr>\n";
            echo "<tr><td bgcolor=\"#FFFFFF\" align=\"center\">" . $ZKError->getMessage() . "</td></tr>\n";
            echo "</table>";
        } else {
            echo "<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" width=\"70%\" align=\"center\" bgcolor=\"red\">\n";
            echo "<tr><td bgcolor=\"#FFFF99\" align=\"center\">" . $ZKError . "</td></tr>\n";
            echo "</table>";
        }
    }
}
?>