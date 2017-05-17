<?php

$course_plugin = 'exe2pdf'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'function.php';
$tool_name = get_lang('UserExercise');
$tpl = new Template($tool_name);
$session_id = intval($_REQUEST['id_session']);
$user_id = $_GET['student'];


if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {
	if (isset($_GET['export_to_pdf'])) {	
    ExportTool::export_quiz($_GET['export_to_pdf']);
	}	

	$user = api_get_user_info($_GET['student']);
	$tool_name = get_lang('ExportExercise');
	$interbreadcrumb[] = array ("url"=>"start.php", "name"=> get_lang('Exe2pdf'));   
	echo Display::display_header($tool_name);
	

	
	if (isset($_GET['export'])){
		ExportTool::quiz_form($_GET['export']);
	}
	echo '<br/>';
	
	$table = new SortableTable('exercises', array('ExportTool', 'get_quiz_number'), array('ExportTool', 'get_quiz_data'), '', 100);

    $table->set_additional_parameters($parameters);
    $tab_table_header = array();    // tab of header texts
    $table->set_header(0, get_lang('Quiz_id'), true);
    $tab_table_header[] = get_lang('Quiz_id');
    $table->set_header(1, get_lang('QuizTitle'), true);
    $tab_table_header[] = get_lang('QuizTitle');
	$table->set_header(2, get_lang('Actions'), false);
    $tab_table_header[] = get_lang('Actions');
	
    // display the table
    echo "<div id='reporting_table'>";
    $table->display();
    echo "</div>";
}else {
	echo Display::return_message(get_lang('NotAllowed'), 'error', false);
}
Display::display_footer();