<?php
/* For licensing terms, see /license.txt */

/**
 *  @package chamilo.admin
 */

// Language files that need to be included.
$language_file = array('admin');

$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'usergroup.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

$this_section = SECTION_PLATFORM_ADMIN;
$user_id = $_user['user_id'];
api_protect_admin_script(true);

$tbl_user				= Database::get_main_table(TABLE_MAIN_USER);
$tbl_usergroup_rel_user	= Database::get_main_table(TABLE_USERGROUP_REL_USER);
$user_field_values		= Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
$usergroup 				= Database::get_main_table(TABLE_USERGROUP);
$tbl_template 			= '`iach_main`.`usergroup_mail_template`';



function class_name($id){
	$sql = "SELECT name FROM usergroup
			WHERE id='".$id."'";
	$result = Database::query($sql);
	while ($row = Database::fetch_array($result)) {	
	$usergroup_name = $row['name'];
	}
	return $usergroup_name;
}


//Add the JS needed to use the jqgrid
$htmlHeadXtra[] = api_get_jquery_ui_js();
// setting breadcrumbs
$interbreadcrumb[]=array('url' => 'index.php','name' => get_lang('PlatformAdmin'));
$action = $_GET['action'];
  if ($action == 'send_mail') {
    $interbreadcrumb[]=array('url' => 'usergroups.php','name' => get_lang('Classes'));  
	$interbreadcrumb[]=array('url' => 'usergroups.php','name' => class_name($_GET['id']));
    $interbreadcrumb[]=array('url' => '#','name' => 'New mail');
} elseif ($action == 'send') {
    $interbreadcrumb[]=array('url' => 'usergroups.php','name' => get_lang('Classes')); 
	$interbreadcrumb[]=array('url' => 'usergroups.php','name' => class_name($_GET['id']));	
    $interbreadcrumb[]=array('url' => '#','name' => 'New mail');
} elseif ($action == 'confirm') {
    $interbreadcrumb[]=array('url' => 'usergroups.php','name' => get_lang('Classes')); 
	$interbreadcrumb[]=array('url' => 'usergroups.php','name' => class_name($_GET['id']));	
    $interbreadcrumb[]=array('url' => '#','name' => 'New mail');
}
// The header.
Display::display_header($tool_name);

// Tool name

if (isset($_GET['action']) && $_GET['action'] == 'send_mail') {
    $tool = 'Send Mail';
    $interbreadcrumb[] = array ('url' => api_get_self(), 'name' => get_lang('Group'));
}
if (isset($_GET['action']) && $_GET['action'] == 'send') {
    $tool = 'Send Mail';
    $interbreadcrumb[] = array ('url' => api_get_self(), 'name' => get_lang('Group'));
}
if (isset($_GET['action']) && $_GET['action'] == 'confirm') {
    $tool = 'Send Mail';
    $interbreadcrumb[] = array ('url' => api_get_self(), 'name' => get_lang('Group'));
}

//jqgrid will use this URL to do the selects

$url            = api_get_path(WEB_AJAX_PATH).'model.ajax.php?a=get_usergroups';
?>
<script>
$(function() {
<?php 
    // grid definition see the $usergroup>display() function
    echo Display::grid_js('usergroups',  $url,$columns,$column_model,$extra_params, array(), $action_links,true);       
?> 
});

function back()
  {
  window.location.assign("usergroups.php?")
  }
</script>   
<?php
// Tool introduction
Display::display_introduction_section(get_lang('Classes'));

$usergroup = new UserGroup();


