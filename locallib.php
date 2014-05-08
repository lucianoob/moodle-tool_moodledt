<?php
/**
 * Library for use to MoodleDT
 *
 * @package    tool_moodledt_lib
 * @copyright  2014 IAutomate http://www.iautomate.com.br
 *
 * <b>License</b>
 * - http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once('../../../config.php');

/**
 * Function for list files in directory.
 * @package tool_moodledt_lib
 * @param string $dir Path of dir.
 * @return array Return files in directory.
 */
function list_files_plugin($dir) {
	$root = scandir($dir);
	foreach($root as $value){
		if($value === '.' || $value === '..' || strpos("$dir/$value", "/.") !== false) {
			continue;
		}
		if(is_file("$dir/$value")) {
			$result[]="$dir/$value";
			continue;
		}
		foreach(list_files_plugin("$dir/$value") as $file) {
			$result[]=$file;
		}
	}
	return empty($result) ? array() : $result;
}

/**
 * Function for list directory in root directory.
 * @package tool_moodledt_lib
 * @param string $base_dir Path of dir.
 * @return array Return directory list.
 */
function list_dirs_plugin($base_dir) {
	if(!isset($directories))
		$directories = array();
    foreach(scandir($base_dir) as $file) {
    	if($file == '.' || $file == '..' || strpos("$base_dir/$file", "/.") !== false) continue;
        $dir = $base_dir.DIRECTORY_SEPARATOR.$file;
        if(is_dir($dir)) {
        	$directories []= $dir;
            $directories = array_merge($directories, list_dirs_plugin($dir));
        }
    }
    return $directories;
}
/**
 * Function for get languages accepted.
 * @package tool_moodledt_lib
 * @param string $rootdir Directory of plugin.
 * @param string $is_array For return an array.
 * @return Ambigous <string, multitype:string > Return a list of languages.
 */
function lang_support($rootdir, $is_array = false){
	$dir_lang = dir("$rootdir/lang");
	if($is_array)
		$langs = array();
	else
		$langs = "";
	while (false !== ($entry = $dir_lang->read())) {
		if(strpos($entry, '.') === false) {
			if($is_array)
				$langs[$entry] = $entry;
			else
				$langs .= $entry." ";
		}
	}
	$dir_lang->close();
	
	return $langs;
}

/**
 * Function for get last modified in plugin files.
 * @package tool_moodledt_lib
 * @param string $rootdir Directory of plugin.
 * @return number Return a last modified date.
 */
function lastmod_plugin($rootdir){
	
	$date_mod = 0;
	$files_plugin = list_files_plugin($rootdir);
	foreach ($files_plugin as $file_plugin) {
		if($date_mod < filemtime($file_plugin))
			$date_mod = filemtime($file_plugin);
	}
	
	return $date_mod;
}

/**
 * Function for count files of plugins.
 * @package tool_moodledt_lib
 * @param string $rootdir Directory of plugin.
 * @return number Return the number of files.
 */
function count_files_plugin($rootdir){
	$files_plugin = list_files_plugin($rootdir);
	return count($files_plugin);
}

/**
 * Function for get package of plugin. 
 * @package tool_moodledt_lib
 * @param string $plugintype Type of plugin.
 * @param string $pluginname Name of plugin.
 * @param string $rootdir Directory of plugin.
 * @return string Return the link of plugin package.
 */
