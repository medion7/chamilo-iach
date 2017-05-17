<?php
/**
 * This script initiates a videoconference session, calling the BigBlueButton 
 * API
 * @package chamilo.plugin.bigbluebutton
 */
/**
 * BigBlueButton-Chamilo connector class
 */
class Cases {


		function get_num_of_cases($c_id){
			
		// getting all the students of the course
		if (empty($session_id)) {
			// Registered students in a course outside session.
			$a_students = CourseManager :: get_student_list_from_course_code(api_get_course_id());
		} else {
		// Registered students in session.
			$a_students = CourseManager :: get_student_list_from_course_code(api_get_course_id(), true, api_get_session_id());
		}
		return count($a_students);
		
		}
		
		function get_number_of_users (){
			
			// getting all the students of the course
			if (empty($session_id)) {
				// Registered students in a course outside session.
				$a_students = CourseManager :: get_student_list_from_course_code(api_get_course_id());
			} else {
			// Registered students in session.
				$a_students = CourseManager :: get_student_list_from_course_code(api_get_course_id(), true, api_get_session_id());
			}

			return count($a_students);
		}
		
		/**
 * Get the users to display on the current page (fill the sortable-table)
 * @param   int     offset of first user to recover
 * @param   int     Number of users to get
 * @param   int     Column to sort on
 * @param   string  Order (ASC,DESC)
 * @see SortableTable#get_table_data($from)
 */
function get_user_data($from, $number_of_items, $column, $direction) {

	$user_table	= 	Database :: get_main_table(TABLE_MAIN_USER);
	$rel_user	= 	Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$admin_table = 	Database :: get_main_table(TABLE_MAIN_ADMIN);
	$courses	= 	Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_info = 	api_get_course_info($cidReq);
	$course_code =	$course_info['code'];

	$sql = "SELECT
                 u.user_id				AS col0,
				 ".(api_is_western_name_order()
                 ? "u.firstname 		AS col1,
                 u.lastname 			AS col2,"
                 : "u.lastname 			AS col1,
                 u.firstname 			AS col2,")."
                 u.username				AS col3
				FROM $user_table AS u 
				INNER JOIN $rel_user AS r ON u.user_id=r.user_id 
				 ";


	if (isset ($_GET['keyword'])) {
		$keyword = Database::escape_string(trim($_GET['keyword']));
		$sql .= " WHERE r.course_code='".$course_code."' AND (u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%' OR concat(u.firstname,' ',u.lastname) LIKE '%".$keyword."%' OR concat(u.lastname,' ',u.firstname) LIKE '%".$keyword."%' OR u.username LIKE '%".$keyword."%'  OR u.user_id LIKE '%".$keyword."%')";
	} elseif (isset ($_GET['keyword_firstname'])) {
		$keyword_firstname = Database::escape_string($_GET['keyword_firstname']);
		$keyword_lastname = Database::escape_string($_GET['keyword_lastname']);
		$keyword_username = Database::escape_string($_GET['keyword_username']);
		$keyword_userId = Database::escape_string($_GET['keyword_userId']);

		$sql .= " WHERE r.course_code='".$course_code."' AND(u.firstname LIKE '%".$keyword_firstname."%' " .
				"AND u.lastname LIKE '%".$keyword_lastname."%' " .
				"AND u.username LIKE '%".$keyword_username."%'  " .
				"AND u.user_id = ".$keyword_status;
		$sql .= " ) ";

	}else {
		$sql .= " WHERE r.course_code='".$course_code."'";
	}
		$sql .= " AND r.status='5'";
    // adding the filter to see the user's only of the current access_url

    if (!in_array($direction, array('ASC','DESC'))) {
    	$direction = 'ASC';
    }
    $column = intval($column);
    $from 	= intval($from);
    $number_of_items = intval($number_of_items);

	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";

	$res = Database::query($sql);

	$users = array ();
    $t = time();
	while ($user = Database::fetch_row($res)) {        
		
		$extra = '<a href="user_exe_pdf.php?student='.$user[0].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
        $users[] = array($user[1], $user[2], $user[3], $extra);
	}
	return $users;
}

	function user_submission($user_id, $course_id, $state){
		
		$sql_sub = "SELECT `id`, `caseID` FROM `c_cases` WHERE cid = ".$course_id." AND session_id = ".api_get_session_id()." AND user_id = ".$user_id." AND state = '".$state."'";
		
		$res_sub = Database::query($sql_sub);
            while ($case = Database::fetch_array($res_sub)) {                
				$cases[] = array($case['id'], $case['caseID']);
            }
		return $cases;
		
	}

	function get_number_of_cases(){
	
		$course_info = api_get_course_info(api_get_course_id());
		$course_id = $course_info['real_id'];
	
		$sql = "SELECT COUNT(id) as count FROM `c_cases` WHERE `state` != 'Draft' AND `cid` = ".$course_id." AND `session_id` = ".api_get_session_id();
		$res = Database::query($sql);
            while ($num = Database::fetch_row($res)) {
                $cases_num = $num['count'];
            }
		return $cases_num;
	}
	
