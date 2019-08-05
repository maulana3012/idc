<?php
require_once 'src/src/Mandrill.php'; 

try {
    $mandrill = new Mandrill('AM07DPTUJMZbdwGBkMGKJA');
    $name = 'Faktur Pajak';
    $from_email = 'neki.sw@medisindo.co.id';
    $from_name = 'Example Name';
    $subject = 'example subject';
    $code = '<div>example code</div>';
    $text = 'Example text content';
    $publish = false;
    $labels = array('example-label');
    $result = $mandrill->templates->add($name, $from_email, $from_name, $subject, $code, $text, $publish, $labels);
    print_r($result);
    /*
    Array
    (
        [slug] => example-template
        [name] => Example Template
        [labels] => Array
            (
                [0] => example-label
            )
    
        [code] => editable content
        [subject] => example subject
        [from_email] => from.email@example.com
        [from_name] => Example Name
        [text] => Example text
        [publish_name] => Example Template
        [publish_code] => different than draft content
        [publish_subject] => example publish_subject
        [publish_from_email] => from.email.published@example.com
        [publish_from_name] => Example Published Name
        [publish_text] => Example published text
        [published_at] => 2013-01-01 15:30:40
        [created_at] => 2013-01-01 15:30:27
        [updated_at] => 2013-01-01 15:30:49
    )
    */
} catch(Mandrill_Error $e) {
    // Mandrill errors are thrown as exceptions
    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
    // A mandrill error occurred: Mandrill_Invalid_Key - Invalid API key
    throw $e;
}
?>