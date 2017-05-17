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

if (isset($_GET['output'])) {	
    ExportTool::export($_GET['output']);
}
}