if (isset($_GET['action']) && $_GET['action'] == 'confirm' && is_numeric($_GET['id'])) {

//$_POST values
$sender_name = $_POST['sender_name'];
$username = $_POST['username'];
$emailsubject = $_POST['emailsubject'];
$receiver_name = $_POST['receiver_name'];
$mail_temp = $_POST['mail_temp'];
$content = $_POST['content'];
$pass = $_POST['pass_msg'];
$instructions = $_POST['instructions'];
$signature = $_POST['signature'];

//Class id
$id = $_GET['id'];

//Mail subject
$subject = '<p style="font-size: 11pt; color: #00000; font-family: arial;">'.$emailsubject.'</p>';

//Receiver if statement
$rec_body='';
if($receiver_name = '1'){
	$rec_body = '<tr><td>Dear (firstname) (lastname)</td></td>';
}

//Template if statement
if($mail_temp != '0'){
	$sql_temp = ("SELECT text FROM $tbl_template WHERE template_id='".$mail_temp."'");
	$res_temp = Database::query($sql_temp);
		while($temp = Database::fetch_array($res_temp)){
			$temp_body = $temp['text']; 
			}
}else {
	$temp_body = '';
}

//Password message if statement
$pass_body = '';
$pass_body1 = '';
$pass_body2 = '';
if($pass == 1){
	$sql_pass1 = ("SELECT text FROM $tbl_template WHERE status='pass' AND title='Password1' AND lang_id='".api_get_language_id($language_interface)."'");
	$res_pass1 = Database::query($sql_pass1);
		while($pass1 = Database::fetch_array($res_pass1)){
			$pass_body1 = $pass1['text']; }
			
	$sql_pass2 = ("SELECT text FROM $tbl_template WHERE status='pass' AND title='Password2' AND lang_id='".api_get_language_id($language_interface)."'");
	$res_pass2 = Database::query($sql_pass2);
		while($pass2 = Database::fetch_array($res_pass2)){
			$pass_body2 = $pass2['text']; }
	$pass_body = $pass_body1.'<p style="font-size: 11pt; color: #00000; font-family: arial;">Login: username<br/>P a s s : password</p>'.$pass_body2;
}

//Instructions message if statement
$instr_body='';
if($instructions == 1){
	$sql_instr = ("SELECT text FROM $tbl_template WHERE status='instructions' AND lang_id='".api_get_language_id($language_interface)."'");
	$res_instr = Database::query($sql_instr);
		while($instr = Database::fetch_array($res_instr)){
			$instr_body = "<br/>".$instr['text']."<br/>"; }
}

//Signature message if statement
$sign_body='';
if($signature == 1){
	$sql_sign = ("SELECT field_value FROM $user_field_values WHERE field_id=23");
	$res_sign = Database::query($sql_sign);
		while($sign = Database::fetch_array($res_sign)){
			$sign_body = $sign['field_value']; }
}

$confirm_mail = '<table border="0" align="left" style="font-size: 11pt; color: #00000; font-family: arial;">'.$subject.$rec_body.$temp_body.$content.$pass_body.$instr_body.$sign_body.'</table>';
$onclick = 'document.location.href="usergroups.php?"';
echo '<div class="row"><div class="formw">';
echo '<table width="700" align="center" border="0">';
echo '<tr height="5%">';
echo '<td valign="top" colspan="2" bgcolor="fffff">';
echo  $confirm_mail;
echo  '</td></tr>';
echo '<tr width="5%" valign="top"><td>';
$form = new FormValidator('confirm_mail', 'post', api_get_self().'?action=send&id='.$id);
$form->addElement('hidden', 'sender_name', $sender_name);
$form->addElement('hidden', 'username', $username);
$form->addElement('hidden', 'emailsubject', $emailsubject);
$form->addElement('hidden', 'receiver_name', $receiver_name);
$form->addElement('hidden', 'content', $content);
$form->addElement('hidden', 'mail_temp', $mail_temp);
$form->addElement('hidden', 'pass', $pass);
$form->addElement('hidden', 'instr_body', $instr_body);
$form->addElement('hidden', 'sign_body', $sign_body);
$form->addElement('style_submit_button', 'submit', get_lang('Send'), 'class="save"');

// Display form
$form->display();
echo '</td>';
echo '<td valign="top">';
echo '<button type="button" class="save" onclick="back()">Cancel</button>';
echo '</td></tr>';
echo  '</table>';
echo '</div></div>';
}

