<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * Initialization
 */
$course_plugin = 'exe2pdf'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'function.php';
$tool_name = get_lang('UserExercise');
$tpl = new Template($tool_name);
$session_id = intval($_REQUEST['id_session']);
$user_id = $_GET['student'];

	
	if (isset($_GET['export_to_pdf'])) {	
    ExportTool::export_pdf($_GET['export_to_pdf']);
}

if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {
	
	$interbreadcrumb[] = array ("url"=>"start.php", "name"=> get_lang('Exe2pdf'));   
	ExportTool::display_header();
	
	echo ExportTool::get_user_image();
	
	echo Display::page_subheader(Display::return_icon('course.png', get_lang('ExportUserTest'), array(), ICON_SIZE_SMALL).' '.get_lang('Exercises'));

	$table = new SortableTable('exercises', array('ExportTool', 'get_exe_number'), array('ExportTool', 'get_exe_data'), '', 20);

    $table->set_additional_parameters($parameters);
    $tab_table_header = array();    // tab of header texts
    $table->set_header(0, get_lang('Exe_id'), false);
    $tab_table_header[] = get_lang('Exe_id');
    $table->set_header(1, get_lang('QuizTitle'), false);
    $tab_table_header[] = get_lang('QuizTitle');
    $table->set_header(2, get_lang('Date'), false);
    $tab_table_header[] = get_lang('Date');
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