<?php
class ExportTool {


	private function count_translate($exe_id, $c_id){
		$tbl_translate = Database::get_main_table('exe_translate_progress');
		
		$sql_trans = "SELECT COUNT(question_id) AS num FROM $tbl_translate WHERE c_id=".$c_id." AND exercice_id=".$exe_id." AND question_id<>0 AND translate='yes' ";
		$res_trans = Database::query($sql_trans);
		
		while($count_trans = Database::fetch_array($res_trans)){
			$num = $count_trans['num'];
		}
		return $num;
		
	}
	
	private function question_translated($exe_id, $c_id, $question_id){
		$tbl_translate = Database::get_main_table('exe_translate_progress');
		
		$sql_trans = "SELECT translate FROM $tbl_translate WHERE c_id=".$c_id." AND exercice_id=".$exe_id." AND question_id=".$question_id;
		$res_trans = Database::query($sql_trans);

		while($trans = Database::fetch_array($res_trans)){
			$translated = $trans['translate'];
		}
		
		return $translated;
		
	}
	
	private function count_quiz_questions($exe_id, $c_id){
		$tbl_rel_quiz = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);
		
		$sql_question = "SELECT COUNT(question_id) AS num FROM $tbl_rel_quiz WHERE c_id=".$c_id." AND exercice_id=".$exe_id;

