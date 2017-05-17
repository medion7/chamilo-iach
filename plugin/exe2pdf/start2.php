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
$tool_name = get_lang('Videoconference');
$tpl = new Template($tool_name);
Display::display_header($nameTools, 'Tracking');
$session_id = intval($_REQUEST['id_session']);

		function get_number_of_users (){
			
			// getting all the students of the course
			if (empty($session_id)) {
				// Registered students in a course outside session.
				$a_students = CourseManager :: get_student_list_from_course_code(api_get_course_id());
			} else {
			// Registered students in session.
				$a_students = CourseManager :: get_student_list_from_course_code(api_get_course_id(), true, api_get_session_id());
			}

			return count($a_students);
		}

		function get_user_data($from, $number_of_items, $column, $direction) {

	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
	$sql = "SELECT
                 u.user_id				AS col0,
				 ".(api_is_western_name_order()
                 ? "u.firstname 		AS col1,
                 u.lastname 			AS col2,"
                 : "u.lastname 			AS col1,
                 u.firstname 			AS col2,")."
                 u.username				AS col3
				FROM $user_table AS u INNER JOIN $rel_user AS r ON u.user_id=r.user_id ";


	if (isset ($_GET['keyword'])) {
		$keyword = Database::escape_string(trim($_GET['keyword']));
		$sql .= " WHERE r.course_code='1' AND (u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%' OR concat(u.firstname,' ',u.lastname) LIKE '%".$keyword."%' OR concat(u.lastname,' ',u.firstname) LIKE '%".$keyword."%' OR u.username LIKE '%".$keyword."%'  OR u.user_id LIKE '%".$keyword."%')";
	} elseif (isset ($_GET['keyword_firstname'])) {
		$keyword_firstname = Database::escape_string($_GET['keyword_firstname']);
		$keyword_lastname = Database::escape_string($_GET['keyword_lastname']);
		$keyword_username = Database::escape_string($_GET['keyword_username']);
		$keyword_userId = Database::escape_string($_GET['keyword_userId']);

		$sql .= " WHERE r.course_code='1' AND(u.firstname LIKE '%".$keyword_firstname."%' " .
				"AND u.lastname LIKE '%".$keyword_lastname."%' " .
				"AND u.username LIKE '%".$keyword_username."%'  " .
				"AND u.user_id = ".$keyword_status;
		$sql .= " ) ";

	}else {
		$sql .= " WHERE r.course_code='1'";
	}
		$sql .= " AND r.status='5'";
    // adding the filter to see the user's only of the current access_url

    if (!in_array($direction, array('ASC','DESC'))) {
    	$direction = 'ASC';
    }
    $column = intval($column);
    $from 	= intval($from);
    $number_of_items = intval($number_of_items);

	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";

	$res = Database::query($sql);

	$users = array ();
    $t = time();
	while ($user = Database::fetch_row($res)) {        
		
		$extra = '<center><a href="../mySpace/myStudents.php?student='.$user[0].'&details=true&course='.api_get_course_id().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a></center>';
        $users[] = array($user[0], $user[1], $user[2], $user[3], $extra);
	}
	return $users;
}



echo '<div class="actions">';
echo Display::return_icon('user_na.png', get_lang('ExportUserTest'), array(), 32);
echo Display::url(Display::return_icon('quiz.png', get_lang('ExportExercise'), array(), 32), dirname(__FILE__).'/exercise_export.php?'.api_get_cidreq());
echo '</div>';

echo '<div class="actions">';
// Create a search-box.
$form_search = new FormValidator('search_simple', 'GET', api_get_path(WEB_ROOT).'plugin/exe2pdf/start.php?'.api_get_cidreq(), '', array('class' => 'form-search'), false);
$renderer = $form_search->defaultRenderer();
$renderer->setElementTemplate('<span>{element}</span>');
$form_search->addElement('hidden', 'from', Security::remove_XSS($from));
$form_search->addElement('hidden', 'session_id', api_get_session_id());
$form_search->addElement('text', 'keyword');
$form_search->addElement('style_submit_button', 'submit', get_lang('SearchUsers'), 'class="search"');
$form_search->display();
echo '</div>';

echo Display::page_subheader(Display::return_icon('course.png', get_lang('ExportUserTest'), array(), ICON_SIZE_SMALL).' '.get_lang('Users'));

$table = new SortableTable('users_tracking', 'get_number_of_users', 'get_user_data' , (api_is_western_name_order() xor api_sort_by_first_name()) ? 3 : 2);

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
	$table->set_header(4, get_lang('Details'), false);
    $tab_table_header[] = get_lang('Details');
	
    // display the table
    echo "<div id='reporting_table'>";
    $table->display();
    echo "</div>";


$tpl->assign('message', $message);
$tpl->display_one_col_template();
