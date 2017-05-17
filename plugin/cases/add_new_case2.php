<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * Initialization
 */

$course_plugin = 'cases'; //needed in order to load the plugin lang variables
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
$case = new Cases;
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
	/*$group = array();
        foreach ($values as $value) {
            $element = & $form->createElement('radio', 'case_num', '', $value, $value);
  	   $group[] = $element;
          }
	*/
	$form->addElement('select', 'case_num', get_lang('case_num'), $values, '');
	$form->addRule('case_num', get_lang('ThisFieldIsRequired'), 'required');
	
	/*
    $form->addGroup($group, 'case_num', get_lang('CaseNumber'), '', false);
	//$default_values['case_num'] = "1";
	$form->addRule('case_num', get_lang('ThisFieldIsRequired'), 'required');
	*/
	$form->addElement('datepicker', 'case_date', get_lang('case_date'), array('form_name'=>'case_submission'), 5);
	$default_values['case_date'] = date('Y-m-d 08:00:00');
	
	//#2
	$genders = array ('male', 'female');
	$gender_group = array();
        foreach ($genders as $gender) {
            $element = & $form->createElement('radio', 'patient_gender', '', get_lang($gender), $gender);
            $gender_group[] = $element;
        }
        $form->addGroup($gender_group, 'patient_gender', get_lang('patient_gender'), '', false);
	$default_values['patient_gender'] = "male";
	$form->addRule('patient_gender', get_lang('ThisFieldIsRequired'), 'required');
	
	//Patient initials
	$form->addElement('text', 'patient_initials', get_lang('patient_initials'));
	$default_values['patient_initials'] = "Enter your patient's initials";
	$form->addRule('patient_initials', get_lang('ThisFieldIsRequired'), 'required');
        
	
	//#3
	$birth_years = $case->form_birth_year();
	$form->addElement('select', 'birth_year', get_lang('birth_year'), $birth_years, '');
	$form->addRule('birth_year', get_lang('ThisFieldIsRequired'), 'required');
    // $default_values[$row['variable']] = $row['selected_value'];
	
	//#4
	$form->addElement('html', '<h2>'.get_lang('personal_circumstances').'</h2>');
	$form->addElement('textarea','personal_circumstances',get_lang('personal_circumstances'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['personal_circumstances'] = "Please enter your personal circumstances";
	$form->addRule('personal_circumstances', get_lang('ThisFieldIsRequired'), 'required');
	
	//New patient
	$form->addElement('html', '<h1>'.get_lang('new_patient').'<br/></h1>');
	
	//Diagnosis
	$form->addElement('html', '<h2>'.get_lang('Diagnosis').':</h2>');
	$form->addElement('textarea','convetionalMedPrescription',get_lang('convetionalMedPrescription'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('convetionalMedPrescription', get_lang('ThisFieldIsRequired'), 'required');
        $default_values['convetionalMedPrescription'] = "Please enter your conventional Medical Prescriptions";
        $form->addElement('html', '<h3>'.get_lang('LaboratoryTests').':</h3>');
	$form->addElement('textarea','LaboratoryTestsTetx',get_lang('LaboratoryTestsTetx'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addElement('file', 'LaboratoryTestsFile', get_lang('LaboratoryTestsFile'));
    $allowed_file_types = array('zip', 'jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx');
    $form->addRule('LaboratoryTestsFile', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	$form->addElement('textarea','PreviousHomeoMed',get_lang('PreviousHomeoMed'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	
	//Main complaint
	$form->addElement('html', '<h2>'.get_lang('mainComplaint').':</h2>');
	$form->addElement('textarea','complaint_origin',get_lang('complaint_origin'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['complaint_origin'] = "Please enter the Origin of the complaint (since when it appeared)";
        $form->addRule('complaint_origin', get_lang('ThisFieldIsRequired'), 'required');
	
        $form->addElement('textarea','causative_factors',get_lang('causative_factors'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['causative_factors'] = "Please enter any possible causative factors";
	$form->addRule('causative_factors', get_lang('ThisFieldIsRequired'), 'required');
	
        $form->addElement('textarea','modalities',get_lang('modalities'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['modalities'] = "Please enter Modalities (what makes the complaint aggravate or ameliorate)";
	$form->addRule('modalities', get_lang('ThisFieldIsRequired'), 'required');
	
        $form->addElement('textarea','occurrence_time',get_lang('occurrence_time'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['occurrence_time'] = "Please enter the Time of occurrence, aggravation or amelioration";
        $form->addRule('occurrence_time', get_lang('ThisFieldIsRequired'), 'required');
	
        $form->addElement('textarea','body_side',get_lang('body_side'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['body_side'] = "Please enter side of the body";
	$form->addRule('body_side', get_lang('ThisFieldIsRequired'), 'required');

	
 
	$form->addElement('textarea','frequency',get_lang('frequency'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['frequency'] = "Please enter frequency of appearance";
        $form->addRule('frequency', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','pain_desc',get_lang('pain_desc'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['pain_desc'] = "Please enter a description of the pain";
        $form->addRule('pain_desc', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','complaint_extension',get_lang('complaint_extension'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['complaint_extension'] = "Please enter any extension of the complaint to other parts";
	$form->addRule('complaint_extension', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','other_complaints',get_lang('other_complaints'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	
	//#7
	$form->addElement('html', '<h2>'.get_lang('personalMedicalHistory').':</h2>');
	
        $form->addElement('textarea','therapies_vaccinations',get_lang('therapies_vaccinations'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['therapies_vaccinations'] = "Please enter Suppressive therapies and vaccinations";
        $form->addRule('therapies_vaccinations', get_lang('ThisFieldIsRequired'), 'required');
	
        $form->addElement('textarea','traumas',get_lang('traumas'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['traumas'] = "Please enter Traumas (physical, emotional, mental)";
        $form->addRule('traumas', get_lang('ThisFieldIsRequired'), 'required');
	
        $form->addElement('textarea','infectious_diseases',get_lang('infectious_diseases'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['infectious_diseases'] = "Please enter Acute infectious diseases";
        $form->addRule('infectious_diseases', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('textarea','personal_other',get_lang('personal_other'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	
	//#8
	$form->addElement('html', '<h2>'.get_lang('familyMedicalHistory').':</h2>');
	$form->addElement('textarea','family_diseases',get_lang('family_diseases'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['family_diseases'] = "Please enter Diseases that occur in the family";
        $form->addRule('family_diseases', get_lang('ThisFieldIsRequired'), 'required');
	
	//#9
	$form->addElement('html', '<h2>'.get_lang('physicalGenerals').':</h2>');
	$form->addElement('textarea','reactions',get_lang('reactions'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addElement('textarea','sleeping_habits',get_lang('sleeping_habits'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addElement('textarea','food_modalities',get_lang('food_modalities'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addElement('textarea','menstruation',get_lang('menstruation'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	
	//#10
	$form->addElement('html', '<h2>'.get_lang('MentalEmotional').':</h2>');
	$form->addElement('textarea','mental_emotional',get_lang('mental_emotional'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['mental_emotional'] = "Please enter mental and emotional symptoms";
	$form->addRule('mental_emotional', get_lang('ThisFieldIsRequired'), 'required');
	
	//#11
	$form->addElement('html', '<h2>'.get_lang('Analysis').'</h2>');
	
	#11.1
	$form->addElement('html', '<h3>'.get_lang('Prognosis').':</h3>');
	
        /*$form->addElement('textarea','anatomopathological',get_lang('anatomopathological'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('anatomopathological', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('textarea','disturbance',get_lang('disturbance'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('disturbance', get_lang('ThisFieldIsRequired'), 'required');*/
	
        $form->addElement('textarea','personal_history_analysis',get_lang('personalMedicalHistory'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['personal_history_analysis'] = "Please enter personal medical history";
        $form->addRule('personal_history_analysis', get_lang('ThisFieldIsRequired'), 'required');
	
        $form->addElement('textarea','family_history_analysis',get_lang('family_history_analysis'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['family_history_analysis'] = "Please enter family medical history and hereditary predisposition";
        $form->addRule('family_history_analysis', get_lang('ThisFieldIsRequired'), 'required');
	
        $levels = $case->form_levels_of_health();
	$form->addElement('select', 'level_of_health', get_lang('level_of_health'), $levels, '');
	$form->addRule('level_of_health', get_lang('ThisFieldIsRequired'), 'required');
    // $default_values[$row['variable']] = $row['selected_value'];
	$form->addElement('textarea','analysis_conclusion',get_lang('conclusion'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['analysis_conclusion'] = "Please enter Conclusion";
	$form->addRule('analysis_conclusion', get_lang('ThisFieldIsRequired'), 'required');
	
	//#11.2
	$form->addElement('html', '<h3>'.get_lang('symptomsSelection').':</h3>');
	$form->addElement('textarea','peculiar_symptoms',get_lang('peculiar_symptoms'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['peculiar_symptoms'] = "Please enter Peculiar symptoms, Rank the intensity of each symptom in scale from 1 (lowest) to 4 (highest)";
       	$form->addRule('peculiar_symptoms', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','intense_symptoms',get_lang('intense_symptoms'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['intense_symptoms'] = "Please enter Intense symptoms, Rank the intensity of each symptom in scale from 1 (lowest) to 4 (highest)";
        $form->addRule('intense_symptoms', get_lang('ThisFieldIsRequired'), 'required');
	
	//#11.3
	$form->addElement('html', '<h3>'.get_lang('Repertorization').':</h3>');
	$form->addElement('textarea','repertorization',get_lang('repertorization'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addElement('file', 'repertorization_file', get_lang('repertorization_file'));
    $allowed_file_types = array('zip', 'jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx');
    $form->addRule('repertorization_file', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	
	//#11.4
	$form->addElement('html', '<h3>'.get_lang('remedies_differentiantion').':</h3>');
	$form->addElement('textarea','remedies_differentiantion',get_lang('remedies_differentiantion'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['remedies_differentiantion'] = "Please enter Differentiation of the remedies";
	$form->addRule('remedies_differentiantion', get_lang('ThisFieldIsRequired'), 'required');
	
	
	//#11.5
	$form->addElement('html', '<h3>'.get_lang('Prescription').':</h3>');
	$form->addElement('text', 'remedy', get_lang('remedy'));
        $default_values['remedy'] = "Please enter remedy";
	$form->addRule('remedy', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('text', 'potency', get_lang('potency'));
        $default_values['potency'] = "Please enter potency";
	$form->addRule('potency', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','regimen',get_lang('regimen'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['regimen'] = "Please enter regimen";
	$form->addRule('regimen', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','other_notes',get_lang('other_notes'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['other_notes'] = "Please enter other notes";
	$form->addRule('other_notes', get_lang('ThisFieldIsRequired'), 'required');
	
	//Followup
	$form->addElement('html', '<h1>'.get_lang('FollowupNum').'1<br/></h1>');

	$form->addElement('datepicker', 'followup_date', get_lang('followup_date'), array('form_name'=>'case_submission'), 5);
	$default_values['followup_date'] = date('Y-m-d 08:00:00');
	
	$form->addElement('textarea','followup_convetionalMedPrescription',get_lang('convetionalMedPrescription'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['followup_convetionalMedPrescription'] = "Please enter conventional medical prescriptions";
	$form->addRule('followup_convetionalMedPrescription', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('html', '<h3>'.get_lang('LaboratoryTests').':</h3>');
	$form->addElement('textarea','followup_LaboratoryTestsTetx',get_lang('LaboratoryTestsTetx'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addElement('file', 'followup_LaboratoryTestsFile', get_lang('LaboratoryTestsFile'));
    $allowed_file_types = array('zip','jpeg','jpg','png','gif','pdf','doc','docx');
    $form->addRule('followup_LaboratoryTestsFile', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	
	$form->addElement('textarea','followup_reaction',get_lang('followup_reaction'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('followup_reaction', get_lang('ThisFieldIsRequired'), 'required');
        $default_values['followup_reaction'] = "Please enter Reaction to the prescription of the former consultation";

	$form->addElement('textarea','followup_analysis',get_lang('followup_analysis'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['followup_analysis'] = "Please enter Analysis of the reaction";
        $form->addRule('followup_analysis', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','followup_repertorization',get_lang('followup_repertorization'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['followup_repertorization'] = "Please enter repertorization";
	$form->addRule('followup_repertorization', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('file', 'followup_repertorization_file', get_lang('followup_repertorization_file'));
    $form->addRule('followup_repertorization_file', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);

	$form->addElement('textarea','followup_conclusion',get_lang('conclusion'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['followup_conclusion'] = "Please enter followup conclusion";
	$form->addRule('followup_conclusion', get_lang('ThisFieldIsRequired'), 'required');
	
	$form->addElement('html', '<h2>'.get_lang('Prescription').':</h2>');

	$form->addElement('text', 'followup_remedy', get_lang('remedy'));
        $default_values['followup_remedy'] = "Please enter follow up remedy";
        $form->addRule('followup_remedy', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('text', 'followup_potency', get_lang('potency'));
	$default_values['followup_potency'] = "Please enter potency";
	$form->addRule('followup_potency', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','followup_regimen',get_lang('regimen'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['followup_regimen'] = "Please enter regimen";
	$form->addRule('followup_regimen', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('textarea','followup_other_notes',get_lang('other_notes'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
        $default_values['followup_other_notes'] = "Please enter other followup notes";
	$form->addRule('followup_other_notes', get_lang('ThisFieldIsRequired'), 'required');
	
	$form->addElement('style_submit_button', 'submit', get_lang('SaveDraft'), 'class="save"');

	$form->setDefaults($default_values);
    if ($form->validate()) {
		$texts = $form->exportValues();
		
		$values = array();
		foreach($texts as $key=>$text){
				
			$new_text = htmlentities($text, ENT_QUOTES, "utf-8");	
			$values[$key] = $new_text;
				
		}
		
		$values['followup_rep_file'] = '';
		$values['rep_file'] = '';
		$values['lab_file'] = '';
		$values['followup_lab_file'] = '';
		
		if(!empty($_FILES['followup_repertorization_file']['size'])){
				$values['followup_rep_file'] = $case->save_file('followup_repertorization_file', $user_id);
			}
		if(!empty($_FILES['repertorization_file']['size'])){
				$values['rep_file'] = $case->save_file('repertorization_file', $user_id);
		}
		if(!empty($_FILES['followup_LaboratoryTestsFile']['size'])){
				$values['followup_lab_file'] = $case->save_file('followup_LaboratoryTestsFile', $user_id);
			}
		if(!empty($_FILES['LaboratoryTestsFile']['size'])){
				$values['lab_file'] = $case->save_file('LaboratoryTestsFile', $user_id);
		}
		
		$caseIDs = array();
		$sql = "SELECT `caseID` FROM `c_cases` WHERE `user_id` = ".$user_id." AND `cid` = ".$course_id." AND `session_id` = ".$session_id;
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
		// Display form
		$form->display();
	}
?>	
	<p>Click the "Try it" button to display the value of each element in the form.</p>

	<button onclick="myFunction()">Try it</button>

	<p id="demo"></p>

<script>
function myFunction() {
    var x = document.getElementById("case_submission");
    var txt = "";
    var i;
    for (i = 0; i < x.length; i++) {
        txt = txt + x.elements[i].name + "=" + x.elements[i].value + "<br>";
    }
    document.getElementById("demo").innerHTML = txt;
}
</script>

<?php
Display :: display_footer();