function package_plugin($plugintype, $pluginname, $rootdir, $package_type){
	global $CFG, $OUTPUT;
	
	$files_plugin = list_files_plugin($rootdir);
	$date_mod = lastmod_plugin($rootdir);
	
	$package_filename = $CFG->dataroot.'/temp/'.$plugintype.'-'.$pluginname.'_'.date('Y-m-d_H-i-s', $date_mod).'.'.$package_type;
	if(file_exists($package_filename)) {
		unlink($package_filename);
	}
	
	if($package_type == 'zip') {
	
		$zip = new zip_archive();
		$zip->open($package_filename);
		foreach ($files_plugin as $file_plugin) {
			$zip->add_file_from_pathname(str_replace($rootdir, $pluginname, $file_plugin), $file_plugin);
		}
		$zip->close();
		
	} else if($package_type == 'tar.gz' || $package_type == 'tar.bz2' || $package_type == 'tar') {
		
		$tar = new PharData(str_replace($package_type, 'tar', $package_filename));
		foreach ($files_plugin as $file_plugin) {
			$tar->addFile($file_plugin, str_replace($rootdir, $pluginname, $file_plugin));
		}
		if($package_type == 'tar.gz')
			$tar->compress(Phar::GZ);
		else if($package_type == 'tar.bz2')
			$tar->compress(Phar::BZ2);
		
	}
	
	$url_export = new moodle_url ( '/admin/tool/moodledt/download_file.php', array ('plugintype' => $plugintype, 'pluginname' => $pluginname, 'file' => $package_filename ));
	$link_export = $OUTPUT->action_link ( $url_export, $plugintype.'-'.$pluginname.'_'.date('Y-m-d_H-i-s', $date_mod).'.'.$package_type.' ('.round(filesize($package_filename)/1024, 2).'Kb)');
	return $link_export;
}

/**
 * Function for get package of plugin content only modified files. 
 * @param string $plugintype Type of plugin.
 * @param string $pluginname Name of plugin.
 * @param string $rootdir Directory of plugin.
 * @return string Return the link of plugin package.
 */
function package_update_files_plugin($plugintype, $pluginname, $rootdir, $package_type, $date_modified){
	global $CFG, $OUTPUT;

	$date_modified = mktime(0, 0, 0, $date_modified['month'], $date_modified['day'], $date_modified['year']);
	
	$files_plugin = list_files_plugin($rootdir);
	$date_mod = lastmod_plugin($rootdir);
	$cont = 0;
	
	$package_filename = $CFG->dataroot.'/temp/'.$plugintype.'-'.$pluginname.'_'.date('Y-m-d_H-i-s', $date_mod).'_update-files.'.$package_type;
	
	if(file_exists($package_filename))
		unlink($package_filename);
	
	if($package_type == 'zip') {
	
		$zip = new zip_archive();
		$zip->open($package_filename);
		foreach ($files_plugin as $file_plugin) {
			if(filemtime($file_plugin) >= $date_modified){
				$zip->add_file_from_pathname(str_replace($rootdir, $pluginname, $file_plugin), $file_plugin);
				$cont++;
			}
		}
		$zip->close();
		
	} else if($package_type == 'tar.gz' || $package_type == 'tar.bz2' || $package_type == 'tar') {
		
		$tar = new PharData(str_replace($package_type, 'tar', $package_filename));
		foreach ($files_plugin as $file_plugin) {
			if(filemtime($file_plugin) >= $date_modified){
				$tar->addFile($file_plugin, str_replace($rootdir, $pluginname, $file_plugin));
				$cont++;
			}
		}
		if($package_type == 'tar.gz')
			$tar->compress(Phar::GZ);
		else if($package_type == 'tar.bz2')
			$tar->compress(Phar::BZ2);
		
	}
	
	if($cont > 0){
		$url_export = new moodle_url ( '/admin/tool/moodledt/download_file.php', array ('plugintype' => $plugintype, 'pluginname' => $pluginname, 'file' => $package_filename ));
		$link_export = $OUTPUT->action_link ( $url_export, $plugintype.'-'.$pluginname.'_'.date('Y-m-d_H-i-s', $date_mod).'_update-files.'.$package_type.' ('.round(filesize($package_filename)/1024, 2).'Kb) ['.$cont.' '.get_string('files', 'tool_moodledt').']');
	} else 
		$link_export = get_string('update_files_empty', 'tool_moodledt');
	return $link_export;
}

