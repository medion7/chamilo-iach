<?php

require_once '../inc/global.inc.php';
//require_once '/var/www/vithoulkas/krumo/class.krumo.php';


Display :: display_header($nameTools);

if (api_is_platform_admin()) {
?>
<div id="submain"><h1> Manage Exam Registrations</h1>

<div id="main">
<?php
$sql = "select * from exam_applications order by date_submitted desc ";
$result = Database::query($sql);
if (Database::num_rows($result) >0 ){
	echo '<table border=1 cellspacing="1" cellpadding="1">';
	echo '<tr><th>Id</th><th>Date Submited</th><th>Title</th><th>Firstname</th><th>Lastname</th><th>Address</th><th>Postcode</th><th>City</th><th>Country</th><th>Telephone</th><th>Country Code</th><th>Available from</th><th>Available to</th><th>Email</th><th>Generation Date</th><th>SSL Filename</th><th>Status</th><th>Actions</th><th>Application</th></tr>';
	while ($row=Database::fetch_array($result, 'ASSOC')) {
			echo '<tr><td>'.$row['id'].'</td>'.'<td>'.$row['date_submitted'].'</td>'
			.'<td>'.$row['salutation'].'</td>'
			.'<td>'.$row['firstname'].'</td>'
			.'<td>'.$row['lastname'].'</td>'
			.'<td>'.$row['address'].'</td>'
			.'<td>'.$row['postcode'].'</td>'
			.'<td>'.$row['city'].'</td>'
			.'<td>'.$row['country'].'</td>'
			.'<td>'.$row['telephone'].'</td>'
			.'<td>'.$row['country_code'].'</td>'
			.'<td>'.$row['hours_from'].'</td>'
			.'<td>'.$row['hours_to'].'</td>'
			.'<td>'.$row['email'].'</td>'
			.'<td>'.$row['generation_date'].'</td>'
			.'<td>'.$row['certificate_file'].'</td>'
			.'<td>'.$row['application_status'].'</td>';
			echo '<td>';
			if($row['application_status']=='VALIDATED'){
			echo '<a href="/main/exauth/adminGenerateCertificate.php?id='.$row['id'].'"><img src="/main/img/ssl_icon.gif" border="0"></a>';
			}
			if($row['application_status']=='CERTIFICATE GENERATED'){
			echo '<a href="/main/exauth/adminGraduateUser.php?id='.$row['id'].'"><img src="/main/img/graduate_icon.png" border="0"></a>';
			}
			echo '</td>';
			echo '<td>';
			if(($row['application_status']=='VALIDATED') or ($row['application_status']=='CERTIFICATE GENERATED')){
			echo '<a href="/main/exauth/createCopy.php?id='.$row['confirmation_id'].'">Print</a>';
			}
			echo '</td></tr>';
                }
	echo '</table>';


}


?>



</div>




<?php
}

Display :: display_footer();

?>

