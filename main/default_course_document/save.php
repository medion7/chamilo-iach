<?php

require_once '../inc/global.inc.php';

$tbl_notes = Database::get_course_table(TABLE_NOTEBOOK);

$sql_sel = "SELECT * FROM $tbl_notes 
			WHERE c_id='".$_POST['course_id']."' AND user_id='".$_POST['user_id']."' AND title='".$_POST['title']."' AND status=12345";

$res_sel = Database::query($sql_sel);
			
while ($note = mysql_fetch_array($res_sel)) { 

	$note_id = $note['notebook_id'];
	
	}
    if ($res_sel && Database::num_rows($res_sel)>0) {
            $sql_up = "UPDATE $tbl_notes SET
						description = '".Database::escape_string($_POST['content'])."', update_date = NOW() WHERE notebook_id=".$note_id." AND c_id=".$_POST['course_id'];
			$res_up = Database::query($sql_up);
            }
	else{
			$sql_in = "INSERT INTO $tbl_notes (c_id, user_id, course, title, description, creation_date, update_date, status) VALUES
					('".$_POST['course_id']."', '".$_POST['user_id']."', '".$_POST['course_code']."', '".$_POST['title']."', '".Database::escape_string($_POST['content'])."', NOW(), NOW(), '12345')";
			$res_in = Database::query($sql_in);
			}

echo "Your notes were saved";

?>