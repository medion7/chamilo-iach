<?php 

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
	
if (empty($objExercise)) {
	$objExercise = new Exercise();
    $objExercise->read($exercise_id);
	
$user_restriction = "AND user_id=".$student_id." ";
$query = "SELECT attempts.question_id, attempts.answer, questions.question from ".$TBL_TRACK_ATTEMPT." as attempts
		  INNER JOIN ".$TBL_QUESTIONS." as questions ON attempts.question_id=questions.id
		  WHERE exe_id='".$id."' $user_restriction
		  GROUP BY questions.position";

$result = Database::query($query);	
$questionList = array();
$answers = array();
$questionss = array();

while ($row = Database::fetch_array($result)) {
	$questionList[] = $row['question_id'];
	$answers[] = $row['answer'];
	$questions[] = $row['question'];
}
$count = mysql_num_rows($result) - 1;
}


$interbreadcrumb[]=array("url" => "exercice.php?gradebook=$gradebook","name" => get_lang('Exercices'));
$interbreadcrumb[]=array("url" => "overview.php?exerciseId=".$exercise_id.'&id_session='.api_get_session_id(),"name" => $objExercise->name);


// Create the form
$form = new FormValidator('edit_my_answer', 'post', 'save.php?cidReq='.$course_code.'&id='.$id);
$form->addElement('header', '', '');

for ($i=0; $i<=$count; $i++) {
$num = $i +1;
if($answers[$i] != '0'){
	$answer = "Your answer:".$answers[$i];
}
else {
	$answer = "You have not answered this question."; 
	}
// Text
$form->addElement ('html', '<div id="question_title" class="sectiontitle">Question '.$num.': '.$questions[$i]);
$form->addElement ('html','</div>');
$form->addElement ('html', '<div id="question_title" class="sectiontitle">'.$answer);
$form->addElement ('html','</div>');
$form->addElement ('html', '<div class="rounded exercise_questions" style="width: 720px; padding: 3px;"><table width="720" class="exercise_options" style="width: 720px; background-color:#fff;"><tr><td colspan="3">');
$form->add_html_editor($questionList[$i], null, null, false, array('ToolbarSet' => 'TestFreeAnswer','Width' => '95%', 'Height' => '250', 'FullPage' => 'false'));
$form->addElement ('html','</table><br/><br/></div>');

} 
// Submit button
$form->addElement('style_submit_button', 'submit', 'Save', 'class="save"');

// Display form
Display::display_header('Edit my answers');
//api_display_tool_title($tool_name);
if(!empty($message)){
	Display::display_normal_message(stripslashes($message));
}

$form->display();



Display :: display_footer();
?>
