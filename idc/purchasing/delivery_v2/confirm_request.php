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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = 'daily_booking_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '')
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
else
	$_code = urldecode($_GET['_code']);

$type[6]	= '[ref marketing request : ';

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_do.php";

//========================================================================================= DEFAULT PROCESS
$sql = "SELECT *, (select req_remark from ".ZKP_SQL."_tb_request where req_code=book_doc_ref) AS remark FROM ".ZKP_SQL."_tb_booking join ".ZKP_SQL."_tb_customer using(cus_code) WHERE book_idx = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['book_doc_type']!=6) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_do.php?_code=$_code");
}

$sql_item = "
SELECT
  a.it_code,			--0
  a.icat_midx,			--1
  a.it_model_no,		--2
  a.it_type,			--3
  a.it_desc,			--4
  (select it_model_no from ".ZKP_SQL."_tb_item where it_code=b.boit_it_code_for) AS it_used_for,	--5
  b.boit_qty,			--6
  b.boit_function,		--7
  b.boit_remark, 		--8
  b.boit_type,			--9
  a.it_ed				--10
FROM
  ".ZKP_SQL."_tb_booking AS c
  JOIN ".ZKP_SQL."_tb_booking_item AS b USING(book_idx)
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE c.book_doc_ref = '".trim($column["book_doc_ref"])."'
ORDER BY a.it_code";
$res_item	= query($sql_item);

$sql_form = "
SELECT 
  a.it_code,
  a.it_model_no,
  a.it_desc,
  a.it_ed,
  SUM(boit_qty)
FROM
  ".ZKP_SQL."_tb_booking AS c
  JOIN ".ZKP_SQL."_tb_booking_item AS b USING(book_idx)
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE c.book_code = '{$column["book_code"]}'
GROUP BY a.it_code, a.it_model_no, a.it_desc, a.it_ed
ORDER BY a.it_code";
$res_form	=& query($sql_form);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
<?php
//current stock
$stock_sql = "SELECT it_code, SUM(stk_qty) FROM ".ZKP_SQL."_tb_stock_v2 GROUP BY it_code ORDER BY it_code";
$stock_res	=& query($stock_sql);

echo "var current_stock = new Array();\n";
while ($rows =& fetchRow($stock_res, 0)) {
	printf("current_stock['%s'] = %s;\n",
		trim($rows[0]),	//item
		$rows[1]		//stock available
	);
}
?>

function seeCurrentStock() {
	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'./p_list_stock.php', '',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

var wInputED;
function insertED(code,i) {
	var f			= window.document.frmInsert;
	var numItem		= window.itemStockPosition.rows.length;
	var numInput	= 4;
	var idx_code	= 11;				/////
	var idx_item	= idx_code+2;
	var idx_booked	= idx_code+3;
	var e = window.document.frmInsert.elements;

	var code		= e(idx_code+i*numInput).value;
	var item		= e(idx_item+i*numInput).value;
	var max_qty		= parseFloat(removecomma(e(idx_booked+i*numInput).value));

	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 250) / 2;

	wInputED = window.open(
		'./p_input_ed.php?_code='+code+'&_item='+item+'&_qty='+max_qty,
		'wSearchED',
		'scrollbars,width=450,height=250,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

function createED() {
	var o	= window.document.frmInsert;
	var f2	= wInputED.document.frmInsert;

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();
	var d	= parseDate(f2.elements[3].value, 'prefer_euro_format');

	var count = EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)+ '-' + f2.elements[2].value + '-' +f2.elements[3].value) {
			alert("this E/D already exist in item ["+trim(f2.elements[0].value)+"] "+ f2.elements[1].value);
			return;
		}
	}

	for (var i=0; i<6; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // CODE
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // IT MODEL NO
				oTD[i].innerText	= f2.elements[1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_model_no[]";
				oTextbox[i].value	= f2.elements[1].value;
				break;

			case 2: // WH LOCATION
				var wh_name = '';
				if (o._function.value == 'IDC') {
					if(f2.elements[2].value == 1) {
						wh_name = 'IDC';
					} else if(f2.elements[2].value == 2) {
						wh_name = 'DNR';
					}
				} else if (o._function.value == 'MED') {
					if(f2.elements[2].value == 1) {
						wh_name = 'MED';
					} 
				}
				var loc					= f2.elements[2].value;
				oTD[i].innerText		= wh_name;
				oTD[i].align			= 'center';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_ed_it_location[]";
				oTextbox[i].value		= loc;
				break;

			case 3: // E/D
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_date[]";
				oTextbox[i].value	= f2.elements[3].value;
				break;

			case 4: // QTY
				oTD[i].innerText	= numFormatval(f2.elements[6].value+'',2);
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_qty[]";
				oTextbox[i].value	= f2.elements[6].value;
				break;

			case 5: // DELETE
				oTD[i].innerHTML	= "<a href=\"javascript:deleteED('"+ trim(f2.elements[0].value)+ '-' + f2.elements[2].value + '-' +f2.elements[3].value +"')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align		= "center";
				break;
		}
		if (i!=5) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value)+ '-' + f2.elements[2].value + '-' +f2.elements[3].value;
		oTR.appendChild(oTD[i]);
	}
	window.EDPosition.appendChild(oTR);
} 

