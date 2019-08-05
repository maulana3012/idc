<?php
$file_sql = "SELECT 
  billf_idx,
  bill_code,
  billf_file_name,
  billf_file_path,
  billf_file_type,
  CASE 
    WHEN billf_file_type = 'Faktur Pajak' AND bill_is_fp_delivery is false THEN 'NOT DELIVERY'
    WHEN billf_file_type = 'Faktur Pajak' AND bill_is_fp_delivery is true THEN 'DELIVERY'
    WHEN billf_file_type = 'Faktur Pajak Rev' AND bill_is_fpp_delivery is false THEN 'NOT DELIVERY'
    WHEN billf_file_type = 'Faktur Pajak Rev' AND bill_is_fpp_delivery is true THEN 'DELIVERY'
  END AS status
 FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_file USING (bill_code) 
 WHERE billf_file_type IN ('Faktur Pajak', 'Faktur Pajak Rev') AND bill_code = '$_code'";
$file_res =& query($file_sql);
?>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>FAKTUR PAJAK</strong></th>
    </tr>
</table>
<?php if ( in_array($S->getValue("ma_account"), $auth["input_efaktur"])) { ?>
<!-- START : print form upload pdf efaktur -->
<form name="frmCfmAttachmentPajak" method="post" enctype="multipart/form-data">
<input type="hidden" name="p_mode">
<input type="hidden" name="_idx">
<input type="hidden" name="_idx_path">
<input type="hidden" name="Faktur_Pajak" value="Faktur Pajak">
<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
<input type="hidden" name="_fp_no" value="<?php echo $column['bill_vat_inv_no'] ?>">
<input type="hidden" name="_date" value="<?php echo date("j-M-Y", strtotime($column['bill_inv_date']))?>">
<?php if(numQueryRows($file_res) == 0){ ?>
<table width="50%" cellpadding="0">
    <tr>
        <td width="25%">UPLOAD FILE</td>
        <td>
            <input type="hidden" name="cboType[]" value="Faktur Pajak">
            <input type="hidden" name="_file_remark[]" value="Faktur Pajak">
            <input type="file" class="req" name="_file[]" style="width:100%">
        </td>
    </tr>
    <tr>
        <td align="right" colspan="2">
            <span class="comment"><i>* File must be pdf file and size under 200kb </i></span>
            <button name='btnSave' class='input_btn' style='width:80px;' onclick='checkFormPjk( window.document.frmCfmAttachmentPajak )'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save"> &nbsp; Save</button>
        </td>
    </tr>
</table>
<?php } ?>
<?php if(numQueryRows($file_res) == 1) { ?>
<table width="50%" cellpadding="0">
<input type="hidden" name="Perbaikan" value="fpb">
    <tr>
        <td width="30%">UPLOAD FILE PERBAIKAN</td>
        <td>
            <input type="hidden" name="cboType[]" value="Faktur Pajak Rev">
            <input type="hidden" name="_file_remark[]" value="Faktur Pajak Rev">
            <input type="file" class="req" name="_file[]" style="width:100%">
        </td>
    </tr>
    <tr>
        <td align="right" colspan="2">
            <span class="comment"><i>* File must be pdf file and size under 200kb </i></span>
            <button name='btnSave' class='input_btn' style='width:80px;' onclick='checkFormPjk( window.document.frmCfmAttachmentPajak )'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save"> &nbsp; Save</button>
        </td>
    </tr>
</table>
<?php  } ?>
</form>
<!-- END : print form upload pdf efaktur -->
<?php  } ?>


<br />
<?php  if(numQueryRows($file_res) > 0) { ?>
<table width="70%" class="table_aa">
    <?php while($items =& fetchRowAssoc($file_res)) { ?>
    <tr>
        <th align="left" style="padding:5px"><b><?php echo $items['billf_file_name'] ?></b></th>
        <th width="15%"><?php echo $items['status'] ?></th>
        <th>
            <button name='btnPrint' class='input_btn' style='width:100px;' onclick="printPdf('<?php echo $items['billf_file_name'] ?>')"><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
            <?php if ( in_array($S->getValue("ma_account"), $auth["input_efaktur"])) { ?>
            <button name='btnDelete' class='input_red' style='width:120px;' onclick="deleteItemPdf('<?php echo $items['billf_idx'] ?>','<?php echo $items['billf_file_path'] ?>')"<?php echo ($items['status']=='DELIVERY') ? " disabled":""  ?>><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete billing"> &nbsp; Delete PDF</button>
            <?php } ?>
        </th>
    </tr>
    <?php } ?>
</table><br />
<?php } ?>
<script language="javascript" type="text/javascript">
oFormFaktur = window.document.frmCfmAttachmentPajak;
<?php if(numQueryRows($file_res) <= 1) { ?>
window.document.frmCfmAttachmentPajak.btnSave.onclick = function() {
    if(confirm("Are you sure to upload the file?")) {
        oFormFaktur.p_mode.value = "upload_file";
        oFormFaktur.submit();
    }
}
<?php } ?>

function printPdf(idx_name) {
    var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
    winforPrint.document.location.href = "../../_include/billing/pdf/download_pdf.php?_source=pajak&_dept=<?php echo $currentDept ?>&_inv_date=<?php echo date("Ym", strtotime($column['bill_inv_date'])) ?>&_file="+idx_name;
}

function deleteItemPdf(idx,idx_path) {
    if(confirm("Are you sure to delete file faktur pajak?")) {
        oFormFaktur._idx.value = idx;
        oFormFaktur._idx_path.value = idx_path;
        oFormFaktur.p_mode.value = 'upload_file_delete';
        oFormFaktur.submit();
    }
}
</script>