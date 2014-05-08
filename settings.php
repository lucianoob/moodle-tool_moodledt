<?php
/**
 * Link to MoodleDT
 *
 * @package    tool_moodledt_settings
 * @copyright  2014 IAutomate http://www.iautomate.com.br
 * 
 * <b>License</b>
 * - http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('development', new admin_externalpage('toolmoodledt', get_string('pluginname', 'tool_moodledt'), "$CFG->wwwroot/$CFG->admin/tool/moodledt/"));
}

?>