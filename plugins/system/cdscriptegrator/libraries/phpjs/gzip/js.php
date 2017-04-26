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

$files = array();
if (isset($_GET['files']) && $_GET['files']) $files = $_GET['files'];

if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

header("Content-type: application/x-javascript; charset=UTF-8");
header('Cache-Control: must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

foreach ($files as $file) {
	
	$file = str_replace( chr( 0 ), '', $file );
	
	// get extension, based on Joomla! JFile::getExt() function
	$ext = substr($file, strrpos($file, '.') + 1);
	
	$filepath = $dir . DS . $file;
	
	if (is_file($filepath) and $ext === 'js') {
		include($filepath);
		echo "\n";
	}
}
?>