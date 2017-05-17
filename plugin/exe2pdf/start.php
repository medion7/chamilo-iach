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
$tool_name = get_lang('Exercise to PDF');
$tpl = new Template($tool_name);
$session_id = intval($_REQUEST['id_session']);
$user_id = $_GET['student'];

if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {
echo Display::display_header($tool_name);

echo '<div class="actions">';
echo Display::return_icon('user_na.png', get_lang('ExportUserTest'), array(), 32);
echo Display::url(Display::return_icon('quiz.png', get_lang('ExportExercise'), array(), 32), api_get_path(WEB_ROOT).'exercise_export.php?'.api_get_cidreq());
echo '</div>';

echo '<div class="actions">';
// Create a search-box.
$form_search = new FormValidator('search_simple', 'GET', api_get_self().'?'.api_get_cidreq(), '', array('class' => 'form-search'), false);
$renderer = $form_search->defaultRenderer();
$renderer->setElementTemplate('<span>{element}</span>');
$form_search->addElement('hidden', 'from', Security::remove_XSS($from));
$form_search->addElement('hidden', 'session_id', api_get_session_id());
$form_search->addElement('text', 'keyword');
$form_search->addElement('style_submit_button', 'submit', get_lang('SearchUsers'), 'class="search"');
$form_search->display();
echo '</div>';

echo Display::page_subheader(Display::return_icon('course.png', get_lang('ExportUserTest'), array(), ICON_SIZE_SMALL).' '.get_lang('Users'));

$table = new SortableTable('users_tracking', array('ExportTool', 'get_number_of_users'), array('ExportTool', 'get_user_data') , (api_is_western_name_order() xor api_sort_by_first_name()) ? 3 : 2);

    $parameters['cidReq'] 		= Security::remove_XSS($_GET['cidReq']);
    $parameters['id_session'] 	= $session_id;
    $parameters['from'] 		= isset($_GET['myspace']) ? Security::remove_XSS($_GET['myspace']) : null;

    $table->set_additional_parameters($parameters);
    $tab_table_header = array();    // tab of header texts
    $table->set_header(0, get_lang('OfficialCode'), true);
    $tab_table_header[] = get_lang('OfficialCode');
    if ($is_western_name_order) {
        $table->set_header(1, get_lang('FirstName'), true);
        $tab_table_header[] = get_lang('FirstName');
        $table->set_header(2, get_lang('LastName'),  true);
        $tab_table_header[] = get_lang('LastName');
    } else {
        $table->set_header(1, get_lang('LastName'),  true);
        $tab_table_header[] = get_lang('LastName');
        $table->set_header(2, get_lang('FirstName'), true);
        $tab_table_header[] = get_lang('FirstName');
    }
    $table->set_header(3, get_lang('Login'), false);
    $tab_table_header[] = get_lang('Login');
	$table->set_header(4, get_lang('Exercises'), false);
    $tab_table_header[] = get_lang('Exercises');
	
    // display the table
    echo "<div id='reporting_table'>";
    $table->display();
    echo "</div>";
	
	$tpl->assign('message', $message);
	$tpl->display_one_col_template();
}else {
	echo Display::return_message(get_lang('NotAllowed'), 'error', false);
}


