<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			
        <title>Video Q&amp;A</title>
		
		<style>
	div.answer {
	font-family:Arial;
	font-size: 0.9em;
	border-radius: 0px 0px 5px 5px; 
	-moz-border-radius: 0px 0px 5px 5px; 
	-webkit-border-radius: 0px 0px 5px 5px; 
	border: 2px solid #E2D9FF;
	padding:7px;
	width:80%;
	background-color:#F0F0F0 ;
	}
	#title{
		font-family:Arial;
		font-size: 1.2em;
	}
	#question {
		font-family:Arial;
		font-size: 0.9em;
		width:80%;
		padding:7px;
		border-radius: 5px 5px 0px 0px; 
		-moz-border-radius: 0px 0px 5px 5px; 
		-webkit-border-radius: 0px 0px 5px 5px;  
		border: 2px solid #B8B8B8  ;
		background-color:#B8B8B8 ;
	}

	</style>
		
    </head>
    <body>

	

<?php 
$language_file = 'learnpath';

require_once '../inc/global.inc.php';

api_protect_course_script(true);
api_block_anonymous_users();


$video_id = $_GET['video_id'];


$course_lang = api_get_language_from_type('course_lang');

	if($course_lang == 'english'){
		$selfAssessmentQuestions = 'Self Assessment Questions';
		$showAnswer = 'Show Answer';
		$hideAnswer = 'Hide answer';
		$answerText = 'Answer';
		$questionText = 'Question';
		$qna_lang = 'english';
	}elseif($course_lang == 'spanish' || $course_lang == 'portuguese'){
		$selfAssessmentQuestions = 'Preguntas de autoevaluación';
		$showAnswer = 'Mostrar respuesta';
		$hideAnswer = 'Ocultar respuesta';
		$answerText = 'Respuesta';
		$questionText = 'Pregunta';
		$qna_lang = 'spanish';
	}elseif($course_lang == 'russian'){

		$selfAssessmentQuestions = 'Самооценка Вопросы';
		$showAnswer = 'показать ответ';
		$hideAnswer = 'Скрыть ответ';
		$answerText = 'ответ';
		$questionText = 'вопрос';
		$qna_lang = 'russian';
	}elseif($course_lang == 'greek'){
		$selfAssessmentQuestions = 'Ερωτήσεις Αυτοαξιολόγησης';
		$showAnswer = 'Προβολή απάντησης';
		$hideAnswer = 'Απόκρυψη απάντησης';
		$answerText = 'Απάντηση';
		$questionText = 'Ερώτηση';
		$qna_lang = 'greek';
	}elseif($course_lang == 'brazilian'){
		$selfAssessmentQuestions = 'Questões de autoavaliação';
		$showAnswer = 'Mostrar a resposta';
		$hideAnswer = 'Ocultar a resposta';
		$answerText = 'Resposta';
		$questionText = 'Questão';
		$qna_lang = 'brazilian';
	}elseif($course_lang == 'italian'){
		$selfAssessmentQuestions = 'Autovalutazione Domande';
		$showAnswer = 'Mostra Risposta';
		$hideAnswer = 'Nascondi Risposta';
		$answerText = 'Risposta';
		$questionText = 'Domanda';
		$qna_lang = 'italian';
	}


?>
	
	<script type='text/javascript'>
			
		function advanced_parameters(i) {
				var notes="notes" + i;
			var icon = "img_plus_and_minus" + i;
			if(document.getElementById(notes).style.display == "none") {
				document.getElementById(notes).style.display = "block";
				document.getElementById(icon).innerHTML=' <p><img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" /><?php echo $hideAnswer; ?></p>';
			} else {
				document.getElementById(notes).style.display = "none";
				document.getElementById(icon).innerHTML=' <p><img style="vertical-align:middle;" src="../img/div_show.gif" alt="" /><?php echo $showAnswer; ?></p>';
			}
		};
	</script>
	
<?php


$sql = "SELECT * FROM course_lp_video_token WHERE video_id=".$video_id;

$result = mysql_query($sql);

while($row = mysql_fetch_array($result)){
$video_id = $row['video_id'];
$title = $row['title_trans'];
$title_video_num = $row['title_video_num'];
}
?>
<div id="title" style="font-size:16px; font-weight:bold;"><?php echo get_lang($title).' '.$title_video_num.' '.$selfAssessmentQuestions; ?></div>
        <div id="my-video">&nbsp;</div> 
<?php

$i = 1;		
$sql_sel = "SELECT * FROM qna_lp_video
			WHERE video_id=".$video_id." AND language='".$qna_lang."' ";
$res_sel = Database::query($sql_sel);
while ($qna = mysql_fetch_array($res_sel)) { 
		$question = $qna['question'];
		$answer = $qna['answer'];
		

echo '<div id="question"><strong>'.$questionText.' '.$i.':</strong> '.$question;
?>		
		
       
            <a href="#" onClick="advanced_parameters(<?php echo $i; ?>)"><span class="expand" id="img_plus_and_minus<?php echo $i; ?>"><div style="vertical-align:top;" >
            <p><img style="vertical-align:middle;" src="../img/div_show.gif" alt="" /><?php echo $showAnswer; ?></div></span></a></p>
        </div>
		<div class="answer" id="notes<?php echo $i; ?>" style="display:none">
        <?php echo '<strong>'.$answerText.':</strong><br/>'.$answer; ?>


		</div><br/><br/>
<?php
$i++;
}
?>
	<br/><br/>
    </body>
</html>

