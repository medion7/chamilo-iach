<?php

$course_plugin = 'cases'; //needed in order to load the plugin lang variables
require_once 'config.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once 'lib/cases.lib.php';
$cases = new Cases;
$case_id = $_POST['case_id'];
$submissions = array();
$submissions = $_POST;


		$values = array();
		foreach($submissions as $key=>$text){
				
		$new_text = html_entity_decode($text, ENT_NOQUOTES, "utf-8");
			$values[$key] = $new_text;
		}
		
			$user_id = api_get_user_id();
			$case_file = $values['xml_filename'].'xml';
			$course_id = api_get_course_int_id();
			$session_id = api_get_session_id();
			$rep_file = $values['old_repertorization_file'];
			$fu_rep_file = $values['old_followup_repertorization_file'];
			if(!empty($_FILES['followup_repertorization_file']['size'])){
				$fu_rep_file = $cases->save_file('followup_repertorization_file', $user_id);
			}
			if(!empty($_FILES['repertorization_file']['size'])){
				$rep_file = $cases->save_file('repertorization_file', $user_id);
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
			
			$age = $doc->createElement('age', $cases->calculate_age($values['birth_year'])); //Create age function
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
			
			
			$anatomopathological = $doc->createElement('anatomopathological', $values['anatomopathological']);
			$prognosis -> appendChild($anatomopathological);
			/*
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
			
				$sql = "UPDATE `c_cases` SET `state` = 'Draft' WHERE `id` = ".$case_id;
				$res = Database::query($sql);
				
				
?>