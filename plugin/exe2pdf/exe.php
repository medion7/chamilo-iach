<?php

	function exe_html($exe_id, $user_save_result=false){
		require_once '../../main/exercice/exercise.lib.php';
		require_once '../../main/exercice/exercise.class.php';
		require_once '../../main/exercice/question.class.php';
		require_once '../../main/exercice/answer.class.php';
		$html = '';
		$html .= '<link rel="stylesheet" type="text/css" href="../../main/css/base.css">';
		if (api_is_allowed_to_edit()==1 || api_is_platform_admin($allow_sessions_admins)==1) {
		global $origin, $debug;
		$track_exercise_info = get_exercise_track_exercise_info($exe_id);
		$exercise_id  = $track_exercise_info['exe_exo_id'];
		
		$objExercise = new Exercise();
		
		if (!empty($exercise_id)) {
			$objExercise->read($exercise_id);
		}
		
		$course_info = 	api_get_course_info($cidReq);
		$exercise_stat_info = $objExercise->get_stat_track_exercise_info_by_exe_id($exe_id);
		
		$question_list = array();
		if (!empty($exercise_stat_info['data_tracking'])) {
			$question_list = explode(',', $exercise_stat_info['data_tracking']);
		} else {
        //Try getting the question list only if save result is off
        if ($save_user_result == false) {
            $question_list = $objExercise->get_validated_question_list();
        }
        error_log("Data tracking is empty! exe_id: $exe_id");
		}
		
		$counter = 1;
		$total_score = $total_weight = 0;

		$exercise_content = null;

		//Hide results
		$show_results     = true;
		$show_only_score  = true;

		$user_info   = api_get_user_info($exercise_stat_info['exe_user_id']);
		//Shows exercise header
		$html .=  $objExercise->show_exercise_result_header($user_info['complete_name'], api_convert_and_format_date($exercise_stat_info['start_date'], DATE_TIME_FORMAT_LONG), $exercise_stat_info['duration']);
		
		$question_list_answers = array();
		$media_list = array();
		$category_list = array();
		
		// Loop over all question to show results for each of them, one by one
		if (!empty($question_list)) {
        if ($debug) { error_log('Looping question_list '.print_r($question_list,1));}
        foreach ($question_list as $questionId) {

            // creates a temporary Question object
            $objQuestionTmp = Question :: read($questionId);

            //this variable commes from exercise_submit_modal.php
            ob_start();

            // We're inside *one* question. Go through each possible answer for this question
            $result = $objExercise->manage_answer($exercise_stat_info['exe_id'], $questionId, null, 'exercise_result', array(), $save_user_result, true, $show_results, $objExercise->selectPropagateNeg(), $hotspot_delineation_result);
            if (empty($result)) {
                continue;
            }

            $total_score     += $result['score'];
            $total_weight    += $result['weight'];

            $question_list_answers[] = array(
                'question' => $result['open_question'],
                'answer' => $result['open_answer'],
                'answer_type' => $result['answer_type']
            );

            $my_total_score  = $result['score'];
            $my_total_weight = $result['weight'];


            //Category report
            $category_was_added_for_this_test = false;

            if (isset($objQuestionTmp->category) && !empty($objQuestionTmp->category)) {
                $category_list[$objQuestionTmp->category]['score'] += $my_total_score;
                $category_list[$objQuestionTmp->category]['total'] += $my_total_weight;
                $category_was_added_for_this_test = true;
            }

            if (isset($objQuestionTmp->category_list) && !empty($objQuestionTmp->category_list)) {
                foreach($objQuestionTmp->category_list as $category_id) {
                    $category_list[$category_id]['score'] += $my_total_score;
                    $category_list[$category_id]['total'] += $my_total_weight;
                    $category_was_added_for_this_test = true;
                }
            }

            //No category for this question!
            if ($category_was_added_for_this_test == false) {
                $category_list['none']['score'] += $my_total_score;
                $category_list['none']['total'] += $my_total_weight;
            }
			
			if ($objExercise->selectPropagateNeg() == 0 && $my_total_score < 0) {
                $my_total_score = 0;
            }

            $comnt = null;
            if ($show_results) {
                $comnt = get_comments($exe_id, $questionId);
                if (!empty($comnt)) {
                   echo '<b>'.get_lang('Feedback').'</b>';
                   echo '<div id="question_feedback">'.$comnt.'</div>';
                }
            }

            $score = array();
            
                $score['result']    = get_lang('Score')." : ".show_score($my_total_score, $my_total_weight, false, true);
                $score['pass']      = $my_total_score >= $my_total_weight ? true : false;
                $score['score']     = $my_total_score;
                $score['weight']    = $my_total_weight;
                $score['comments']  = $comnt;
          

            $contents = ob_get_clean();

            $question_content = '<div class="question_row">';

            if ($show_results) {
                $show_media = false;
                /*if ($objQuestionTmp->parent_id != 0 && !in_array($objQuestionTmp->parent_id, $media_list)) {
                    $show_media = true;
                    $media_list[] = $objQuestionTmp->parent_id;
                }*/
                //Shows question title an description
                $question_content .= $objQuestionTmp->return_header(null, $counter, $score);
            }
            $counter++;

            $question_content .= $contents;
            $question_content .= '</div>';

            $exercise_content .= $question_content;

        } // end foreach() block that loops over all questions
    }

    $total_score_text = null;

    if ($origin != 'learnpath') {
        if ($show_results || $show_only_score) {
            $total_score_text .= '<div class="question_row">';
            $total_score_text .= get_ribbon($objExercise, $total_score, $total_weight, true);
            $total_score_text .= '</div>';
        }
    }
	if (!empty($category_list) && ($show_results || $show_only_score) ) {
        //Adding total
        $category_list['total'] = array('score' => $total_score, 'total' => $total_weight);
        $html .= Testcategory::get_stats_table_by_attempt($objExercise->id, $category_list);
    }

    $html.= $total_score_text;
    $html .= $exercise_content;
	
	return $html;
	}
}


