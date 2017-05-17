<?php

$course= array("278","002","009", "004","006","008","005","44");

$con = mysql_connect("localhost","root","Cham1l0!");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("iach_main", $con);

$count = count($course);
for ($i = 0; $i < $count; $i++) {
mysql_query("DELETE FROM course_rel_user WHERE status='5' AND course_code='$course[$i]'");
}

?>