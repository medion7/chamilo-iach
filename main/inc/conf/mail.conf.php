<?php

// ============================== mail ================================= 

// smtp values for phpmailer Class 
$platform_email['SMTP_FROM_EMAIL']   = 'admin@vithoulkas.edu.gr'; 
$platform_email['SMTP_FROM_NAME']    = 'Chamilo Admin';
$platform_email['SMTP_HOST']         = 'localhost'; 
$platform_email['SMTP_PORT']         = 25; 
$platform_email['SMTP_MAILER']       = 'mail'; //mail, sendmail or smtp 
$platform_email['SMTP_AUTH']         = 0; 
$platform_email['SMTP_USER']         = ''; 
$platform_email['SMTP_PASS']         = '';
$platform_email['SMTP_CHARSET']      = 'ISO-8859-15'; 
global $charset;
if (!empty($charset) && $charset != $platform_email['SMTP_CHARSET']) { 
    $platform_email['SMTP_CHARSET']      = $charset; 
}
// ====================================================================
?>
