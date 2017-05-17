<?php
/* For licensing terms, see /license.txt */

/**
* This class provides some functions for statistics
* @package chamilo.statistics
*/
class Videostats {
	
	function video_path($title_trans, $title_video_num){
	
	$titles = array('AudioCase' => 'audiocase', 'PaperCase' => 'papercase', 'CaseStudy' => 'case', 'Theory' => '', 'MateriaMedica' => 'mm', 'Repertorization' => 'rep', 'Topics' => 'topics', 'Q&A' => 'qna', 'Levels2012' => 'levels', 'Levels2012WelcomeSession' => 'levels_intro', 'Levels2012Theory' => 'levels_theory', 'Levels2012Q&A' => 'levelsqna', 'Part' => 'organon', 'NivelesDeSalud' => 'niveles', 'Observaciones' => 'observaciones');
	
	foreach($titles as $a => $b){
		if($a == $title_trans){
			$title = $b;
		}
	}
	
	$path = $title.$title_video_num;
	
	return $path;
	
	}
	
	
	function sec2hms ($sec, $padHours = false) {

		// start with a blank string
		$hms = "";
    
		// do the hours first: there are 3600 seconds in an hour, so if we divide
		// the total number of seconds by 3600 and throw away the remainder, we're
		// left with the number of hours in those seconds
		$hours = intval(intval($sec) / 3600); 

		// add hours to $hms (with a leading 0 if asked for)
		$hms .= ($padHours) 
			? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
			: $hours. ":";
    
		// dividing the total seconds by 60 will give us the number of minutes
		// in total, but we're interested in *minutes past the hour* and to get
		// this, we have to divide by 60 again and then use the remainder
		$minutes = intval(($sec / 60) % 60); 

		// add minutes to $hms (with a leading 0 if needed)
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

		// seconds past the minute are found by dividing the total number of seconds
		// by 60 and using the remainder
		$seconds = intval($sec % 60); 

		// add seconds to $hms (with a leading 0 if needed)
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

		// done!
		return $hms;
    
	}
	
	function video_view_time_diff($video, $view) {
		
		//Video time convert
		sscanf($video, "%d:%d:%d", $v_hours, $v_minutes, $v_seconds);

		$video_seconds = isset($v_seconds) ? $v_hours * 3600 + $v_minutes * 60 + $v_seconds : $v_hours * 60 + $v_minutes;
		
		//View time convert
		sscanf($view, "%d:%d:%d", $hours, $minutes, $seconds);

		$view_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
		
		//Seconds diff
		if(intval($view_seconds) == 0){
			$icon = '<img src="Error-icon.png">';	
		}elseif(intval($view_seconds) <= intval($video_seconds)){
			$icon = '<img src="Warning-icon.png">';	
		}elseif(intval($view_seconds) >= intval($video_seconds)){
			$icon = '<img src="Good-or-Tick-icon.png">';	
		}
		 return $icon;
	}
	
	function get_user_video_view_time($video = '', $user_id, $period = ''){
		
		$total = 0;
		$sql = "SELECT start, end FROM c_videostats WHERE user_id = ".$user_id;
		if(!empty($video)){
				$sql .= " AND video = '".$video.".smil'";
		}elseif(!empty($period) && $period == 'total'){
				
		}elseif(!empty($period) && $period == 'month'){
			$min = strtotime("last month");
			$sql .= " AND end > ".$min;
		}elseif(!empty($period) && $period == 'week'){
			$min = strtotime("-1 week");
			$sql .= " AND end > ".$min;
		}
		$res = Database::query($sql);
		
		while ($view = Database::fetch_array($res)) {
				$total_secs = intval($view['end']) - intval($view['start']);
				$total += $total_secs;
		}
		
		$video_view_time = self::sec2hms($total);
		return $video_view_time;
	}
	
	function video_views($video, $user_id){
			
		$sql = "SELECT COUNT(id) as view FROM c_videostats WHERE user_id = ".$user_id." AND video = '".$video.".smil'";
		
		$res = Database::query($sql);
        $obj = Database::fetch_object($res);
        $total_views = $obj->view;
			
		return $total_views;
	}
	
	function get_videos_per_course($course_code, $user_id){
			
			$video_c_code = substr($course_code, 2);
			$language_file = array('learnpath');
			$html = '';
			$sql = "SELECT * FROM course_lp_video_token WHERE c_code = '".$video_c_code."' ORDER BY title_video_num ASC";
			$res = Database::query($sql);
			while ($video = Database::fetch_array($res)) {
					$video_path = self::video_path($video['title_trans'], $video['title_video_num']);
					$video_duration = $video['duration'].':00';
					$user_view = self::get_user_video_view_time($video_path, $user_id);
				 
					$html .= '<tr>
								<td>'.get_lang($video['title_trans']).' '.$video['title_video_num'].'</td>
								<td>'.$video_duration.'</td>
								<td>'.$user_view.'</td>
								<td>'.self::video_views($video_path, $user_id).'</td>
								<td>'.self::video_view_time_diff($video_duration, $user_view).'</td>
							</tr>';
			 }
			
			
			return $html;
			
	}
	
