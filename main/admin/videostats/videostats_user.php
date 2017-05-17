<?php
/* For licensing terms, see /license.txt */
/**
 * Implements the tracking of students in the Reporting pages
 * @package chamilo.reporting
 */
/**
 * Code
 */
// name of the language file that needs to be included
$language_file = array('registration', 'index', 'tracking', 'exercice', 'admin', 'gradebook', 'survey', 'learnpath');

require_once '../../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';
require_once api_get_path(SYS_CODE_PATH).'mySpace/myspace.lib.php';
require_once api_get_path(SYS_CODE_PATH).'gradebook/lib/be/gradebookitem.class.php';
require_once api_get_path(SYS_CODE_PATH).'gradebook/lib/be/evaluation.class.php';
require_once api_get_path(SYS_CODE_PATH).'gradebook/lib/be/result.class.php';
require_once api_get_path(SYS_CODE_PATH).'gradebook/lib/be/linkfactory.class.php';
require_once api_get_path(SYS_CODE_PATH).'gradebook/lib/be/category.class.php';
require_once api_get_path(LIBRARY_PATH).'attendance.lib.php';
require_once api_get_path(SYS_CODE_PATH).'survey/survey.lib.php';
require_once 'videostats.lib.php';

api_block_anonymous_users();

$videostats = new Videostats;

if (!api_is_allowed_to_create_course() && !api_is_session_admin() && !api_is_drh()) {
    // Check if the user is tutor of the course
    $user_course_status = CourseManager::get_tutor_in_course_status(api_get_user_id(), api_get_course_id());
    if ($user_course_status != 1) {    
        api_not_allowed(true);
    }
}

$htmlHeadXtra[] = '<script>
function show_image(image,width,height) {
	width = parseInt(width) + 20;
	height = parseInt(height) + 20;
	window_x = window.open(image,\'windowX\',\'width=\'+ width + \', height=\'+ height + \'\');
}
</script>';


$from_myspace = false;
if (isset ($_GET['from']) && $_GET['from'] == 'myspace') {
	$from_myspace = true;
	$this_section = SECTION_TRACKING;
} else {
	$this_section = SECTION_COURSES;
}

$nameTools = '<a href="'.api_get_path(REL_CODE_PATH).'admin/">'.get_lang('Administartion').'</a> / <a href="'.api_get_path(REL_CODE_PATH).'admin/user_list.php">'.get_lang('UserList').'</a> / '.get_lang('VideoStats');

$get_course_code = Security :: remove_XSS($_GET['course']);

// Database Table Definitions
$tbl_course_user 			= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_stats_exercices 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

if (isset($_GET['user_id']) && $_GET['user_id'] != "") {
	$user_id = intval($_GET['user_id']);
} else {
	$user_id = api_get_user_id();
}

$session_id = intval($_GET['id_session']);
if (empty($session_id)) {
    $session_id = api_get_session_id();
}

$student_id = intval($_GET['student']);

// Action behaviour
$check= Security::check_token('get');

if ($check) {
	switch ($_GET['action']) {
		case 'reset_lp' :
			$course		= isset($_GET['course']) ? $_GET['course']:"";
			$lp_id		= isset($_GET['lp_id'])	 ? intval($_GET['lp_id']):"";
						
			if (api_is_allowed_to_edit() && !empty($course) && !empty($lp_id) && !empty($student_id)) {					   
				$course_info 	= api_get_course_info($course);                    
                delete_student_lp_events($student_id, $lp_id, $course_info, $session_id);
			
				//@todo delete the stats.track_e_exercices records. First implement this http://support.chamilo.org/issues/1334					
				$message = Display::return_message(get_lang('LPWasReset'),'success');
			}				
            break;			
		default:
			break;		
	}
	Security::clear_token();	
}		

// infos about user
$user_info = api_get_user_info($student_id);

$courses_in_session = array();

//See #4676
$drh_can_access_all_courses = false;

if (api_is_drh() || api_is_platform_admin()) {
    $drh_can_access_all_courses = true;
}

$courses = CourseManager::get_course_list_of_user_as_course_admin(api_get_user_id());

$courses_in_session_by_coach = array();
$sessions_coached_by_user = Tracking::get_sessions_coached_by_user(api_get_user_id());

//RRHH or session admin
if (api_is_session_admin() || api_is_drh()) {	
    $courses = CourseManager::get_courses_followed_by_drh(api_get_user_id());
    
	$session_by_session_admin = SessionManager::get_sessions_followed_by_drh(api_get_user_id());
	if (!empty($session_by_session_admin)) {
		foreach ($session_by_session_admin as $session_coached_by_user) {		
			$courses_followed_by_coach = Tracking :: get_courses_list_from_session($session_coached_by_user['id']);	
			$courses_in_session_by_coach[$session_coached_by_user['id']] = $courses_followed_by_coach;
		}
	}
}

// Teacher or admin
if (!empty($sessions_coached_by_user)) {
	foreach ($sessions_coached_by_user as $session_coached_by_user) {
		$sid = intval($session_coached_by_user['id']);
		$courses_followed_by_coach = Tracking :: get_courses_followed_by_coach(api_get_user_id(), $sid);
		$courses_in_session_by_coach[$sid] = $courses_followed_by_coach;
	}
}

$sql = "SELECT course_code FROM $tbl_course_user WHERE relation_type <> ".COURSE_RELATION_TYPE_RRHH." AND user_id = ".intval($user_info['user_id']);
$rs = Database::query($sql);

while ($row = Database :: fetch_array($rs)) {    
    if ($drh_can_access_all_courses) {
        $courses_in_session[0][] = $row['course_code'];
    } else {
        if (isset($courses[$row['course_code']])) {
            $courses_in_session[0][] = $row['course_code'];
        }    
    }
}

// Get the list of sessions where the user is subscribed as student
$sql = 'SELECT id_session, course_code FROM ' . Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER) . ' WHERE id_user=' . intval($user_info['user_id']);
$rs = Database::query($sql);
$tmp_sessions = array();
while ($row = Database :: fetch_array($rs)) {
	$tmp_sessions[] = $row['id_session'];
    if ($drh_can_access_all_courses) {
        if (in_array($row['id_session'], $tmp_sessions)) {
            $courses_in_session[$row['id_session']][] = $row['course_code'];
        }
    } else {
        if (isset($courses_in_session_by_coach[$row['id_session']])) {
            if (in_array($row['id_session'], $tmp_sessions)) {
                $courses_in_session[$row['id_session']][] = $row['course_code'];
            }
        }
    }
}