		$res_question = Database::query($sql_question);
		while($count_question = Database::fetch_array($res_question)){
			$num = $count_question['num'];
		}
		return $num;
		
	}
	
	public function get_quiz_number(){
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$course_info = 	api_get_course_info($_GET['cidReq']);
		$c_id =	$course_info['real_id'];
		
		$sql = "SELECT COUNT(id) AS num FROM $tbl_quiz 
		WHERE c_id=".$c_id;
		$res = Database::query($sql);
		while($quiz_num = Database::fetch_array($res)){
			$num = $quiz_num['num'];
		}
		return $num;
	}
	
	public function get_quiz_data(){
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$course_info = 	api_get_course_info($_GET['cidReq']);
		$c_id =	$course_info['real_id'];
		$quizes = array();
		
		$sql = "SELECT id, title
				FROM $tbl_quiz
				WHERE c_id=".$c_id;

			$res = Database::query($sql);
			while ($quiz = Database::fetch_row($res)){
			
				$translated = self::count_translate($quiz[0], $c_id);
				$questions = self::count_quiz_questions($quiz[0], $c_id);
				$progress = $translated.' / '.$questions;
				$extra = '<a href="translate_quiz.php?exercise='.$quiz[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
				$quizes[] = array($quiz[0], $quiz[1], $progress, $extra);
			}
			return ($quizes);
	}
	
	public function get_question_number(){
		$tbl_rel_quiz = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);
		$tbl_question = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$course_info = 	api_get_course_info($_GET['cidReq']);
		$c_id =	$course_info['real_id'];
		$exe_id = $_GET['exercise'];
		
		$sql = "SELECT COUNT(question_id) AS num FROM $tbl_rel_quiz WHERE c_id=".$c_id." AND exercice_id=".$exe_id;

				
		$res = Database::query($sql);
		while($question = Database::fetch_array($res)){
			$num = $question['num'];
		}
		return $num;		
	}
	
	public function get_question_data(){
		$tbl_rel_quiz = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);
		$tbl_question = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$course_info = 	api_get_course_info($_GET['cidReq']);
		$c_id =	$course_info['real_id'];
		$exe_id = $_GET['exercise'];
		$questions = array();
		
		$sql = "SELECT id, question 
				FROM $tbl_question 
				WHERE id IN (SELECT question_id FROM $tbl_rel_quiz WHERE c_id=".$c_id." AND exercice_id=".$exe_id.") AND c_id=".$c_id;
				
			$res = Database::query($sql);
			while ($question = Database::fetch_row($res)){
				$extra = '<a href="translate_question.php?question='.$question[0].'&exercise='.$exe_id.'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
				$progress = self::question_translated($exe_id, $c_id, $question[0]);
				if($progress == null){
					$translated = 'No';
				}else{
					$translated = 'Yes';
					$extra = '<img src="'.api_get_path(WEB_IMG_PATH).'completed.png" border="0" />';
				}
				
				$questions[] = array($question[0], $question[1], $translated, $extra);
			}
			return ($questions);
	}
	
	public function question($c_id, $id){
		$tbl_question = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$data = array();
		$sql = "SELECT question, description FROM $tbl_question 
				WHERE c_id=".$c_id." AND id=".$id;
				
		$res = Database::query($sql);
		while ($question = Database::fetch_row($res)){
			$data = array('question' => $question[0], 'description' => $question[1]);
		}
		return ($data);
	} 
	
	public function get_exe_data($id, $c_id){
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		
		$exe_info = array();
		
		$sql = "SELECT id, title, description FROM $tbl_quiz 
				WHERE c_id=".$c_id." AND id=".$id;

			$res = Database::query($sql);
			while ($exe = Database::fetch_row($res)){
				$exe_info = array('id' => $exe[0], 'title' => $exe[1], 'description' => $exe[2]);
			}
			return ($exe_info);
	}
	
	public function save_question_translation($id, $exe_id){
	
 		$tbl_question = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$tbl_answers = Database :: get_course_table(TABLE_QUIZ_ANSWER);
		$tbl_translate = Database::get_main_table('exe_translate_progress');
		$course_info = 	api_get_course_info($_GET['cidReq']);
		$c_id =	$course_info['real_id'];
		
		$sql_question = "UPDATE $tbl_question SET question = '".$_POST['question']."', description='".$_POST['description']."'
						 WHERE c_id=".$c_id." AND id=".$id;
		
		$res_question = Database::query($sql_question);
		
		$sql_num = "SELECT id FROM $tbl_answers WHERE c_id=".$c_id." AND question_id=".$id;
		
		$res_num = Database::query($sql_num);
		while($answer = Database::fetch_array($res_num)){
			$answer_id = $answer['id'];
			$sql_answers = "UPDATE $tbl_answers SET answer = '".$_POST[$answer_id]."'
							WHERE c_id=".$c_id." AND question_id=".$id." AND id=".$answer_id;
			
			$res_answers = Database::query($sql_answers);
		}
		
		$sql_translated = "INSERT INTO $tbl_translate (c_id, exercice_id, question_id, translate)
						   VALUES (".$c_id.", ".$exe_id.", ".$id.", 'yes')";
						   
		$res_translated = Database::query($sql_translated);
		
		header("Location: ".api_get_path(WEB_PLUGIN_PATH)."/exetranslate/translate_quiz.php?exercise=".$_GET['exercise']."&".api_get_cidreq());
	}
	
	public function save_quiz_translation($exe_id, $c_id){
	
		$tbl_translate = Database::get_main_table('exe_translate_progress');
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		
		$sql_exe = "UPDATE $tbl_quiz SET title = '".$_POST['exe_title']."', description = '".$_POST['exe_desc']."'
							WHERE c_id=".$c_id." AND id=".$exe_id;
			
		$res_exe = Database::query($sql_exe);
		
		
		$sql_translated = "INSERT INTO $tbl_translate (c_id, exercice_id, question_id, translate)
						   VALUES (".$c_id.", ".$exe_id.", 0, 'yes')";
						   
		$res_translated = Database::query($sql_translated);
		
		header("Location: ".api_get_self()."?exercise=".$exe_id."&".api_get_cidreq());
	}
	
	public function quiz_intro_translated($id, $c_id){
		$tbl_translate = Database::get_main_table('exe_translate_progress');
		
		$sql = "SELECT COUNT(exercice_id) AS num FROM $tbl_translate 
		WHERE c_id=".$c_id." AND exercice_id=".$id." AND question_id=0";
		
		$res = Database::query($sql);
		while($quiz_num = Database::fetch_array($res)){
			$num = $quiz_num['num'];
		}
		return $num;
	}
	
}
?>