<?php

require_once 'main/inc/global.inc.php';

if(!isset($_GET['action'])){
?>


<form name="theory_bibliography" action="theory_bibliography.php?action=save_step1" method="post">
<select name="video_id">
<?php

$sql1 = "SELECT video_id, title_trans, title_video_num
		FROM iach_main.course_lp_video WHERE course_code IN ('0001', '0002')";
$res1 = $res1 = Database::query($sql1);
		while ($row1 = mysql_fetch_array($res1)) { 
			$video_id = $row1['video_id'];
			$title = $row1['title_tans'].$row1['title_video_num'];
			echo '<option value='.$video_id.'>'.$title.'</option>';
			}
?>
</select>
Header: <input type="text" name="header">
<select name="lang">
<option value="en">English</option>
<option value="es">Spanish</option>
</select>
<input type="hidden" name="video_title" value="<?php echo $title; ?>">
<input type="submit" value="Save header">
</form>

<?php
}

elseif(isset($_GET['action']) && $_GET['action']=='save_step1'){
$video_id = $_POST['video_id'];
$video_title = $_POST['video_title'];
$header = $_POST['header'];
$lang = $_POST['lang'];

$sql3 = 'INSERT INTO iach_main.bibliography_theory (video_id, lang, title) 
		VALUES ('.$video_id.', "'.$lang.'", "'.$header.'")';
$res3= Database::query($sql3);
		
$bibliography_id = mysql_insert_id();

echo 'Video: '.$video_title.'<br/>Header: '.$header.'<br/>';

?>

<form name="reference" action="theory_bibliography.php?action=save_step2" method="post">

<?php	
	
$sql2 = "SELECT book_id, title
		FROM iach_main.bibliography_theory_books";
$res2 = Database::query($sql2);
		while ($row2 = mysql_fetch_array($res2)) { 
			$book_id = $row2['book_id'];
			$book_title = $row2['title'];
			echo $book_title.': <textarea name="content_'.$book_id.'"></textarea><br/>';
			}
?>
<input type="hidden" name="<?php echo $bibliography_id; ?>" value="<?php echo $bibliography_id; ?>">
<input type="submit" value="Save header">
</form>

<?php
}
elseif(isset($_GET['action']) && $_GET['action']=='save_step2'){

$bibliography_id = $_POST['bibliography_id'];

$sql4 = "SELECT book_id
		FROM iach_main.bibliography_theory_books";
$res4 = Database::query($sql4);
while ($row4 = mysql_fetch_array($res4)) { 
			$book_id = $row4['book_id'];
			if(isset($_POST['content_'.$book_id])){
			$sql5 = 'INSERT INTO iach_main.bibliography_theory_ref (book_id, bibliography_id, content) 
					VALUES ('.$book_id.', "'.$bibliography_id.'", "'.$_POST["content_".$book_id].'")';
			$res5 = Database::query($sql5);
			}
		}
	}
?>
