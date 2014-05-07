<?php
/**
 * This is the main script for MoodleDT
 *
 * @package    tool_moodledt
 * @copyright  2014 IAutomate http://www.iautomate.com.br
 * 
 * <b>License</b>
 * - http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once('locallib.php');
require_once('moodledt_forms.php');

admin_externalpage_setup('toolmoodledt');

$PAGE->set_docs_path("http://docs.moodle.org/en/MoodleDT_-_Development_Tools_Plugins_for_Moodle");

$pluginman = plugin_manager::instance();
$plugins = $pluginman->get_plugins();
$plugin_url = '/admin/tool/moodledt/index.php';

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_moodledt'));
echo $OUTPUT->box_start();
echo get_string('plugin_description', 'tool_moodledt');
echo $OUTPUT->box_end();

$mform = new action_form();
$mform->display();

$plugin_type = optional_param ( 'plugintype', '', PARAM_TEXT );
$plugin_name = optional_param ( 'pluginname', '', PARAM_TEXT );
$action = optional_param ( 'action', '', PARAM_TEXT );
$info = optional_param ( 'info', '', PARAM_TEXT );
if(!empty($plugin_type) && !empty($plugin_name))
	$rootdir = $plugins[$plugin_type][$plugin_name]->rootdir;

$execute = optional_param ( 'execute', '', PARAM_TEXT );
if(!empty($execute)){
	switch ($execute){
		case "index_fix":
			$index = index_folders($rootdir);
			index_fix($index);
			echo $OUTPUT->notification(get_string ( 'index_fix_ok', 'tool_moodledt' ), 'notifysuccess');
		break;
		case "tags_order":
			tags_order($rootdir);
			echo $OUTPUT->notification(get_string ( 'tags_order_ok', 'tool_moodledt' ), 'notifysuccess');
			break;
	}
}

if(!empty($action)){
	
	$params = array ('plugintype' => $plugin_type, 'pluginname' => $plugin_name, 'info' => $info, 'action' => $action);
	
	echo $OUTPUT->box_start();
	switch ($info){
		case "package":
			$package_type = optional_param ( 'package_type', '', PARAM_TEXT );
			echo $OUTPUT->heading(get_string('package_header', 'tool_moodledt'), 3, null);
			echo '<b>'.get_string('package_label', 'tool_moodledt').'</b> '.package_plugin($plugin_type, $plugin_name, $rootdir, $package_type);
			break;
		case "update_files":
			$package_type = optional_param ( 'package_type', '', PARAM_TEXT );
			$date_modified = optional_param_array ( 'date_modified', array(), PARAM_INT );
			echo $OUTPUT->heading(get_string('update_files_header', 'tool_moodledt'), 3, null);
			echo '<b>'.get_string('package_label', 'tool_moodledt').'</b> '.package_update_files_plugin($plugin_type, $plugin_name, $rootdir, $package_type, $date_modified);
			break;
		case "language":
			$lang_default = optional_param ( 'lang_default', '', PARAM_TEXT );
			$lang_data = language_analize($plugin_type, $plugin_name, $rootdir, $lang_default);
			
			$params['execute'] = 'tags_order';
			$params['lang_default'] = $lang_default;
			$url_execute = new moodle_url($plugin_url,  $params);

			echo simple_button_link('tags_order', $url_execute, true).'<br>'.$OUTPUT->heading(get_string('lines_header', 'tool_moodledt'), 3, null);
			foreach ($lang_data['lines_language_files'] as $lang => $lines_language_file) {
				echo $lang.': '. $lines_language_file.'<br>';
			}

			echo $OUTPUT->heading(get_string('tags_header', 'tool_moodledt'), 3, null);
			if(!empty($lang_data['tags_not_found'])) {
				foreach ($lang_data['tags_not_found'] as $lang => $tags) {
					echo $lang.': <br>', implode('<br>', $tags).'<br>';
				}
			} else
				echo get_string('tags_empty', 'tool_moodledt');

			echo $OUTPUT->heading(get_string('in_file_header', 'tool_moodledt'), 3, null);
			if(!empty($lang_data['tag_in_files'])) {
				echo get_string('in_file_text', 'tool_moodledt', count($lang_data['tag_in_files'])).'<br>';
				foreach ($lang_data['tag_in_files'] as $i => $tags) {
					echo $tags.'<br>';
				}
			} else
				echo get_string('in_file_empty', 'tool_moodledt');

			break;
		case "index":
			echo $OUTPUT->heading(get_string('index_header', 'tool_moodledt'), 3, null);
			$index = index_folders($rootdir);
			if(!empty($index)) {
				$params['execute'] = 'index_fix';
				$url_execute = new moodle_url($plugin_url,  $params);
				echo '<b>'.get_string('index_label', 'tool_moodledt').'</b> <br>'.$index.'<br>'.simple_button_link('index_fix', $url_execute, true);
			} else
				echo '<b>'.get_string('index_label', 'tool_moodledt').'</b> '.get_string('index_empty', 'tool_moodledt');
			break;
	}
	echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();



die;

?>

