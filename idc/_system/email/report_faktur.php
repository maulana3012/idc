<?php 
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php"; 
require_once "mandrill-api-php/src/src/Mandrill.php";


//QUERY
$sql = "
SELECT
  CASE 
    WHEN '$_file_type' = 'FP' THEN 'Faktur Pajak '
    WHEN '$_file_type' = 'FP Perbaikan' THEN 'Faktur Pajak Perbaikan 011' || substr(bill_vat_inv_no,4) || ' '
  END AS subject,
  CASE 
    WHEN '".ZKP_SQL."' = 'IDC' THEN 'PT. Indocore Perkasa'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 THEN 'PT. Medisindo Bahana'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 2 THEN 'PT. Samudia Bahtera'
  END AS company,
  CASE 
    WHEN '".ZKP_SQL."' = 'IDC' THEN 'Logo_Indocore.png'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 THEN 'Logo_Medisindo.jpg'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 2 THEN 'Logo_Samudia.png'
  END AS logo,
  CASE 
    WHEN '".ZKP_SQL."' = 'IDC' THEN '#002C6E'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 THEN '#FF0000'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 2 THEN '#8FBC2E'
  END AS color_top,
  CASE
    WHEN substr(bill_pajak_to,1,2) = '2F' THEN '[' || trim(bill_ship_to) || '] ' || bill_ship_to_name
    ELSE bill_pajak_to_name
  END AS customer,
  CASE 
    WHEN '".ZKP_SQL."' = 'IDC' THEN 'hesti.accounting@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 THEN 'ratna.accounting@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 2 THEN 'ratna.accounting@samudia.co.id'
  END AS email_accounting,
  ".ZKP_SQL."_getemail(bill_pajak_to, bill_ship_to) AS email_customer,
  CASE 
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'A' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'D' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'H' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'M' THEN 'putri.marketing@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'P' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'T' THEN 'putri.marketing@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'A' THEN 'dewi.apotik@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'D' THEN 'linda.dealer@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'H' THEN 'nuri.hospital@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'T' THEN 'sarah.bs@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 2 AND bill_dept = 'T' THEN 'sarah.bs@medisindo.co.id'
  END AS email_admin,

  bill_code as invoice_no,
  CASE 
    WHEN '$_file_type' = 'FP' THEN bill_vat_inv_no
    WHEN '$_file_type' = 'FP Perbaikan' THEN '011' || substr(bill_vat_inv_no,4)
  END AS fp_no,
  to_char(bill_inv_date, 'dd/Mon/yy') AS invoice_date,
  ROUND((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100)) as dpp,
  ROUND((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) * bill_vat/100) as ppn,
  ROUND(bill_total_billing - bill_delivery_freight_charge)  as total,
  CASE 
    WHEN '$_file_type' = 'FP' THEN (SELECT billf_file_name FROM ".ZKP_SQL."_tb_billing_file WHERE bill_code = b.bill_code AND billf_file_type = 'Faktur Pajak') 
    WHEN '$_file_type' = 'FP Perbaikan' THEN (SELECT billf_file_name FROM ".ZKP_SQL."_tb_billing_file WHERE bill_code = b.bill_code AND billf_file_type = 'Faktur Pajak Rev') 
  END AS pdf_file_name,
  CASE
    WHEN '$_file_type' = 'FP' THEN (SELECT '/home/pdf/indocore/archieve'||billf_file_path FROM ".ZKP_SQL."_tb_billing_file WHERE bill_code = b.bill_code AND billf_file_type = 'Faktur Pajak')
    WHEN '$_file_type' = 'FP Perbaikan' THEN (SELECT '/home/pdf/indocore/archieve'||billf_file_path FROM ".ZKP_SQL."_tb_billing_file WHERE bill_code = b.bill_code AND billf_file_type = 'Faktur Pajak Rev')
  END AS pdf_file_path
FROM
  ".ZKP_SQL."_tb_billing AS b
WHERE bill_code  in ($_bill_code)
ORDER BY bill_code
";

$result = & query($sql);
$col =& fetchRowAssoc($result);
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Send Faktur Pajak to Customer</title>        
</head>
 <body style="margin:0; padding:0; font-family:Helvetica,sans-serif; font-size:14px; line-height:150%;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="750">
            <tr>
                <td style="text-align:center; padding:20px 0px 0px 0px; border-top:10px solid '.$col["color_top"].';">
                    <img src="http://medisindo.co.id/images/'.$col["logo"].'" width="100" height="100" alt="Logo"/>
                </td>
            </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="700" style="border-collapse:collapse; border:0px; background-color:#ffffff;">
            <tr>
                <td style="padding: 10px 0 20px 0;">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="520" style="border-collapse: collapse;">
                    <tr>                  
                        <td style="font-size: 13px; padding: 0px 0px 10px 0px;">
                            <p>
                                Dear Customer ( <b>'.$col["customer"].'</b> ),<br />
                                Berikut kami lampirkan file PDF faktur pajak atas pembelian anda.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <table width="730" border="0" style="border-collapse: collapse; margin: auto;">
                            <tr>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">No.</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">Invoice No</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">FP. No</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">Date</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">DPP</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">PPN</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">Total</th>
                            </tr>';
$i = 1;
pg_result_seek($result, 0);
while ($row =& fetchRowAssoc($result)) {
    $html .= '
                            <tr>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">'.$i++.'</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">'.$row["invoice_no"].'</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">'.$row["fp_no"].'</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">'.$row["invoice_date"].'</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">'.number_format($row["dpp"],0).'</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">'.number_format($row["ppn"],0).'</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">'.number_format($row["total"],0).'</td>
                            </tr>
    ';

    $attach_file[]  = array(
                        "type" => "application/pdf", 
                        "name"=> $row["pdf_file_name"], 
                        "content"=>base64_encode(file_get_contents($row["pdf_file_path"]))
                      );

}

$html .=                    '</table>
                            <p>
                                &nbsp;
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 13px; padding: 0px 0px 10px 0px;">
                            Best Regards,<br />Accounting - '.$col["company"].'
                        </td>
                    </tr>
                    </table>
                </td>
            </tr>             
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="750">
            <tr>
                <td style="text-align: center; padding: 10px; border-bottom: 30px solid #E0E4CC;">&nbsp;</td>
            </tr>
            </table>
        </td>
    </tr>
    </table>
</body>
</html>';


$email = explode(', ', $col["email_customer"]);
foreach($email as $val) {
    $attach_email[] = array("email" => $val,  'type' => 'to');
}

$email = explode(', ', $col["email_admin"]);
foreach($email as $val) {
    $attach_email[] = array("email" => $val,  'type' => 'cc');
}

$email = explode(', ', $col["email_accounting"]);
foreach($email as $val) {
    $attach_email[] = array("email" => $val,  'type' => 'bcc');
}

$attach_email[] = array("email" => 'neki.sw@medisindo.co.id',  'type' => 'bcc');

try {
    $mandrill = new Mandrill(API_KEY_M);
    $message = array(
        'from_email' => $col["email_accounting"],
        'from_name' => 'Accounting - '.$col["company"],
        'subject' => $col["subject"].$col["company"], 
        'text' => 'Faktur Pajak '.$col["company"],
        'to' => $attach_email,
        'headers' => array('Reply-To' => $col["email_accounting"]),
        'html' => $html,
        'attachments' => $attach_file
    );

    $async = false;
    $result = $mandrill->messages->send($message, $async);
    print_r($result);

} catch(Mandrill_Error $e) {
    // Mandrill errors are thrown as exceptions
    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
    throw $e;
}