/*if (empty($courses_in_session)) {
    Display :: display_header($nameTools);
	echo '<div class="actions">';
	echo '<a href="javascript: window.back();" ">'.Display::return_icon('back.png', get_lang('Back'),'',ICON_SIZE_MEDIUM).'</a>';
	echo '</div>';
	Display::display_warning_message(get_lang('NoDataAvailable'));
	Display::display_footer();
	exit;
}*/

if (!empty($student_id)) {
	if (api_is_drh() && !UserManager::is_user_followed_by_drh($student_id, api_get_user_id())) {        
		api_not_allowed();
	}
}

Display :: display_header($nameTools);

if (isset($message)) {
    echo $message;
}

if (!empty($student_id)) {	

	// is the user online ?
    if (user_is_online($_GET['student'])) {
        $online = get_lang('Yes');
    } else {
        $online = get_lang('No');
    }
		
	// get average of score and average of progress by student
	$avg_student_progress = $avg_student_score = 0;
	$course_code = Security :: remove_XSS($_GET['course']);	
	
	if (!CourseManager :: is_user_subscribed_in_course($user_info['user_id'], $course_code, true)) {
		unset($courses[$key]);
	} else {		
		$avg_student_progress = Tracking::get_avg_student_progress($user_info['user_id'], $course_code, array(), $session_id);
		//the score inside the Reporting table
		$avg_student_score 	  = Tracking::get_avg_student_score($user_info['user_id'], $course_code, array(), $session_id);
		//var_dump($avg_student_score);	
	}	
    
	$avg_student_progress = round($avg_student_progress, 2);
	
	// time spent on the course
    
	$time_spent_on_the_course = api_time_to_hms(Tracking :: get_time_spent_on_the_course($user_info['user_id'], $course_code, $session_id));
    	
	// get information about connections on the platform by student
	$first_connection_date = Tracking :: get_first_connection_date($user_info['user_id']);
	if ($first_connection_date == '') {
		$first_connection_date = get_lang('NoConnexion');
	}

	$last_connection_date = Tracking :: get_last_connection_date($user_info['user_id'], true);
	if ($last_connection_date == '') {
		$last_connection_date = get_lang('NoConnexion');
	}

    
    //Show title
    $info_course = CourseManager :: get_course_information($course_code);
    $coachs_name  = '';
    $session_name = '';     
    $nb_login = Tracking :: count_login_per_student($user_info['user_id'], $_GET['course']);    
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
            $coach_info = UserManager::get_user_info_by_id($session_coach_id);
            $info_course['tutor_name'] = api_get_person_name($coach_info['firstname'], $coach_info['lastname']);
        }
        $coachs_name  = $info_course['tutor_name'];
        $session_name = $session_info['name'];
    } // end
 
    $info_course  = CourseManager :: get_course_information($get_course_code);    
    $table_title = Display::return_icon('user.png', get_lang('User'), array(), ICON_SIZE_SMALL).$user_info['complete_name'];
    
    echo Display::page_subheader($table_title);
    
    echo '<table width="100%" border="0">';
    echo '<tr>';
    
	$image_array = UserManager :: get_user_picture_path_by_id($user_info['user_id'], 'web', false, true);
	echo '<td class="borderRight" width="10%" valign="top">';

	// get the path,width and height from original picture
	$image_file = $image_array['dir'] . $image_array['file'];
	$big_image = $image_array['dir'] . 'big_' . $image_array['file'];
	$big_image_size = api_getimagesize($big_image);
	$big_image_width = $big_image_size['width'];
	$big_image_height = $big_image_size['height'];
	$url_big_image = $big_image . '?rnd=' . time();
	$img_attributes = 'src="' . $image_file . '?rand=' . time() . '" ' .
	'alt="' . $user_info['complete_name']. '" ' .
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
					<td><?php echo get_lang('Name') . ' : '.$user_info['complete_name']; ?></td>
				</tr>
				<tr>
					<td><?php echo get_lang('Email') . ' : ';					
					if (!empty ($user_info['email'])) {
						echo '<a href="mailto:' . $user_info['email'] . '">' . $user_info['email'] . '</a>';
					} else {
						echo get_lang('NoEmail');
					} ?>
					</td>
				</tr>
				<tr>
					<td> <?php echo get_lang('Tel') . ' : ';					
					if (!empty ($user_info['phone'])) {
						echo $user_info['phone'];
					} else {
						echo get_lang('NoTel');
					}
                    ?>
					</td>
				</tr>
				<tr>
					<td> <?php echo get_lang('OfficialCode') . ' : ';					
					if (!empty ($user_info['official_code'])) {
						echo $user_info['official_code'];
					} else {
						echo get_lang('NoOfficialCode');
					}
			?>
					</td>
				</tr>
				<tr>
					<td><?php echo get_lang('OnLine') . ' : '.$online; ?> </td>
				</tr>
			<?php
			
			// Display timezone if the user selected one and if the admin allows the use of user's timezone
			$timezone = null;
			$timezone_user = UserManager::get_extra_user_data_by_field($user_info['user_id'],'timezone');
			$use_users_timezone = api_get_setting('use_users_timezone', 'timezones');
			if ($timezone_user['timezone'] != null && $use_users_timezone == 'true') {
				$timezone = $timezone_user['timezone'];
			}
			if ($timezone !== null) {
			?>
				<tr>
					<td> <?php echo get_lang('Timezone') . ' : '.$timezone; ?> </td>
				</tr>
			<?php
			}
			?>
			</table>
			</td>
			
			<td class="borderLeft" width="35%" valign="top">
				<table width="100%" class="data_table">
					<tr>
						<th colspan="2"><?php echo get_lang('Tracking'); ?></th>
					</tr>
					<tr><td align="right"><?php echo get_lang('TotalTime') ?></td>
						<td align="left"><?php echo $videostats->get_user_video_view_time(null, $student_id, 'total') ?></td>
					</tr>
					<tr>
						<td align="right"><?php echo get_lang('LastMonth') ?></td>
						<td align="left"><?php echo $videostats->get_user_video_view_time(null, $student_id, 'month') ?></td>
					</tr>
					<tr>
						<td align="right"><?php echo get_lang('LastWeek') ?></td>
						<td align="left"><?php echo $videostats->get_user_video_view_time(null, $student_id, 'week') ?></td>
					</tr>
					
					<?php if (isset($_GET['details']) && $_GET['details'] == 'true') {?>
					<tr>
						<td align="right"><?php echo get_lang('TimeSpentInTheCourse') ?></td>
						<td align="left"><?php echo  $time_spent_on_the_course ?></td>
					</tr>
					<tr>
						<td align="right"><?php echo get_lang('Progress').' '; Display :: display_icon('info3.gif', get_lang('ScormAndLPProgressTotalAverage'), array ('align' => 'absmiddle', 'hspace' => '3px'));?></td>
						<td align="left"><?php echo $avg_student_progress.'%' ?></td>
					</tr>
					<tr>
						<td align="right"><?php echo get_lang('Score').' '; Display :: display_icon('info3.gif', get_lang('ScormAndLPTestTotalAverage'), array ('align' => 'absmiddle', 'hspace' => '3px')); ?>
						</td>
						<td align="left"><?php if (is_numeric($avg_student_score)) { echo $avg_student_score.'%';} else { echo $avg_student_score ;}  ?></td>
					</tr>
                    <?php
                        if (!empty($nb_login)) {
                        	echo '<tr><td align="right">'.get_lang('CountToolAccess').'</td>';
                            echo '<td align="left">'.$nb_login.'</td>';
                            echo '</tr>';
                        }
					} ?>
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
if (!empty($info_course['title'])) {
	$table_title .= ($info_course ? Display::return_icon('course.png', get_lang('Course'), array(), ICON_SIZE_SMALL).' '.$info_course['title'].'  ':'');
}


