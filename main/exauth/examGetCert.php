<?php
$browser=$_SERVER['HTTP_USER_AGENT'];
$fileName=$_GET["id"];

if(strlen(strstr($browser,'MSIE'))>0) {
header( 'Location: https://www.vithoulkas.edu.gr/usercert/'.$fileName.'-cert.p12' ) ;



} else {
header( 'Location: https://www.vithoulkas.edu.gr/usercert/'.$fileName.'-cert.p12' ) ;


}

?>
