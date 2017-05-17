<?php
/* For licensing terms, see /license.txt */
/**
 *  Shows the exercise results
 *
 * @author Julio Montoya Armas  - Simple exercise result page
 *
 */

/**
 * Code
 */
// name of the language file that needs to be included
$language_file = array('exercice');
$new_path = '../../main/exercice/';

// including additional libraries
require_once $new_path.'exercise.class.php';
require_once $new_path.'question.class.php';
require_once $new_path.'answer.class.php';

require_once '../../main/inc/global.inc.php';
require_once $new_path.'exercise.lib.php';

if (empty($origin)) {
    $origin = $_REQUEST['origin'];
}

$id 	       = isset($_REQUEST['id']) 	  ? intval($_GET['id']) : null; //exe id
$show_headers  = isset($_REQUEST['show_headers']) ? intval($_REQUEST['show_headers']) : null; //exe id

if ($origin == 'learnpath') {
	$show_headers = false;
}

api_protect_course_script($show_headers);

if (empty($id)) {
	api_not_allowed($show_headers);
}

$is_allowedToEdit   = api_is_allowed_to_edit(null,true) || $is_courseTutor;

//Getting results from the exe_id. This variable also contain all the information about the exercise
$track_exercise_info = get_exercise_track_exercise_info($id);

//No track info
if (empty($track_exercise_info)) {
    api_not_allowed($show_headers);
}

$exercise_id        = $track_exercise_info['exe_exo_id'];
$student_id         = $track_exercise_info['exe_user_id'];

$objExercise = new Exercise();

if (!empty($exercise_id)) {
    $objExercise->read($exercise_id);
}


if ($show_headers) {
	$interbreadcrumb[] = array("url" => "exercice.php","name" => get_lang('Exercices'));
	$interbreadcrumb[] = array("url" => "#","name" => get_lang('Result'));
	$this_section = SECTION_COURSES;
	Display::display_header();
} else {
	Display::display_reduced_header();
}

display_question_list_by_attempt($objExercise, $id, false);

if ($show_headers) {
	Display::display_footer();
}