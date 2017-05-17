<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * Initialization
 */

$course_plugin = 'exams'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
$tool_name = get_lang('Exams');
$tpl = new Template($tool_name);
$session_id = intval($_REQUEST['id_session']);
$user_id = $_GET['student'];

if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {

echo Display::display_header($tool_name);

	if (isset($_GET['action']) && $_GET['action'] == 'subscribe_user' && is_numeric($_GET['student'])){
	
		ExportTool::subscribe($_GET['student']);
	
	}elseif (isset($_GET['action']) && ($_GET['action'] == 'edit_oid' || $_GET['action'] == 'import_oid')&& is_numeric($_GET['student'])){
		
		$exams = 'iach_main.exams';
		$oid = '';
		$action = 'NewOID';
		$student_id = $_GET['student'];
		
		$sql = "SELECT `order_id` FROM $exams WHERE user_id = ".$student_id;
		$res = Database::query($sql);
			while($exam = Database::fetch_array($res)){
				$oid = $exam['order_id'];	
			}
		if($oid != null){
			$action = 'EditOID';
		}
		echo '<div class="actions">';
		echo Display::url(Display::return_icon('back.png', get_lang('Back'), array(), 32), api_get_path(WEB_ROOT).'start.php?'.api_get_cidreq());
		echo '</div>';
		
		$form = new FormValidator('oid', 'post', 'start.php?action=save_oid&'.api_get_cidreq());
		$form->addElement('header', get_lang($action));
		
		$form->addElement('hidden', 'user_id', $student_id);
		$form->addElement('hidden', 'action', $action);
		
		$form->addElement('text', 'order_id', get_lang('OID'), array('size'=>35));
		
		$form->setDefaults(array('order_id' => $oid));
		
		$form->addElement('style_submit_button', 'submit', get_lang('Confirm'), 'class="save"');

		$form->display();
	
	}elseif (isset($_GET['action']) && $_GET['action'] == 'save_oid' && isset($_POST['order_id'])){
	
		ExportTool::save_oid();
	
	}else{

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
			if ($is_western_name_order) {
				$table->set_header(0, get_lang('FirstName'), true);
				$tab_table_header[] = get_lang('FirstName');
				$table->set_header(1, get_lang('LastName'),  true);
				$tab_table_header[] = get_lang('LastName');
			} else {
				$table->set_header(0, get_lang('LastName'),  true);
				$tab_table_header[] = get_lang('LastName');
				$table->set_header(1, get_lang('FirstName'), true);
				$tab_table_header[] = get_lang('FirstName');
			}
			$table->set_header(2, get_lang('Login'), true);
			$tab_table_header[] = get_lang('Login');
			$table->set_header(3, get_lang('OrderID'), true);
			$tab_table_header[] = get_lang('OrderID');
			$table->set_header(4, get_lang('Status'), true);
			$tab_table_header[] = get_lang('Status');
			$table->set_header(5, get_lang('Action'), false);
			$tab_table_header[] = get_lang('Action');
	
			// display the table
			echo "<div id='reporting_table'>";
			$table->display();
			echo "</div>";
	}
	$tpl->assign('message', $message);
	$tpl->display_one_col_template();
	
}else {
	echo Display::return_message(get_lang('NotAllowed'), 'error', false);
}


