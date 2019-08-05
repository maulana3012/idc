<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//GLOBAL
$left_loc = "summary_by_personal_info.php";
$_chk	  = (isset($_GET['chkAcara'])) ? $_GET['chkAcara']:'off';
$_kode_acara 	= isset($_GET['_kode_acara'])? urldecode($_GET['_kode_acara']) : "";
$_nama_acara 	= isset($_GET['_nama_acara'])? urldecode($_GET['_nama_acara']) : "";
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT * FROM ".ZKP_SQL."_tb_event JOIN ".ZKP_SQL."_tb_event_peserta USING(ev_idx)");
$tmp 	= array();
$strGet	= "";

//Search Option 1 : by Category
if(isset($_GET['_kode_acara']) && $_GET['_kode_acara'] != '') {
	$tmp[] = "ev_idx = $_kode_acara";
	$strGet .= "&_kode_acara=$_kode_acara";
}
$tmp[] = "ev_tanggal_acara BETWEEN DATE '$period_from' AND DATE '$period_to'";
$strGet .= "&period_from=$period_from&period_to=$period_to";
$strWhere = implode(" AND ", $tmp);
$sqlQuery->whereCaluse = $strWhere;
$sqlQuery->setOrderBy("ev_tanggal_acara DESC, evp_code");

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 100);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language='text/javascript' type='text/javascript'>
function fillAcara(obj) {
	if(obj.checked) {
		var x = (screen.availWidth - 400) / 2;
		var y = (screen.availHeight - 600) / 2;
		var win = window.open('p_list_event.php', '', 'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	} else {
		window.location.href = 'summary_by_personal_info.php';
	}
}

function chkFillAcara() {
	if(window.document.frmSearch.chkAcara.checked) {
		fillAcara(window.document.frmSearch.chkAcara);
	}
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
			<?php //require_once "_left_menu.php";?>
			<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr height="5px"><td></td></tr>
	<tr>
		<td width="55%" rowspan="2"><h4>DAFTAR PESERTA EVENT</h4></td>
		<td> NAMA ACARA </td>
		<td> EVENT PERIOD </td>
	</tr>
	<tr>
		<td>	
			<input type="checkbox" name="chkAcara" onclick="fillAcara(this)"<?php echo  ($_chk=='on')?' checked':'' ?>>
			<input type="hidden" name="_kode_acara" value="<?php echo $_kode_acara ?>">
			<input type="text" name="_nama_acara" size="30" class="fmt" value="<?php echo $_nama_acara ?>" onclick="chkFillAcara()" readonly>
		</td>
		<td>
			<select name="cboPeriod">
				<option value=""></option>
				<option value="lastWeek">LAST WEEK</option>
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisWeek">THIS WEEK</option>
				<option value="thisMonth">THIS MONTH</option>
			</select>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
	</tr>
</table><br />
</form>
<table width="100%" class="table_f">
	<tr height="30px">
		<th width="2%">No</th>
		<th width="5%">No Input</th>
		<th width="15%">Nama Acara</th>
		<th width="7%">Tanggal</th>
		<th width="18%">Nama Customer</th>
		<th width="3%">JK</th>
		<th width="10%">HP</th>
		<th width="10%">Telp.</th>
		<th width="10%">E-mail</th>
		<th>Alamat</th>
	</tr>
	<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><?php echo $column["evp_code"] ?></td>
		<td><?php echo $column["ev_nama_acara"] ?></td>
		<td align="center"><?php echo date("d-M-y", strtotime($column["ev_tanggal_acara"])) ?></td>
		<td><a href="revise_event_peserta.php?_id=<?php echo $column["evp_code"];?>"><span style="color:blue"><?php echo $column['evp_nama']?></span></a></td>
		<td align="center"><?php echo strtoupper($column["evp_jenis_kelamin"]) ?></td>
		<td><?php echo $column["evp_contact_handphone"] ?></td>
		<td><?php echo $column["evp_contact_telepon"] ?></td>
		<td><?php echo $column["evp_contact_email"] ?></td>
		<td><?php echo $column["evp_contact_alamat"] ?></td>
	</tr>
	<?php } ?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");

	f.cboPeriod.onchange = function() {
		if (this.value != "") {
			setPeriod(ts, this.value, f.period_from, f.period_to);
			f.submit();
		}
	}


	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.cboPeriod.value = '';
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