if (isset($_GET['action']) && $_GET['action'] == 'send' && is_numeric($_GET['id'])) {

$sender_name = $_POST['sender_name'];
$username = $_POST['username'];
$emailsubject = $_POST['emailsubject'];
$receiver_name = $_POST['receiver_name'];
$mail_temp = $_POST['mail_temp'];
$content = $_POST['content'];
$pass = $_POST['pass'];
$instr_body = $_POST['instr_body'];
$sign_body = $_POST['sign_body'];
$id = $_GET['id'];
//$new_password = $_POST['new_password'];
$signature = $_POST['signature'];
$confirm_mail = "<html><head></head><body><p>Dear (user's firstname)(user's lastname),</p>".$content."<p>".$signature."</p></body></html>";

$headers = "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=UTF-8\n";
$headers .= "From: International Academy of Classical Homeopathy <".$username."@vithoulkas.edu.gr>\n";
$headers .= "Bcc:medion7bcc@gmail.com\n";
   
$sql = "SELECT user.lastname, user.firstname, user.email, user.username, user.user_id
		FROM usergroup_rel_user 
		INNER JOIN  $tbl_user user ON user.user_id = usergroup_rel_user.user_id
		WHERE usergroup_rel_user.usergroup_id = '$id'";
		$result = Database::query($sql);
		while ($row = Database::fetch_array($result)) {				
				$receiver =  $row['firstname']." ".$row['lastname'];
				$rec_email = $row['email'];
				$urname = $row['username'];
				$user_id = $row['user_id'];
				
				//Template if statement
				if($mail_temp != '0'){
				$sql_temp = ("SELECT text FROM $tbl_template WHERE template_id='".$mail_temp."'");
				$res_temp = Database::query($sql_temp);
				while($temp = Database::fetch_array($res_temp)){
					$temp_body = $temp['text']; 
					}
				}else {
					$temp_body = '';
				}
				
				//Receiver if statement
				$rec_body='';
				if($receiver_name = '1'){
					$rec_body = '<div style="font-size: 11pt; color: #00000; font-family: arial;">Dear '.$receiver.',</div>';
				}
				
				//Password message if statement
				$pass_body = '';
				$pass_body1 = '';
				$pass_body2 = '';
				if($pass == 1){
					$sql_pass1 = ("SELECT text FROM $tbl_template WHERE status='pass' AND title='Password1' AND lang_id='".api_get_language_id($language_interface)."'");
					$res_pass1 = Database::query($sql_pass1);
					while($pass1 = Database::fetch_array($res_pass1)){
						$pass_body1 = $pass1['text']; }
			
					$sql_pass2 = ("SELECT text FROM $tbl_template WHERE status='pass' AND title='Password2' AND lang_id='".api_get_language_id($language_interface)."'");
					$res_pass2 = Database::query($sql_pass2);
					while($pass2 = Database::fetch_array($res_pass2)){
						$pass_body2 = $pass2['text']; }
						
					$pass_body = $pass_body1.'<p style="font-size: 11pt; color: #00000; font-family: arial;">Login: '.$urname.'<br/>P a s s : '.$urname.'1</p>'.$pass_body2;
}
				
				$message = '<html><head></head><body>'.$rec_body.'<table border="0" align="top" style="font-size: 11pt; color: #00000; font-family: arial;">'.$temp_body.$content.$pass_body.$instr_body.$sign_body.'</table></body></html>';
				mail($rec_email, $emailsubject, $message, $headers);
				
		} 
		Display::display_confirmation_message('Email sent');
}

// Action handling: Send new mail
if (isset($_GET['action']) && $_GET['action'] == 'send_mail' && is_numeric($_GET['id'])) {


$sql = "SELECT $tbl_user.username, $tbl_user.lastname, $tbl_user.firstname, $user_field_values.field_value
				FROM $tbl_user
				INNER JOIN $user_field_values ON $tbl_user.user_id = $user_field_values.user_id
				WHERE $tbl_user.user_id = '$user_id' AND $user_field_values.field_id = '23'";
	
		$result = Database::query($sql);
		if (Database::num_rows($result) > 0) {
			while ($row = Database::fetch_array($result)) {
				$sender_name =  $row['firstname']." ".$row['lastname'];
				$username = $row['username'];
				$signature = $row['field_value'];
			} }
			
			
// Create the form
$form = new FormValidator('send_mail', 'post', api_get_self().'?action=confirm&id='.$_GET['id']);
$form->addElement('header', 'New email to class: '.class_name($_GET['id']));

// Sender name
$form->addElement('hidden', 'sender_name', $sender_name);

// Sender username
$form->addElement('hidden', 'username', $username);


// Subject
$form->addElement('text', 'emailsubject', get_lang('MailSubject'), array('size'=>35));
$form->applyFilter('emailsubject', 'html_filter');
$form->applyFilter('emailsubject', 'trim');
$form->addRule('emailsubject', get_lang('ThisFieldIsRequired'), 'required');

// Dear (receiver name)
$form->addElement('checkbox', 'receiver_name', get_lang('IncludeReceiverName'), '' );
$form->setDefaults(array('receiver_name' => '1'));

// Mail content templates
$lang_id = api_get_language_id($language_interface);

$temp_arr = array();
$temp_arr[0]='No template';
$sql_temp = "SELECT template_id, title FROM $tbl_template WHERE status='content' AND lang_id=".$lang_id." ORDER BY template_id ASC";
	
		$res_temp = Database::query($sql_temp);
		while($temp = Database::fetch_array($res_temp)){
		$temp_id = $temp['template_id'];
		$temp_title = $temp['title'];
		$temp_arr[$temp_id] = $temp_title;
		}

$form->addElement('select', 'mail_temp', get_lang('SelectTemplate'), $temp_arr);

// Text
$form->add_html_editor('content', get_lang('MailText'), false, false, array('Width' => '95%', 'Height' => '250'));

// Password message
$form->addElement('checkbox', 'pass_msg', null, get_lang('PasswordMsg'));

// Insruction message
$form->addElement('checkbox', 'instructions', null, get_lang('InstructionMesg'));

// Signature
$form->addElement('checkbox', 'signature', null, get_lang('Signature'));
$form->setDefaults(array('signature' => '1'));
 
// Submit button
$form->addElement('style_submit_button', 'submit', get_lang('Confirm'), 'class="save"');

$form->display();

}

Display :: display_footer();
