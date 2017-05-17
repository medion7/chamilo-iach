<?php
require_once '../inc/global.inc.php';
//require_once '/var/www/vithoulkas/krumo/class.krumo.php';
$confirmationId=$_GET["id"];
$applicationValidated='NO';
$sql = "select id,confirmation_id,application_status from exam_applications where confirmation_id=\"$confirmationId\" ";
$result = Database::query($sql);
if (Database::num_rows($result) == 1) {
	$row = Database :: fetch_array($result, 'ASSOC');
        $dbConfirmationId  = $row['confirmation_id'];
        $dbApplicationId  = $row['id'];
        $applicationStatus  = $row['application_status'];
	if($applicationStatus=='NEW' and $confirmationId==$dbConfirmationId){
		$sql1="update exam_applications set application_status='VALIDATED' where id=$dbApplicationId ";
		Database::query($sql1);

		$applicationValidated='YES';	
	}

}


Display :: display_header($nameTools);

?>
<?php

if($applicationValidated=='YES'){
?>
<div class="normal-message">
Your exam application has been validated. Thank you. <P>
To download your exam request please <a href="/main/exauth/createCopy.php?id=<?php echo $confirmationId ?>" target="_blank">click here.</a> <p>
You should print the request form, enter the current date, sign it, put it in an envelope and have it available in the next few days. A courier service will contact you in order to pick up the envelope from you and deliver it to the Academy.
</div>
<?} else {

echo '<div class="error-message">There was an error validating your submission or your validation id has expired. Please contact the administrator.</div>';
}

Display :: display_footer();
?>
