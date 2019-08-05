<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: tpl_leftMenu.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/

if(!isset($left_loc)) $left_loc = "";

$numArray = count($cat);
if ($numArray > 0 ) {
    for ($i = 0; $i < $numArray; $i++ ) {
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F0F5F6">
    <tr>
        <td height="10"><img src="../../_images/properties/p_dot.gif" width="1" height="1"></td>
    </tr>
    <tr>
        <td>&nbsp;<img src="../../_images/properties/p_leftmenu_icon01.gif" width="5" height="11" align="absmiddle">
        &nbsp;<strong><?php echo $cat[$i]?></strong></td>
    </tr>
    <tr>
        <td height="1" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif" height="1"></td>
    </tr>
    <tr>
        <td height="3"><img src="../../_images/properties/p_dot.gif" width="1" height="1"></td>
    </tr>
<?php
        foreach(${"item".$i} as $string) {
            list($url, $pageName) = explode("::", $string);
?>
    <tr>
        <td height="22">&nbsp;<img src="../../_images/properties/p_leftmenu_icon02.gif" width="7" height="7" align="absmiddle">
        <a href="<?php echo $url?>"><?php echo ($url == $left_loc) ? "<span class=\"menuIndicator\">$pageName</span>" : $pageName ?></a></td>
    </tr>
<?php
            unset(${"item".$i});
        }
?>
    <tr>
        <td height="10"><img src="../../_images/properties/p_dot.gif" width="1" height="1"></td>
    </tr>
</table>
<?php
    }

    unset($string, $cat, $url, $pageName, $numArray);
} else {
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFED">
    <tr>
        <td height="10"><img src="../../_images/properties/p_dot.gif" width="1" height="1"></td>
    </tr>
    <tr>
        <td height="10" align="center"><b>PT. ZONEKOM</b></td>
    </tr>
</table>
<?php
}
?>