function get_ribbon($objExercise, $score, $weight, $check_pass_percentage = false) {
    $ribbon = '<div class="ribbon">';
    if ($check_pass_percentage) {
        $is_success = is_success_exercise_result($score, $weight, $objExercise->selectPassPercentage());
        // Color the final test score if pass_percentage activated
        $ribbon_total_success_or_error = "";
        if (is_pass_pourcentage_enabled($objExercise->selectPassPercentage())) {
            if ($is_success) {
                $ribbon_total_success_or_error = ' ribbon-total-success';
            } else {
                $ribbon_total_success_or_error = ' ribbon-total-error';
            }
        }
        $ribbon .= '<div class="rib rib-total '.$ribbon_total_success_or_error.'">';
    } else {
        $ribbon .= '<div class="rib rib-total">';
    }
    $ribbon .= '<h3>'.get_lang('YourTotalScore').":&nbsp;";
    $ribbon .= show_score($score, $weight, false, true);
    $ribbon .= '</h3>';
    $ribbon .= '</div>';
    if ($check_pass_percentage) {
        $ribbon .= show_success_message($score, $weight, $objExercise->selectPassPercentage());
    }


    $ribbon .= '</div>';
    return $ribbon;
}
		
	function quiz_html($quiz_id){
		require_once '../../main/exercice/exercise.lib.php';
		require_once '../../main/exercice/exercise.class.php';
		require_once '../../main/exercice/question.class.php';
		require_once '../../main/exercice/answer.class.php';
		global $origin;
		
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$tbl_question = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$tbl_rel_question = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);
		$course_info = 	api_get_course_info($cidReq);
		$c_id =	$course_info['real_id'];
		$content = array();
		$i=1;
		
		$sql = "SELECT question_id FROM $tbl_rel_question
				WHERE c_id=".$c_id." AND exercice_id=".$quiz_id;
		$res = Database::query($sql);
		while ($quiz = Database::fetch_array($res)){
			$question_id =  $quiz['question_id'];
			$content[] = showQuestion($question_id, false, $origin, $i, true, false, null, false);
			$i++;
		}
		return ($content);
	}
	
	function quiz_header($quiz_id){
		$tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST);
		$course_info = api_get_course_info();
		$course_id = $course_info['real_id'];
		$info = array();
		
		$sql = "SELECT title, description FROM $tbl_quiz
				WHERE c_id=".$course_id." AND id=".$quiz_id;
		$res = Database::query($sql);
		while ($quiz = Database::fetch_array($res)){
			$title = $quiz['title'];
			$desc = $quiz['description'];
			$info = array('title'=>$title, 'desc'=>$desc);
		}
		return ($info);
	}
	
		function quiz_replace_string($string){
		$string=preg_replace('/<p[^>]*>/i', '', $string);
		$string=preg_replace('/<\/p>/i', '<br/>', $string);
		$string=preg_replace('/<select[^>]*>/i', '', $string);
		$string=preg_replace('/<\/select>/i', '', $string);
		$string=preg_replace('/<option[^>]*>/i', '', $string);
		$string=preg_replace('/<\/option>/i', '', $string);
		$string=preg_replace('/<iframe[^>]*>/i', '', $string);
		$string=preg_replace('/<\/iframe>/i', '<br/><br/><br/>', $string);
		$string=preg_replace('/<div class="question_title"  >/i', '<br/><div class="question_title">', $string);
		$string=str_replace('[]', '_____________', $string);
		$string=str_replace('DoubtScore', 'Do not know', $string);
		$string=preg_replace('/--[^>]*>/i', '</td>', $string);
		$string=preg_replace('/<input type="hidden"[^>]*>/i', '', $string);
		return $string;
		}
		
		function exe_replace_string($string){
			$string=preg_replace('/<\/td>/i', '', $string);
		}

?>