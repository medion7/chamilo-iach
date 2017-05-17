<?php

$servername = "localhost";
$username = "newv";
$password = "oAfakG4oisNOAkEQ";
$dbname = "iach_main";

$conn = new mysqli($servername, $username, $password, $dbname);	

$new_sessionid = '';

//Get current_sessions.txt file modification time
$last_modified = filemtime('/var/tmp/current_sessions.txt');

//Current time
$c_time = time();

//echo ($last_modified - $c_time);

// Log entire incoming request to see what we have in it.
$fp = fopen('/var/tmp/request-demo.xml', 'w');
fwrite($fp, $HTTP_RAW_POST_DATA);
fclose($fp);

//Get xml code
$xml= simplexml_load_file('/var/tmp/request-demo.xml', 'SimpleXMLElement', LIBXML_NOCDATA);


if(!empty($xml->PayPerViewInfo)){

$new_sessionids = array();
$current_sessionids = array();
//Get current sessionids
$lines = file('/var/tmp/current_sessions.txt');
foreach($lines as $line){
		$new_id = substr($line, 0, -1); 
		$current_sessionids[] = $new_id;
}
/*echo gettype($current_sessionids).'<br/>';
print_r($current_sessionids);
foreach($current_sessionids as $id){
		echo $id;
}*/

foreach($xml->PayPerViewInfo->VHost->Application->Instance->Stream as $stream) {
	
	$video_info = explode("/", $stream->name);
	//$course_code = $video_info[1];
	$video = $video_info[2];
	$users = $stream->Player;
	//$v_users = count($users);
	
    foreach($stream->Player as $user) {
	
		$user_info = explode("-", $user->id);
		$user_id = $user_info[0];
		$c_code= $user_info[1];
		$user_ip = $user->ip;
		$sessionid = substr($user->sessionid, 0, -1);
		//echo $user->sessionid.' '.gettype($sessionid)."\n";
		
		//Check if sessionid is new

		if(!in_array($sessionid, $current_sessionids)){
		
			//echo $sessionid.' Session does exist <br/>';
			
			$sql = "INSERT INTO `c_videostats` (session_id, user_id, user_ip, video, c_code, start, state)
					VALUES (".$sessionid.", ".$user_id.", '".$user_ip."', '".$video."', '".$c_code."', ".$c_time.", 'active')";
					
			mysqli_query($conn, $sql);
			
		}
		
		//Insert sessionid to new_sessions array
		$new_sessionid .= $sessionid."\n";
		$new_sessionids[] = $sessionid;
		
	}
} 
/*echo '<br/>';
print_r($new_sessionids);
foreach($new_sessionids as $nid){
		echo $nid;
}*/
//Find the closed sessions
//Compare current and new session arrays


$closed = array_diff($current_sessionids, $new_sessionids);
//echo '<br/>';
//print_r($closed);

foreach($closed as $key => $value){
	
		$sql2 = "UPDATE `c_videostats` SET `end` = '".$c_time."', `state` = 'closed' WHERE `session_id` = ".$value;
		mysqli_query($conn, $sql2);
	}



//Save new current_sessionids.txt
$file = '/var/tmp/current_sessions.txt';
file_put_contents($file, $new_sessionid);
}elseif(($c_time-$last_modified) > 120){
	
	$sql2 = "UPDATE `c_videostats` SET `end` = '".$c_time."', `state` = 'closed' WHERE `state` = 'active'";
	mysqli_query($conn, $sql2);
	
	$file = '/var/tmp/current_sessions.txt';
	$content = '';
	file_put_contents($file, $content);
	
}else{
	
}
?>