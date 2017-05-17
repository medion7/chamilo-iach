<?php


function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function createRandomPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;
    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;

    }
    return $pass;
}


require_once '../inc/global.inc.php';
//require_once '/var/www/vithoulkas/krumo/class.krumo.php';

if (api_is_platform_admin()) {



$applicationId=$_GET["id"];
$generateCertificate='NO';

$sql = "select id,confirmation_id,application_status from exam_applications where id=\"$applicationId\" ";
$result = Database::query($sql);

if (Database::num_rows($result) == 1) {
        $row = Database :: fetch_array($result, 'ASSOC');
        $dbConfirmationId  = $row['confirmation_id'];
        $dbApplicationId  = $row['id'];                                                                                                 
        $applicationStatus  = $row['application_status'];                                                                               
        if($applicationStatus=='VALIDATED'){                                                    
                $generateCertificate='YES';
        }                                                                                                                               
                                                                                                                                        
}

if($generateCertificate=='YES'){

	$filename = gen_uuid();
	$passphrase = createRandomPassword();
	$sql = "select u.username, e.email from user u , exam_applications e where e.user_id=u.user_id and e.id=$applicationId";
	$result = Database::query($sql);
	if (Database::num_rows($result) >0 ){
		$row = Database :: fetch_array($result, 'ASSOC');
		$userName=$row['username'];
		$email=$row['email'];

	}
	// RUN THE SCRIPT TO GENERATE THE CERTIFICATE
	$generateCertResult = exec ('/usr/bin/sudo /etc/ssl/certadm/makecert.sh '.$userName.' '.$filename.' '.$passphrase.' 60');

	
	$sql1="update exam_applications set application_status='CERTIFICATE GENERATED', certificate_file=\"$filename\" , generation_date=now() where id=$dbApplicationId ";                         
	Database::query($sql1);                                                                                                 


        //send email
         $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
        $email_admin = api_get_setting('emailAdministrator');
        $emailsubject = 'SSL Certificate for Classical Homeopathy E-Learning PROGRAM ';

        $emailbody = 'Thank you for registering for your exam. In order to access your exam you need to install your personal SSL certificate by clicking  https://www.vithoulkas.edu.gr/main/exauth/examGetCert.php?id='.$filename.' .
 You will be contacted by a courier service commissioned from the Academy, who will deliver to you a printed password which you will need in order to install the certificate, and pick up from you the signed copy of your application.'; 
        @api_mail('', $email, $emailsubject, $emailbody, $sender_name, $email_admin);
}


Display :: display_header($nameTools);
?>
<div id="main">
<div id="submain"><h1> Certificate Generation</h1>


</div>

<?php if($generateCertificate=='YES' and $generateCertResult=='Success!'){ ?>
<div class="normal-message">
The user certificate has been generated.<br/>
The file is <?php echo $filename ?> <br/>
The passphrase  is "<?php echo $passphrase ?>" <br/>
</div>
<?php } else { 
echo '<div class="error-message">There was an error generating the user SSL certificate. Please contact the administrator.</div>';


}?>


<?php
}

Display :: display_footer();

?>

