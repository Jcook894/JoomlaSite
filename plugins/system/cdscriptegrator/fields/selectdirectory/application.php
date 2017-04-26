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

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'application.php';

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

if (!JRequest::get('post')) jexit();

$directory = JRequest::getString('directory', '', 'post');

if ($directory === '\\') $directory = '';

$called_path = JPath::clean(JPATH_BASE . DS . $directory);

if (!JFolder::exists($called_path))
{
	$directory = '';
	$called_path = JPATH_BASE;
}

$folders_in_directory = JFolder::folders($called_path, '.', false, true);

$html = '';

ob_start();
	require_once dirname(__FILE__) . DS . 'tmpl' . DS . 'default.php';
	$html .= ob_get_contents();
ob_end_clean();

jexit($html);
?>