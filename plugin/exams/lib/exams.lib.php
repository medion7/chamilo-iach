<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton 
 * API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * BigBlueButton-Chamilo connector class
 */
//class Exams {
		
/*function get_number_of_users (){
			
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
		
		/**
 * Get the users to display on the current page (fill the sortable-table)
 * @param   int     offset of first user to recover
 * @param   int     Number of users to get
 * @param   int     Column to sort on
 * @param   string  Order (ASC,DESC)
 * @see SortableTable#get_table_data($from)
 */
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
		
	$registered = $this->is_registered($user[0]);
	
	if(isset($registered[1]) && $registered[0] == 'validating...'){
			$order = $registered[1];
			$status = '<a href="start.php?action=edit_oid&student='.$user[0].'&'.api_get_cidreq().'">'.$registered[0].'</a>';
			$extra = '<a href="start.php?action=subscribe_user&student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
	}elseif(!isset($registered[1])){
			$order = 'Enter Order ID to continue';
			$status = $registered[0];
			$extra = '<a href="start.php?action=import_oid&student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';	
	}elseif(isset($registered[1]) && $registered[0] == 'validated'){
			$order = '<a href="start.php?action=edit_oid&student='.$user[0].'&'.api_get_cidreq().'">'.$registered[0].'</a>';
			$status = $registered[0];
			$extra = '<a href="http://exams1.vithoulkas.edu.gr/courses/'.$course_code.'/?id_session=0"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';	
	}
        $users[] = array($user[1], $user[2], $user[3], $order, $status, $extra);
	}
	return $users;
}

function is_registered($user_id){
	
	$exams = 'iach_main.exams';
	
	$sql = "SELECT status, order_id FROM $exams 
			WHERE user_id=".$user_id;
			
	$res = Database::query($sql);
	$exams = array();
	
	while ($exam = Database::fetch_row($res)) {
		
		$exams[] = array($exam[0], $exam[1]);
	}
	
	return $exams;
}*/

/*function subscribe($user_id){
		
		$user_table	= 	Database :: get_main_table(TABLE_MAIN_USER);
		$current_date = date('Y-m-d H:i:s', time());
		$exams = 'iach_main.exams';
		
		//create user's new password (todo : add password field to exams table)
		$new_pass = $this->new_password();
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
		
		$sql_new = "INSERT INTO exams2.user
                SET lastname =         '".Database::escape_string(trim($lastName))."',
                firstname =         '".Database::escape_string(trim($firstName))."',
                username =            '".Database::escape_string(trim($loginName))."',
                status =             '".Database::escape_string($status)."',
                password =             '".Database::escape_string($password)."',
                email =             '".Database::escape_string($email)."',
                official_code    =     '".Database::escape_string($official_code)."',
                creator_id      =     '2',
                language =             '".Database::escape_string($language)."',
                registration_date = '".$current_date."'";
        $result = Database::query($sql_new);
	
	//@api_mail_html($recipient_name, $email, $emailsubject, $emailbody, $sender_name, $email_admin);
	
	//Update exams status
	$sql_exams = "UPDATE $exams SET status = 'validated' AND password = '".$new_pass."' WHERE user_id = ".$user_id;
	$res_exams = Database::query($sql_exams);
	
	}
	
function new_password(){

	$key = '';
	keys = array_merge(range(0,9), range('a', 'z'));
    for($i=0; $i < 8; $i++) {

        $key .= $keys[array_rand($keys)];
    }

    return $key;
	}*/
//}