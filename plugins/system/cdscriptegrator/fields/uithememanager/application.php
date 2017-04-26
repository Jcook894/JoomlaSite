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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

// no post request
if (!JRequest::get('post')) jexit();

$return = JRequest::getString('return', '', 'post');
$return = base64_decode($return);

$JScriptegrator = JScriptegrator::getInstance('2.5.x.2.1.8');
$uitheme_root_dir = $JScriptegrator->folderPath(true) . DS . 'libraries' . DS . 'jqueryui' . DS . 'css';

switch(JRequest::getCmd('task', 'install', 'post'))
{
	case 'install':
		
		if (!isset($_FILES['uithememanager_uploadfile']) or $_FILES['uithememanager_uploadfile']['error'])
		{
			$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
		}
		
		$file_uploadname = $_FILES['uithememanager_uploadfile']['name'];
		$file_tmp_path = $_FILES['uithememanager_uploadfile']['tmp_name'];
		$file_mime = $_FILES['uithememanager_uploadfile']['type'];
		$file_size = $_FILES['uithememanager_uploadfile']['size'];
		
		$root_dir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
		$tmp_dir = JFactory::getConfig()->get('tmp_path', $root_dir . DS . 'tmp') . DS . 'uitheme';
		
		if (JFolder::exists($tmp_dir))
		{
			if (!JFolder::delete($tmp_dir))
			{
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
			}
		}
		
		unset($root_dir);
		
		// upload UI Theme to jQuery UI Themes root directory
		if(JFile::upload($file_tmp_path, $tmp_dir . DS . $file_uploadname))
		{
			
			// unpack file
			if(!JArchive::extract($tmp_dir . DS . $file_uploadname, $tmp_dir))
			{
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
			}
			
			// delete uploaded ZIP file
			if(!JFile::delete($tmp_dir . DS . $file_uploadname))
			{
				// file have been uploaded
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
			}
			
			$folders = JFolder::folders($tmp_dir);
			if (count($folders) !== 1)
			{
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
			}
			
			$uitheme_foldername = $folders[0];
			
			// theme already installed
			if (JFolder::exists($uitheme_root_dir . DS . $uitheme_foldername))
			{
				$application->redirect($return, JText::sprintf('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_ALREADY_INSTALLED', $uitheme_foldername), 'error');
			}
			
			// check folder existence
			if (!JFolder::exists($tmp_dir . DS . $uitheme_foldername))
			{
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_NOT_COMPATIBLE'), 'error');
			}
			
			// check file structure in extracted folder
			$filelist = JFolder::files($tmp_dir . DS . $uitheme_foldername);
			$folderlist = JFolder::folders($tmp_dir . DS . $uitheme_foldername);
			
			// check count of files
			// Installation packace must contains 4 files and one directory.
			if (count($filelist) !== 3 and count($folderlist) !== 1)
			{
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_NOT_COMPATIBLE'), 'error');
			}
			
			// check files
			foreach($filelist as $file)
			{
				// index.html
				if ($file === 'index.html')
				{
					continue;
				}
				// screeenshot
				if ($file === $uitheme_foldername . '.png')
				{
					continue;
				}
				// CSS
				if (JFile::getExt($file) === 'css')
				{
					if (!preg_match('#^jquery-ui-[0-9.]*?\.custom\.css$#', $file))
					{
						$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_NOT_COMPATIBLE'), 'error');
						break;
					}
					continue;
				}
				
			}
		
			if (!JFolder::move($tmp_dir . DS . $uitheme_foldername, $uitheme_root_dir . DS . $uitheme_foldername))
			{
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
			}
			
			// delete previously created "tmp/uitheme" folder
			if (!JFolder::delete($tmp_dir))
			{
				$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
			}
			
			// installation success
			$application->redirect($return, JText::sprintf('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_SUCCESS', $uitheme_foldername), 'message');
		}
		
		// upload error
		$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_INSTALLATION_FAILED'), 'error');
		
		break;
		
	case 'uninstall':
		$uitheme = JRequest::getString('uitheme', '', 'post');
		
		// prevent non existing variable
		if (!$uitheme)
		{
			$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME_UNINSTALLATION_FAILED'), 'error');
		}
		
		// check if this theme really exists
		if (!in_array($uitheme, $JScriptegrator->themeList()))
		{
			$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME_UNINSTALLATION_FAILED'), 'error');
		}
		
		if (!JFolder::delete($uitheme_root_dir . DS . $uitheme))
		{
			$application->redirect($return, JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME_UNINSTALLATION_FAILED'), 'error');
		}
		
		$application->redirect($return, JText::sprintf('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME_UNINSTALLATION_SUCCESS', $uitheme), 'message');
		break;
	
	default:
		jexit();
		break;
}
?>