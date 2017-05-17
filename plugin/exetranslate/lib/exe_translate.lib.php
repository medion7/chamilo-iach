<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton 
 * API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * BigBlueButton-Chamilo connector class
 */
class ExeTrans {


		function get_exercises($c_id){
			
			$return = '';
			$c_quiz = Database::get_course_table(TABLE_QUIZ_TEST);
			
			$sql = "SELECT * FROM $c_quiz WHERE c_id=".$c_id;
			$result = Database::query($sql);
			
			$num_rows = mysql_num_rows($result);
			
			if($num_rows = 0){
				$message = 'There are no exercises in this course';
				$return = $message;
			}else {
				while ($exercise[] = Database::fetch_array($result)); 
				$return = $exercise;
			}
			return $return;
		}
		
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
		
		/**
 * Get the users to display on the current page (fill the sortable-table)
 * @param   int     offset of first user to recover
 * @param   int     Number of users to get
 * @param   int     Column to sort on
 * @param   string  Order (ASC,DESC)
 * @see SortableTable#get_table_data($from)
 */
function get_user_data($from, $number_of_items, $column, $direction) {

	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
	$sql = "SELECT
                 u.user_id				AS col0,
				 ".(api_is_western_name_order()
                 ? "u.firstname 		AS col1,
                 u.lastname 			AS col2,"
                 : "u.lastname 			AS col1,
                 u.firstname 			AS col2,")."
                 u.username				AS col3,
				FROM $user_table u ";


	if (isset ($_GET['keyword'])) {
		$keyword = Database::escape_string(trim($_GET['keyword']));
		$sql .= " WHERE (u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%' OR concat(u.firstname,' ',u.lastname) LIKE '%".$keyword."%' OR concat(u.lastname,' ',u.firstname) LIKE '%".$keyword."%' OR u.username LIKE '%".$keyword."%'  OR u.user_id LIKE '%".$keyword."%')";
	} elseif (isset ($_GET['keyword_firstname'])) {
		$keyword_firstname = Database::escape_string($_GET['keyword_firstname']);
		$keyword_lastname = Database::escape_string($_GET['keyword_lastname']);
		$keyword_username = Database::escape_string($_GET['keyword_username']);
		$keyword_userId = Database::escape_string($_GET['keyword_userId']);

		$sql .= $query_admin_table." WHERE (u.firstname LIKE '%".$keyword_firstname."%' " .
				"AND u.lastname LIKE '%".$keyword_lastname."%' " .
				"AND u.username LIKE '%".$keyword_username."%'  " .
				"AND u.user_id = ".$keyword_status;

		$sql .= " ) ";
	}

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
}
