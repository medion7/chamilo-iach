<html>
    <head>
			<script type="text/javascript" src='https://content.jwplatform.com/libraries/aT1OQSGd.js'></script>
			<!--<script type="text/javascript" src='https://jwpsrv.com/library/oOUr+Lh3EeKSgCIACqoQEQ.js'></script>-->
			<!--<script src="https://courses.vithoulkas.edu.gr/jwplayer/jwplayer.js" ></script>
			<script>jwplayer.key="B4YYymYqwhnbhb4JXDmVgwSlhBJ5unDRGn4HHm41+CU=";</script> -->
			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script type="text/javascript" src="https://code.jquery.com/jquery-1.9.1.js"></script>
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
require_once 'course_lp_video_functions.php';

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
$br_bullets = $row['html_bullets_br'];
$el_bullets = $row['html_bullets_el'];
$it_bullets = $row['html_bullets_it'];
$ru_bullets = $row['html_bullets_ru'];
$subs_path = $row['subs_file_name']; 
$start = $row['start_time'];
$course = $row['c_code'];
$full_title = $row['title_trans'].' '.$row['title_video_num'];
}

protect_videos($user_id, $course_code, $course);


    $file = 'https://videos.vithoulkas.edu.gr:443/wms/_definst_/smil:iach/'.video_path($course, $title, $title_video_num).'.smil/playlist.m3u8?wmsAuthSign='.create_token($user_id, $course_code);
//    $file = 'http://wms.medion7.com:443/vod/_definst_/smil:iach/'.video_path($course, $title, $title_video_num).'.smil/playlist.m3u8';	

$sql_sel = "SELECT description FROM $tbl_notes 
			WHERE c_id='".$course_id."' AND user_id='".$user_id."' AND title='".get_lang($title)." ".$title_video_num."' AND status=12345";
$res_sel = Database::query($sql_sel);
while ($note = mysql_fetch_array($res_sel)) { 
		$content = $note['description'];
		}		


?>		
		<div id="title" style="font-size:16px; font-weight:bold;"><?php echo get_lang($title).' '.$title_video_num.' ('.$duration.' '.get_lang("hours").')';?></div>
        <div id="my-video">You need to install flash player in order to view this video. Please click <a href="http://get.adobe.com/flashplayer/" target="_blank">here</a> to download it.</div> <br /><br />
       <div style="width:80%;"><button onclick="pausePlayer()">Pause</button><button onclick="resumePlayer()">Resume</button><button id="font-size-up"  style="background: url('/main/default_course_document/images/player/font_size_up-25x25.png') no-repeat; width:30px; height:30px; margin:5px; float:right;"></button><button id="font-size-down" style="background: url('/main/default_course_document/images/player/font_size_down-25x25.png') no-repeat; width:30px; height:30px; margin:5px; float:right;"></button></div>
        <script type='text/javascript'>
			current_font_size = 15;
            var file_name = '<?php echo $file; ?>';
			jwplayer('my-video').setup({
				playlist: [{
				sources: [{
					file: file_name
				}],
				image: "video_img.jpg",
			tracks: [<?php echo subs($course, $title, $title_video_num) ?>]
			}],
				width: "80%",
				aspectratio: "16:9",
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
		<p></p>
		<?php if($course != 301){ ?>
	    <div id="drop_down" class="open_notes" style="">
            <a href="#" onClick="advanced_parameters()"><span id="img_plus_and_minus"><div style="vertical-align:top;" >
            <img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />Personal notes</div></span></a>
        </div>
		<div id="notes" style="display:none">
		<form action="" id="notes" method="post">
		<input type="hidden" name="title" value="<?php echo get_lang($title)." ".$title_video_num; ?>">
		<input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
		<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
		<input type="hidden" name="course_code" value="<?php echo $course_code; ?>">
		<label for="content">My notes for  <?php echo get_lang($title)." ".$title_video_num; ?><br/></label>
		<textarea name="content"><?php echo $content; ?></textarea><br/>
		<input type="submit" value="Save my notes" />
		</form>
		<?php }else{ } ?>
		

<script>
/* attach a submit handler to the form */
$("#notes").submit(function(event) {
/* stop form from submitting normally */
event.preventDefault();
/* get some values from elements on the page: */
				var course_id = $("[name='course_id']").val();
				var user_id = $("[name='user_id']").val();
				var course_code = $("[name='course_code']").val();
				var title = $("[name='title']").val();
				var content = $("[name='content']").val();
/* Send the data using post */
$.post( "save.php", { 
					course_id:course_id, 
					user_id: user_id,
					course_code: course_code,
					title: title,
					content: content
 } )
/* Put the results in a div */
.done(function(data) {
alert(data);
});
});
</script>


		<span class="error" style="display:none"> Please Enter Valid Data</span>
		<span class="success" style="display:none"> Registration Successfully</span>
		</div>
        
<div>

<?php 

	if($course_suf == 'english'){
		echo $en_bullets;
	}elseif($course_suf == 'spanish' || $course_suf ==  'portuguese'){
		echo $es_bullets;
	}elseif($course_suf == 'german'){
		
	}elseif($course_suf == 'brazilian'){
		echo $br_bullets;
	}elseif($course_suf == 'greek'){
		echo $el_bullets;
	}elseif($course_suf == 'italian'){
		echo $it_bullets;
	}elseif($course_suf == 'russian'){
		echo $ru_bullets;
	}
	
?>

</div>
<div>
<?php

if($course == 001 || $course == 002){
	theory_bg($video_id, $course_suf);
}

if($course == '003' || $course == '004' || $course == '005'){
	mm_reference($video_id, $course_suf);
}

if($course == '601' || $course == '602' || $course == '603'){
	organon_par($video_id);
}
?>
</div><br/><br/>

<script>
function getVideoName(){
    var a = file_name;
    var href = a.split('?')[0];
    return href;
}

function pausePlayer(){
    jwplayer().pause(true);
    setCookie(getVideoName(), jwplayer().getPosition());
}

function resumePlayer(){   
    var position = getCookie(getVideoName());
    
    $.post('course_lp_video_ajax.php', {method: 'get_token'}, function(data){
        if (data.status == 'ok'){
            jwplayer().load([{sources: [{file: getVideoName() + '?wmsAuthSign=' + data.token}],image: "video_img.jpg",tracks: [<?php echo subs($course, $title, $title_video_num) ?>]}]);
            if (position > 30) position -= 30;
            if (position < 0) position = 0;
            jwplayer().play(true);
            jwplayer().on('firstFrame', function() { 
				jwplayer().seek(position)
			});
        }
    }, 'json');
}

function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}

function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires*1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) { 
  	   options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for(var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];    
        if (propValue !== true) { 
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}

$("#font-size-up").on("click", function(){
	current_font_size = current_font_size + 5;
	jwplayer().setCaptions({"fontSize": current_font_size});
});

$("#font-size-down").on("click", function(){
	current_font_size = current_font_size - 5;
	jwplayer().setCaptions({"fontSize": current_font_size});
});
</script>
	
    </body>
</html>

