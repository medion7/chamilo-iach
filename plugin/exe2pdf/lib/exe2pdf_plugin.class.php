<?php
/* For licensing terms, see /license.txt */

/* To showing the plugin course icons you need to add these icons:
     * main/img/icons/22/plugin_name.png
     * main/img/icons/64/plugin_name.png
     * main/img/icons/64/plugin_name_na.png
*/
class Exe2PdfPlugin extends Plugin
{
    public $is_course_plugin = true;

    //When creating a new course this settings are added to the course
//public $course_settings = array(
 //                array('name' => 'big_blue_button_welcome_message',  'type' => 'text'),
   //              array('name' => 'big_blue_button_record_and_store', 'type' => 'checkbox')
  // );

    static function create() {
        static $result = null;
        return $result ? $result : $result = new self();
    }

    protected function __construct() {
        parent::__construct('1.0', 'Medion7');
    }

    function install() {
        //Installing course settings
        $this->install_course_fields_in_all_courses();
    }

    function uninstall() {
        $t_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $t_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);
        $t_tool = Database::get_course_table(TABLE_TOOL_LIST);

        //Old settings deleting just in case
        $sql = "DELETE FROM $t_settings WHERE sub_key = 'exe2pdf'";
        Database::query($sql);

        //hack to get rid of Database::query warning (please add c_id...)
        $sql = "DELETE FROM $t_tool WHERE name = 'exe2pdf' AND c_id = c_id";
        Database::query($sql);

        //Deleting course settings
        $this->uninstall_course_fields_in_all_courses();
    }
}