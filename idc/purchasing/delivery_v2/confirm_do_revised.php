<script src="../../_script/jQuery.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
<?php
//current stock
$stock_sql = "SELECT it_code, SUM(stk_qty) FROM ".ZKP_SQL."_tb_stock_v2 GROUP BY it_code ORDER BY it_code";
$stock_res	=& query($stock_sql);

echo "var current_stock = new Array();\n";
while ($rows =& fetchRow($stock_res, 0)) {
	printf("current_stock['%s'] = %s;\n",
		trim($rows[0]),	//item
		$rows[1]	//stock available
	);
}
?>
</script>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>REVISED ITEM</strong> for <?php echo $col[0]['out_code'] ?></th>
    </tr>
</table><br />
<form name="frmRevised" method="post">
<input type="hidden" name="p_mode" value="confirm_do_revised">
<input type="hidden" name="_out_idx" value="<?php echo  $col[0]['out_idx'] ?>">
<input type="hidden" name="_out_type" value="<?php echo  $col[0]['out_type'] ?>">
<input type="hidden" name="_doc_type" value="<?php echo $col[0]['book_doc_type']?>">
<input type="hidden" name="_doc_ref" value="<?php echo $col[0]['book_doc_ref']?>">
<input type="hidden" name="_book_idx" value="<?php echo $col[0]['book_idx']?>">
<input type="hidden" name="_book_date" value="<?php echo $col[0]['book_date']?>">
<input type='hidden' name='_function' value='<?php echo ZKP_SQL ?>'>
<table width="100%">
	<tr>
		<td width="50%" valign="top">
<strong class="info">RECAP ITEM</strong>
<table width="100%" class="table_box" cellspacing="1">
  <thead>
	<tr>
		<th width="15%">CODE</th>
		<th>ITEM NO</th>
		<th width="15%">QTY</th>
	</tr>
  </thead>
  <tbody id="revItemPosition">
<?php
$amount=0;
while($items =& fetchRowAssoc($col[5])) {
?>
	<tr>
		<td>
		  <?php if($items['it_ed']=='t') {?>
		  <a href="javascript:insertED(<?php echo "'".trim($items['it_code'])."', '".trim($items['it_model_no'])."', ". $items['boit_qty']?>)"><b><?php echo $items['it_code']?></b></a>
		  <?php } else {echo $items['it_code'];} ?>
		  <input type="hidden" name="_it_code[]" value="<?php echo $items['it_code'] ?>">
		  
		</td>
		<td><?php echo $items['it_model_no']?><input type="hidden" name="_it_model_no[]" value="<?php echo $items['it_model_no'] ?>"></td>
		<td><input type="text" name="_it_qty[]" class="fmtn" style="width:100%" value="<?php echo number_format($items['boit_qty'],2)?>" readonly></td>
		<td><input type="hidden" name="_it_ed[]" value="<?php echo $items['it_ed'] ?>"></td>
	</tr>
<?php
	$amount += $items['boit_qty'];
}
?>
  </tbody>
      <tr>
	      <th align="right" colspan="2">TOTAL QTY</th>
	      <th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format($amount,2) ?>" readonly></th>
      </tr>
</table><br />
		</td>
		<td width="50%" valign="top">
<span class="bar_bl">DETAIL ITEM PER E/D</span>
<table width="100%" class="table_l">
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
  <tbody id="revEDPosition">
  </tbody>
</table><br />
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2">
			Your password : &nbsp;
			<input type="password" name="_password" class="reqd" size="15" value=""> &nbsp;
			<button name='btnConfirm' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle" alt="Confirm DO"> &nbsp; Confirm DO</button>&nbsp;
		</td>
	</tr>
