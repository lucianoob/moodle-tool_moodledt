<?php
/**
 * Forms for use to MoodleDT
 *
 * @package    tool_moodledt_forms
 * @copyright  2014 IAutomate http://www.iautomate.com.br
 *
 * <b>License</b>
 * - http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir . '/pluginlib.php');
require_once('locallib.php');

/**
 * Class of action form.
 */
class action_form extends moodleform {
	/**
	 * Definition of action form.
	 */
	public function definition() {
		global $CFG, $DB, $OUTPUT, $PAGE;
		$plugin_url = '/admin/tool/moodledt/index.php';

		$mform = $this->_form;
		$pluginman = plugin_manager::instance();
		$plugins = $pluginman->get_plugins();
		
		$plugin_type = optional_param ( 'plugintype', '', PARAM_TEXT );
		$plugin_name = optional_param ( 'pluginname', '', PARAM_TEXT );
		
		$is_view = false;
		$mform->addElement('header', 'bookmarks', get_string('bookmarks_header', 'tool_moodledt').$OUTPUT->help_icon('bookmarks_header', 'tool_moodledt'));
		if(method_exists($mform, 'setExpanded'))
			$mform->setExpanded('bookmarks');
		$bookmarks = $DB->get_records ( "moodledt_bookmark", array());
		if($bookmarks) {
			$link_bookmarks = "";
			foreach ($bookmarks as $bookmark) {
				$url_bookmark = new moodle_url($plugin_url, array ('plugintype' => $bookmark->plugin_type, 'pluginname' => $bookmark->plugin_name) );
				$link_bookmark = $OUTPUT->action_link ( $url_bookmark, $bookmark->plugin_type." > ".$bookmark->plugin_name );
				if(!$is_view) {
					if($is_view = ($bookmark->plugin_type == $plugin_type && $bookmark->plugin_name == $plugin_name))
						$link_bookmark = "<b>$link_bookmark</b>";
				}
				if($PAGE->user_is_editing() == 1) {
					$url_delete = new moodle_url ( '/admin/tool/moodledt/bookmark.php', array ('action' => 'del', 'id' => $bookmark->id) );
					$link_bookmark = $link_bookmark."  ".$OUTPUT->action_link ( $url_delete, "<font color='red'><b>x</b></font>", null, array('title' => get_string('bookmarks_del', 'tool_moodledt')) );
				}
				$link_bookmarks .= $link_bookmark."<br>";
			}
			if($PAGE->user_is_editing() == 1) {
				$url_empty = new moodle_url ( "$CFG->wwwroot/admin/tool/moodledt/bookmark.php", array ('action' => 'empty') );
				$link_empty = $OUTPUT->action_link ( $url_empty, "<font color='red'><b>".get_string("bookmarks_del_all", 'tool_moodledt')."</b></font>" );
				$link_bookmarks .= "<br>".$link_empty."<br>";
			}
			$mform->addElement('html', $link_bookmarks);
		} else
			$mform->addElement('html', get_string('bookmarks_empty', 'tool_moodledt'));
		
		$mform->addElement('header', 'plugin', get_string('plugin_header', 'tool_moodledt'));
		if(method_exists($mform, 'setExpanded'))
			$mform->setExpanded('plugin');
		
		if(empty($plugin_type)) {
			
			$option = array();
			foreach ($plugins as $key => $value) {
				$option[$key] = $key;
			}
			$mform->addElement('select', 'plugintype', get_string('type_label', 'tool_moodledt'), $option);
			
			$this->add_action_buttons(false, get_string('select_button', 'tool_moodledt'));
			
		} else {
			
			$mform->addElement('hidden', 'plugintype', $plugin_type);
			$mform->setType('plugintype', PARAM_TEXT);
			$mform->addElement('static', 'plugintype_static', get_string('type_label', 'tool_moodledt'), $plugin_type);
			if(empty($plugin_name)){
			
				$option = array();
				foreach ($plugins[$plugin_type] as $key => $value) {
					$option[$key] = $key;
				}
				$mform->addElement('select', 'pluginname', get_string('name_label', 'tool_moodledt'), $option);
			
				$action_buttons = array();
				$action_buttons[] = &$mform->createElement('submit', 'select_button', get_string('select_button', 'tool_moodledt'));
				$action_buttons[] = &$mform->createElement('button', 'cancel_button', get_string('cancel'), array('onClick' => "window.location = '".new moodle_url($plugin_url)."';"));
				$mform->addGroup($action_buttons, 'action_buttons', '', array(' '), false);
				$mform->closeHeaderBefore('action_buttons');
			
			} else {
				
				$mform->addElement('hidden', 'pluginname', $plugin_name);
				$mform->setType('pluginname', PARAM_TEXT);
				$mform->addElement('static', 'pluginname_static', get_string('name_label', 'tool_moodledt'), $plugin_name);
				
				$displayname = $plugins[$plugin_type][$plugin_name]->displayname;
				$rootdir = $plugins[$plugin_type][$plugin_name]->rootdir;
				$versiondb = $plugins[$plugin_type][$plugin_name]->versiondb;
				
				$mform->addElement('static', 'title', get_string('title_label', 'tool_moodledt'), $displayname);
				$mform->addElement('static', 'path', get_string('path_label', 'tool_moodledt'), $rootdir);
				$mform->addElement('static', 'version', get_string('version_label', 'tool_moodledt'), $versiondb);
				$mform->addElement('static', 'langs', get_string('languages_label', 'tool_moodledt'), lang_support($rootdir));
				$mform->addElement('static', 'files', get_string('files_label', 'tool_moodledt'), count_files_plugin($rootdir).get_string('files_text', 'tool_moodledt'));
				$mform->addElement('static', 'last_mod', get_string('last_mod_label', 'tool_moodledt'), userdate(lastmod_plugin($rootdir)));
				
				if(!$is_view) {
					$url_add = new moodle_url ( '/admin/tool/moodledt/bookmark.php', array ('action' => 'add', 'plugintype' => $plugin_type, 'pluginname' => $plugin_name) );
					$link_add = $OUTPUT->action_link ( $url_add, get_string("bookmarks_add", 'tool_moodledt') );
					$mform->addElement('static', 'bookmarks_add', '', $link_add);
				}
				$mform->addElement('button', 'cancel_button', get_string('cancel'), array('onClick' => "window.location = '".new moodle_url($plugin_url)."';"));
				$mform->closeHeaderBefore('cancel_button');
				
				$mform->addElement('header', 'tool', get_string('tool_header', 'tool_moodledt'));
				if(method_exists($mform, 'setExpanded'))
					$mform->setExpanded('tool');
				
				$option = array('' => '',
						'package' => get_string('package_option', 'tool_moodledt'),
						'update_files' => get_string('update_files_option', 'tool_moodledt'),
						'language' => get_string('language_option', 'tool_moodledt'),
						'index' => get_string('index_option', 'tool_moodledt')
				);
				$mform->addElement('select', 'info', get_string('action_label', 'tool_moodledt'), $option, array('onChange' => 'if(this.value != ""){this.form.submit()};'));
				
				$package_types = array('zip' => 'zip',
						'tar.gz' => 'tar.gz',
						'tar.bz2' => 'tar.bz2',
						'tar' => 'tar'
				);
				
				$info = optional_param ( 'info', '', PARAM_TEXT );
				if(!empty($info)){
					$mform->setDefault('info', $info);
					switch ($info){
						case "package":
							$mform->addElement('static', 'description', get_string('description_label', 'tool_moodledt'), get_string('package_description', 'tool_moodledt'));
							$mform->addElement('select', 'package_type', get_string('package_type', 'tool_moodledt'), $package_types);
							$mform->addHelpButton('package_type', 'package_type', 'tool_moodledt');
							break;
						case "update_files":
							$mform->addElement('static', 'description', get_string('description_label', 'tool_moodledt'), get_string('update_files_description', 'tool_moodledt'));
							$mform->addElement('date_selector', 'date_modified', get_string('date_modified', 'tool_moodledt'));
							$mform->addHelpButton('date_modified', 'date_modified', 'tool_moodledt');
							$mform->addElement('select', 'package_type', get_string('package_type', 'tool_moodledt'), $package_types);
							$mform->addHelpButton('package_type', 'package_type', 'tool_moodledt');
							break;
						case "language":
							$mform->addElement('static', 'description', get_string('description_label', 'tool_moodledt'), get_string('language_description', 'tool_moodledt'));
							$mform->addElement('select', 'lang_default', get_string('lang_default_label', 'tool_moodledt'), lang_support($rootdir, true));
							$mform->setDefault('lang_default', current_language());
							$mform->addHelpButton('lang_default', 'lang_default_label', 'tool_moodledt');
							break;
						case "index":
							$mform->addElement('static', 'description', get_string('description_label', 'tool_moodledt'), get_string('index_description', 'tool_moodledt'));
							break;
					}
					$mform->addElement('submit', 'action', get_string('execute_button', 'tool_moodledt'));
					$mform->closeHeaderBefore('action');
				}
			}
		}
		$mform->disable_form_change_checker();
	}
}
?>