<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * Initialization
 */

$course_plugin = 'casestest'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'lib/cases.lib.php';
$tool_name = get_lang('Case Study Submission Tool');
$tpl = new Template($tool_name);
$session_id = intval($_REQUEST['id_session']);
$user_id = api_get_user_id();
$course_info = api_get_course_info(api_get_course_id());
$course_id = $course_info['real_id'];

// Setting the tabs
$this_section = SECTION_COURSES;

$htmlHeadXtra[] = api_get_jqgrid_js();

// Access control
api_protect_course_script(true, false, true);

require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';

// document path
$documentPath = api_get_path(SYS_COURSE_PATH).$_course['path']."/document";

/* 	Constants and variables */
$is_allowedToEdit = api_is_allowed_to_edit(null, true) || api_is_drh();
$is_tutor = api_is_allowed_to_edit(true);

$course_id = api_get_course_int_id();
if (api_is_allowed_to_edit()==1) {
echo Display::display_header($tool_name);

//$case = new Cases;
//echo $case->get_number_of_user_cases();

echo '<div class="actions">';
echo Display::return_icon('folder.png', get_lang('SubmittedCases'), array(), 32);
echo Display::url(Display::return_icon('user_na.png', get_lang('CasesPerUser'), array(), 32), api_get_path(WEB_ROOT).'cases_of_users.php?'.api_get_cidreq());
echo '</div>';

echo '<div class="actions">';
// Create a search-box.
$form_search = new FormValidator('search_simple', 'GET', api_get_self().'?'.api_get_cidreq(), '', array('class' => 'form-search'), false);
$renderer = $form_search->defaultRenderer();
$renderer->setElementTemplate('<span>{element}</span>');
$form_search->addElement('hidden', 'from', Security::remove_XSS($from));
$form_search->addElement('hidden', 'session_id', api_get_session_id());
$form_search->addElement('html', get_lang('SearchUser').': ');
$form_search->addElement('text', 'keyword');
$form_search->addElement('html', get_lang('SearchCase').': ');
$form_search->addElement('text', 'case_keyword', get_lang('Category'));
$form_search->addElement('style_submit_button', 'submit', get_lang('Search'), 'class="search"');
$form_search->display();
echo '</div>';

echo Display::page_subheader(Display::return_icon('folder.png', get_lang('SubmittedCases'), array(), ICON_SIZE_SMALL).' '.get_lang('SubmittedCases'));

$table = new SortableTable('users_tracking', array('Casestest', 'get_number_of_cases'), array('Casestest', 'get_data_of_cases') , (api_is_western_name_order() xor api_sort_by_first_name()) ? 3 : 2);

    $parameters['cidReq'] 		= Security::remove_XSS($_GET['cidReq']);
    $parameters['id_session'] 	= $session_id;
    $parameters['from'] 		= isset($_GET['myspace']) ? Security::remove_XSS($_GET['myspace']) : null;

    $table->set_additional_parameters($parameters);
    $tab_table_header = array();    // tab of header texts
	$table->set_header(0, get_lang('CaseStudy'), true);
    $tab_table_header[] = get_lang('CaseStudy');
	$table->set_header(1, get_lang('SubmissionDate'), true);
    $tab_table_header[] = get_lang('SubmissionDate');
	$table->set_header(2, get_lang('State'), true);
    $tab_table_header[] = get_lang('State');
    if ($is_western_name_order) {
        $table->set_header(3, get_lang('FirstName'), true);
        $tab_table_header[] = get_lang('FirstName');
        $table->set_header(4, get_lang('LastName'),  true);
        $tab_table_header[] = get_lang('LastName');
    } else {
        $table->set_header(3, get_lang('LastName'),  true);
        $tab_table_header[] = get_lang('LastName');
        $table->set_header(4, get_lang('FirstName'), true);
        $tab_table_header[] = get_lang('FirstName');
    }
    $table->set_header(5, get_lang('Login'), true);
    $tab_table_header[] = get_lang('Login');
	$table->set_header(6, get_lang('Action'), true);
    $tab_table_header[] = get_lang('Action');
	
    // display the table
    echo "<div id='reporting_table'>";
    $table->display();
    echo "</div>";
	
	$tpl->assign('message', $message);
	$tpl->display_one_col_template();
}elseif(api_is_allowed_to_edit()!=1){
	echo Display::display_header($tool_name);

	$case = new CasesTest;
	//echo $case->get_number_of_user_cases();

	echo '<div class="actions">';
	echo Display::return_icon('folder.png', get_lang('MyCases'), array(), 32);
	echo Display::url(Display::return_icon('new_folder_na.png', get_lang('SubmitNewCase'), array(), 32), api_get_path(WEB_ROOT).'add_new_case.php?'.api_get_cidreq());
	echo '</div>';
	
	echo Display::page_subheader(Display::return_icon('folder.png', get_lang('Session'), array(), ICON_SIZE_SMALL).' '.get_lang('MyCases'));	
	
	echo '<table width="100%" class="data_table">
			<tr>
				<th>'.get_lang('CaseStudy').'</th>
				<th>'.get_lang('SubmissionDate').'</th>
				<th>'.get_lang('State').'</th>
				<th>'.get_lang('Actions').'</th>
			</tr>
			'.$case->get_data_of_user_cases($user_id).'
		</table>';
}
Display :: display_footer();