<?php
/* For licensing terms, see /license.txt */
/**
 * Implements the tracking of students in the Reporting pages
 * @package chamilo.reporting
 */
/**
 * Code
 */
// name of the language file that needs to be included
$language_file = array('registration', 'index', 'tracking', 'exercice', 'admin', 'gradebook', 'survey', 'learnpath');

require_once '../../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once 'videostats.lib.php';

api_block_anonymous_users();

api_protect_admin_script(true);

$videostats = new Videostats;

if (!api_is_allowed_to_create_course() && !api_is_session_admin() && !api_is_drh()) {
    // Check if the user is tutor of the course
    $user_course_status = CourseManager::get_tutor_in_course_status(api_get_user_id(), api_get_course_id());
    if ($user_course_status != 1) {    
        api_not_allowed(true);
    }
}


$nameTools = '<a href="'.api_get_path(REL_CODE_PATH).'admin/">'.get_lang('Administartion').'</a> / '.get_lang('VideoStats');

Display :: display_header($nameTools);

$form = new FormValidator('search', 'post', api_get_self().'?action=search');
$form->addElement('text', 'keyword', 'Search user');
$form->addElement('datepicker', 'publicated_on', 'From date:', array('form_name'=>'search'), 5);
$defaults['publicated_on'] = date('Y-m-d 12:00:00', time()-84600);
$form->addElement('datepicker', 'expired_on', 'To date:', array('form_name'=>'search'), 5);
$defaults['expired_on'] = date('Y-m-d 12:00:00');
$form->addElement('style_submit_button', 'submit', get_lang('Search'), 'class="save"');

$form->setDefaults($defaults);
$form->display();


if(isset($_GET['action']) && $_GET['action'] = 'search'){
		// print_r($_POST);
		echo $videostats->search_user($_POST);
}


/*		FOOTER  */
Display :: display_footer();
?>