<?php
/* For licensing terms, see /license.txt */
/**
 *	This script displays a form for registering new users.
 *	@package	 chamilo.auth
 */
/**
 * Code
 */
$language_file = array('registration', 'admin');
if (!empty($_POST['language'])) { //quick hack to adapt the registration form result to the selected registration language
    $_GET['language'] = $_POST['language'];
}
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(CONFIGURATION_PATH).'profile.conf.php';
require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'legal.lib.php';

// Load terms & conditions from the current lang
if (api_get_setting('allow_terms_conditions') == 'true') {
    $get = array_keys($_GET);
    if (isset($get)) {
        if ($get[0] == 'legal'){
            $language = api_get_interface_language();
            $language = api_get_language_id($language);
            $term_preview = LegalManager::get_last_condition($language);
            if (!$term_preview) {
                //look for the default language
                $language = api_get_setting('platformLanguage');
                $language = api_get_language_id($language);
                $term_preview = LegalManager::get_last_condition($language);
            }
            $tool_name = get_lang('TermsAndConditions');
            Display :: display_header('');
            echo '<div class="actions-title">';
            echo $tool_name;
            echo '</div>';
            if (!empty($term_preview['content'])) {
                echo $term_preview['content'];
            } else {
                echo get_lang('ComingSoon');
            }
            Display :: display_footer();
            exit;
        }
    }
}

$tool_name = get_lang('Registration',null,(!empty($_POST['language'])?api_get_valid_language($_POST['language']):$_user['language']));
Display :: display_header($tool_name);

echo Display::tag('h1', $tool_name);

$home = api_get_path(SYS_PATH).'home/';
if ($_configuration['multiple_access_urls']) {
    $access_url_id = api_get_current_access_url_id();
    if ($access_url_id != -1) {
        $url_info = api_get_access_url($access_url_id);
        $url = api_remove_trailing_slash(preg_replace('/https?:\/\//i', '', $url_info['url']));
        $clean_url = replace_dangerous_char($url);
        $clean_url = str_replace('/', '-', $clean_url);
        $clean_url .= '/';
        $home_old  = api_get_path(SYS_PATH).'home/';
        $home = api_get_path(SYS_PATH).'home/'.$clean_url;
    }
}

if (!empty($_SESSION['user_language_choice'])) {
    $user_selected_language = $_SESSION['user_language_choice'];
} elseif (!empty($_SESSION['_user']['language'])) {
    $user_selected_language = $_SESSION['_user']['language'];
} else {
    $user_selected_language = get_setting('platformLanguage');
}

if (file_exists($home.'register_top_'.$user_selected_language.'.html')) {
    $home_top_temp = @(string)file_get_contents($home.'register_top_'.$user_selected_language.'.html');
    $open = str_replace('{rel_path}', api_get_path(REL_PATH), $home_top_temp);
    $open = api_to_system_encoding($open, api_detect_encoding(strip_tags($open)));
    if (!empty($open)) {
        echo '<div style="border:1px solid #E1E1E1; padding:2px;">'.$open.'</div>';
    }
}

// Forbidden to self-register
if (api_get_setting('allow_registration') == 'false') {
    api_not_allowed();
}

//api_display_tool_title($tool_name);
if (api_get_setting('allow_registration') == 'approval') {
    Display::display_normal_message(get_lang('YourAccountHasToBeApproved'));
}

Display::display_normal_message(get_lang('RegInstructions'));
 echo '<div class="normal-message">'; 
 echo '<b>Submission of this form implies that you have read and accepted the <a href="../../home/default_platform_document/pdf/E-learning_Student_Agreement.pdf" target="_blank"><span style="color:#002E8A"><ins>Student Agreement</span></ins></a></b>.';
 echo '</div>';
//if openid was not found
if (!empty($_GET['openid_msg']) && $_GET['openid_msg'] == 'idnotfound') {
    Display::display_warning_message(get_lang('OpenIDCouldNotBeFoundPleaseRegister'));
}


$form = new FormValidator('subscription', 'post', 'inscription.php');

$form->addElement('radio', 'subscription', 'I am interested in:', 'E-learning Course (full) - FREE TRIAL', 'E-learning Course (full)');
$form->addElement('radio', 'subscription', null, 'Individual Module of the E-learning Course', 'Individual Module of the E-learning Course');
$form->addElement('radio', 'subscription', null, 'Postgraduate "Levels of Health" Course', 'Levels of Health 2012');

$form->addElement('style_submit_button', 'submit', get_lang('Continue'), 'class="save"');

$form->display();


echo '<div class="row">';
echo '<div class="error-message">';
echo 'PLEASE NOTE: If you are already a registered member, DO NOT register again. <br>Duplicate registrations will be removed. <br><br>You may log into the platform from the <a href="https://www.vithoulkas.edu.gr/">homepage</a>, <br>go to the Trainings tab and you will see on the bottom of the right side menu <br>a new section "Register to another course". <br>Click the selected course to send an email to the Academy with your request.';
echo '</div></div>';
Display :: display_footer();
