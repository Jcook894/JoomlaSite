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

defined('_JEXEC') or die;

/**
 * jQuery UI class
 * 
 * @author Daniel Rataj
 * 
 */
class PlgSystemCDScriptegratorLibraryjQueryUI
{
	public 			$params 	=	 array();
	private static 	$ui_version	=	'1.8.21';
	
	/**
	 * Constructor
	 * 
	 * @param 	array	 $params
	 * @return 	void
	 */
	public function __construct($params = array())
	{
		$this->params = $params;
		
		if (!isset($this->params['uitheme']))
		{
			$this->params['uitheme'] = 'ui-lightness';
		}
		if (!isset($this->params['withoutScript']))
		{
			$this->params['withoutScript'] = false;
		}
		if (!isset($this->params['withoutTheme']))
		{
			$this->params['withoutTheme'] = false;
		}
	}
	
	/**
	 * Get instance
	 * 
	 * @param	array		$params
	 * @return	instance
	 */
	public static function getInstance($params = array())
	{
		static $instances;
		
		if (!isset($instances))
		{
			$instances = array();
		}
		
		$hash = md5('jqueryui_' . serialize($params));
		
		if (empty($instances[$hash]))
		{
			$library = new PlgSystemCDScriptegratorLibraryjQueryUI($params);
			$instances[$hash] = $library;
		}
		
		return $instances[$hash];
	}
	
	/**
	 * Import files to header
	 * 
	 * @return 	array
	 */
	public function importFiles()
	{
		$ui_files = array();
		$ui_files['uiscript'] = 'js/jquery-ui-' . self::$ui_version . '.custom.min.js';
		$ui_files['uitheme'] = 'css/' . $this->params['uitheme'] . '/jquery-ui-' . self::$ui_version . '.custom.css';
		
		// no JS
		if ($this->params['withoutScript'] === true)
		{
			unset($ui_files['uiscript']);
		}
		
		// no CSS
		if ($this->params['withoutTheme'] === true)
		{
			unset($ui_files['uitheme']);
		}
		
		// check if UI theme really exists - if required
		if ($this->params['withoutTheme'] === false and $this->params['uitheme'])
		{
			if(!in_array($this->params['uitheme'], JScriptegrator::getInstance()->themeList()))
			{
				return JText::sprintf('PLG_SYSTEM_CDSCRIPTEGRATOR_ERROR_MISSING_JQUERY_UI_THEME', $this->params['uitheme']);
			}
		}
		
		return $ui_files;
	}
}
?>