function deleteED(idx) {
	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.EDPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
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
						<?php require_once "_left_menu.php";?>
						<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ITEM REQUEST FOR DEMO STOCK &nbsp; <small class="comment"><?php echo $type[$column['book_doc_type']] . trim($column['book_doc_ref']).']'?></small></h4>
<form name="frmInsert" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_cus_code" value="<?php echo $column['cus_code']?>">
<input type="hidden" name="_out_type" value="2">
<input type="hidden" name="_book_idx" value="<?php echo $column['book_idx']?>">
<input type="hidden" name="_book_dept" value="<?php echo $column['book_dept']?>">
<input type="hidden" name="_book_code" value="<?php echo $column['book_code']?>">
<input type="hidden" name="_book_date" value="<?php echo $column['book_date']?>">
<input type="hidden" name="_doc_ref" value="<?php echo trim($column['book_doc_ref'])?>">
<input type="hidden" name="_doc_type" value="<?php echo $column['book_doc_type']?>">
<input type="hidden" name="_received_by" value="<?php echo $column['book_received_by']?>">
<input type="hidden" name="_revision_time" value="<?php echo $column['book_revision_time']?>">
	<table width="100%" class="table_box">
		<tr>
			<td><span class="bar_bl">DO INFORMATION</span></td>
			<?php if($column['book_revision_time']>0) {?>
			<td colspan="5" align="right">
				<span class="comment"><i>
				Last unconfirmed by : <?php echo $column['book_last_cancelled_by'].', '.date('d-M-Y g:i:s', strtotime($column['book_last_cancelled_timestamp'])).' <b>['.$column['book_revision_time'].']</b>' ?>
				</i></span>
			</td>
			<?php } ?>
		</tr>
		<tr>
			<th width="15%">REQUEST NO</th>
			<td><span class="bar"><?php echo $column['book_code'] ?></span></td>
			<th width="15%">ISSUED BY</th>
			<td width="15%"><?php echo $column['book_received_by'] ?></td>
			<th width="15%">DATE</th>
			<td><?php echo date('d-M-Y', strtotime($column['book_date'])) ?></td>
		</tr>
	</table><br />
	<span class="bar_bl">SUMMARY ITEM</span> &nbsp; <small class="comment"><i><a href="javascript:seeCurrentStock()">( see current stock <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
	<table width="100%" class="table_box" cellspacing="1">
	  <thead>
		<tr height="25px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">QTY</th>
		</tr>
	  </thead>
	  <tbody id="itemStockPosition">
<?php
$i = 0;
$amount = 0;
while($items =& fetchRow($res_form)) {
?>
		<tr>
			<td>
				<input type="hidden" name="_it_code[]" value="<?php echo $items[0] ?>">
				<input type="hidden" name="_it_ed[]" value="<?php echo $items[3] ?>">
				<?php if($items[3]=='t') {?>
				<a href="javascript:insertED(<?php echo "'".trim($items[0])."',".$i ?>)"><b><?php echo $items[0]?></b></a>
				<?php } else {echo $items[0];} ?>
			</td>
			<td><?php echo $items[1]?><input type="hidden" name="_it_model_no[]" value="<?php echo $items[1] ?>" readonly></td>
			<td><?php echo $items[2]?></td>
			<td><input type="text" name="_it_booked_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format($items[4],2)?>" readonly></td>
		</tr>
<?php
	$i++; 
	$amount += $items[4];
}
?>
	  </tbody>
		<tr>
			<th align="right" colspan="3">TOTAL QTY</th>
			<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format($amount,2) ?>" readonly></th>
		</tr>
	</table><br />
	<span class="bar_bl">DETAIL ITEM PER E/D</span>
	<table width="50%" class="table_l">
	  <thead>
		<tr height="25px">
			<th width="15%">CODE</th>
			<th>ITEM NO</th>
			<th width="10%">SOURCE</th>
			<th width="20%">E/D</th>
			<th width="15%">QTY</th>
			<th width="5%"></th>
		</tr>
	  </thead>
	  <tbody id="EDPosition">
	  </tbody>
	</table><br />
	<span class="bar_bl">OTHERS</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">CONFIRMED BY</th>
			<td><input type="text" name="_confirmed" class="req" value="<?php echo $S->getValue("ma_account") ?>" disabled></td>
			<th width="15%">CONFIRMED DATE</th>
			<td><input type="text" name="_confirmed_date" class="reqd" value="<?php echo date('j-M-Y') ?>"></td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="4"><?php echo $column["remark"]?></textarea></td>
		</tr>
	</table>
<input type='hidden' name='_function' value='<?php echo ZKP_SQL ?>'>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name='btnConfirm' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle" alt="Confirm DO"> &nbsp; Confirm DO</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnConfirm.onclick = function() {

		//variable
		var countI		= window.itemStockPosition.rows.length;
		var countII		= window.EDPosition.rows.length;
		var e 			= window.document.frmInsert.elements;
		var numInput	= 4;
		var numInputII	= 5;
		var idx_code	= 11;				/////
		var idx_ed		= idx_code+1;
		var idx_item	= idx_code+2;
		var idx_book	= idx_code+3;
		var idx_codeII	= idx_code+(numInput*countI)+1;
		var idx_qty		= idx_code+(numInput*countI)+5;

		//checking there is stock or not
		for (var i=0; i<countI; i++) {
			var code		= e(idx_code+i*numInput).value;
			var item		= e(idx_item+i*numInput).value;
			var stock		= current_stock[trim(code)];
			var book		= removecomma(numFormatval(e(idx_book+i*numInput).value+'',2));

			if(stock == '') {stock = 0;}

			if(book > stock){
				alert(
					"Check booking qty for:\n\n" +
					"Code : "+ code +"\nItem  : "+ item + "\n" +
					"Current stock qty     : "+numFormatval(stock+'',2)+"\n" +
					"Current booking qty : "+numFormatval(book+'',2));
				return;
			}
		}

		//checking E/D
		for (var i=0; i<countI; i++) {
			if(e(idx_ed+i*numInput).value=='t') {
				var istrue	= false;
				var code	= trim(e(idx_code+i*numInput).value);
				var item	= e(idx_item+i*numInput).value;
				var booked_qty	= removecomma(numFormatval(e(idx_book+i*numInput).value+'',2));

				var inputed_qty	= 0;
				if(booked_qty > 0) {
					if(countII<=0) { alert("Please complete data for outgoing Expired Date");return;}

					for (var j=0; j<countII; j++) {
						if(e(idx_codeII+j*numInputII).value==code) {
							if(parseFloat(removecomma(e(idx_qty+j*numInputII).value))=='') {
								var value = 0;
							} else {
								var value = parseFloat(removecomma(e(idx_qty+j*numInputII).value));
							}
							inputed_qty = inputed_qty + value;
						}
					}
					if(booked_qty != inputed_qty) {
						alert(
							"Check outgoing expired date for:\n\n" +
							"Code : "+ code +"\nItem  : "+ item + "\n" +
							"Current booking qty       : "+numFormatval(removecomma(booked_qty)+'',2)+"\n" +
							"Current inputed E/D qty : "+numFormatval(inputed_qty+'',2)
						);
						return;
					}
				} 

			}
		}

		//final alert
		if(confirm("Are you sure to confirm Request Demo Stock?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'confirm_do';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_booking_by_group.php?cboSource=".$column["book_doc_type"]?>';
	}
</script>
<!--END Button-->
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