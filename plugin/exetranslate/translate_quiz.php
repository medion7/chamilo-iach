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
$exe_id = $_GET['exercise'];
$default_course_info = 	api_get_course_info($_GET['cidReq']);
$c_id =	$default_course_info['real_id'];
$ExportTool = new ExportTool();


$exe_info = $ExportTool::get_exe_data($exe_id, $c_id);


if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {

if (isset($_GET['action']) && $_GET['action'] == 'translate' && is_numeric($_GET['exercise'])) {
	$ExportTool::save_quiz_translation($_GET['exercise'], $c_id);
	
}

	$tool_name = get_lang('QuizTranslate');
	$interbreadcrumb[] = array ("url"=>"start.php", "name"=> get_lang('TranslateExe'));
	echo Display::display_header($tool_name);
	
	echo Display::page_subheader(Display::return_icon('translate.png', get_lang('QuizTranslate'), array(), ICON_SIZE_SMALL).' '.get_lang('Exercises'));
	
	$config = array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '100%', 'Height' => '200');
	echo '<div class="control-group ">';
    echo '<div class="controls">';
    echo '<table class="data_table">';
	echo '<tr><th>Content</th><th>Text</th><th>Translate</th></tr>';
	echo '</div></div>';
	
	$quiz_translated = $ExportTool::quiz_intro_translated($exe_id, $c_id);
	
	if($quiz_translated == 0){
	
		$form = new FormValidator('translate', 'post', api_get_self().'?action=translate&exercise='.$_GET['exercise'].'&'.api_get_cidreq());
		$form->addElement('html', '<tr><td>Test name</td><td>');
		$form->addElement('html', $exe_info['title'].'</td><td>');
		$form->addElement('text', 'exe_title');
		$form->addRule('exe_title', get_lang('ThisFieldIsRequired'), 'required');
		$form->addElement('html', '</td></tr>');
		$form->addElement('html', '<tr><td>Description</td><td>');
		$form->addElement('html', $exe_info['description'].'</td><td width="40%">');
		$form->add_html_editor('exe_desc', '', true, false, $config);
		$form->addElement('html', '</td></tr>');
		$form->addElement('html', '<tr><td></td><td></td><td width="40%">');
		$form->addElement('style_submit_button', 'submit', get_lang('Save'), 'class="save"');
		$form->addElement('html', '</td></tr>');
		$form->display(); 
	}else{
		echo '<tr><td>Title</td><td>'.$exe_info['title'].'</td><td><img src="'.api_get_path(WEB_IMG_PATH).'completed.png" border="0" /></td></tr>';
		echo '<tr><td>Description</td><td>'.$exe_info['description'].'</td><td><img src="'.api_get_path(WEB_IMG_PATH).'completed.png" border="0" /></td></tr>';
	}

	echo '</table>';

	$table = new SortableTable('exercises', array('ExportTool', 'get_question_number'), array('ExportTool', 'get_question_data'), '', 300);

    $table->set_additional_parameters($parameters);
    $tab_table_header = array();    // tab of header texts
    $table->set_header(0, get_lang('Question_id'), true);
    $tab_table_header[] = get_lang('Question_id');
    $table->set_header(1, get_lang('QuestionTitle'), true);
    $tab_table_header[] = get_lang('QuestionTitle');
    $table->set_header(2, get_lang('Translated'), false);
    $tab_table_header[] = get_lang('Translated');
	$table->set_header(3, get_lang('Actions'), false);
    $tab_table_header[] = get_lang('Actions');
	
    // display the table
    echo "<div id='reporting_table'>";
    $table->display();
    echo "</div>";
}else {
	echo Display::return_message(get_lang('NotAllowed'), 'error', false);
}
Display::display_footer();