</table>
</form>
<?php
echo "<pre>";
echo "</pre>";
?>
<script language="javascript" type="text/javascript">
var wInputED;
function insertED(code, item, max_qty) {
	var f = window.document.frmRevised;
	var e = window.document.frmRevised.elements;

	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 250) / 2;

	wInputED = window.open(
		'./p_input_ed.php?_code='+code+'&_item='+item+'&_qty='+max_qty,
		'wSearchED',
		'scrollbars,width=450,height=250,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

var id = 0;
function createED() {
	var o	= window.document.frmRevised;
	var f2	= wInputED.document.frmInsert;
	var ed_date = formatDate(parseDate(f2.elements[3].value, 'prefer_euro_format'), 'NNN-yyyy');
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

	// Check for duplicate item + ED
	var is_double = false;
	$("#revEDPosition tr").each(function() {
	    if( f2.elements[0].value+wh_name+ed_date ==
		$(this).find('td:nth-child(1) input').val()+$(this).find('td:nth-child(3) input').val()+$(this).find('td:nth-child(4) input').val()
	       )
		is_double = true;
	});
	if(is_double) {
		alert("This E/D already exist in item ["+trim(f2.elements[0].value)+"] "+ f2.elements[1].value+"\nPlease choose another ED");
		return;
	}

	// Insert element to table
	var txt = '<tr id="item_'+id+'">'+
		    '<td>'+f2.elements[0].value+'<input type="hidden" name="_ed_it_code[]" value="'+f2.elements[0].value+'"></td>'+
		    '<td>'+f2.elements[1].value+'<input type="hidden" name="_ed_it_model_no[]" value="'+f2.elements[1].value+'"></td>'+
		    '<td>'+wh_name+'<input type="hidden" name="_ed_it_location[]" value="'+f2.elements[2].value+'"></td>'+
		    '<td>'+ed_date+'<input type="hidden" name="_ed_it_date[]" value="'+ed_date+'"></td>'+
		    '<td>'+f2.elements[6].value+'<input type="hidden" name="_ed_it_qty[]" value="'+f2.elements[6].value+'"></td>'+
		    '<td><a href="" onclick="remove_key(\''+id+'\'); return false;">[ - ]</a></td>'+
		   '</tr>';
	$('#revEDPosition').append( txt );
} 

function remove_key ( VAL )
{
    $('#item_'+VAL).remove();
    return false;
}

window.document.frmRevised.btnConfirm.onclick = function() {
    var o = window.document.frmRevised;

    if(o._password.value.length <= 0) {
	alert('PASSWORD must be entered');
	o._password.focus();
	return;
    }

    //checking there is stock or not
    var chk = true;
    $("#revItemPosition tr").each(function() {
	var code = $(this).find('td:nth-child(1) input').val();
	var item = $(this).find('td:nth-child(2) input').val();
	var book = $(this).find('td:nth-child(3) input').val();
	var stock = current_stock[code];
	if(stock == '') {stock = 0;}
    
	if(book > stock){
		alert(
			"Check booking qty for:\n\n" +
			"Code : "+ code +"\nItem  : "+ item + "\n" +
			"Current stock qty     : "+numFormatval(stock+'',2)+"\n" +
			"Current booking qty : "+numFormatval(book+'',2));
		chk = false;
	}

	//checking E/D
	if ($(this).find('td:nth-child(4) input').val() == 't')
	{
	    var ed_qty = 0;
	    $("#revEDPosition tr").each(function() {
		if(code == $(this).find('td:nth-child(1) input').val())
		{
		    ed_qty = ed_qty + parseFloat(removecomma($(this).find('td:nth-child(5) input').val()));
		}
	    });

	    if (book != ed_qty) {
		alert(
			"Check outgoing expired date for:\n\n" +
			"Code : "+ code +"\nItem  : "+ item + "\n" +
			"Current booking qty        : "+numFormatval(removecomma(book)+'',2)+"\n" +
			"Current inputed E/D qty  : "+numFormatval(ed_qty+'',2)
		);
		chk = false;
	    }
	}
    });
    if (!chk) return;

    //final alert
    if(confirm("Are you sure to confirm DO?")) {
	o.submit();
    }

}
</script>