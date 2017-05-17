<?php

require_once '../inc/global.inc.php';
$applicationId=$_GET["id"];
$sql1="update exam_applications set application_status='GRADUATED' where id=$applicationId ";
Database::query($sql1);

header( 'Location: /main/exauth/adminExamApplications.php' ) ;




?>
