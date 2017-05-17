<?php

require_once '../inc/global.inc.php';

function create_token(){
	
	$today = gmdate("n/j/Y g:i:s A");
	$ip = $_SERVER['REMOTE_ADDR'];
	$key = "PPxo6fwcjh91HejZ8x1L"; //this is also set up in WMSPanel rule
	$validminutes = 180;
	$str2hash = $ip . $key . $today . $validminutes;
	$md5raw = md5($str2hash, true);
	$base64hash = base64_encode($md5raw);
	$urlsignature = "server_time=" . $today ."&hash_value=" . $base64hash. "&validminutes=$validminutes";
	$base64urlsignature = base64_encode($urlsignature);
	
	return $base64urlsignature;
}

function theory_bg($video_id, $lang){


	$course_info = 	api_get_course_info($_GET['cidReq']);

	$sql = "SELECT title, id FROM iach_main.bibliography_theory 
			WHERE video_id=".$video_id." AND lang='".$lang."'";
	$res = Database::query($sql);
	if(mysql_num_rows($res) > 0){
	    $html = "<p><h1 style='font-size:1.3em;'>Bibliography:<h1></p>";
		while ($bg = mysql_fetch_array($res)) { 
			$header = $bg['title'];
			$bg_id = $bg['id'];
			$html .= '<p><h2 style="font-size:1.1em;">'.$header.'</h2><p><ul>';
			$sql_c = "SELECT r.content AS ref, b.title AS book FROM iach_main.bibliography_theory_ref AS r
					  INNER JOIN iach_main.bibliography_theory_books AS b ON b.book_id = r.book_id
					  WHERE r.bibliography_id=".$bg_id;
			$res_c = Database::query($sql_c);
			while ($c = mysql_fetch_array($res_c)) { 
			$book = $c['book'];
			$ref = $c['ref'];
			$html .= '<li>'.$book.': '.$ref.'</li>';
		}	
		$html .= '</ul>';
	} }else{ $html = ''; }
	echo $html;
}

function link_ref($link){
	$new_link = '';
	$string = substr($link, 0, 4);
	
	if($string == 'http'){
		$new_link = '<a href='.$link.' target=_blank>'.get_lang("link").'</a>';
	}else{
		$new_link = $link;
	}
	return $new_link;
}

function mm_reference($video_id, $lang){

	$i = 1;
	$ref_ids = array();
	$html = "<p><h1 style='font-size:1.3em;'>Bibliography:<h1></p>";
	
	$html .= '<table border=1><tr><th>Remedy</th>';
	$sql = "SELECT * FROM iach_main.mm_reference";
			$res = Database::query($sql);
	if(mysql_num_rows($res) > 0){
			while ($row = mysql_fetch_array($res)) { 
				$ref = $row['ref'];
				$ref_id = $row['ref_id'];
				$ref_ids[$i] = $ref_id;
				$i++;
				$html .= '<th>'.$ref.'</th>';
			}
	$html .= '</tr><tr>';
	
	$sql_link1 = "SELECT remedy, start_time, link FROM iach_main.mm_reference_link
				  WHERE video_id=".$video_id." AND ref_id=".$ref_ids[1];
	$res_link1 = Database::query($sql_link1);
			while ($link1 = mysql_fetch_array($res_link1)) { 
				$remedy = $link1['remedy'];
				$start_time = $link1['start_time'];
				$first_link = $link1['link'];
				$html .= '<th>'.$remedy.'<br/>('.get_lang("time").' : '.$start_time.')</th>';
				$html .= '<td>'.link_ref($first_link).'</td>';

	foreach ($ref_ids as $value){
	if($value != $ref_ids[1]){
	$sql_link = "SELECT * FROM iach_main.mm_reference_link
				  WHERE video_id=".$video_id." AND ref_id=".$value." AND remedy ='".$remedy."'";
	$res_link = Database::query($sql_link);
			while ($link = mysql_fetch_array($res_link)) { 
				$rest_link = $link['link'];
				if($rest_link == ''){
					$html .= '<td>&nbsp</td>';
				}else{
				$html .= '<td>'.link_ref($rest_link).'</td>';
				}
			}
	}
	}
	$html .= '</tr>';
	}
	$html .= '</table>';
	}else { $html = '';}
	echo $html;
}

function subs($c_code, $title_trans, $title_video_num){

	$course_lang = api_get_language_from_type('course_lang');
	
	$langs = array('english' => 'EN', 'spanish' => 'ES', 'japanese' => 'JP', 'portuguese' => 'PT',  'greek' => 'GR', 'russian' => 'RU', 'brazilian' => 'BR', 'romanian' => 'RO', 'croatian' => 'HR', 'slovenian' => 'SL', 'serbian' => 'SR', 'italian' => 'IT');
	$html ='';
	
	$video_path = video_path($c_code, $title_trans, $title_video_num);
	
	$video_file = substr( $video_path, 3);
	
	foreach($langs as $string => $type){
		
		$html .=	'{
					file: "subs/'.$type.$c_code.''.$video_file.'.srt", 
					label: "'.$string.'",
					kind: "captions"';
				
		if($string == $course_lang){
			$html .= ', 
					"default": true'
					;
		}
		$html .= '
				}';
		if($type == end($langs)){
			$html .= '';
		}else{
			$html .= ',';
		}
		
	}
	
	return $html;
	
}

function course_suffix(){
	$course_lang = api_get_language_from_type('course_lang');
	
	return $course_lang;
}

function video_path($course, $title_trans, $title_video_num){
	
	$titles = array('AudioCase' => '/audiocase', 'PaperCase' => '/papercase', 'CaseStudy' => '/case', 'Theory' => '/', 'MateriaMedica' => '/mm', 'Repertorization' => '/rep', 'Topics' => '/topics', 'Q&A' => '/qna', 'Levels2012' => '/levels', 'Levels2012WelcomeSession' => '/levels_intro', 'Levels2012Theory' => '/levels_theory', 'Levels2012Q&A' => '/levelsqna', 'Part' => '/organon', 'Levels2012Birth'=> '/levels_birth');
	
	foreach($titles as $a => $b){
		if($a == $title_trans){
			$title = $b;
		}
	}
	
	$path = $course.$title.$title_video_num;
	
	return $path;
	
}

function protect_videos($user_id, $course_code){

	$user_course = array();
	$user_course = api_get_user_courses($user_id);
	
	$c_course = $course_code;

	if(in_array($course_code, $user_course, false)){
		Display::display_error_message(get_lang('ProtectedDocument'));//api_not_allowed backbutton won't work.
    	exit;
	}
}

function organon_par($video_id){
	$i = 1;
	$checked = '';
	$sql = "SELECT * FROM iach_main.organon_tabs WHERE video_id =".$video_id." ORDER BY paragraph ASC";
			$res = Database::query($sql);
	if(mysql_num_rows($res) > 0){
	
	echo '<div class="main">
	        	<ul class="tabs">';
				
			while ($row = mysql_fetch_array($res)) { 
				$par = $row['paragraph'];
				$content = $row['content'];
				if($i == "1"){
					$checked = 'checked';
				}else{
					$checked = '';
				}
				echo '<li>
			          <input type="radio" '.$checked.' name="tabs" id="tab'.$i.'">
			          <label for="tab'.$i.'">&sect; '.$par.'</label>
			          <div id="tab-content'.$i.'" class="tab-content">
			            <div class="animated  fadeInRight">
			              '.$content.'
			            </div>
			          </div>
			        </li>';
				$i++;
			}
			
		echo '</ul>
	      	</div>';
	}
}

?>