/**
 * Function for analize the language files of plugin.
 * @package tool_moodledt_lib
 * @param string $plugintype Type of plugin.
 * @param string $pluginname Name of plugin.
 * @param string $rootdir Directory of plugin.
 * @param string $lang_default Language default of plugin.
 * @return Ambigous <multitype:, mixed> Return an array with information of languages files.
 */
function language_analize($plugintype, $pluginname, $rootdir, $lang_default){
	
	$file_default = "$rootdir/lang/$lang_default/".$plugintype."_".$pluginname.".php";
	if(!file_exists($file_default))
		$file_default = "$rootdir/lang/$lang_default/".$pluginname.".php";
	if(!file_exists($file_default))
		$file_default = "$rootdir/lang/en/".$pluginname.".php";
	$tmp = file($file_default);
	$lines_default = array();
	for ($i = 0; $i < count($tmp); $i++){
		$tmp[$i] = str_replace(" ", "", $tmp[$i]);
		if(strpos($tmp[$i], 'string[') !== false)
			$lines_default[] = str_replace("\$string['", "", explode("']" , $tmp[$i])[0]);
	}
	
	$langs_plugin = list_files_plugin("$rootdir/lang");
	
	$return = array();
	
	foreach (array_count_values($lines_default) as $tag => $value){
		if($value > 1)
			$return['tags_duplicates'][$lang_default][] = $tag;
	}
	
	$return['lines_language_files'][$lang_default] = "<b>".count($lines_default).get_string('lines_text', 'tool_moodledt')."</b>";
	
	foreach ($langs_plugin as $lang_plugin) {
		if(strpos($lang_plugin, '.php') !== false && strpos($lang_plugin, "/$lang_default/") === false && strpos($lang_plugin, 'index.php') === false){
			$lang = explode('/', $lang_plugin)[count(explode('/', $lang_plugin))-2];
			$tmp = file($lang_plugin);
			$lines[$lang] = array();
			for ($i = 0; $i < count($tmp); $i++){
				$tmp[$i] = str_replace(" ", "", $tmp[$i]);
				if(strpos($tmp[$i], 'string[') !== false)
					$lines[$lang][] = str_replace("\$string['", "", explode("']" , $tmp[$i])[0]);
			}
			if(count($lines_default) < count($lines[$lang]))
				$return['lines_language_files'][$lang] = "<font color='orange'><b>".count($lines[$lang]).get_string('lines_text', 'tool_moodledt')."</b></font>";
			else if(count($lines_default) > count($lines[$lang]))
				$return['lines_language_files'][$lang] = "<font color='red'><b>".count($lines[$lang]).get_string('lines_text', 'tool_moodledt')."</b></font>";
			else 
				$return['lines_language_files'][$lang] = count($lines[$lang]).get_string('lines_text', 'tool_moodledt');
		}
	}
	if(!empty($lines)) {
		foreach ($lines as $key => $line) {
			foreach (array_count_values($line) as $tag => $value){
				if($value > 1)
					$return['tags_duplicates'][$key][] = $tag;
			}
			
			$diff = array_diff($lines_default, $line);
			if(!empty($diff)) {
				foreach ($diff as $value)
					$return['tags_not_found'][$key][] = $value;
			} else 
				$return['tags_not_found'][$key][] = get_string('ok_text', 'tool_moodledt');
			
			$left = array_diff($line, $lines_default);
			if(!empty($left)) {
				foreach ($left as $value)
					$return['tags_left'][$key][] = $value;
			} else
				$return['tags_left'][$key][] = get_string('ok_text', 'tool_moodledt');
		}
	}
	
	$return['tag_in_files'] = array();
	$files_plugin = list_files_plugin($rootdir);
	foreach($lines_default as $tag) {
		$in_file = false;
		//echo "<br>".$tag.": ";
		if($tag != ($plugintype.'_'.$pluginname) && strpos($tag, ":") === false && strpos($tag, "_help") !== strlen($tag)-5) {
			foreach($files_plugin as $file_plugin) {
				$file = str_replace(' ', '', file_get_contents($file_plugin));
				$in_file |= (strpos($file, "get_string('$tag'") !== false || strpos($file, 'get_string("'.$tag.'"') !== false || strpos($file, "simple_button_link('".$tag."'") !== false) ? true : false;
				//if($in_file)
					//echo "<br>----> $file_plugin";
			}
			if (!$in_file)
				$return['tag_in_files'][] = $tag;
		}
	}
	
	return $return;
}

