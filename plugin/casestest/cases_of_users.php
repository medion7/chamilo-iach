<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * Initialization
 */

$course_plugin = 'cases'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'lib/cases.lib.php';
$session_id = intval($_REQUEST['id_session']);
$user_id = $_GET['student'];
$course_info = api_get_course_info(api_get_course_id());
$course_id = $course_info['real_id'];
$tool_name = get_lang('Case Study Submission Tool');
$tpl = new Template($tool_name);

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

$case = new CasesTest;
//echo $case->get_data_of_user_cases();

if(empty($_GET['student_id'])){

echo '<div class="actions">';
echo Display::url(Display::return_icon('folder_na.png', get_lang('CasesPerUser'), array(), 32), api_get_path(WEB_ROOT).'start.php?'.api_get_cidreq());
echo Display::return_icon('user.png', get_lang('SubmittedCases'), array(), 32);
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

echo Display::page_subheader(Display::return_icon('user.png', get_lang('CasesPerUser'), array(), ICON_SIZE_SMALL).' '.get_lang('CasesPerUser'));

$table = new SortableTable('users_tracking', array('Cases', 'get_number_of_users_cases'), array('Cases', 'get_data_of_users_cases') , (api_is_western_name_order() xor api_sort_by_first_name()) ? 3 : 2);

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
    $table->set_header(2, get_lang('Login'), false);
    $tab_table_header[] = get_lang('Login');
	$table->set_header(3, get_lang('SubmittedCases'), false);
    $tab_table_header[] = get_lang('SubmittedCases');
	$table->set_header(4, get_lang('ApprovedCases'), false);
    $tab_table_header[] = get_lang('ApprovedCases');
	$table->set_header(5, get_lang('Actions'), false);
    $tab_table_header[] = get_lang('Actions');
	
    // display the table
    echo "<div id='reporting_table'>";
    $table->display();
    echo "</div>";
	
	$tpl->assign('message', $message);
	$tpl->display_one_col_template();
	
}elseif(!empty($_GET['student_id'])){
	
	$student_info = api_get_user_info($_GET['student_id']);
	$tool_name = '<a href="case.php?'.api_get_cidreq().'">'.get_lang('Case Study Submission Tool').'</a> / '.get_lang('UserCases');
	$tpl = new Template($tool_name);
		
	// Actions bar
	echo '<div class="actions">';
    echo '<a href="case.php?'.api_get_cidreq().'">'.Display::return_icon('back.png', get_lang('Back'),'',ICON_SIZE_MEDIUM).'</a>';
	echo '</div>';		
	
		// is the user online ?
    if (user_is_online($_GET['student'])) {
        $online = get_lang('Yes');
    } else {
        $online = get_lang('No');
    }
		
	// get average of score and average of progress by student
	$course_code = Security :: remove_XSS($_GET['course']);	
	
	if (!CourseManager :: is_user_subscribed_in_course($student_info['user_id'], $course_code, true)) {
		unset($courses[$key]);
	} else {		
		$avg_student_progress = Tracking::get_avg_student_progress($student_info['user_id'], $course_code, array(), $session_id);
		//the score inside the Reporting table
		$avg_student_score 	  = Tracking::get_avg_student_score($student_info['user_id'], $course_code, array(), $session_id);
		//var_dump($avg_student_score);	
	}	
    
	$avg_student_progress = round($avg_student_progress, 2);
	
	// time spent on the course
    
	$time_spent_on_the_course = api_time_to_hms(Tracking :: get_time_spent_on_the_course($student_info['user_id'], $course_code, $session_id));
    	
	// get information about connections on the platform by student
	$first_connection_date = Tracking :: get_first_connection_date($student_info['user_id']);
	if ($first_connection_date == '') {
		$first_connection_date = get_lang('NoConnexion');
	}

	$last_connection_date = Tracking :: get_last_connection_date($student_info['user_id'], true);
	if ($last_connection_date == '') {
		$last_connection_date = get_lang('NoConnexion');
	}
    
    //Show title
    $info_course = CourseManager :: get_course_information($course_code);
    $coachs_name  = '';
    $session_name = '';     
    $nb_login = Tracking :: count_login_per_student($student_info['user_id'], $_GET['course']);    
    //get coach and session_name if there is one and if session_mode is activated
    if ($session_id > 0) {
        
        $session_info  = api_get_session_info($session_id);         
        $course_coachs = api_get_coachs_from_course($session_id, $course_code);
        $nb_login = '';         
        if (!empty($course_coachs)) {
            $info_tutor_name = array();
            foreach ($course_coachs as $course_coach) {
                $info_tutor_name[] = api_get_person_name($course_coach['firstname'], $course_coach['lastname']);
            }
            $info_course['tutor_name'] = implode(",",$info_tutor_name);
        } elseif ($session_coach_id != 0) {                 
            $session_coach_id = intval($session_info['id_coach']);      
            $coach_info = UserManager::get_student_info_by_id($session_coach_id);
            $info_course['tutor_name'] = api_get_person_name($coach_info['firstname'], $coach_info['lastname']);
        }
        $coachs_name  = $info_course['tutor_name'];
        $session_name = $session_info['name'];
    } // end
 
    $info_course  = CourseManager :: get_course_information($get_course_code);    
    $table_title = Display::return_icon('user.png', get_lang('User'), array(), ICON_SIZE_SMALL).$student_info['complete_name'];
    
    echo Display::page_subheader($table_title);
    
    echo '<table width="100%" border="0">';
    echo '<tr>';
    
	$image_array = UserManager :: get_user_picture_path_by_id($student_info['user_id'], 'web', false, true);
	echo '<td class="borderRight" width="10%" valign="top">';

	// get the path,width and height from original picture
	$image_file = $image_array['dir'] . $image_array['file'];
	$big_image = $image_array['dir'] . 'big_' . $image_array['file'];
	$big_image_size = api_getimagesize($big_image);
	$big_image_width = $big_image_size['width'];
	$big_image_height = $big_image_size['height'];
	$url_big_image = $big_image . '?rnd=' . time();
	$img_attributes = 'src="' . $image_file . '?rand=' . time() . '" ' .
	'alt="' . $student_info['complete_name']. '" ' .
	'style="float:' . ($text_dir == 'rtl' ? 'right' : 'left') . '; padding:5px;" ';

	if ($image_array['file'] == 'unknown.jpg') {
		echo '<img ' . $img_attributes . ' />';
	} else {
		echo '<input type="image" ' . $img_attributes . ' onclick="javascript: return show_image(\'' . $url_big_image . '\',\'' . $big_image_width . '\',\'' . $big_image_height . '\');"/>';
	}
	echo '</td>';
?>
		<td width="40%" valign="top">
			<table width="100%" class="data_table">
				<tr>
					<th><?php echo get_lang('Information'); ?></th>
				</tr>
				<tr>
					<td><?php echo get_lang('Name') . ' : '.$student_info['complete_name']; ?></td>
				</tr>
				<tr>
					<td><?php echo get_lang('Email') . ' : ';					
					if (!empty ($student_info['email'])) {
						echo '<a href="mailto:' . $student_info['email'] . '">' . $student_info['email'] . '</a>';
					} else {
						echo get_lang('NoEmail');
					} ?>
					</td>
				</tr>
				<tr>
					<td> <?php echo get_lang('Tel') . ' : ';					
					if (!empty ($student_info['phone'])) {
						echo $student_info['phone'];
					} else {
						echo get_lang('NoTel');
					}
                    ?>
					</td>
				</tr>
				<tr>
					<td> <?php echo get_lang('OfficialCode') . ' : ';					
					if (!empty ($student_info['official_code'])) {
						echo $student_info['official_code'];
					} else {
						echo get_lang('NoOfficialCode');
					}
			?>
					</td>
				</tr>
				<tr>
					<td><?php echo get_lang('OnLine') . ' : '.$online; ?> </td>
				</tr>
			</table>
			</td>
		</tr>
	</table>
	
	<?php
	$table_title = '';

		if (!empty($session_id)) {
			$session_name = api_get_session_name($session_id);
			$table_title  = ($session_name? Display::return_icon('session.png', get_lang('Session'), array(), ICON_SIZE_SMALL).' '.$session_name.' ':'');
		}
		if (!empty($course_info['title'])) {
			$table_title .= ($course_info ? Display::return_icon('course.png', get_lang('Course'), array(), ICON_SIZE_SMALL).' '.$course_info['title'].'  ':'');
		}

	echo Display::page_subheader($table_title);	
	
	echo '<table width="100%" class="data_table">
			<tr>
				<th>'.get_lang('CaseStudy').'</th>
				<th>'.get_lang('SubmissionDate').'</th>
				<th>'.get_lang('State').'</th>
				<th>'.get_lang('Actions').'</th>
			</tr>
			'.$case->get_data_of_user_cases($_GET['student_id'], 'teacher').'
			</table>';
		
}
}else {
	echo Display::display_header($tool_name);
	echo Display::return_message(get_lang('NotAllowed'), 'error', false);
}
Display :: display_footer();