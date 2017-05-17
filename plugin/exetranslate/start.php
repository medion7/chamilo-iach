<?php

$course_plugin = 'exetranslate'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'function.php';
$tpl = new Template($tool_name);
$session_id = intval($_REQUEST['id_session']);
$user_id = $_GET['student'];

if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {
$tool_name = get_lang('TranslateExe');
echo Display::display_header($tool_name);
	
	
	$table = new SortableTable('exercises', array('ExportTool', 'get_quiz_number'), array('ExportTool', 'get_quiz_data'), '', 100);

    $table->set_additional_parameters($parameters);
    $tab_table_header = array();    // tab of header texts
    $table->set_header(0, get_lang('Quiz_id'), true);
    $tab_table_header[] = get_lang('Quiz_id');
    $table->set_header(1, get_lang('QuizTitle'), true);
    $tab_table_header[] = get_lang('QuizTitle');
	$table->set_header(2, get_lang('Progress'), false);
    $tab_table_header[] = get_lang('Proggress');
	$table->set_header(3, get_lang('Translate'), false);
    $tab_table_header[] = get_lang('Translate');
	
    // display the table
    echo "<div id='reporting_table'>";
    $table->display();
    echo "</div>";
}else {
	echo Display::return_message(get_lang('NotAllowed'), 'error', false);
}
Display::display_footer();