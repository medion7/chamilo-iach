<html>
    <head>
<!--<script src="./jwplayer/jwplayer.js"></script>
<script>jwplayer.key="Qc1xZuCJEgqZf1xaqo60AOOghwePRXAr+9AH6qI+kMM=";</script>-->

			<script src='https://content.jwplatform.com/libraries/aT1OQSGd.js'></script>
			<!--<script src='https://jwpsrv.com/library/oOUr+Lh3EeKSgCIACqoQEQ.js'></script>-->
			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
		<!--	<script>jwplayer.key="sN0U2stfItmXdHwAx8glseOg3CGsxMCb7WclDX7tz93BFfQXgGdqAQ==";</script>-->
    			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
			<script type="text/javascript" src="css/animate.min.css"></script>
			<link href='css/animate.min.css' rel='stylesheet' type='text/css'>
			
        <title></title>
		
	<style>
	form#notes textarea{
	width: 577px;
	height: 189px;
	background-color: #FFFFAE;
	font-family: Comic Sans MS;
	}
	
	.main{
		margin-bottom: 10px;
	}
	
	.tabs input[type=radio] {
	          position: absolute;
	          top: -9999px;
	          left: -9999px;
	      }
	      .tabs {
	        width: 90%;
	        float: none;
	        list-style: none;
	        position: relative;
	        padding: 0;
	        margin: 20px 0px 0px;
	      }
	      .tabs li{
	        float: left;
	      }
	      .tabs label {
	          display: block;
	          padding: 10px 10px;
	          border-radius: 2px 2px 0 0;
	          color: #693B8D;
	          font-size: 16px;
	          font-weight: normal;
	          font-family: 'Roboto', helveti;
	          background: rgba(255,255,255,0.2);
	          cursor: pointer;
	          position: relative;
	          top: 3px;
	          -webkit-transition: all 0.2s ease-in-out;
	          -moz-transition: all 0.2s ease-in-out;
	          -o-transition: all 0.2s ease-in-out;
	          transition: all 0.2s ease-in-out;
	      }
	      .tabs label:hover {
	        background: rgba(255,255,255,0.5);
	        top: 0;
	      }
	      
	      [id^=tab]:checked + label {
	        background: #693B8D;
	        color: white;
	        top: 0;
	      }
	      
	      [id^=tab]:checked ~ [id^=tab-content], [id^=tab]:checked ~ [id^=tab-content] > div {
	          display: block;
	      }
	      .tab-content{
	        z-index: 2;
	        display: none;
	        text-align: left;
	        overflow: hidden;
	        width: 100%;
	        font-size: 16px;
	        line-height: 140%;
	        padding-top: 10px;
	        padding-bottom: 10px;
	        background: #693B8D;
	        padding: 15px;
	        color: white;
	        position: absolute;
	        top: 35px;
	        left: 0;
	        box-sizing: border-box;
	        margin: 0px 0px 20px;
	        border-bottom:50px solid white;
	      }
	      .tab-content > div{
	        display: none;
	        -webkit-animation-duration: 0.5s;
	        -o-animation-duration: 0.5s;
	        -moz-animation-duration: 0.5s;
	        animation-duration: 0.5s;
	      }
	</style>
    </head>
    <body>

	

<?php 

$language_file = 'learnpath';

require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once 'translation_lp_video_functions.php';

api_protect_course_script();
api_block_anonymous_users();
$is_allowedToEdit = api_is_allowed_to_edit(null,true);

$video_id = $_GET['video_id'];
$user_id = api_get_user_id();
$course_id  = api_get_course_int_id();
$course_code= api_get_course_id();
$tbl_notes = Database::get_course_table(TABLE_NOTEBOOK);
$course_suf = course_suffix();

$sql = "SELECT * FROM course_lp_video_token WHERE video_id='".$video_id."'";
$result = mysql_query($sql);

while($row = mysql_fetch_array($result)){
$video_id = $row['video_id'];
$title = $row['title_trans'];
$title_video_num = $row['title_video_num'];
$duration = $row['duration'];
$en_bullets = $row['html_bullets_en'];
$es_bullets = $row['html_bullets_es'];
$subs_path = $row['subs_file_name']; 
$start = $row['start_time'];
$course = $row['c_code'];
$full_title = $row['title_trans'].' '.$row['title_video_num'];
}

protect_videos($user_id, $course_code);


    $file = 'https://videos.vithoulkas.edu.gr:443/vod/_definst_/mp4:iach/'.video_path($course, $title, $title_video_num).'_350.mp4/playlist.m3u8';

?>		
		<div id="title" style="font-size:16px; font-weight:bold;"><?php echo get_lang($title).' '.$title_video_num.' ('.$duration.' '.get_lang("hours").')';?></div>
        <div id="my-video">&nbsp;</div> 
        <script type='text/javascript'>
			jwplayer('my-video').setup({
				playlist: [{
				sources: [{
					file: "<?php echo $file; ?>"
				}],
				image: "video_img.jpg",
			tracks: [<?php echo subs($course, $title, $title_video_num) ?>]
			}],
				primary: 'flash',
				width: "60%",
				aspectratio: "4:3",
				androidhls: "true",
				skin: 'beelden',
			});
			
		function advanced_parameters() {
			if(document.getElementById("notes").style.display == "none") {
				document.getElementById("notes").style.display = "block";
			} else {
				document.getElementById("notes").style.display = "none";
			}
		};
</script>	
        
    </body>
</html>

