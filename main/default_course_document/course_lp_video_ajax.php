<?php
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once 'course_lp_video_functions.php';

api_protect_course_script();
api_block_anonymous_users();

$method = isset($_POST['method']) ? $_POST['method'] : '';
$json = array();

if ($method == 'get_token'){
    $json['status'] = 'ok';
    $json['token'] = create_token();
} else {
    $json['status'] = 'error';
}

echo json_encode($json);