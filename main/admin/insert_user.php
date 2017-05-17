<?php


require ('../inc/global.inc.php');
// including additional libraries
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
include_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');



$con = mysql_connect("localhost","root","Cham1l0!");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("iach_main", $con);

$result = mysql_query("SELECT user_id  FROM course_rel_user WHERE course_code='0001'");

while($row = mysql_fetch_array($result)){
$user_id=$row['user_id'];
$course_code='901';

if ($user_id != 1)
{
CourseManager::subscribe_user($user_id,$course_code);
}					
		}
?>
