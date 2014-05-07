<?php
/**
 * Script for manager bookmarks plugins.
 *
 * @package    tool_moodledt_bookmark
 * @copyright  2014 IAutomate http://www.iautomate.com.br
 *
 * <b>License</b>
 * - http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

global $CFG, $DB;

$action = optional_param ( 'action', '', PARAM_TEXT );
$id = optional_param ( 'id', 0, PARAM_INT );
$plugintype = optional_param ( 'plugintype', '', PARAM_TEXT );
$pluginname = optional_param ( 'pluginname', '', PARAM_TEXT );

if(!empty($action)) {

	$entry = new stdClass ();
	$entry->plugin_type = $plugintype;
	$entry->plugin_name = $pluginname;

	switch ($action) {
		case 'add':
			if(!empty($plugintype) && !empty($pluginname))
				$entry->id = $DB->insert_record ( "moodledt_bookmark", $entry );
			break;
		case 'del':
			if($id != 0)
				$DB->delete_records ( "moodledt_bookmark", array ('id' => $id ) );
			break;
		case 'empty':
			$DB->delete_records ( "moodledt_bookmark", array () );
			break;
	}
}

redirect(new moodle_url("$CFG->wwwroot/admin/tool/moodledt/index.php"));
?>