	function get_video_view_time($view_id){
		
		$total = 0;
		$sql = "SELECT start, end FROM c_videostats WHERE id = ".$view_id;
		$res = Database::query($sql);
		while ($view = Database::fetch_array($res)) {
				$total_secs = intval($view['end']) - intval($view['start']);
				$total += $total_secs;
		}
		
		$video_view_time = self::sec2hms($total);
		return $video_view_time;
	}
	
	function get_videostats_records(){
		
		$html = '';
		$sql = "SELECT 
						v.id AS view_id,
						v.user_id AS user_id,
						v.video AS video,
						v.user_ip AS user_ip,
						v.end AS end,
						v.start AS start,
						u.username AS username,
						u.firstname AS firstname,
						u.lastname AS lastname
					FROM `c_videostats` as v
					INNER JOIN `user` as u ON v.user_id = u.user_id
					";
			$res = Database::query($sql);
			while ($rec = Database::fetch_array($res)) {
				$user_view = self::get_video_view_time($rec["view_id"]);	
				$view_date = date("F j, Y, H:i:s", $rec["end"]);
				
				$html .= '<tr>
							<td>'.$rec["user_id"].'</td>
							<td>'.$rec["firstname"].' '.$rec["lastname"].' ('.$rec["username"].')</td>
							<td>'.$rec["video"].'</td>
							<td>'.$rec["user_ip"].'</td>
							<td>'.$view_date.'</td>
							<td>'.$user_view.'</td>
						</tr>';	
			}
		return $html;
	}
	
	function search_user($values){
		
		$html = '';
		
		$start_date = $values['publicated_on']['Y'].'-'.str_pad($values['publicated_on']['F'], 2, 0, STR_PAD_LEFT).'-'.str_pad($values['publicated_on']['d'], 2, 0, STR_PAD_LEFT);
		$new_start_date = new DateTime($start_date);
		$start_date_timestamp = $new_start_date->getTimestamp();
		
		$end_date = $values['expired_on']['Y'].'-'.str_pad($values['expired_on']['F'], 2, 0, STR_PAD_LEFT).'-'.str_pad($values['expired_on']['d'], 2, 0, STR_PAD_LEFT);
		$new_end_date = new DateTime($end_date);
		$end_date_timestamp = $new_end_date->getTimestamp();
		
		$interval = $new_start_date->diff($new_end_date);
		$diff_days = $interval->format('%a');
		
		if(empty($values['keyword']) && intval($diff_days) > 2){
			$html .= Display::display_warning_message('Day Difference greater than 2 days');
		}else{
			
		$html .= '<table class="data_table">';
		$html .= '<tr>
					<th>'.get_lang('UserID').'</th>
					<th>'.get_lang('User').'</th>
					<th>'.get_lang('Video').'</th>
					<th>'.get_lang('UserIP').'</th>
					<th>'.get_lang('Date').'</th>					
					<th>'.get_lang('Duration').'</th>					
				</tr>';
		
		$sql = "SELECT v.id AS view_id,
						v.user_id AS user_id,
						v.video AS video,
						v.user_ip AS user_ip,
						v.end AS end,
						v.start AS start,
						u.username AS username,
						u.firstname AS firstname,
						u.lastname AS lastname
					FROM `c_videostats` as v
					INNER JOIN `user` as u ON v.user_id = u.user_id
					WHERE v.start > ".$start_date_timestamp."  AND v.end < ".$end_date_timestamp;
					
					if(!empty($values['keyword'])){
						$sql .= " AND (v.user_id = '".$values['keyword']."' OR v.user_ip = '".$values['keyword']."' OR u.username LIKE '%".$values['keyword']."%' OR u.firstname LIKE '%".$values['keyword']."%' OR u.lastname LIKE '%".$values['keyword']."%')";
					}
					
			$res = Database::query($sql);
			while ($rec = Database::fetch_array($res)) {
			$user_view = self::get_video_view_time($rec["view_id"]);	
			$view_date = date("F j, Y, H:i:s", $rec["end"]);
				
			$html .= '<tr>
							<td>'.$rec["user_id"].'</td>
							<td>'.$rec["firstname"].' '.$rec["lastname"].' ('.$rec["username"].')</td>
							<td>'.$rec["video"].'</td>
							<td>'.$rec["user_ip"].'</td>
							<td>'.$view_date.'</td>
							<td>'.$user_view.'</td>
						</tr>';	
			}
			$html .= '</table>';
			
		}	
		return $html;			
	}
	
	
}