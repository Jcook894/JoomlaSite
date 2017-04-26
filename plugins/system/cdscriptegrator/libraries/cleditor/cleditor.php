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
 * CLEditor class
 * 
 * @author Daniel Rataj
 */
class PlgSystemCDScriptegratorLibraryCLEditor
{
	/* @var object - Scriptegrator plugin parameters */
	private $plg_params	= null;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->plg_params = JScriptegrator::getInstance()->scriptegratorPluginParams();
	}
	
	/**
	 * Get instance
	 * 
	 * @param	$params
	 * @return	instance
	 */
	public static function getInstance()
	{
		static $instance;
		if ( $instance == null )
		{
			$instance = new PlgSystemCDScriptegratorLibraryCLEditor();
		}
		return $instance;
	}
	
	/**
	 * Import files to header
	 * 
	 * @return array
	 */
	public function importFiles()
	{
		return array(
			'js/jquery.cleditor.min.js',
			'css/jquery.cleditor.css'
		);
	}
	
	/**
	 * Script declaration
	 * 
	 * @return string
	 */
	public function scriptDeclaration()
	{
		$script_options = array();
		
		// define database parameters
		$outlineType = $this->plg_params->get( 'outlineType', 'rounded-white' );
		
		$editor_options = array();
		$editor_options[] = 'width : ' . (int) $this->plg_params->get( 'editor_width', 500 );
		$editor_options[] = 'height : ' . (int) $this->plg_params->get( 'editor_height', 300 );
		$editor_options[] = 'controls : "' . (string) $this->plg_params->get( 'editor_controls', 'bold italic underline strikethrough subscript superscript | font size style | color highlight removeformat | bullets numbering | outdent indent | alignleft center alignright justify | undo redo | rule image link unlink | cut copy paste pastetext | print source' ) . '"';
		
		$script = "
		<!--
		(function (){
			if (typeof CLEditor === 'undefined') CLEditor = {};
			CLEditor.configuration = {" . implode( ", ", $editor_options ) . " };
		})();
		//-->
		";
		
		return $script;
	}
}
?>