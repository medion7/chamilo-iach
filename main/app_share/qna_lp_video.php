<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			
        <title>Video Q&amp;A</title>
        
         <script type='text/javascript'>
			
		function advanced_parameters(i) {
				var notes="notes" + i;
			var icon = "img_plus_and_minus" + i;
			if(document.getElementById(notes).style.display == "none") {
				document.getElementById(notes).style.display = "block";
				document.getElementById(icon).innerHTML=' <br/><img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" /> Hide Answer';
			} else {
				document.getElementById(notes).style.display = "none";
				document.getElementById(icon).innerHTML=' <br/><img style="vertical-align:middle;" src="../img/div_show.gif" alt="" /> Show Answer';
			}
		};
		</script>	
		
		<style>
	div.answer {
	font-family:Arial;
	font-size: 1em;
	border-radius: 0px 0px 5px 5px; 
	-moz-border-radius: 0px 0px 5px 5px; 
	-webkit-border-radius: 0px 0px 5px 5px; 
	border: 2px solid #E2D9FF;
	padding:5px;
	width:80%;
	height: 50px;
	background-color:#F0F0F0 ;
	}
	#title{
		font-family:Arial;
		font-size: 1.2em;
	}
	#question {
		font-family:Arial;
		font-size: 1em;
		width:80%;
		padding:5px;
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

require_once '../inc/global.inc.php';

api_protect_course_script(true);
$is_allowedToEdit = api_is_allowed_to_edit(null,true);


$video_id = $_GET['video_id'];


$sql = "SELECT * FROM course_lp_video WHERE video_id=".$video_id;
$result = mysql_query($sql);

while($row = mysql_fetch_array($result)){
$video_id = $row['video_id'];
$title = $row['title_trans'];
$title_video_num = $row['title_video_num'];
}
?>
<div id="title" style="font-size:16px; font-weight:bold;"><?php echo 'Questions for '.$title.' '.$title_video_num; ?></div>
        <div id="my-video">&nbsp;</div> 
<?php

$i = 1;		
$sql_sel = "SELECT * FROM qna_lp_video
			WHERE video_id=".$video_id;
$res_sel = Database::query($sql_sel);
while ($qna = mysql_fetch_array($res_sel)) { 
		$question = $qna['question'];
		$answer = $qna['answer'];
		

echo '<div id="question">Question '.$i.': '.$question;
?>		
		
       
            <a href="#" onClick="advanced_parameters(<?php echo $i; ?>)"><span class="expand" id="img_plus_and_minus<?php echo $i; ?>"><div style="vertical-align:top;" >
            <br/><img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />Show answer</div></span></a>
        </div>
		<div class="answer" id="notes<?php echo $i; ?>" style="display:none">
        <?php echo 'Answer: '.$answer; ?>


		</div><br/><br/>
<?php
$i++;
}
?>
	
    </body>
</html>

