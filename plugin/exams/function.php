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
	
	$registered = self::is_registered($user[0]);
	
	if(isset($registered[1]) && $registered[0] == 'validating...'){
			$order = '<a href="https://drupal.vithoulkas.edu.gr/?q=admin/store/orders/'.$registered[1].'" target="_blank">'.$registered[1].'</a><a href="start.php?action=edit_oid&student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'icons/22/edit.png" border="0" /></a>';
			$status = $registered[0];
			$extra = '<a href="start.php?action=subscribe_user&student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
	}elseif(!isset($registered[1])){
			$order = $registered[1];
			$status = $registered[0];
			$extra = '<a href="start.php?action=import_oid&student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';	
	}elseif(isset($registered[1]) && $registered[0] == 'validated'){
			$order = '<a href="https://drupal.vithoulkas.edu.gr/?q=admin/store/orders/'.$registered[1].'" target="_blank">'.$registered[1].'</a><a href="start.php?action=edit_oid&student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'icons/22/edit.png" border="0" /></a>';
			$status = $registered[0];
			$extra = '';	
	}
        $users[] = array($user[1], $user[2], $user[3], $order, $status, $extra);
	}
	return $users;
}

	function is_registered($user_id){
	
		$exams = 'iach_main.exams';
	
		$sql = "SELECT `status`, `order_id` FROM $exams 
				WHERE user_id=".$user_id;
			
		$res = Database::query($sql);
		$exams = array();
	
		while ($exam = Database::fetch_array($res)) {
			$status = $exam['status'];
			$order_id = $exam['order_id'];
		}
	
		return array($status, $order_id);
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

	function subscribe($user_id){
		
		$user_table	= 	Database :: get_main_table(TABLE_MAIN_USER);
		$current_date = date('Y-m-d H:i:s', time());
		$exams = 'iach_main.exams';
		$cert = 'exams2.certification';
		$user2course = 'exams2.course_rel_user';
		
		//create user's new password (todo : add password field to exams table)
		$new_pass = self::new_password();
		$password = md5($new_pass);
		
		$sql = "SELECT * FROM $user_table WHERE user_id=".$user_id;
		$res = Database::query($sql);
		
		while ($user = Database::fetch_array($res)) {
			$lastName = $user['lastname'];
			$firstName = $user['firstname'];
			$loginName = $user['username'];
			$status = $user['status'];
			$email = $user['email'];
			$official_code = $user['official_code'];
			$language = $user['language'];
		}
		
		$sql_new = "INSERT INTO exams2.user (`lastname`, `firstname`, `username`, `status`, `password`, `email`,  `official_code`, `creator_id`, `language`, `registration_date`) VALUES 
                ('".Database::escape_string(trim($lastName))."',
                '".Database::escape_string(trim($firstName))."',
                '".Database::escape_string(trim($loginName))."',
                '".Database::escape_string($status)."',
                '".Database::escape_string($password)."',
                '".Database::escape_string($email)."',
                '".Database::escape_string($official_code)."',
                '2', '".Database::escape_string($language)."',
                '".$current_date."')";
		$result = Database::query($sql_new);
		
		$last_user_id = Database::insert_id();
		
		$sql_copy = "SELECT `order_id` FROM $exams WHERE user_id = ".$user_id;
		$res_copy = Database::query($sql_copy);
		while($data = Database::fetch_array($res_copy)){
			$order_id = $data['order_id'];
			}
		
		$sql_cert = "INSERT INTO $cert (user_id, order_id, status) VALUES (".$last_user_id.", ".$order_id.", 'validated')";
		$res_cert = Database::query($sql_cert);
		
		$sql_user2course = "INSERT INTO $user2course (user_id, course_code, status) VALUES (".$last_user_id.", '999', '5')";
		$res_user2course = Database::query($sql_user2course);
		
	//@api_mail_html($recipient_name, $email, $emailsubject, $emailbody, $sender_name, $email_admin);
	
	
	//Update exams status
	$sql_exams = "UPDATE $exams SET status = 'validated' WHERE user_id = ".$user_id;
	$res_exams = Database::query($sql_exams);
	
		echo '<div class="actions">';
		echo Display::url(Display::return_icon('back.png', get_lang('Back'), array(), 32), api_get_path(WEB_ROOT).'start.php?'.api_get_cidreq());
		echo '</div>';
		
		Display::display_confirmation_message('User was subscribed to Exams Platform. Password: '.$new_pass);

	}
	

	function new_password( $length = 8){

	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    	$password = substr( str_shuffle( $chars ), 0, $length );
    	return $password;
	
	}
	
	function save_oid(){

		$exams = 'iach_main.exams';
		$user_id = $_POST['user_id'];
		$order_id = $_POST['order_id'];
					
		
		
		if($_POST['action'] == 'NewOID'){
			$sql = "INSERT INTO $exams (`user_id`, `order_id`, `status`) VALUES (".$user_id.", ".$order_id.", 'validating...')";
		}else{
			$sql = "UPDATE $exams SET `order_id` = ".$order_id;
			$sql .= " WHERE user_id = ".$user_id;
		}
		
		
		$res = Database::query($sql);
		
		echo '<div class="actions">';
		echo Display::url(Display::return_icon('back.png', get_lang('Back'), array(), 32), api_get_path(WEB_ROOT).'start.php?'.api_get_cidreq());
		echo '</div>';
		
		Display::display_confirmation_message('Order ID Saved');
	}
	
}
?>