	function get_data_of_cases(){	
		$course_info = api_get_course_info(api_get_course_id());
		$course_id = $course_info['real_id'];
		
		$cases = array();
		$sql = "SELECT 
					u.firstname as firstname, 
					u.lastname as lastname,
					u.username as username,
					c.id as id,
					c.caseID as caseID,
					c.submission_date as submission_date,
					c.state as state,
					c.mark as mark
				FROM `c_cases` as c
				INNER JOIN user as u ON u.user_id = c.user_id
				WHERE `state` != 'Draft' AND `c`.`cid` = ".$course_id." AND `c`.`session_id` = ".api_get_session_id();
				
		if (!empty($_GET['keyword']) && empty($_GET['case_keyword'])) {
			$keyword = Database::escape_string(trim($_GET['keyword']));
			$sql .= " AND (u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%' OR concat(u.firstname,' ',u.lastname) LIKE '%".$keyword."%' OR concat(u.lastname,' ',u.firstname) LIKE '%".$keyword."%' OR u.username LIKE '%".$keyword."%'  OR u.user_id LIKE '%".$keyword."%')";
		}elseif(!empty($_GET['case_keyword']) && empty($_GET['keyword'])) {
			$case_keyword = Database::escape_string(trim($_GET['case_keyword']));
			$sql .= " AND (c.caseID LIKE '%".$case_keyword."%' OR c.state LIKE '%".$case_keyword."%')";
		}elseif(!empty($_GET['case_keyword']) && !empty($_GET['keyword'])){
			$case_keyword = Database::escape_string(trim($_GET['case_keyword']));
			$keyword = Database::escape_string(trim($_GET['keyword']));
			$sql .= " AND (u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%' OR concat(u.firstname,' ',u.lastname) LIKE '%".$keyword."%' OR concat(u.lastname,' ',u.firstname) LIKE '%".$keyword."%' OR u.username LIKE '%".$keyword."%'  OR u.user_id LIKE '%".$keyword."%') AND (c.caseID LIKE '%".$case_keyword."%' OR c.state LIKE '%".$case_keyword."%')";
		}
		$res = Database::query($sql);
            while ($case = Database::fetch_array($res)) {  

				$extra = '<a href="case.php?case='.$case['id'].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
				$cases[] = array($case['caseID'], $case['submission_date'], $case['state'], $case['firstname'], $case['lastname'], $case['username'], $extra);
            }
		return $cases;
	}
	
	function get_number_of_users_cases(){
	
		$course_info = api_get_course_info(api_get_course_id());
		$course_id = $course_info['real_id'];
	
		$sql = "SELECT DISTINCT(user_id) as users FROM `c_cases` WHERE `state` != 'Draft' AND `cid` = ".$course_id." AND `session_id` = ".api_get_session_id();
		$res = Database::query($sql);
        $users_num = Database::num_rows($res);
		
		return $users_num;
	}
	
	function get_data_of_users_cases(){	
		$course_info = api_get_course_info(api_get_course_id());
		$course_id = $course_info['real_id'];
		
		$cases = array();
		$sql = "SELECT
					DISTINCT(c.user_id) as user_id,
					u.firstname as firstname, 
					u.lastname as lastname,
					u.username as username
				FROM `c_cases` as c
				INNER JOIN user as u ON u.user_id = c.user_id
				WHERE `c`.`state` != 'Draft' AND `c`.`cid` = ".$course_id." AND `c`.`session_id` = ".api_get_session_id();
		if (!empty($_GET['keyword'])) {
			$keyword = Database::escape_string(trim($_GET['keyword']));
			$sql .= " AND (u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%' OR concat(u.firstname,' ',u.lastname) LIKE '%".$keyword."%' OR concat(u.lastname,' ',u.firstname) LIKE '%".$keyword."%' OR u.username LIKE '%".$keyword."%'  OR u.user_id LIKE '%".$keyword."%')";
		}
		$res = Database::query($sql);
            while ($case = Database::fetch_array($res)) { 
				
				$submitted_cases = self::user_submission($case['user_id'], $course_id, 'Submitted');
				$submitted_cases_num = count($submitted_cases);
				
				$approved_cases = self::user_submission($case['user_id'], $course_id, 'Approved');
				$approved_cases_num = count($approved_cases);
				
				$extra = '<a href="cases_of_users.php?student_id='.$case['user_id'].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
				$cases[] = array($case['firstname'], $case['lastname'], $case['username'], $submitted_cases_num, $approved_cases_num, $extra);
            }
		return $cases;
	}
	
