<?php

require_once 'src/src/Mandrill.php'; 

$attachment = file_get_contents('/home/pdf/medisindo/billing/apotik/201506/BO-01956A-F15_rev_0.pdf');
$attachment_encoded = base64_encode($attachment); 

try {
    $mandrill = new Mandrill('AM07DPTUJMZbdwGBkMGKJA');
    $message = array(
        'from_email' => 'ratna.accounting@medisindo.co.id',
        'from_name' => 'Accounting - PT Medisindo Bahana',
        'subject' => 'Faktur Pajak PT. Medisindo Bahana',
        'text' => 'Faktur Pajak PT. Medisindo Bahana',
        'to' => array(
            array('email' => 'neki.sw@medisindo.co.id','type' => 'to'), 
            //array('email' => 'medisindobahana@gmail.com','type' => 'to'), 
            array('email' => 'medisindobahana@gmail.com','type' => 'cc'), 
            //array('email' => 'neki.arismi@gmail.com','type' => 'bcc')
        ),
        'headers' => array('Reply-To' => 'ratna.accounting@medisindo.co.id'),
        'html' => '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Message Title</title>        
</head>
 <body style="margin:0; padding:0; font-family:Helvetica,sans-serif; font-size:13pt; line-height:150%;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="700">
            <tr>
                <td style="text-align:center; padding:20px 0px 0px 0px; border-top:10px solid #FA6900;">
                    <img src="http://medisindo.co.id/images/Logo_Medisindo.jpg" width="100" height="100" alt="Logo"/>
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
                                Dear Customer ( <b>PT. Kimia Farma</b> ),<br />
                                Berikut kami lampirkan file PDF faktur pajak atas pembelian anda.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <table width="650" border="0" style="border-collapse: collapse; margin: auto;">
                            <tr>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">No.</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">Invoice No</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">FP. No</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">DPP</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">PPN</th>
                                <th style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">Total</th>
                            </tr>
                            <tr>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">1</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">BO-01973H-F15</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">010.001-15.59550456</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">35,002,200</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">3,500,220</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">38,502,420</td>
                            </tr>
                            <tr>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">2</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">BO-01973H-F15</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">010.001-15.59550456</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">35,002,200</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">3,500,220</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">38,502,420</td>
                            </tr>
                            <tr>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">3</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">BO-01973H-F15</td>
                                <td style="text-align: center; border-bottom: 1px solid #A7DBD8; padding: 5px;">010.001-15.59550456</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">35,002,200</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">3,500,220</td>
                                <td style="text-align: right; border-bottom: 1px solid #A7DBD8; padding: 5px;">38,502,420</td>
                            </tr>
                            </table>
                            <p>
                                &nbsp;
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 13px; padding: 0px 0px 10px 0px;">
                            Best Regards,<br />Accounting - PT Medisindo Bahana
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
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="700">
            <tr>
                <td style="text-align: center; padding: 10px; border-bottom: 30px solid #E0E4CC;">&nbsp;</td>
            </tr>
            </table>
        </td>
    </tr>
    </table>
</body>
</html>
        ',
        'attachments' => array(
            array(
                'type' => 'application/pdf',
                'name' => 'BO-01956A-F15',
                'content' => $attachment_encoded
            )
        ),
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
?>