<?php
/**
 * This is the download script for MoodleDT
 *
 * @package    tool_moodledt
 * @copyright  2014 IAutomate http://www.iautomate.com.br
 * 
 * <b>License</b>
 * - http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir . '/pluginlib.php');

$file = htmlentities(optional_param ( 'file', '', PARAM_TEXT ));

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header('Content-type: application/x-gzip');
header("Content-Disposition: attachment; filename=\"".basename($file)."\";");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".@filesize($file));
set_time_limit(0);
@readfile($file) or die("File not found.");
unlink($file);
exit;

?>