echo Display::page_subheader($table_title);

	$attendance = new Attendance();

	foreach ($courses_in_session as $key => $courses) {	
		$session_id   = $key;
		$session_info = api_get_session_info($session_id);
		$session_name = $session_info['name'];
		$date_start = '';
		
		if (!empty($session_info['date_start']) && $session_info['date_start'] != '0000-00-00') {			
			$date_start = api_format_date($session_info['date_start'], DATE_FORMAT_SHORT);
		}
		
		$date_end = '';
		if (!empty($session_info['date_end']) && $session_info['date_end'] != '0000-00-00') {			
			$date_end = api_format_date($session_info['date_end'], DATE_FORMAT_SHORT);
		}
		if (!empty($date_start) && !empty($date_end)) {
			$date_session = get_lang('From') . ' ' . $date_start . ' ' . get_lang('Until') . ' ' . $date_end;
		}
		$title = '';
		if (!empty($session_id)) {
			$title = Display::return_icon('session.png', get_lang('Session'), array(), ICON_SIZE_SMALL).' '.$session_name.($date_session?' ('.$date_session.')':'');
		}
			
		// Courses
		/*echo '<table class="data_table">';
		echo '<tr>
				<th>'.get_lang('Course').'</th>
				<th>'.get_lang('Time').'</th>
				<th>'.get_lang('Progress').'</th>
				<th>'.get_lang('Score').'</th>
				<th>'.get_lang('AttendancesFaults').'</th>
				<th>'.get_lang('Evaluations').'</th>
				<th>'.get_lang('Details').'</th>					
			</tr>';
		*/
		
		
		if (!empty($courses)) {
            foreach ($courses as $course_code) {
				
                 
                if (CourseManager :: is_user_subscribed_in_course($student_id, $course_code, true)) {
                    $course_info = CourseManager :: get_course_information($course_code);
    												
    				$time_spent_on_course = api_time_to_hms(Tracking :: get_time_spent_on_the_course($user_info['user_id'], $course_code, $session_id));
    
    				// get average of faults in attendances by student
    				$results_faults_avg = $attendance->get_faults_average_by_course($student_id, $course_code, $session_id);
    				if (!empty($results_faults_avg['total'])) {		
    					if (api_is_drh()) {
    						$attendances_faults_avg = '<a title="'.get_lang('GoAttendance').'" href="'.api_get_path(WEB_CODE_PATH).'attendance/index.php?cidReq='.$course_code.'&id_session='.$session_id.'&student_id='.$student_id.'">'.$results_faults_avg['faults'].'/'.$results_faults_avg['total'].' ('.$results_faults_avg['porcent'].'%)</a>';	
    					} else {
    						$attendances_faults_avg = $results_faults_avg['faults'].'/'.$results_faults_avg['total'].' ('.$results_faults_avg['porcent'].'%)';
    					}
    				} else {
    					$attendances_faults_avg = '0/0 (0%)';
    				}
    		
    				// get evaluatios by student				
    				
    				$cats = Category::load(null, null, $course_code, null, null, $session_id);
                    
    				$scoretotal = array();
    				if (isset($cats) && isset($cats[0])) {
    					if (!empty($session_id)) {					    
                            $scoretotal= $cats[0]->calc_score($student_id, $course_code, $session_id);	
    					} else {
                            $scoretotal= $cats[0]->calc_score($student_id, $course_code);
    					}
    				}
    
    				$scoretotal_display = '0/0 (0%)';
    				if (!empty($scoretotal)) {
    					$scoretotal_display =  round($scoretotal[0],1).'/'.round($scoretotal[1],1).' ('.round(($scoretotal[0] / $scoretotal[1]) * 100,2) . ' %)';
    				}
    	 
    				$progress = Tracking::get_avg_student_progress($user_info['user_id'], $course_code, null, $session_id);
    				$score = Tracking :: get_avg_student_score($user_info['user_id'], $course_code, null, $session_id);
    				$progress = empty($progress) ? '0%' : $progress.'%';
    				$score = empty($score) ? '0%' : $score.'%';
    			
    				$csv_content[] = array (
        				$session_name,
        				$course_info['title'],
        				$time_spent_on_course,
        				$progress,
        				$score,
        				$attendances_faults_avg,
        				$scoretotal_display
    				);
					echo '<h3>'.Display::return_icon('course.png', get_lang('Courses'), array(), ICON_SIZE_SMALL).' '.$course_info['title'].'</h3>';
					echo '<table class="data_table">';
					echo '<tr>
							<th>'.get_lang('Video').'</th>
							<th>'.get_lang('VideoTime').'</th>
							<th>'.get_lang('ViewedTime').'</th>
							<th>'.get_lang('Views').'</th>
							<th>'.get_lang('Details').'</th>					
						</tr>';
    
    				/*echo '<tr>
    				<td >'.$course_info['title'].'</td>
    				<td >'.$time_spent_on_course .'</td>
    				<td >'.$progress.'</td>
    				<td >'.$score.'</td>
    				<td >'.$attendances_faults_avg.'</td>
                    <td >'.$scoretotal_display.'</td>';
    			
    				if (isset ($_GET['id_coach']) && intval($_GET['id_coach']) != 0) {
    					echo '<td width="10"><a href="'.api_get_self().'?student='.$user_info['user_id'].'&details=true&course='.$course_info['code'].'&id_coach='.Security::remove_XSS($_GET['id_coach']).'&origin='.Security::remove_XSS($_GET['origin']).'&id_session='.$session_id.'#infosStudent"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a></td>';
    				} else {
    					echo '<td width="10"><a href="'.api_get_self().'?student='.$user_info['user_id'].'&details=true&course='.$course_info['code'].'&origin='.Security::remove_XSS($_GET['origin']).'&id_session='.$session_id.'#infosStudent"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a></td>';
    				}
				echo '</tr>';*/
				
				echo $videostats->get_videos_per_course($course_code, $user_info['user_id']);
				}
				echo '</table>';
			}
		} else {
        	echo "<tr><td colspan='5'>".get_lang('NoCourse')."</td></tr>";
		}
	}
	
}
/*		FOOTER  */
Display :: display_footer();