	function get_data_of_user_cases($student_id, $role='student'){	
		$course_info = api_get_course_info(api_get_course_id());
		$course_id = $course_info['real_id'];
		
		$$html = '';
		
		$sql = "SELECT
					id,
					caseID,
					submission_date,
					state,
					mark
				FROM `c_cases`
				WHERE `cid` = ".$course_id." AND `session_id` = ".api_get_session_id()." AND `user_id` = ".$student_id;
				
				if($role == 'teacher'){
					$sql .= " AND `state` NOT IN ('Draft', 'Commented')";
				}				
				$sql .= " ORDER BY `caseID`";
			$res = Database::query($sql);
            while ($case = Database::fetch_array($res)) { 
				
				$extra = '<a href="case.php?case='.$case['id'].'&'.api_get_cidreq().'"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
				$html .= '<tr>
							<td>'.$case['caseID'].'</td>
							<td>'.$case['submission_date'].'</td>
							<td>'.$case['state'].'</td>
							<td>'.$extra.'</td>
						</tr>';
            }
		return $html;
	}
	
	function form_birth_year(){
			
		date_default_timezone_set('UTC');
		$birth_years = array();
		
		$years = range(date("Y"), date("Y", strtotime("now - 100 years")));
		foreach($years as $year){
			$birth_years[$year] = $year;
		}
		
		return $birth_years;
	}
	
	function form_levels_of_health(){
		
		$levels = array();
		foreach (range(1, 12) as $number){
			$levels[$number] = $number;
		}
		
		return $levels;
	}
	
	function calculate_age($birth_year){
			
		$age = date("Y") - intval($birth_year);
		return $age;
	}
	
	function case_file_id(){
			
		$ID = hash('md5', time().api_get_user_id());
		return $ID;
	}
	
	function save_file($file, $user_id){

	//Create user file (if it does not exist)
	$my_path = UserManager::get_user_picture_path_by_id(api_get_user_id(), 'system');
	$user_folder = $my_path['dir'] . 'my_cases/';
	$my_path = null;
		
	if (!file_exists($user_folder)) {
		$perm = api_get_permissions_for_new_directories();
		@mkdir($user_folder, $perm, true);
	}
			
	$updir = api_get_path(SYS_CODE_PATH).'upload/users/'.$user_id;
	$send_path = api_get_path(WEB_CODE_PATH).'upload/users/'.$user_id;
		
		// Try to add an extension to the file if it has'nt one
        $new_file_name = add_ext_on_mime(stripslashes($_FILES[$file]['name']), $_FILES[$file]['type']);
		
		// Replace dangerous characters
        $new_file_name = replace_dangerous_char($new_file_name, 'strict');
		
		// Transform any .php file in .phps fo security
        $new_file_name = php2phps($new_file_name);
		
		$new_file_name = api_get_unique_id();
        $curdirpath = basename($my_folder_data['url']);

        //if we come from the group tools the groupid will be saved in $work_table
        $result = @move_uploaded_file($_FILES[$file]['tmp_name'], $updir.'/'.$_FILES[$file]['name']);
        if ($result) {
            $filename = $send_path.'/'.$_FILES[$file]['name'];
			}	
			
		return $filename;
		}
		
		function htmlspecialchars_decode($text){
			
			return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
		}
	
