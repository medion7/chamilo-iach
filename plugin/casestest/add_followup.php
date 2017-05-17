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
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'lib/cases.lib.php';
//$tool_name = get_lang('Case Study Submission Tool');
$tpl = new Template($tool_name);
$case_id = intval($_GET['case']);
$user_id = api_get_user_id();
$course_info = api_get_course_info(api_get_course_id());
$course_id = $course_info['real_id'];
$case = new CasesTest;

$case_db = $case->get_c_case_data($case_id);
$default_values = array();
$xml= simplexml_load_file('xml/'.$case_db['file'].'.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
$birth_year = $case->age2birthyear($case_db['submission_date'], $xml->patient->age);

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

echo Display::display_header('<a href="start.php?'.api_get_cidreq().'">'.get_lang('Case Study Submission Tool').'</a> / Case #'.$case_db['caseID'].' / Add Followup');
echo '<div class="actions">';
echo Display::return_icon('folder.png', get_lang('MyCases'), array(), 32);
echo Display::url(Display::return_icon('new_folder_na.png', get_lang('SubmitNewCase'), array(), 32), api_get_path(WEB_ROOT).'add_new_case.php?'.api_get_cidreq());
echo '</div>';
	
echo Display::page_subheader(Display::return_icon('folder.png', get_lang('Session'), array(), ICON_SIZE_SMALL).' '.get_lang('MyCases'));

if(isset($_GET['action']) && $_GET['action'] == 'submit'){
		$case->add_followup($case_id, $_POST['followup_num']);
		Display::display_confirmation_message('Your case has been saved as Draft');
		echo 'Click <a href="case.php?case='.$case_id.'&'.api_get_cidreq().'">here</a> to edit your case or submit it, or <a href="start.php?'.api_get_cidreq().'">go back</a> to first page';	
}else{

if((api_is_allowed_to_edit()!=1 || api_is_allowed_to_edit()==1) && ($case_db['state'] == 'Draft' || $case_db['state'] == 'Review')){
	

	if($case_db['state'] == 'Review'){
	echo '<h2>Teacher\'s Comments and Mark</h2>';	
	echo '<p><strong>Comments:</strong> '.$case_db['comment'].'</p>';	
	echo '<p><strong>Mark:</strong> '.$case_db['mark'].'</p>';	
	}
	
$i = 1;
foreach($xml->followups->followup as $followup){
	$i++;
}
	$case->add_followup($case_id, $i);
	echo '<script type="text/javascript">window.location.href="case.php?'.api_get_cidreq().'&case='.$case_id.'#fu'.$i.'"</script>';
}
}
Display :: display_footer();
