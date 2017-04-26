<?php
/**
 * Core Design Scriptegrator plugin for Joomla! 2.5
 * @author		Daniel Rataj, <info@greatjoomla.com>
 * @package		Joomla 
 * @subpackage	System
 * @category	Plugin
 * @version		2.5.x.2.2.5
 * @copyright	Copyright (C) 2007 - 2012 Great Joomla!, http://www.greatjoomla.com
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL 3
 * 
 * This file is part of Great Joomla! extension.   
 * This extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

define('DS', DIRECTORY_SEPARATOR);
$dir = dirname(dirname(__FILE__));

// Set file
$files = array();
if (isset($_GET['files']) && $_GET['files']) $files = $_GET['files'];

// zlib output
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

$content = '';

foreach ($files as $file) {
	
	$file = str_replace( chr( 0 ), '', $file );
	
	// get extension, based on Joomla! JFile::getExt() function
	$ext = substr($file, strrpos($file, '.') + 1);
	
	$theme = substr(str_replace('css/', '', $file), 0, strpos(str_replace('css/', '', $file), '/'));
	
	$filepath = $dir . DS . $file;
	
	if (is_file($filepath) and $ext === 'css') {
		ob_start();
			include($filepath);
			$tmp_content = ob_get_contents();
		ob_end_clean();
		
		if (strpos($tmp_content, 'images/') !== false) {
			$content .= str_replace('images/', '../css/' . $theme . '/images/', $tmp_content);
		}
	}
}

header('Content-type: text/css; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

echo $content;
?>