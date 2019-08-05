<?php
$file_sql = "SELECT * FROM ".ZKP_SQL."_tb_order_file WHERE ord_code = '$_code'";
$file_res =& query($file_sql);
?>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>ATTACHMENT</strong></th>
    </tr>
</table>
<form name="frmCfmAttachment" method="post" enctype="multipart/form-data">
<input type="hidden" name="p_mode">
<input type="hidden" name="_code" value="<?php echo $column['ord_code']?>">
<input type="hidden" name="_date" value="<?php echo date("j-M-Y", strtotime($column['ord_po_date']))?>">
<table width="100%" cellpadding="0">
    <tr>
	<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
	<td><strong>Archieve Information</strong></td>
    </tr>
    <tr>
	<td></td>
	<td>
	    <table width="100%" class="table_box">
		<tr>
		    <td width="5%" valign="top" >
			<button name="btnAddAttachment" class="input_sky" style="color:#003d78;width:100%" onclick="insert_item_print()">+</button></td>
		    <td>
		    <td>    
			<table width="100%" class="table_box">
			<thead>
			    <tr>
			        <td width="5%"></td>
			        <td width="50%"></td>
			        <td></td>
			        <td width="5%"></td>
			    </tr>
			</thead>
			<tbody id="attachment">
			    
			</tbody>
			</table>
		    </td>
		</tr>
		<tr>
		    <td colspan="3" align="right">
			<span class="comment"><i>* File must be image file and size under 200kb </i></span>
			<button name='btnSave' class='input_btn' style='width:80px;' onclick='checkForm(window.document.frmCfmAttachment)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save"> &nbsp; Save</button>
		    </td>
		</tr>
	    </table>
	</td>
    </tr>
    <tr height="50px">
	<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
	<td><strong>List File</strong></td>
    </tr>
</table>
<?php
if(numQueryRows($file_res) <= 0):
    echo "<span class='comment'><i>( No uploaded file )</i></span>";
else:
?>
<table width="100%" class="table_c">
    <thead>
	<tr height="30px">
	    <th colspan="4">ATTACHMENT</th>
	</tr>
	<tr>
	    <td width="25%"></td>
	    <td width="25%"></td>
	    <td width="25%"></td>
	    <td width="25%"></td>
	</tr>
    </thead>
    <tbody>
	<?php
	$i = 0;
	while($items =& fetchRowAssoc($file_res)) {
	    if( $i == 0 ) { echo "<tr height='350px'>"; }
	    echo "
	    <td align='center'>
		<img  src='". USER_DATA . "archieve" . $items['ordf_file_path'] ."' width='200px'>
		<a target='_blank' href='". USER_DATA . "archieve" . $items['ordf_file_path'] ."'>Download</a> |
		<a href=\"javascript:deleteFile(". $items['ordf_idx'] .", '" . $items['ordf_file_type'].' - '.$items['ordf_file_name'] ."', '".$items['ordf_file_path']."')\">Delete</a><br />
		<strong>[ ". $items['ordf_file_type'] ." ]</strong> ". $items['ordf_file_desc'] ."
	    </td>
	    ";
	    $i++;
	    if( $i == 4 ) { echo "</tr>"; $i=0; }
	}
	?>
    </tbody>
</table>
<?php
endif;
?>
    	</td>
    </tr>
</table>
<input type="hidden" name="_idx">
<input type="hidden" name="_idx_path">
</form>
<script language="javascript" type="text/javascript">
var id = 0;
function insert_item_print ()
{
    $string = "<tr id=\"file_"+id+"\">\n"+
		"<td>\n"+
		    "\t<select name=\"cboType[]\">\n"+
			"\t\t<option value=\"PO\">PO</option>\n"+
			"\t\t<option value=\"Surat Jalan\">Surat Jalan</option>\n"+
			"\t\t<option value=\"order\">Order</option>\n"+
			"\t\t<option value=\"Invoice\">Invoice</option>\n"+
			"\t\t<option value=\"Other\">Other</option>\n"+
		    "\t</select>\n"+
		"</td>\n"+
		"<td><input type=\"input\" class=\"fmt\" name=\"_file_remark[]\" style=\"width:100%\"></td>\n"+
		"<td><input type=\"file\" class=\"req\" name=\"_file[]\" style=\"width:100%\"></td>\n"+
		"<td><input type=\"button\" class=\"fmt\" onclick=\"remove_item('"+id+"')\" value=\" - \"></td>\n"+
		"</tr>";
    $('#attachment').append( $string );
    id++;
}

function remove_item ( val )
{
    $('#file_'+val).remove();
    return false;
}

function checkForm ( form )
{

    var e = window.document.frmCfmAttachment.elements;
    var numItem = window.attachment.rows.length;
    var numInput = 4;
    var idx_letter = 6;

    // count file > 1
    if( numItem <= 0 ) {
	alert("Please fill minimal 1 file"); return;
    }

    // check field file required
    for (var i = 0; i< numItem; i++) {
	var str = e(idx_letter+i*numInput).value;
	var ext = str.substring(str.lastIndexOf(".")+1);

	if(str <= 0)  { alert("Please complete your form"); return; }
	if (ext != "jpg" && ext != "jpeg" && ext != "png") {
	    alert ("You can only upload image file.\nPlease check your submit file.");
	    return;
	} 
    }

    if (verify(form)) {
	if(confirm("Are you sure to upload the file?")) {
	    form.p_mode.value = "upload_file";
	    form.submit();
	}
    }
}

function deleteFile(idx, name, path)
{
    form = window.document.frmCfmAttachment;
    if(confirm("Are you sure to delete file "+name+" ?")) {
	form.p_mode.value = "upload_file_delete";
	form._idx.value = idx;
	form._idx_path.value = path;
	form.submit();
    }
}
</script>