	function save_case($submissions){
			
			$values = array();
			
			foreach($submissions as $key=>$text){
				
			$new_text = html_entity_decode($text, ENT_NOQUOTES, "utf-8");
				$values[$key] = $new_text;
			}
			
			$case_file = 'EL'.self::case_file_id($user_id);
			$course_id = api_get_course_int_id();
			$session_id = api_get_session_id();
			$user_id = api_get_user_id();
			
			//Create the xml
			$doc = new DOMDocument('1.0', 'utf-8');
			// we want a nice output
			$doc->formatOutput = true;
			$case = $doc->createElement('case');
			$doc -> appendChild($case);
			
			$ID = $doc->createElement('ID', $case_file);
			$case -> appendChild($ID);
			
			$patient = $doc->createElement('patient');
			$case -> appendChild($patient);
			
			$case_date = $doc->createElement('case_date', '');
			$patient -> appendChild($case_date);
			
			$gender = $doc->createElement('gender', '');
			$patient -> appendChild($gender);
			
			$patient_initials = $doc->createElement('patient_initials', '');
			$patient -> appendChild($patient_initials);
			
			$age = $doc->createElement('age', ''); //Create age function
			$patient -> appendChild($age);
			
			$circumstances = $doc->createElement('circumstances', 'Please enter the personal circumstances');
			$patient -> appendChild($circumstances);
			
			$new_patient = $doc->createElement('new_patient');
			$case -> appendChild($new_patient);
			
			$description = $doc->createElement('description');
			$new_patient -> appendChild($description);
			
			$diagnosis = $doc->createElement('diagnosis');
			$description -> appendChild($diagnosis);
			
			$conv_med_presc = $doc->createElement('conv_med_presc', 'Please enter the conventional Medical Prescriptions');
			$diagnosis -> appendChild($conv_med_presc);
			
			$lab_tests = $doc->createElement('lab_tests');
			$diagnosis -> appendChild($lab_tests);
			
			$lab_tests_desc = $doc->createElement('desc', '');
			$lab_tests -> appendChild($lab_tests_desc);
			
			$lab_tests_file = $doc->createElement('file', '');
			$lab_tests -> appendChild($lab_tests_file);
			
			$pre_homeo_presc = $doc->createElement('pre_homeo_presc', '');
			$diagnosis -> appendChild($pre_homeo_presc);
			
			$main_complaint = $doc->createElement('main_complaint');
			$description -> appendChild($main_complaint);
			
			$complaint_origin = $doc->createElement('origin', 'Please enter the Origin of the complaint (since when it appeared)');
			$main_complaint -> appendChild($complaint_origin);
			
			$causative_factors = $doc->createElement('causative_factors', 'Please enter any possible causative factors');
			$main_complaint -> appendChild($causative_factors);
			
			$modalities = $doc->createElement('modalities', 'Please enter Modalities (what makes the complaint aggravate or ameliorate)');
			$main_complaint -> appendChild($modalities);
			
			$occurrence_time = $doc->createElement('occurrence_time', 'Please enter the Time of occurrence, aggravation or amelioration');
			$main_complaint -> appendChild($occurrence_time);
			
			$body_side = $doc->createElement('body_side', 'Please enter side of the body');
			$main_complaint -> appendChild($body_side);
			
			$appearence_frequency = $doc->createElement('appearence_frequency','Please enter frequency of appearance');
			$main_complaint -> appendChild($appearence_frequency);
			
			$pain_desc = $doc->createElement('pain_desc', 'Please enter a description of the pain');
			$main_complaint -> appendChild($pain_desc);
			
			$complaint_ext = $doc->createElement('complaint_ext', 'Please enter any extension of the complaint to other parts');
			$main_complaint -> appendChild($complaint_ext);
			
			$other_complaints = $doc->createElement('other_complaints', '');
			$main_complaint -> appendChild($other_complaints);
			
			$medical_history = $doc->createElement('medical_history');
			$description -> appendChild($medical_history);
			
			$personal_medical_history = $doc->createElement('personal');
			$medical_history -> appendChild($personal_medical_history);
			
			$therapies_vaccination = $doc->createElement('therapies_vaccination', 'Please enter Suppressive therapies and vaccinations');
			$personal_medical_history -> appendChild($therapies_vaccination);
			
			$traumas = $doc->createElement('traumas', 'Please enter Traumas (physical, emotional, mental)');
			$personal_medical_history -> appendChild($traumas);
			
			$infections = $doc->createElement('infections', 'Please enter Acute infectious diseases');
			$personal_medical_history -> appendChild($infections);
			
			$personal_other = $doc->createElement('other', '');
			$personal_medical_history -> appendChild($personal_other);
			
			$family_medical_history = $doc->createElement('family');
			$medical_history -> appendChild($family_medical_history);
			
			$family_diseases = $doc->createElement('diseases', 'Please enter Diseases that occur in the family');
			$family_medical_history -> appendChild($family_diseases);
			
			$physical = $doc->createElement('physical');
			$description -> appendChild($physical);
			
			$reactions = $doc->createElement('reactions', '');
			$physical -> appendChild($reactions);
			
			$sleeping_habits = $doc->createElement('sleeping_habits', '');
			$physical -> appendChild($sleeping_habits);
			
			$food_modalities = $doc->createElement('food_modalities', '');
			$physical -> appendChild($food_modalities);
			
			$menstruation = $doc->createElement('menstruation', '');
			$physical -> appendChild($menstruation);
			
			$mental_emotional = $doc->createElement('mental_emotional','Please enter mental and emotional symptoms');
			$description -> appendChild($mental_emotional);
			
			$analysis = $doc->createElement('analysis');
			$new_patient -> appendChild($analysis);
			
			$prognosis = $doc->createElement('prognosis');
			$analysis -> appendChild($prognosis);
			
			/*
			$anatomopathological = $doc->createElement('anatomopathological', $values['anatomopathological']);
			$prognosis -> appendChild($anatomopathological);
			
			$disturbance_depth = $doc->createElement('disturbance_depth', $values['disturbance']);
			$prognosis -> appendChild($disturbance_depth);
			*/
			
			$personal_history_analysis = $doc->createElement('personal_medical_history', 'Please enter personal medical history');
			$prognosis -> appendChild($personal_history_analysis);
			
			$family_history_analysis = $doc->createElement('family_medical_history', 'Please enter family medical history and hereditary predisposition');
			$prognosis -> appendChild($family_history_analysis);
			
			$level_of_health = $doc->createElement('level_of_health', '1');
			$prognosis -> appendChild($level_of_health);
			
			$prognosis_conclusion = $doc->createElement('conclusion', 'Please enter Conclusion');
			$prognosis -> appendChild($prognosis_conclusion);
			
			$symptoms_selection = $doc->createElement('symptoms_selection');
			$analysis -> appendChild($symptoms_selection);
			
			$peculiar_symptoms = $doc->createElement('peculiar', 'Please enter Peculiar symptoms, Rank the intensity of each symptom in scale from 1 (lowest) to 4 (highest)');
			$symptoms_selection -> appendChild($peculiar_symptoms);
			
			$intense_symptoms = $doc->createElement('intense', 'Please enter Intense symptoms, Rank the intensity of each symptom in scale from 1 (lowest) to 4 (highest)');
			$symptoms_selection -> appendChild($intense_symptoms);
			
			$repertorization = $doc->createElement('repertorization');
			$analysis -> appendChild($repertorization);
			
			$repertorization_desc = $doc->createElement('desc', '');
			$repertorization -> appendChild($repertorization_desc);
			
			$repertorization_file_url = $doc->createElement('file_url', '');
			$repertorization -> appendChild($repertorization_file_url);
			
			$remedies_differentiation = $doc->createElement('remedies_differentiation', 'Please enter Differentiation of the remedies');
			$analysis -> appendChild($remedies_differentiation);
			
			$prescription = $doc->createElement('prescription');
			$analysis -> appendChild($prescription);
			
			$remedy = $doc->createElement('remedy', 'Please enter remedy');
			$prescription -> appendChild($remedy);
			
			$potency = $doc->createElement('potency', 'Please enter potency');
			$prescription -> appendChild($potency);
			
			$regimen = $doc->createElement('regimen', 'Please enter regimen');
			$prescription -> appendChild($regimen);
			
			$prescription_notes = $doc->createElement('other_notes', 'Please enter other notes');
			$prescription -> appendChild($prescription_notes);
			
			$followups = $doc->createElement('followups');
			$case -> appendChild($followups);
			
			$followup = $doc->createElement('followup');
			$followups -> appendChild($followup);
			
			$fu_number = $doc->createElement('number', '1');
			$followup -> appendChild($fu_number);
			
			$fu_date = $doc->createElement('followup_date', '');
			$followup -> appendChild($fu_date);
			
			$fu_conv_med_presc = $doc->createElement('conv_med_presc', 'Please enter conventional medical prescriptions');
			$followup -> appendChild($fu_conv_med_presc);
			
			$fu_lab_tests = $doc->createElement('lab_tests');
			$followup -> appendChild($fu_lab_tests);
			
			$fu_lab_tests_desc = $doc->createElement('desc', '');
			$fu_lab_tests -> appendChild($fu_lab_tests_desc);
			
			$fu_lab_tests_file = $doc->createElement('file', '');
			$fu_lab_tests -> appendChild($fu_lab_tests_file);
			
			$fu_reaction = $doc->createElement('reaction', 'Please enter Reaction to the prescription of the former consultation');
			$followup -> appendChild($fu_reaction);
			
			$fu_analysis = $doc->createElement('analysis', 'Please enter Analysis of the reaction');
			$followup -> appendChild($fu_analysis);
			
			$fu_conlusion = $doc->createElement('conclusion', 'Please enter followup conclusion');
			$followup -> appendChild($fu_conlusion);
			
			$fu_repertorization = $doc->createElement('repertorization');
			$followup -> appendChild($fu_repertorization);
			
			$fu_repertorization_desc = $doc->createElement('desc', 'Please enter repertorization');
			$fu_repertorization -> appendChild($fu_repertorization_desc);
			
			$fu_repertorization_file_url = $doc->createElement('file_url', '');
			$fu_repertorization -> appendChild($fu_repertorization_file_url);
			
			$fu_prescription = $doc->createElement('prescription');
			$followup -> appendChild($fu_prescription);
			
			$fu_remedy = $doc->createElement('remedy', 'Please enter follow up remedy');
			$fu_prescription -> appendChild($fu_remedy);
			
			$fu_potency = $doc->createElement('potency', 'Please enter potency');
			$fu_prescription -> appendChild($fu_potency);
			
			$fu_regimen = $doc->createElement('regimen', 'Please enter regimen');
			$fu_prescription -> appendChild($fu_regimen);
			
			$fu_prescription_notes = $doc->createElement('other_notes','Please enter other followup notes');
			$fu_prescription -> appendChild($fu_prescription_notes);
			
			
			$doc->save("xml/".$case_file.".xml");
			
			//Save to database
			$sql = "INSERT INTO `c_cases` (`cid`, `session_id`, `user_id`, `caseID`, `file`, `submission_date`, `state`)
					VALUES (".$course_id.", ".$session_id.", ".$user_id.", ".$_POST['case_num'].", '".$case_file."', NOW(), 'Draft')";
					
			$res = Database::query($sql);
			$last_id = Database::insert_id();
			
			return $last_id;
	}
	
