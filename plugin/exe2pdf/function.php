<?php
class ExportTool {

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

	$user_table	= 	Database :: get_main_table(TABLE_MAIN_USER);
	$rel_user	= 	Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$admin_table = 	Database :: get_main_table(TABLE_MAIN_ADMIN);
	$courses	= 	Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_info = 	api_get_course_info($cidReq);
	$course_code =	$course_info['code'];

	$sql = "SELECT
                 u.user_id				AS col0,
				 ".(api_is_western_name_order()
                 ? "u.firstname 		AS col1,
                 u.lastname 			AS col2,"
                 : "u.lastname 			AS col1,
                 u.firstname 			AS col2,")."
                 u.username				AS col3
				FROM $user_table AS u 
				INNER JOIN $rel_user AS r ON u.user_id=r.user_id 
				 ";


	if (isset ($_GET['keyword'])) {
		$keyword = Database::escape_string(trim($_GET['keyword']));
		$sql .= " WHERE r.course_code='".$course_code."' AND (u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%' OR concat(u.firstname,' ',u.lastname) LIKE '%".$keyword."%' OR concat(u.lastname,' ',u.firstname) LIKE '%".$keyword."%' OR u.username LIKE '%".$keyword."%'  OR u.user_id LIKE '%".$keyword."%')";
	} elseif (isset ($_GET['keyword_firstname'])) {
		$keyword_firstname = Database::escape_string($_GET['keyword_firstname']);
		$keyword_lastname = Database::escape_string($_GET['keyword_lastname']);
		$keyword_username = Database::escape_string($_GET['keyword_username']);
		$keyword_userId = Database::escape_string($_GET['keyword_userId']);

		$sql .= " WHERE r.course_code='".$course_code."' AND(u.firstname LIKE '%".$keyword_firstname."%' " .
				"AND u.lastname LIKE '%".$keyword_lastname."%' " .
				"AND u.username LIKE '%".$keyword_username."%'  " .
				"AND u.user_id = ".$keyword_status;
		$sql .= " ) ";

	}else {
		$sql .= " WHERE r.course_code='".$course_code."'";
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
		
		$extra = '<a href="user_exe_pdf.php?student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
        $users[] = array($user[0], $user[1], $user[2], $user[3], $extra);
	}
	return $users;
}

	function restriction(){
		$restriction = api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1;
		return $restriction;
	}

	function display_header(){
	
		$user = api_get_user_info($_GET['student']);
		$tool_name = $user['complete_name'].(empty($user['official_code'])?'':' ('.$user['official_code'].')');
		$header = Display::display_header($tool_name);
	
		$interbreadcrumb[] = array ("url" => 'start.php', "name" => get_lang('Exe2pdf'));
		$page_header = Display::page_header($tool_name);
		
		$return = array($header, $page_header);
		return $return;
	}
	
	function get_user_image(){
		$user = api_get_user_info($_GET['student']);
		
		$sysdir_array = UserManager::get_user_picture_path_by_id($user['user_id'],'system',false,true);
		$sysdir = $sysdir_array['dir'];
		$webdir_array = UserManager::get_user_picture_path_by_id($user['user_id'],'web',false,true);
		$webdir = $webdir_array['dir'];
		$fullurl=$webdir.$webdir_array['file'];
		$system_image_path=$sysdir.$webdir_array['file'];
		list($width, $height, $type, $attr) = @getimagesize($system_image_path);
		$resizing = (($height > 200) ? 'height="200"' : '');
		$height += 30;
		$width += 30;
		$window_name = 'window'.uniqid('');
		$onclick = $window_name."=window.open('".$fullurl."','".$window_name."','alwaysRaised=yes, alwaysLowered=no,alwaysOnTop=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=".$width.",height=".$height.",left=200,top=20'); return false;";
		$image = '<a href="javascript: void(0);" onclick="'.$onclick.'" ><img src="'.$fullurl.'" '.$resizing.' alt="'.$alt.'"/></a><br />';
		$status = '<p>'. ($user['status'] == 1 ? get_lang('Teacher') : get_lang('Student')).'</p>';
		
		$return = $image.$status;
		return $return;
	}

	function get_exe_number($user_id){
		$tbl_exe = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$course_info = 	api_get_course_info($cidReq);
		$c_id =	$course_info['real_id'];
		$course = $course_info['id'];
		$user_id = $_GET['student'];
		
		$sql = "SELECT COUNT(exe_id) AS num FROM $tbl_exe 
		WHERE exe_cours_id=".$course." AND exe_user_id=".$user_id." AND status<>'incomplete'";
		$res = Database::query($sql);
		while($exe_num = Database::fetch_array($res)){
			$num = $exe_num['num'];
		}

		return $num;
	}
	
	function get_exe_data($user_id){
		$tbl_exe = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$tbl_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT_RECORDING);
		$tbl_courses = Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_info = 	api_get_course_info($cidReq);
		$c_id =	$course_info['real_id'];
		$course = $course_info['id'];
		$user_id = $_GET['student'];
		$exercise = array();
		$validated ='';
		
		$sql = "SELECT e.exe_id AS id, q.title AS title, e.exe_date AS date
				FROM $tbl_exe AS e
				INNER JOIN $tbl_courses AS c ON c.code = e.exe_cours_id
				INNER JOIN $tbl_quiz AS q ON q.id = e.exe_exo_id
				WHERE e.exe_user_id=".$user_id." AND q.c_id=".$c_id." AND e.status<>'incomplete'
				ORDER BY q.title";

			$res = Database::query($sql);
			while ($exe = Database::fetch_row($res)){
				$extra = '<a href="'.api_get_self().'?'.api_get_cidreq().'&export_to_pdf='.$exe[0].'">'.Display::return_icon('pdf.png',get_lang('ExportToPDF'),'', ICON_SIZE_MEDIUM).'</a>';
				$exercise[] = array($exe[0], $exe[1], $exe[2], $extra);
			}
			return ($exercise);
	}
	
	function get_quiz_number(){
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$course_info = 	api_get_course_info($cidReq);
		$c_id =	$course_info['real_id'];
		
		$sql = "SELECT COUNT(id) AS num FROM $tbl_quiz 
		WHERE c_id=".$c_id;
		$res = Database::query($sql);
		while($quiz_num = Database::fetch_array($res)){
			$num = $quiz_num['num'];
		}
		return $num;
	}
	
	function get_quiz_data(){
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$course_info = 	api_get_course_info($cidReq);
		$c_id =	$course_info['real_id'];
		$quizes = array();
		
		$sql = "SELECT id, title
				FROM $tbl_quiz
				WHERE c_id=".$c_id;

			$res = Database::query($sql);
			while ($quiz = Database::fetch_row($res)){
				$extra = '<a href="'.api_get_self().'?'.api_get_cidreq().'&export_to_pdf='.$quiz[0].'">'.Display::return_icon('pdf.png',get_lang('ExportToPDF'),'', ICON_SIZE_MEDIUM).'</a>';
				$quizes[] = array($quiz[0], $quiz[1], $extra);
			}
			return ($quizes);
	}
	
	function exe_attempts($user_id){
		$tbl_exe = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$course_info = 	api_get_course_info($cidReq);
		$c_id =	$course_info['real_id'];
		$user_id = $_GET['student'];
		
		$sql1 = "SELECT title,id FROM $tbl_quiz WHERE c_id=".$c_id;
		$res1 = Database::query($sql1);
		while ($quiz = Database::fetch_array($res1)){
			$title = $quiz['title'];
			$id = $quiz['id'];
			$sql = "SELECT COUNT(e.exe_id) AS attempts FROM $tbl_exe AS e
					INNER JOIN $tbl_quiz AS q ON q.c_id = e.exe_cours_id AND q.id = e.exe_exo_id
					WHERE e.exe_user_id=".$user_id." AND q.c_id=".$c_id." AND e.exe_exo_id=".$id." AND e.status<>'incomplete'";
			$res = Database::query($sql);
			while ($attempt = Database::fetch_array($res)){
				$att = $attempt['attempts'];
				$attempts = $title.' : '.$att.' attempts<br/>';
			}
		}
		return $attempts.'<br/>';
	}
	
	function save_html($string){
		$filePath = 'exe.txt';

		$temp = fopen($filePath, "w");
		fwrite($temp, $string);
		fclose($temp);
	}

	
	function export_pdf($id){
		require_once 'exe.php';
		$cacheFile = 'faq.html';
		$content = exe_html($id, false);
		//$string = exe_replace_string($content);
		file_put_contents($cacheFile,$content);
		$title = 'Test';
		$course_code = api_get_course_id();
		$pdf = new PDF();
		//$css_file = api_get_path(TO_SYS, WEB_CSS_PATH).api_get_setting('stylesheets').'/base.css';
        //$css = file_exists($css_file) ? @file_get_contents($css_file) : '';
		$pdf->set_custom_header($title);        
		$pdf->html_to_pdf($cacheFile, $css, get_lang('Test'), null);
		}
		
		function export_quiz($quiz_id){
		$cacheFile = 'myHtml.html';
		require_once 'exe.php';
		require_once api_get_path(SYS_CODE_PATH).'/exercice/exercise.class.php';
		require_once api_get_path(SYS_CODE_PATH).'/exercice/question.class.php';
		//$exercise = new Exercise(api_get);
		$quiz_header = quiz_header($quiz_id);
		$title = $quiz_header['title'];
		$desc = $quiz_header['desc'];
		$header = '<h2>'.$title.'<hr></h2>';
		
		//$title = $exercise['title'];
		ob_start();
		$html = utf8_encode(quiz_html($quiz_id));
		$content = ob_get_contents();
		ob_end_clean();
		$my_css = api_get_path('WEB_CSS_PATH').'base.css';
		$html = '<html><head><link rel="stylesheet" type="text/css" href="'.$my_css .'"></head><body>';
		$html .= '<br/><h3>Description : '.$desc.'</h3><hr>';
		$html .= $content;
		$html .= '</body></html>';
		$string = quiz_replace_string($html);
		file_put_contents($cacheFile,$string);
		
			$course_code = api_get_course_id();
			$css = file_get_contents($my_css);
			require_once api_get_path('LIBRARY_PATH').'mpdf/mpdf.php';
			$pdf = new PDF();
			$css_file = api_get_path(TO_SYS, WEB_CSS_PATH).api_get_setting('stylesheets').'/base.css';
			$css = file_exists($css_file) ? @file_get_contents($css_file) : '';
			$pdf->set_custom_header($header);                
			$pdf->content_to_pdf($string, $css, get_lang('Test'), null);
			
		/* $mpdf = new mPDF('utf-8', 'A4');
		$stylesheet = file_get_contents($my_css);
		$mpdf->WriteHTML($stylesheet, 1);
		$mpdf->WriteHTML($html, 2);
		$mpdf->Output(); */
		
		}
		
		
		function quiz_form($quiz_id){
			require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
			$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
			$course_info = 	api_get_course_info($cidReq);
			$c_id =	$course_info['real_id'];
			
			$sql = "SELECT title FROM $tbl_quiz WHERE c_id=".$c_id." AND id=".$quiz_id;
			$res = Database::query($sql);
			while ($quiz = Database::fetch_array($res)){
				$title =  $quiz['title'];
			}
			$form = new FormValidator('profile', 'post', api_get_self().'?'.api_get_cidreq().'&export_to_pdf='.$quiz_id, null);
			$form->addElement('header', '', get_lang('InsertNumberOfCopies').' : '.$title);
			//Copies
			$form->addElement('text', 'copies', get_lang('ExerciseCopies'), array('size' => 40));
			
			$form->addElement('style_submit_button', 'submit', get_lang('Export'), 'class="save"');
			$form->display();
		}
	
}
?>