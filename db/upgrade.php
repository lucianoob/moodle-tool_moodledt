<?php
/**
 * Script for upgrade all tables.
 *
 * @package    tool_moodledt_upgrade
 * @copyright  2014 IAutomate http://www.iautomate.com.br
 *
 * <b>License</b>
 * - http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_tool_moodledt_upgrade($oldversion) {

	global $CFG, $DB, $USER;
	
	$dbman = $DB->get_manager();
	
	if ($oldversion < 2014030500) {
		
		$table = new xmldb_table('moodledt_bookmark');
		if(!$dbman->table_exists($table)){
			 
			$field_id = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
			$field_plugin_type = new xmldb_field('plugin_type', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'id');
			$field_plugin_name = new xmldb_field('plugin_name', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'plugin_type');
	
			$key = new xmldb_key('primary');
			$key->set_attributes(XMLDB_KEY_PRIMARY, array('id'), null, null);
	
			$index = new xmldb_index('plugin_type_name');
			$index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('plugin_type','plugin_name'));
	
			$table->addField($field_id);
			$table->addField($field_plugin_type);
			$table->addField($field_plugin_name);
	
			$table->addKey($key);
	
			$table->addIndex($index);
	
			$status = $dbman->create_table($table);
			
			upgrade_plugin_savepoint(true, 2014030500, 'tool', 'moodledt');
		}
		
	}
    
    return true;
}

?>
