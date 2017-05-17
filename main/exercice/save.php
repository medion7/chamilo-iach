<?php 

$language_file = array('exercice');

require_once '../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

require_once 'exercise.class.php';
require_once 'exercise.lib.php';
require_once 'question.class.php'; //also defines answer type constants
require_once 'answer.class.php';


// Database table definitions
$TBL_EXERCICE_QUESTION 	= Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES         	= Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS         	= Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES          	= Database::get_course_table(TABLE_QUIZ_ANSWER);
$main_user_table 		= Database::get_main_table(TABLE_MAIN_USER);
$main_course_user_table = Database::get_main_table(TABLE_MAIN_COURSE_USER);
$TBL_TRACK_EXERCICES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_TRACK_ATTEMPT		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);


$id 	       = intval($_REQUEST['id']); //exe id
$track_exercise_info = get_exercise_track_exercise_info($id);

//No track info
if (empty($track_exercise_info)) {
    api_not_allowed();
}

$exercise_id        = $track_exercise_info['id'];
$exercise_date      = $track_exercise_info['start_date'];
$student_id         = $track_exercise_info['exe_user_id'];
$learnpath_id       = $track_exercise_info['orig_lp_id'];
$learnpath_item_id  = $track_exercise_info['orig_lp_item_id'];    
$lp_item_view_id    = $track_exercise_info['orig_lp_item_view_id'];
$course_code        = api_get_course_id();
$current_user_id    = api_get_user_id();

if ($student_id != $current_user_id) {
    	api_not_allowed();
    }
	
	foreach ($_POST as $key => $value){
			if($value != ""){
			$sql="UPDATE $TBL_TRACK_ATTEMPT SET answer='".$value."' WHERE exe_id=$id AND question_id=$key";	
			Database::query($sql);
			echo $sql;
			echo $value.$id.$current_user_id.$key."<br/>";
		} 
	}
	$sql="UPDATE $TBL_TRACK_EXERCICES SET status='' WHERE exe_id=$id";	
	Database::query($sql);
	
			
header('Location: exercice.php?gradebook=&cidReq='.$course_code);
