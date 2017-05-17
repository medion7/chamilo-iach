<?php
/* For licensing terms, see /license.txt */

/* To showing the plugin course icons you need to add these icons:
     * main/img/icons/22/plugin_name.png
     * main/img/icons/64/plugin_name.png
     * main/img/icons/64/plugin_name_na.png
*/
class ExeTranslate extends Plugin
{
    public $is_course_plugin = true;

    //When creating a new course this settings are added to the course
	public $course_settings = array(
           array('name' => 'Insert course id',  'type' => 'text')
	);

    static function create() {
        static $result = null;
        return $result ? $result : $result = new self();
    }

    protected function __construct() {
        parent::__construct('1.0', 'Medion7');
    }

    function install() {
	
	$table = Database::get_main_table('exe_translate_progress');
        $sql = "CREATE TABLE IF NOT EXISTS $table (
                `c_id` int(11) NOT NULL,
				`question_id` int(10) unsigned NOT NULL,
				`exercice_id` int(10) unsigned NOT NULL,
				`translate` varchar(255) DEFAULT NULL,
				PRIMARY KEY (`c_id`,`question_id`,`exercice_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8";
				
        Database::query($sql);
		
		
		
		
        //Installing course settings
        $this->install_course_fields_in_all_courses();
    }

    function uninstall() {
        $t_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $t_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);
        $t_tool = Database::get_course_table(TABLE_TOOL_LIST);

        //Old settings deleting just in case
        $sql = "DELETE FROM $t_settings WHERE sub_key = 'exe_translate'";
        Database::query($sql);

        //hack to get rid of Database::query warning (please add c_id...)
        $sql = "DELETE FROM $t_tool WHERE name = 'exe_translate' AND c_id = c_id";
        Database::query($sql);

        //Deleting course settings
        $this->uninstall_course_fields_in_all_courses();
    }
}