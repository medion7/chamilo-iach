<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * Initialization
 */

$course_plugin = 'casestest'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
include_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileDisplay.lib.php';
require_once 'lib/cases.lib.php';
$session_id = intval($_REQUEST['id_session']);
$course_info = api_get_course_info(api_get_course_id());
$course_id = $course_info['real_id'];
$tool_name = get_lang('<a href="start.php?'.api_get_cidreq().'">'.get_lang('Case Study Submission Tool').'</a> / Add New Case');
$tpl = new Template($tool_name);
$user_id = api_get_user_id();
$case = new CasesTest;
// Setting the tabs
$this_section = SECTION_COURSES;

$htmlHeadXtra[] = api_get_jqgrid_js();

// Access control
api_protect_course_script(true, false, true);

require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';

// document path
$documentPath = api_get_path(SYS_COURSE_PATH).$_course['path']."/document";

/* 	Constants and variables */
$is_allowedToEdit = api_is_allowed_to_edit(null, true) || api_is_drh();
$is_tutor = api_is_allowed_to_edit(true);

$course_id = api_get_course_int_id();

echo Display::display_header($tool_name);

echo '<div class="actions">';
echo Display::return_icon('new_folder.png', get_lang('AddNewCase'), array(), 32);
echo Display::url(Display::return_icon('folder_na.png', get_lang('MyCases'), array(), 32), api_get_path(WEB_ROOT).'start.php?'.api_get_cidreq());
echo '</div>';


	$config = array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '100%', 'Height' => '120');
	$form = new FormValidator('case_submission', 'post', api_get_self().'?'.api_get_cidreq());
	
	//#1
	$values = array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
	$group = array();
        foreach ($values as $value) {
            $element = & $form->createElement('radio', 'case_num', '', $value, $value);
  	   $group[] = $element;
          }
		  
	$form->addGroup($group, 'case_num', get_lang('CaseNumber'), '', false);
	//$default_values['case_num'] = "1";
	$form->addRule('case_num', get_lang('ThisFieldIsRequired'), 'required');
	
	$form->addElement('style_submit_button', 'submit', get_lang('Continue'), 'class="save"');

    $form->setDefaults($default_values);
	
	// Check if user has already submitted a case
	$sql_userCases = "SELECT `id` FROM `c_casestest` WHERE `user_id` = ".$user_id." AND `cid` = ".$course_id." AND `session_id` = ".$session_id;
	$res_userCases = Database::query($sql_userCases);
	
	if ($form->validate()) {
		$texts = $form->exportValues();
		
		$values = array();
		foreach($texts as $key=>$text){
				
			$new_text = htmlentities($text, ENT_QUOTES, "utf-8");	
			$values[$key] = $new_text;
				
		}
		
		$caseIDs = array();
		$sql = "SELECT `caseID` FROM `c_casestest` WHERE `user_id` = ".$user_id." AND `cid` = ".$course_id." AND `session_id` = ".$session_id;
		$res = Database::query($sql);
		while ($caseID = Database::fetch_array($res)) { 
			$caseIDs[] = $caseID['caseID'];
		}
		
		if(in_array($values['case_num'], $caseIDs)){
			Display::display_warning_message(get_lang('CaseIDExists'));
			$form->display();
		}else{
			echo '<script type="text/javascript">window.location.href="case.php?action=saved&'.api_get_cidreq().'&case='.$case->save_case($values).'"</script>';
		}
		$form->freeze();
	}else{
//		Database::num_rows($res_userCases) == 0
		
		if(!isset($_GET['terms']) && empty($_POST)){
			echo get_lang('Terms');
			
			$form_terms = new FormValidator('case_submission', 'get', api_get_self().'?'.api_get_cidreq());
			
			$form_terms->addElement('checkbox', 'terms', get_lang('AcceptTerms'));
			
			$form_terms->addElement('style_submit_button', 'submit', get_lang('Continue'), 'class="save"');
			
			$form_terms->display();
			
		}else{
			// Display form
			$form->display();
		}
	}

Display :: display_footer();