	function get_c_case_data($case_id){
			
		$sql = "SELECT * FROM `c_cases` WHERE id = ".$case_id;
		$res = Database::query($sql);
		$case = Database::fetch_array($res);
		
		return $case;
	}
	
	function age2birthyear($date, $age){
			
		$new_date = new DateTime($date);
		$new_date_year = intval($new_date->format("Y"));
		
		$birthyear = $new_date_year - intval($age);
		return $birthyear;
		
	}
	
	function case_num_submitted($user_id){
		$submitted_cases = array();
		$sql = "SELECT caseID FROM `c_cases` WHERE user_id = ".$user_id;
		$res = Database::query($sql);
		while ($cases = Database::fetch_array($res))
		foreach($cases as $key=>$value){
			$submitted_cases[] = $value;
		}
		
		return $submitted_cases;
	}
	
	function update_case($submissions, $case_id, $action){
		
		$values = array();
		foreach($submissions as $key=>$text){
				
		$new_text = html_entity_decode($text, ENT_NOQUOTES, "utf-8");
			$values[$key] = $new_text;
		}
			
			$case_file = 'EL'.self::case_file_id($user_id);
			$course_id = api_get_course_int_id();
			$session_id = api_get_session_id();
			$user_id = api_get_user_id();
			$rep_file = $values['old_repertorization_file'];
			$fu_rep_file = $values['old_followup_repertorization_file'];
			if(!empty($_FILES['followup_repertorization_file']['size'])){
				$fu_rep_file = self::save_file('followup_repertorization_file', $user_id);
			}
			if(!empty($_FILES['repertorization_file']['size'])){
				$rep_file = self::save_file('repertorization_file', $user_id);
			}
			
			//Create the xml
			$doc = new DOMDocument('1.0', 'utf-8');
			// we want a nice output
			$doc->formatOutput = true;
			$case = $doc->createElement('case');
			$doc -> appendChild($case);
			
			$ID = $doc->createElement('ID', $values['xml_filename']);
			$case -> appendChild($ID);
			
			$patient = $doc->createElement('patient');
			$case -> appendChild($patient);
			
			$case_date = $doc->createElement('case_date', $values['case_date']);
			$patient -> appendChild($case_date);
			
			$gender = $doc->createElement('gender', $values['patient_gender']);
			$patient -> appendChild($gender);
			
			$patient_initials = $doc->createElement('patient_initials', $values['patient_initials']);
			$patient -> appendChild($patient_initials);
			
			$age = $doc->createElement('age', self::calculate_age($values['birth_year'])); //Create age function
			$patient -> appendChild($age);
			
			$circumstances = $doc->createElement('circumstances', $values['personal_circumstances']);
			$patient -> appendChild($circumstances);
			
			$new_patient = $doc->createElement('new_patient');
			$case -> appendChild($new_patient);
			
			$description = $doc->createElement('description');
			$new_patient -> appendChild($description);
			
			$main_complaint = $doc->createElement('main_complaint');
			$description -> appendChild($main_complaint);
			
			$diagnosis = $doc->createElement('diagnosis');
			$description -> appendChild($diagnosis);
			
			$conv_med_presc = $doc->createElement('conv_med_presc', $values['convetionalMedPrescription']);
			$diagnosis -> appendChild($conv_med_presc);
			
			$lab_tests = $doc->createElement('lab_tests');
			$diagnosis -> appendChild($lab_tests);
			
			$lab_tests_desc = $doc->createElement('desc', $values['LaboratoryTestsTetx']);
			$lab_tests -> appendChild($lab_tests_desc);
			
			$lab_tests_file = $doc->createElement('file', $values['lab_file']);
			$lab_tests -> appendChild($lab_tests_file);
			
			$pre_homeo_presc = $doc->createElement('pre_homeo_presc', $values['PreviousHomeoMed']);
			$diagnosis -> appendChild($pre_homeo_presc);
			
			$complaint_origin = $doc->createElement('origin', $values['complaint_origin']);
			$main_complaint -> appendChild($complaint_origin);
			
			$causative_factors = $doc->createElement('causative_factors', $values['causative_factors']);
			$main_complaint -> appendChild($causative_factors);
			
			$modalities = $doc->createElement('modalities', $values['modalities']);
			$main_complaint -> appendChild($modalities);
			
			$occurrence_time = $doc->createElement('occurrence_time', $values['occurrence_time']);
			$main_complaint -> appendChild($occurrence_time);
			
			$body_side = $doc->createElement('body_side', $values['body_side']);
			$main_complaint -> appendChild($body_side);
			
			$appearence_frequency = $doc->createElement('appearence_frequency', $values['frequency']);
			$main_complaint -> appendChild($appearence_frequency);
			
			$pain_desc = $doc->createElement('pain_desc', $values['pain_desc']);
			$main_complaint -> appendChild($pain_desc);
			
			$complaint_ext = $doc->createElement('complaint_ext', $values['complaint_extension']);
			$main_complaint -> appendChild($complaint_ext);
			
			$other_complaints = $doc->createElement('other_complaints', $values['other_complaints']);
			$main_complaint -> appendChild($other_complaints);
			
			$medical_history = $doc->createElement('medical_history');
			$description -> appendChild($medical_history);
			
			$personal_medical_history = $doc->createElement('personal');
			$medical_history -> appendChild($personal_medical_history);
			
			$therapies_vaccination = $doc->createElement('therapies_vaccination', $values['therapies_vaccinations']);
			$personal_medical_history -> appendChild($therapies_vaccination);
			
			$traumas = $doc->createElement('traumas', $values['traumas']);
			$personal_medical_history -> appendChild($traumas);
			
			$infections = $doc->createElement('infections', $values['infectious_diseases']);
			$personal_medical_history -> appendChild($infections);
			
			$personal_other = $doc->createElement('other', $values['personal_other']);
			$personal_medical_history -> appendChild($personal_other);
			
			$family_medical_history = $doc->createElement('family');
			$medical_history -> appendChild($family_medical_history);
			
			$family_diseases = $doc->createElement('diseases', $values['family_diseases']);
			$family_medical_history -> appendChild($family_diseases);
			
			$physical = $doc->createElement('physical');
			$description -> appendChild($physical);
			
			$reactions = $doc->createElement('reactions', $values['reactions']);
			$physical -> appendChild($reactions);
			
			$sleeping_habits = $doc->createElement('sleeping_habits', $values['sleeping_habits']);
			$physical -> appendChild($sleeping_habits);
			
			$food_modalities = $doc->createElement('food_modalities', $values['food_modalities']);
			$physical -> appendChild($food_modalities);
			
			$menstruation = $doc->createElement('menstruation', $values['menstruation']);
			$physical -> appendChild($menstruation);
			
			$mental_emotional = $doc->createElement('mental_emotional', $values['mental_emotional']);
			$description -> appendChild($mental_emotional);
			
			$analysis = $doc->createElement('analysis');
			$new_patient -> appendChild($analysis);
			
			$prognosis = $doc->createElement('prognosis');
			$analysis -> appendChild($prognosis);
			
			/*
			$anatomopathological = $doc->createElement('anatomopathological', $values['anatomopathological']);
			$prognosis -> appendChild($anatomopathological);
			
			$disturbance_depth = $doc->createElement('disturbance_depth', $values['disturbance']);
			$prognosis -> appendChild($disturbance_depth);
			*/
			
			$personal_history_analysis = $doc->createElement('personal_medical_history', $values['personal_history_analysis']);
			$prognosis -> appendChild($personal_history_analysis);
			
			$family_history_analysis = $doc->createElement('family_medical_history', $values['family_history_analysis']);
			$prognosis -> appendChild($family_history_analysis);
			
			$level_of_health = $doc->createElement('level_of_health', $values['level_of_health']);
			$prognosis -> appendChild($level_of_health);
			
			$prognosis_conclusion = $doc->createElement('conclusion', $values['analysis_conclusion']);
			$prognosis -> appendChild($prognosis_conclusion);
			
			$symptoms_selection = $doc->createElement('symptoms_selection');
			$analysis -> appendChild($symptoms_selection);
			
			$peculiar_symptoms = $doc->createElement('peculiar', $values['peculiar_symptoms']);
			$symptoms_selection -> appendChild($peculiar_symptoms);
			
			$intense_symptoms = $doc->createElement('intense', $values['intense_symptoms']);
			$symptoms_selection -> appendChild($intense_symptoms);
			
			$repertorization = $doc->createElement('repertorization');
			$analysis -> appendChild($repertorization);
			
			$repertorization_desc = $doc->createElement('desc', $values['repertorization']);
			$repertorization -> appendChild($repertorization_desc);
			
			$repertorization_file_url = $doc->createElement('file_url', $values['rep_file']);
			$repertorization -> appendChild($repertorization_file_url);
			
			$remedies_differentiation = $doc->createElement('remedies_differentiation', $values['remedies_differentiantion']);
			$analysis -> appendChild($remedies_differentiation);
			
			$prescription = $doc->createElement('prescription');
			$analysis -> appendChild($prescription);
			
			$remedy = $doc->createElement('remedy', $values['remedy']);
			$prescription -> appendChild($remedy);
			
			$potency = $doc->createElement('potency', $values['potency']);
			$prescription -> appendChild($potency);
			
			$regimen = $doc->createElement('regimen', $values['regimen']);
			$prescription -> appendChild($regimen);
			
			$prescription_notes = $doc->createElement('other_notes', $values['other_notes']);
			$prescription -> appendChild($prescription_notes);
			
			$followups = $doc->createElement('followups');
			$case -> appendChild($followups);
			
			for($j = 1; $j <= intval($values['num_of_followups']); $j++){
			
				$followup = $doc->createElement('followup');
				$followups -> appendChild($followup);
			
				$fu_number = $doc->createElement('number', $j);
				$followup -> appendChild($fu_number);
				
				$fu_date = $doc->createElement('followup_date', $values['followup_date_'.$j]);
				$followup -> appendChild($fu_date);
			
				$fu_conv_med_presc = $doc->createElement('conv_med_presc', $values['followup_convetionalMedPrescription_'.$j]);
				$followup -> appendChild($fu_conv_med_presc);
			
				$fu_lab_tests = $doc->createElement('lab_tests');
				$followup -> appendChild($fu_lab_tests);
			
				$fu_lab_tests_desc = $doc->createElement('desc', $values['followup_LaboratoryTestsTetx_'.$j]);
				$fu_lab_tests -> appendChild($fu_lab_tests_desc);
			
				$fu_lab_tests_file = $doc->createElement('file', $values['followup_lab_file_'.$j]);
				$fu_lab_tests -> appendChild($fu_lab_tests_file);
			
				$fu_reaction = $doc->createElement('reaction', $values['followup_reaction_'.$j]);
				$followup -> appendChild($fu_reaction);
			
				$fu_analysis = $doc->createElement('analysis', $values['followup_analysis_'.$j]);
				$followup -> appendChild($fu_analysis);
			
				$fu_conlusion = $doc->createElement('conclusion', $values['followup_conclusion_'.$j]);
				$followup -> appendChild($fu_conlusion);
			
				$fu_repertorization = $doc->createElement('repertorization');
				$followup -> appendChild($fu_repertorization);
			
				$fu_repertorization_desc = $doc->createElement('desc', $values['followup_repertorization_'.$j]);
				$fu_repertorization -> appendChild($fu_repertorization_desc);
			
				$fu_repertorization_file_url = $doc->createElement('file_url', $values['followup_rep_file_'.$j]);
				$fu_repertorization -> appendChild($fu_repertorization_file_url);
			
				$fu_prescription = $doc->createElement('prescription');
				$followup -> appendChild($fu_prescription);
			
				$fu_remedy = $doc->createElement('remedy', $values['followup_remedy_'.$j]);
				$fu_prescription -> appendChild($fu_remedy);
			
				$fu_potency = $doc->createElement('potency', $values['followup_potency_'.$j]);
				$fu_prescription -> appendChild($fu_potency);
			
				$fu_regimen = $doc->createElement('regimen', $values['followup_regimen_'.$j]);
				$fu_prescription -> appendChild($fu_regimen);
			
				$fu_prescription_notes = $doc->createElement('other_notes', $values['followup_other_notes_'.$j]);
				$fu_prescription -> appendChild($fu_prescription_notes);
			}
			
			$doc->save("xml/".$values['xml_filename'].".xml");
			
			//Save to database
			
				$sql = "UPDATE `c_cases` SET `state` = '".$action."' WHERE `id` = ".$case_id;
				$res = Database::query($sql);
			
			return $action;
	}
	