/**
 * Function for list folders without index.
 * @param string $rootdir Directory of plugin.
 * @return string Return an string with folders without index.
 */
function index_folders($rootdir){
	$index = "";
	$folders = list_dirs_plugin($rootdir);
	array_unshift($folders, $rootdir);
	foreach($folders as $folder) {
		if(!file_exists("$folder/index.html") && !file_exists("$folder/index.htm") && !file_exists("$folder/index.php"))
			$index .= "$folder<br>";
	}
	return $index;
}

/**
 * Function for insert index in folders of plugin.
 * @param string $listFolders List of folders without index.
 */
function index_fix($listFolders){
	$listFolders = explode("<br>", $listFolders);
	
	$indexphp = "<?php";
	$indexphp .= "\n\theader('HTTP/1.0 403 Forbidden');";
	$indexphp .= "\n\n\trequire_once('#REF#config.php');";
	$indexphp .= "\n\n\t\$PAGE->set_url(\$_SERVER['PHP_SELF']);";
	$indexphp .= "\n\t\$PAGE->set_pagelayout('admin');";
	$indexphp .= "\n\t\$PAGE->set_context(context_system::instance());";
	$indexphp .= "\n\techo \$OUTPUT->header();";
	$indexphp .= "\n\techo \$OUTPUT->heading(get_string('error'));";
	$indexphp .= "\n\techo '<center>'.get_string('nopermissiontoshow', 'core_error').'</center>';";
	$indexphp .= "\n?>";
	
	$path_root = explode("/", __DIR__);
	$path_root = implode("/", array_slice($path_root, 0, count($path_root)-3));
	
	foreach ($listFolders as $folder){
		if($folder != '') {
			
			$path_ref = explode("/", str_replace($path_root, "", $folder));
			array_pop($path_ref);
			$ref = "";
			foreach ($path_ref as $tmp) {
				$ref .= "../";
			}
			file_put_contents($folder.'/index.php', str_replace("#REF#", $ref, $indexphp));
		}
	} 
}
/**
 * Function for order tags in language files.
 * @param string $rootdir Directory of plugin.
 */
function tags_order($rootdir) {
	$langs_plugin = list_files_plugin("$rootdir/lang");
	foreach ($langs_plugin as $lang_plugin) {
		if(strpos($lang_plugin, '.php') !== false && strpos($lang_plugin, 'index.php') === false){
			$tmp = file($lang_plugin);
			$lines_header = array();
			$lines_tags = array();
			for ($i = 0; $i < count($tmp); $i++){
				if(strpos($tmp[$i], 'string[') !== false)
					$lines_tags[] = $tmp[$i];
				else if(strpos($tmp[$i], '?>') === false)
					$lines_header[] = $tmp[$i];
			}
			asort($lines_tags);
			$tmp = array_merge($lines_header, $lines_tags);
			$tmp[] = '?>';
			file_put_contents($lang_plugin, $tmp);
		}
	}
}
/**
 * Function for create an simple html button for load a location.
 * @param string $id Code of string for button text.
 * @param string $link Url for load location on user click.
 * @param boolean $is_help If button help is visible.
 * @return string Return string with button.
 */
function simple_button_link($id, $link, $is_help = false) {
	global $OUTPUT;
	
	$text = get_string($id, 'tool_moodledt');
	$button = '<input type="button" value="'.$text.'" onClick="javascript: window.location=\''.$link.'\';"/>';
	if($is_help)
		$button .= $OUTPUT->help_icon($id, 'tool_moodledt');
	return $button;
}
?>