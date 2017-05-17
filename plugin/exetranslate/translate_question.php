<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * Initialization
 */
$course_plugin = 'exetranslate'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once 'function.php';
$tpl = new Template($tool_name);
$session_id = intval($_REQUEST['id_session']);
$question_id = $_GET['question'];
$default_course_info = 	api_get_course_info($_GET['cidReq']);
$c_id =	$default_course_info['real_id'];
$ExportTool = new ExportTool();



if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {

if (isset($_GET['action']) && $_GET['action'] == 'translate' && is_numeric($_GET['id'])) {
	$ExportTool::save_question_translation($_GET['id'], $_GET['exercise']);
	
}
	$tool_name = get_lang('UserExercise');
	$interbreadcrumb[] = array ("url"=>"start.php", "name"=> get_lang('TranslateExe'));
	$interbreadcrumb[] = array ("url"=>"translate_quiz.php?exercise=".$_GET['exercise']."&".api_get_cidreq(), "name"=> get_lang('QuizTranslate'));
	echo Display::display_header($tool_name);
	
	echo Display::page_subheader(Display::return_icon('translate.png', get_lang('UserExercise'), array(), ICON_SIZE_SMALL).' '.get_lang('UserExercise'));
	
	echo '<div class="control-group ">';
    echo '<div class="controls">';
    echo '<table class="data_table">';
	echo '<tr><th width="10px">ID</th><th width="10px">True</th><th width="50%">Answer</th></tr>';
	echo '</div></div>';
	
	$config = array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '100%', 'Height' => '120');
	$question_info = $ExportTool::question($c_id, $question_id);
	
	$form = new FormValidator('translate', 'post', api_get_self().'?action=translate&id='.$question_id.'&exercise='.$_GET['exercise'].'&'.api_get_cidreq());
	$form->addElement('html', '<tr><td>Question</td><td>');
	$form->addElement('html', $question_info['question'].'</td><td>');
	$form->addElement('text', 'question');
	$form->addRule('question', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('html', '</td></tr>');
	$form->addElement('html', '<tr><td>Enrich question</td><td>');
	$form->addElement('html', $question_info['description'].'</td><td>');
	$form->add_html_editor('description', '', true, false, $config);
	$form->addRule('description', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('html', '</td></tr>');
	
	
	$tbl_answers = Database :: get_course_table(TABLE_QUIZ_ANSWER);
		
		$sql = "SELECT id, answer FROM $tbl_answers WHERE c_id=".$c_id." AND question_id=".$question_id;
		$res = Database::query($sql);
		while($answer = Database::fetch_array($res)){
			$form->addElement('html', '<tr><td>Answer '.$answer['id'].'</td><td>');
			$form->addElement('html', $answer['answer'].'</td><td>');
			$form->add_html_editor($answer['id'], '', true, false, $config);
			$form->addRule($answer['id'], get_lang('ThisFieldIsRequired'), 'required');
			$form->addElement('html', '</td></tr>');
		}
	
	$form->addElement('html', '<tr><td></td><td></td><td>');
	$form->addElement('style_submit_button', 'submit', get_lang('Save'), 'class="save"');
	$form->addElement('html', '</td></tr>');
	


// Display form
$form->display(); 

	echo '</table>';
}else {
	echo Display::return_message(get_lang('NotAllowed'), 'error', false);
}
Display::display_footer();