	function teacher_comment($case_id, $state, $comment){
		
		$sql = "UPDATE `c_cases` SET `comment` = '".$comment."', `state` = '".$state."'";
		$sql .= " WHERE `id` = ".$case_id;
		$res = Database::query($sql);
		
		return $ok;
	}
	
	function add_followup($case_id, $followup_num){
		
			
		$case_info = self::get_c_case_data($case_id);
		
		$doc = simplexml_load_file('xml/'.$case_info['file'].'.xml');
		$followup = $doc->followups->addChild('followup');
		$followup->addChild('number', $followup_num);
		$followup -> addChild('followup_date', '');
		$followup->addChild('conv_med_presc', 'Please enter conventional medical prescriptions');
		$lab_test = $followup->addChild('lab_tests');
		$lab_test->addChild('desc', '');
		$lab_test->addChild('file', '');
		$followup->addChild('reaction', 'Please enter Reaction to the prescription of the former consultation');
		$followup->addChild('analysis', 'Please enter Analysis of the reaction');
		$followup->addChild('conclusion', 'Please enter followup conclusion');
		$repertorization = $followup->addChild('repertorization');
		$repertorization->addChild('desc', 'Please enter repertorization');
		$repertorization->addChild('file_url', '');
		$prescription = $followup->addChild('prescription');
		$prescription->addChild('remedy', 'Please enter follow up remedy');
		$prescription->addChild('potency', 'Please enter potency');
		$prescription->addChild('regimen', 'Please enter regimen');
		$prescription->addChild('other_notes', 'Please enter other followup notes');
		

		$doc->asXML('xml/'.$case_info['file'].'.xml');
	}
}