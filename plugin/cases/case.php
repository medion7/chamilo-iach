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
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'lib/cases.lib.php';
//$tool_name = get_lang('Case Study Submission Tool');
$tpl = new Template($tool_name);
$case_id = intval($_GET['case']);
$user_id = api_get_user_id();
$course_info = api_get_course_info(api_get_course_id());
$course_id = $course_info['real_id'];
$case = new Cases;

$htmlHeadXtra[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
					<script type="text/javascript" src="https://code.jquery.com/jquery-1.9.1.js"></script>';


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
if (api_is_allowed_to_edit()==1 || (api_is_allowed_to_edit()!=1 && $user_id == $case_db['user_id'] && ($case_db['state'] == 'Approved'  || $case_db['state'] == 'Submitted'))) {
//$case = new Cases;
//echo $case->get_number_of_user_cases();

if(api_is_allowed_to_edit()==1){
	echo Display::display_header('<a href="start.php?'.api_get_cidreq().'">'.get_lang('CaseSabmissionTool').'</a> / Case #'.$case_db['caseID']);
	echo '<div class="actions">';
	echo Display::return_icon('folder.png', get_lang('SubmittedCases'), array(), 32);
	echo Display::url(Display::return_icon('user_na.png', get_lang('CasesPerUser'), array(), 32), api_get_path(WEB_ROOT).'cases_of_users.php?'.api_get_cidreq());
	echo '</div>';
	
	echo Display::page_subheader(Display::return_icon('folder.png', get_lang('UserCase'), array(), ICON_SIZE_SMALL).' '.get_lang('SubmittedCases'));
}elseif(api_is_allowed_to_edit()!=1){
	echo Display::display_header('<a href="start.php?'.api_get_cidreq().'">'.get_lang('Case Study Submission Tool').'</a> / Case #'.$case_db['caseID']);
	echo '<div class="actions">';
	echo Display::return_icon('folder.png', get_lang('MyCases'), array(), 32);
	echo Display::url(Display::return_icon('new_folder_na.png', get_lang('SubmitNewCase'), array(), 32), api_get_path(WEB_ROOT).'add_new_case.php?'.api_get_cidreq());
	echo '</div>';
	
	echo Display::page_subheader(Display::return_icon('folder.png', get_lang('Session'), array(), ICON_SIZE_SMALL).' '.get_lang('MyCases'));	
}


if (isset($_GET['action']) && $_GET['action'] == 'teacher_comment') {
		Display::display_confirmation_message(get_lang('CommentMarkSaved'));
	}
	
if(api_is_allowed_to_edit()!=1 || (api_is_allowed_to_edit()==1 && $case_db['state'] == 'Approved')){
	echo '<h2>'.get_lang('TeacherCommentsMark').'</h2>';	
	echo '<p><strong>'.get_lang('Comments').':</strong> '.$case_db['comment'].'</p>';		
}

$c_date = new DateTime($xml->patient->case_date);
$new_c_date = $c_date->format('d-m-Y H:i:s');

if(api_is_allowed_to_edit()==1 && $case_db['state'] == 'Submitted'){
	$config = array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '100%', 'Height' => '120');
	$form = new FormValidator('comment_case', 'post', api_get_self().'?case='.$case_id.'&'.api_get_cidreq());
	$form->add_html_editor('comment', get_lang('addComment'), true, false, $config);
	$default_values['comment'] = $case_db['comment'];
	$values = array ('Review', 'Approved');
	$group = array();
        foreach ($values as $value) {
           $element = & $form->createElement('radio', 'state', '', get_lang($value), $value);
           $group[] = $element;
        }
    $form->addGroup($group, 'state', get_lang('State'), '', false);
	$default_values['state'] = $case_db['state'];
	$form->addRule('state', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('style_submit_button', 'submit', get_lang('Save'), 'class="save"');
	$form->setDefaults($default_values);
	if ($form->validate()) {
		$values = $form->exportValues();
		
		$form->freeze();
		
		$case->teacher_comment($case_id, $values['state'], $values['comment']);
		echo '<script type="text/javascript">window.location.href="case.php?'.api_get_cidreq().'&case='.$case_id.'&action=teacher_comment"</script>';
		
		// Display form
	}else{
		$form->display();
	}
}

echo '<h2>Case information</h2>
		<p><strong>'.get_lang('CaseNumber').':</strong> '.$case_db["caseID"].'<br/>
		<p><strong>'.get_lang('case_date').':</strong> '.$new_c_date.'<br/>
		<strong>'.get_lang('patient_gender').':</strong> '.$xml->patient->gender.'<br/>
		<strong>'.get_lang('patient_age').':</strong> '.$xml->patient->age.'<br/>
		<strong>'.get_lang('patient_initials').':</strong> '.$xml->patient->patient_initials.'<br/>
		<strong>'.get_lang('personal_circumstances').':</strong> '.$xml->patient->circumstances.'</p>
		
		
<h2>'.get_lang('Diagnosis').'</h2>
<ol type="a">
	<li><strong>'.get_lang('convetionalMedPrescription').':</strong><br/>'.$xml->new_patient->description->diagnosis->conv_med_presc.'</li>
	<li><strong>'.get_lang('LaboratoryTests').':</strong><br/>
		<ul>
			<li><strong>'.get_lang('LaboratoryTestsTetx').':</strong><br/>'.$xml->new_patient->description->diagnosis->lab_tests->desc.'</li>
			<li><strong>'.get_lang('uploaded_file').': </strong><br/><a href="'.$xml->new_patient->description->diagnosis->lab_tests->file.'" target="_blank">'.$xml->new_patient->description->diagnosis->lab_tests->file.'</a></li>
		</ul>
	</li>
	<li><strong>'.get_lang('PreviousHomeoMed').':</strong><br/>'.$xml->new_patient->description->diagnosis->pre_homeo_presc.'</li>
</ol>

<h2>'.get_lang('mainComplaint').'</h2>
<ol type="a">
	<li><strong>'.get_lang('complaint_origin').':</strong><br/>'.$xml->new_patient->description->main_complaint->origin.'</li>
	<li><strong>'.get_lang('causative_factors').':</strong><br/>'.$xml->new_patient->description->main_complaint->causative_factors.'</li>
	<li><strong>'.get_lang('modalities').':</strong><br/>'.$xml->new_patient->description->main_complaint->modalities.'</li>
	<li><strong>'.get_lang('occurrence_time').':</strong><br/>'.$xml->new_patient->description->main_complaint->occurrence_time.'</li>
	<li><strong>'.get_lang('body_side').':</strong><br/>'.$xml->new_patient->description->main_complaint->body_side.'</li>
	<li><strong>'.get_lang('frequency').':</strong><br/>'.$xml->new_patient->description->main_complaint->appearence_frequency.'</li>
	<li><strong>'.get_lang('pain_desc').':</strong><br/>'.$xml->new_patient->description->main_complaint->pain_desc.'</li>
	<li><strong>'.get_lang('complaint_extension').':</strong><br/>'.$xml->new_patient->description->main_complaint->complaint_ext.'</li>
	<li><strong>'.get_lang('other_complaints').':</strong><br/>'.$xml->new_patient->description->main_complaint->other_complaints.'</li>
</ol>

<h2>'.get_lang('personalMedicalHistory').'</h2>
<ol type="a">
	<li><strong>'.get_lang('therapies_vaccinations').':</strong><br/>'.$xml->new_patient->description->medical_history->personal->therapies_vaccination.'</li>
	<li><strong>'.get_lang('traumas').':</strong><br/>'.$xml->new_patient->description->medical_history->personal->traumas.'</li>
	<li><strong>'.get_lang('infectious_diseases').':</strong><br/>'.$xml->new_patient->description->medical_history->personal->infections.'</li>
	<li><strong>'.get_lang('personal_other').':</strong><br/>'.$xml->new_patient->description->medical_history->personal->other.'</li>
</ol>

<h2>'.get_lang('familyMedicalHistory').'</h2>
	<p><strong>'.get_lang('family_diseases').':</strong><br/>'.$xml->new_patient->description->medical_history->family->diseases.'</p>
	
<h2>'.get_lang('physicalGenerals').'</h2>
<ol type="a">
	<li><strong>'.get_lang('reactions').':</strong><br/>'.$xml->new_patient->description->physical->reactions.'</li>
	<li><strong>'.get_lang('sleeping_habits').':</strong><br/>'.$xml->new_patient->description->physical->sleeping_habits.'</li>
	<li><strong>'.get_lang('food_modalities').':</strong><br/>'.$xml->new_patient->description->physical->food_modalities.'</li>
	<li><strong>'.get_lang('menstruation').':</strong><br/>'.$xml->new_patient->description->physical->menstruation.'</li>
</ol>
	
<h2>'.get_lang('MentalEmotional').'</h2>
	<p>'.$xml->new_patient->description->mental_emotional.'</p>
	
<h2>'.get_lang('Analysis').'</h2>

<h3>'.get_lang('Prognosis').'</h3>
<ol type="a">
	<li><strong>'.get_lang('personalMedicalHistory').':</strong><br/>'.$xml->new_patient->analysis->prognosis->personal_medical_history.'</li>
	<li><strong>'.get_lang('family_history_analysis').':</strong><br/>'.$xml->new_patient->analysis->prognosis->family_medical_history.'</li>
	<li><strong>'.get_lang('level_of_health').':</strong><br/>'.$xml->new_patient->analysis->prognosis->level_of_health.'</li>
	<li><strong>'.get_lang('conclusion').':</strong><br/>'.$xml->new_patient->analysis->prognosis->conclusion.'</li>
</ol>

<h3>'.get_lang('symptomsSelection').'</h3>
<ol type="a">
	<li><strong>'.get_lang('peculiar_symptoms').':</strong><br/>'.$xml->new_patient->analysis->symptoms_selection->peculiar.'</li>
	<li><strong>'.get_lang('intense_symptoms').':</strong><br/>'.$xml->new_patient->analysis->symptoms_selection->intense.'</li>
</ol>

<h3>'.get_lang('Repertorization').'</h3>
<ol type="a">
	<li><strong>'.get_lang('repertorization').':</strong><br/>'.$xml->new_patient->analysis->repertorization->desc.'</li>
	<li><strong>'.get_lang('uploaded_file').':</strong><br/><a href="'.$xml->new_patient->analysis->repertorization->file_url.'" target="_blank">'.$xml->new_patient->analysis->repertorization->file_url.'</a></li>
</ol>

<h3>'.get_lang('remedies_differentiantion').':</h3>
	<p>'.$xml->new_patient->analysis->remedies_differentiation.'</p>
	
<h3>'.get_lang('Prescription').'</h3>
<ol type="a">
	<li><strong>'.get_lang('remedy').':</strong><br/>'.$xml->new_patient->analysis->prescription->remedy.'</li>
	<li><strong>'.get_lang('potency').':</strong><br/>'.$xml->new_patient->analysis->prescription->potency.'</li>
	<li><strong>'.get_lang('regimen').':</strong><br/>'.$xml->new_patient->analysis->prescription->regimen.'</li>
	<li><strong>'.get_lang('other_notes').':</strong><br/>'.$xml->new_patient->analysis->prescription->other_notes.'</li>
</ol>';

foreach($xml->followups->followup as $followup){

$f_date = new DateTime($followup->followup_date);
$new_f_date = $f_date->format('d-m-Y H:i:s');

echo '<h2>'.get_lang('FollowupNum').$followup->number.'</h2>
<ol type="a">
	<li><strong>'.get_lang('followup_date').':</strong><br/>'.$new_f_date.'</li>
	<li><strong>'.get_lang('convetionalMedPrescription').':</strong><br/>'.$followup->conv_med_presc.'</li>
	<li><strong>'.get_lang('LaboratoryTests').':</strong><br/>
		<ul>
			<li><strong>'.get_lang('LaboratoryTestsTetx').':</strong><br/>'.$followup->lab_tests->desc.'</li>
			<li><strong>'.get_lang('uploaded_file').': </strong><br/><a href="'.$followup->lab_tests->file.'" target="_blank">'.$followup->lab_tests->file.'</a></li>
		</ul>
	</li>
	<li><strong>'.get_lang('followup_reaction').':</strong><br/>'.$followup->reaction.'</li>
	<li><strong>'.get_lang('followup_analysis').':</strong><br/>'.$followup->analysis.'</li>
	<li><strong>'.get_lang('Repertorization').'</strong>
		<ul>
			<li>'.$followup->repertorization->desc.'</li>
			<li>'.get_lang('uploaded_file').':<a href="'.$followup->repertorization->file_url.'" target="_blank">'.$followup->repertorization->file_url.'</a></li>
		</ul>
	</li>
	<li><strong>'.get_lang('conclusion').':</strong><br/>'.$followup->conclusion.'</li>
</ol>

<h3>'.get_lang('Prescription').'</h3>
<ol type="a">
	<li><strong>'.get_lang('remedy').':</strong><br/>'.$followup->prescription->remedy.'</li>
	<li><strong>'.get_lang('potency').':</strong><br/>'.$followup->prescription->potency.'</li>
	<li><strong>'.get_lang('regimen').':</strong><br/>'.$followup->prescription->regimen.'</li>
	<li><strong>'.get_lang('other_notes').':</strong><br/>'.$followup->prescription->other_notes.'</li>
</ol>';
}


$tpl->assign('message', $message);
$tpl->display_one_col_template();
}
elseif(api_is_allowed_to_edit()!=1 && $user_id == $case_db['user_id'] && ($case_db['state'] == 'Review' || $case_db['state'] == 'Draft')){

$htmlHeadXtra[] = '<script>
		setInterval(function () {
			$.post( "save.php", $( "form" ).serialize());
		}, 10000);
</script>
';

	echo Display::display_header('<a href="start.php?'.api_get_cidreq().'">'.get_lang('Case Study Submission Tool').'</a> / Case #'.$case_db['caseID']);


	//echo $case->get_number_of_user_cases();

	echo '<div class="actions">';
	echo Display::return_icon('folder.png', get_lang('MyCases'), array(), 32);
	echo Display::url(Display::return_icon('new_folder_na.png', get_lang('SubmitNewCase'), array(), 32), api_get_path(WEB_ROOT).'add_new_case.php?'.api_get_cidreq());
	echo '</div>';
	
	echo Display::page_subheader(Display::return_icon('folder.png', get_lang('Session'), array(), ICON_SIZE_SMALL).' '.get_lang('MyCases'));	
	
	if (isset($_GET['action']) && $_GET['action'] == 'saved') {
		Display::display_confirmation_message(get_lang('CaseSavedAsDraft'));
	}
	
	if(!empty($case_db['comment'])){
		echo '<h2>Teacher\'s Comments</h2>';	
		echo '<p><strong>Comments:</strong> '.$case_db['comment'].'</p>';	
	}
	
	$config = array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '100%', 'Height' => '120');
	$form = new FormValidator('my_case', 'post', api_get_self().'?case='.$case_id.'&'.api_get_cidreq());
	$renderer = & $form->defaultRenderer();
	$form->addElement('hidden', 'xml_filename', $xml->ID);
	$form->addElement('hidden', 'case_id', $case_id);
	$form->addElement('hidden', 'old_repertorization_file', $xml->new_patient->analysis->repertorization->file_url);
	$form->addElement('hidden', 'old_followup_repertorization_file', $xml->followups->followup->repertorization->file_url);
	//#1
	$values = array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
	$group = array();
        foreach ($values as $value) {
            $element = & $form->createElement('radio', 'case_num', '', $value, $value);
            $group[] = $element;
        }
    $form->addGroup($group, 'case_num', get_lang('CaseNumber'), '', false);
	$default_values['case_num'] = $case_db['caseID'];
	$form->addRule('case_num', get_lang('ThisFieldIsRequired'), 'required');
	
	$form->addElement('datepicker', 'case_date', get_lang('case_date'), array('form_name'=>'my_case'), 5);
	$c_date = new DateTime($xml->patient->case_date);
	$default_values['case_date'] = $c_date->format('Y-m-d H:i:s');
	
	
	//#2
	$genders = array ('male', 'female');
	$gender_group = array();
        foreach ($genders as $gender) {
            $element = & $form->createElement('radio', 'patient_gender', '', get_lang($gender), $gender);
            $gender_group[] = $element;
        }
    $form->addGroup($gender_group, 'patient_gender', get_lang('patient_gender'), '', false);
	$default_values['patient_gender'] = $xml->patient->gender;
	$form->addRule('patient_gender', get_lang('ThisFieldIsRequired'), 'required');
	
	//Patient initials
	$form->addElement('text', 'patient_initials', get_lang('patient_initials'));
	$form->addRule('patient_initials', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['patient_initials'] = $xml->patient->patient_initials;
	
	//#3
	$birth_year = $case->age2birthyear($case_db['submission_date'], $xml->patient->age);
	$birth_years = $case->form_birth_year();
	$form->addElement('select', 'birth_year',get_lang('year_of_birth'), $birth_years, '');
	$default_values['birth_year'] = $birth_year;
	
	//#4
	$form->addElement('html', '<h2>'.get_lang('personal_circumstances').'</h2>');
	$form->addElement('textarea','personal_circumstances',get_lang('personal_circumstances'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('personal_circumstances', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['personal_circumstances'] = $xml->patient->circumstances;

	//#5 and #6
	
	
	//Diagnosis
	$diagnosis = $xml->new_patient->description->diagnosis;
	
	$form->addElement('html', '<h2>'.get_lang('Diagnosis').':</h2>');
	$form->addElement('textarea','convetionalMedPrescription',get_lang('convetionalMedPrescription'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('convetionalMedPrescription', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['convetionalMedPrescription'] = $diagnosis->conv_med_presc;
	$form->addElement('html', '<h3>'.get_lang('LaboratoryTests').':</h3>');
	$form->addElement('textarea','LaboratoryTestsTetx',get_lang('LaboratoryTestsTetx'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['LaboratoryTestsTetx'] = $diagnosis->lab_tests->desc;
	if(!empty($diagnosis->lab_tests->file)){
		$form->addElement('html', get_lang('uploaded_file').':<a href="'.$diagnosis->lab_tests->file.'" target="_blank">'.$diagnosis->lab_tests->file.'</a>');	
	}
	$form->addElement('hidden', 'current_lab_file', $diagnosis->lab_tests->file);
	$form->addElement('file', 'LaboratoryTestsFile', get_lang('LaboratoryTestsFile'));
    $allowed_file_types = array('zip', 'jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx');
    $form->addRule('LaboratoryTestsFile', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	$form->addElement('textarea','PreviousHomeoMed',get_lang('PreviousHomeoMed'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['PreviousHomeoMed'] = $diagnosis->pre_homeo_presc;
	
	//Main complaint
	$main_complaint = $xml->new_patient->description->main_complaint;
	
	$form->addElement('html', '<h1>'.get_lang('new_patient').'<br/></h1>');
	$form->addElement('html', '<h2>'.get_lang('mainComplaint').':</h2>');
	$form->addElement('textarea','complaint_origin',get_lang('complaint_origin'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('complaint_origin', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['complaint_origin'] = $main_complaint->origin;
	$form->addElement('textarea','causative_factors',get_lang('causative_factors'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('causative_factors', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['causative_factors'] = $main_complaint->causative_factors;
	$form->addElement('textarea','modalities',get_lang('modalities'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('modalities', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['modalities'] = $main_complaint->modalities;
	$form->addElement('textarea','occurrence_time',get_lang('occurrence_time'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('occurrence_time', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['occurrence_time'] = $main_complaint->occurrence_time;
	$form->addElement('textarea','body_side',get_lang('body_side'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('body_side', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['body_side'] = $main_complaint->body_side;
	$form->addElement('textarea','frequency',get_lang('frequency'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('frequency', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['frequency'] = $main_complaint->appearence_frequency;
	$form->addElement('textarea','pain_desc',get_lang('pain_desc'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('pain_desc', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['pain_desc'] = $main_complaint->pain_desc;
	$form->addElement('textarea','complaint_extension',get_lang('complaint_extension'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('complaint_extension', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['complaint_extension'] = $main_complaint->complaint_ext;
	$form->addElement('textarea','other_complaints',get_lang('other_complaints'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['other_complaints'] = $main_complaint->other_complaints;
	
	//#7
	$medical_history = $xml->new_patient->description->medical_history;
	$form->addElement('html', '<h2>'.get_lang('personalMedicalHistory').':</h2>');
	$form->addElement('textarea','therapies_vaccinations',get_lang('therapies_vaccinations'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('therapies_vaccinations', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['therapies_vaccinations'] = $medical_history->personal->therapies_vaccination;
	$form->addElement('textarea','traumas',get_lang('traumas'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('traumas', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['traumas'] = $medical_history->personal->traumas;
	$form->addElement('textarea','infectious_diseases',get_lang('infectious_diseases'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('infectious_diseases', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['infectious_diseases'] = $medical_history->personal->infections;
	$form->addElement('textarea','personal_other',get_lang('personal_other'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['personal_other'] = $medical_history->personal->other;
	
	//#8
	$form->addElement('html', '<h2>'.get_lang('familyMedicalHistory').':</h2>');
	$form->addElement('textarea','family_diseases',get_lang('family_diseases'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('family_diseases', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['family_diseases'] = $medical_history->family->diseases;
	
	//#9
	$physical = $xml->new_patient->description->physical;
	$form->addElement('html', '<h2>'.get_lang('physicalGenerals').':</h2>');
	$form->addElement('textarea','reactions',get_lang('reactions'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['reactions'] = $physical->reactions;
	$form->addElement('textarea','sleeping_habits',get_lang('sleeping_habits'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['sleeping_habits'] = $physical->sleeping_habits;
	$form->addElement('textarea','food_modalities',get_lang('food_modalities'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['food_modalities'] = $physical->food_modalities;
	$form->addElement('textarea','menstruation',get_lang('menstruation'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['menstruation'] = $physical->menstruation;
	
	//#10
	$mental_emotional = $xml->new_patient->description->mental_emotional;
	$form->addElement('html', '<h2>'.get_lang('MentalEmotional').':</h2>');
	$form->addElement('textarea','mental_emotional',get_lang('mental_emotional'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('mental_emotional', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['mental_emotional'] = $mental_emotional;
	
	//#11
	$form->addElement('html', '<h2>'.get_lang('Analysis').'</h2>');
	
	#11.1
	$prognosis = $xml->new_patient->analysis->prognosis;
	$form->addElement('html', '<h3>'.get_lang('Prognosis').':</h3>');
	/*$form->addElement('textarea','anatomopathological',get_lang('anatomopathological'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('anatomopathological', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['anatomopathological'] = $prognosis->anatomopathological;
	$form->addElement('textarea','disturbance',get_lang('disturbance'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('disturbance', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['disturbance'] = $prognosis->disturbance_depth;*/
	$form->addElement('textarea','personal_history_analysis',get_lang('personalMedicalHistory'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('personal_history_analysis', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['personal_history_analysis'] = $prognosis->personal_medical_history;
	$form->addElement('textarea','family_history_analysis',get_lang('family_history_analysis'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('family_history_analysis', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['family_history_analysis'] = $prognosis->family_medical_history;
	$levels = $case->form_levels_of_health();
	$form->addElement('select', 'level_of_health', get_lang('level_of_health'), $levels, '');
	$form->addRule('level_of_health', get_lang('ThisFieldIsRequired'), 'required');
    $default_values['level_of_health'] = $prognosis->level_of_health;
	$form->addElement('textarea','analysis_conclusion',get_lang('conclusion'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('analysis_conclusion', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['analysis_conclusion'] = $prognosis->conclusion;
	
	//#11.2
	$symptoms = $xml->new_patient->analysis->symptoms_selection;
	$form->addElement('html', '<h3>'.get_lang('symptomsSelection').':</h3>');
	$form->addElement('textarea','peculiar_symptoms',get_lang('peculiar_symptoms'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('peculiar_symptoms', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['peculiar_symptoms'] = $symptoms->peculiar;
	$form->addElement('textarea','intense_symptoms',get_lang('intense_symptoms'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('intense_symptoms', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['intense_symptoms'] = $symptoms->intense;
	
	//#11.3
	$repertorization = $xml->new_patient->analysis->repertorization;
	$form->addElement('html', '<h3>'.get_lang('Repertorization').':</h3>');
	$form->addElement('textarea','repertorization',get_lang('repertorization'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['repertorization'] = $repertorization->desc;
	if(!empty($xml->new_patient->analysis->repertorization->file_url)){
		$form->addElement('html', get_lang('uploaded_file').':<a href="'.$xml->new_patient->analysis->repertorization->file_url.'" target="_blank">'.$xml->new_patient->analysis->repertorization->file_url.'</a>');	
	}
	$form->addElement('hidden', 'current_rep_file', $xml->new_patient->analysis->repertorization->file_url);
	$form->addElement('file', 'repertorization_file', get_lang('repertorization_file'));
    $allowed_file_types = array('zip', 'jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx');
    $form->addRule('repertorization_file', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	
	//#11.4
	$remedies_differentiation = $xml->new_patient->analysis->remedies_differentiation;
	$form->addElement('html', '<h3>'.get_lang('remedies_differentiantion').':</h3>');
	$form->addElement('textarea','remedies_differentiantion',get_lang('remedies_differentiantion'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('remedies_differentiantion', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['remedies_differentiantion'] = $remedies_differentiation;
	
	
	//#11.5
	$prescription = $xml->new_patient->analysis->prescription;
	$form->addElement('html', '<h3>'.get_lang('Prescription').':</h3>');
	$form->addElement('text', 'remedy', get_lang('remedy'));
	$form->addRule('remedy', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['remedy'] = $prescription->remedy;
	$form->addElement('text', 'potency', get_lang('potency'));
	$form->addRule('potency', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['potency'] = $prescription->potency;
	$form->addElement('textarea','regimen',get_lang('regimen'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('regimen', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['regimen'] = $prescription->regimen;
	$form->addElement('textarea','other_notes',get_lang('other_notes'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('other_notes', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['other_notes'] = $prescription->other_notes;
	
	//Followup
	$f_num = 0;
	foreach($xml->followups->followup as $followup){
	$form->addElement('html', '<h1 id=fu'.$followup->number.'>'.get_lang('FollowupNum').$followup->number.'<br/></h1>');
	$form->addElement('datepicker', 'followup_date_'.$followup->number, get_lang('followup_date'), array('form_name'=>'my_case'), 5);

	$f_date = new DateTime($followup->followup_date);
	$default_values['followup_date_'.$followup->number] = $f_date->format('Y-m-d H:i:s');
	$form->addElement('textarea','followup_convetionalMedPrescription_'.$followup->number, get_lang('convetionalMedPrescription'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('followup_convetionalMedPrescription_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_convetionalMedPrescription_'.$followup->number] = $followup->conv_med_presc;
	$form->addElement('html', '<h3>'.get_lang('LaboratoryTests').':</h3>');
	$form->addElement('textarea','followup_LaboratoryTestsTetx_'.$followup->number, get_lang('LaboratoryTestsTetx'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$default_values['followup_LaboratoryTestsTetx_'.$followup->number] = $followup->lab_tests->desc;
	if(!empty($followup->lab_tests->file)){
		$form->addElement('html', get_lang('uploaded_file').':<a href="'.$followup->lab_tests->file.'" target="_blank">'.$followup->lab_tests->file.'</a>');	
	}
	$form->addElement('hidden', 'current_followup_lab_file'.$followup->number, $followup->lab_tests->file);
	$form->addElement('file', 'followup_LaboratoryTestsFile_'.$followup->number, get_lang('LaboratoryTestsFile'));
    $allowed_file_types = array('zip', 'jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx');
    $form->addRule('followup_LaboratoryTestsFile_'.$followup->number, get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	
	$form->addElement('textarea','followup_reaction_'.$followup->number, get_lang('followup_reaction'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('followup_reaction_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_reaction_'.$followup->number] = $followup->reaction;
	$form->addElement('textarea','followup_analysis_'.$followup->number, get_lang('followup_analysis'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('followup_analysis_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_analysis_'.$followup->number] = $followup->analysis;
	$form->addElement('textarea','followup_repertorization_'.$followup->number, get_lang('followup_repertorization'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	// $form->addRule('followup_repertorization', get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_repertorization_'.$followup->number] = $followup->repertorization->desc;
	if(!empty($followup->repertorization->file_url)){
		$form->addElement('html', get_lang('uploaded_file').':<a href="'.$followup->repertorization->file_url.'" target="_blank">'.$followup->repertorization->file_url.'</a>');		
	}
	$form->addElement('hidden', 'current_followup_rep_file_'.$followup->number, $followup->repertorization->file_url);
	$form->addElement('file', 'followup_repertorization_file_'.$followup->number, get_lang('followup_repertorization_file'));
    $form->addRule('followup_repertorization_file_'.$followup->number, get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	$form->addElement('textarea','followup_conclusion_'.$followup->number, get_lang('conclusion'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('followup_conclusion_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_conclusion_'.$followup->number] = $followup->conclusion;
	
	$form->addElement('html', '<h2>'.get_lang('Prescription').':</h2>');
	$form->addElement('text', 'followup_remedy_'.$followup->number, get_lang('remedy'));
	$form->addRule('followup_remedy_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_remedy_'.$followup->number] = $followup->prescription->remedy;
	$form->addElement('text', 'followup_potency_'.$followup->number, get_lang('potency'));
	$form->addRule('followup_potency_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_potency_'.$followup->number] = $followup->prescription->potency;
	$form->addElement('textarea','followup_regimen_'.$followup->number, get_lang('regimen'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('followup_regimen_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_regimen_'.$followup->number] = $followup->prescription->regimen;
	$form->addElement('textarea','followup_other_notes_'.$followup->number, get_lang('other_notes'), array('class' => 'span8', 'rows' => '6'), 'wrap="soft"');
	$form->addRule('followup_other_notes_'.$followup->number, get_lang('ThisFieldIsRequired'), 'required');
	$default_values['followup_other_notes_'.$followup->number] = $followup->prescription->other_notes;
	$f_num++;
	}
	
	$form->addElement('hidden', 'num_of_followups', $f_num);
	$form->addElement('hidden', 'case_id',  $case_id);
	
	$form->addElement('style_submit_button', 'SaveDraft', get_lang('SaveDraft'),'class="btn notepad"');
    $form->addElement('style_submit_button', 'AddFollowup', get_lang('AddFollowup'),'class="btn plus"');
    $form->addElement('style_submit_button','FinalSubmission', get_lang('FinalSubmission'), 'class="btn add add"');
	
	$renderer->setElementTemplate('{element}&nbsp;','SaveDraft');
	$renderer->setElementTemplate('{element}&nbsp;','AddFollowup');
	$renderer->setElementTemplate('{element}&nbsp;','FinalSubmission');
	

	$form->setDefaults($default_values);
	if ($form->validate()) {
		$texts = $form->exportValues();
		
		$values = array();
		foreach($texts as $key=>$text){
				
			$new_text = htmlentities($text, ENT_QUOTES, "utf-8");	
			$values[$key] = $new_text;
				
		}
		
		$form->freeze();
		$values['rep_file'] = $_POST['current_rep_file'];
		$values['lab_file'] = $_POST['current_lab_file'];

		
		for($j = 1; $j <= intval($_POST['num_of_followups']); $j++){
			
			$values['followup_rep_file_'.$j] = $_POST['current_followup_rep_file_'.$j];	
			$values['followup_lab_file_'.$j] = $_POST['current_followup_lab_file_'.$j];
		
			if(!empty($_FILES['followup_repertorization_file_'.$j]['size'])){
					$values['followup_rep_file_'.$j] = $case->save_file('followup_repertorization_file_'.$j, $user_id);
			}	
			if(!empty($_FILES['followup_LaboratoryTestsFile_'.$j]['size'])){
					$values['followup_lab_file_'.$j] = $case->save_file('followup_LaboratoryTestsFile_'.$j, $user_id);
			}
		}
		if(!empty($_FILES['repertorization_file']['size'])){
				$values['rep_file'] = $case->save_file('repertorization_file', $user_id);
		}
		if(!empty($_FILES['LaboratoryTestsFile']['size'])){
				$values['lab_file'] = $case->save_file('LaboratoryTestsFile', $user_id);
		}
		
		if(isset($_POST['FinalSubmission'])){
			$case->update_case($values, $case_id, 'Submitted');
			echo '<script type="text/javascript">window.location.href="start.php?'.api_get_cidreq().'"</script>';
		}elseif(isset($_POST['SaveDraft'])){
			$case->update_case($values, $case_id, 'Draft');
			echo '<script type="text/javascript">window.location.href="case.php?action=saved&'.api_get_cidreq().'&case='.$case_id.'"</script>';
		}elseif(isset($_POST['AddFollowup'])){
			$case->update_case($values, $case_id, 'Draft');
			echo '<script type="text/javascript">window.location.href="add_followup.php?'.api_get_cidreq().'&case='.$case_id.'"</script>';
		}
		
		
	// Display form
	}else{
		$form->display();
		echo $xml->new_patient->analysis->repertorization->file_url;
	}
}elseif(api_is_allowed_to_edit()!=1 && $user_id != $case_db['user_id']){
	echo Display::display_header('<a href="start.php?'.api_get_cidreq().'">'.get_lang('Case Study Submission Tool').'</a> / Case #'.$case_db['caseID']);
	Display::display_error_message('You are not allowed to edit this case!', false);	
}


Display :: display_footer();
