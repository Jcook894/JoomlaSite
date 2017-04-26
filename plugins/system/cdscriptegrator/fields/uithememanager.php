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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldUIThemeManager extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public	$type 		= 'UIThemeManager';
	
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		return '';
	}
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getLabel()
	{
		jimport('joomla.plugin.helper');
		
		$error_msg_tmpl = '<div style="font-weight: bold; border: 3px solid red; background-color: #FFCCCC; font-size: 120%; margin: 10px 0 0 0; padding: 3px; text-align: center">%error%</div>';
		
		if (!JPluginHelper::isEnabled('system', 'cdscriptegrator'))
		{
			echo str_replace('%error%', 'Please enable Core Design Scriptegrator plugin.', $error_msg_tmpl);
			return false;
		}
		
		$type = strtolower($this->type);
		
		// Build the script.
		$script = array();
		
		// UI theme styling
		$uitheme = ($this->element['uitheme'] ? $this->element['uitheme'] : 'smoothness');
		
		$JScriptegrator = JScriptegrator::getInstance('2.5.x.2.1.8');
		$JScriptegrator->importLibrary(
		array(
			'phpjs',
			'jQuery',
			'jQueryUI' => array(
				'uitheme' => (string) $uitheme
			)
		));
		
		if ($error = $JScriptegrator->getError())
		{
			echo str_replace('%error%', $error, $error_msg_tmpl);
			return false;
		}
		
		// Add the script to the document head.
		$document = JFactory::getDocument();
		
		$document->addScript($JScriptegrator->folderPath() . '/fields/' . $type . '/js/' . $type . '.js');
		$document->addStyleSheet($JScriptegrator->folderPath() . '/fields/' . $type . '/css/' . $type . '.css');
		
		// Script function
		$script[] = "
		jQuery(document).ready(function($){
			$('a." . $this->id . "')." . $type . "({
				fncname : '$type',
				uitheme : '$uitheme',
				language : {
					INSTALL_DIALOG_TITLE : '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_SELECT', true) . "',
					UNINSTALL_DIALOG_TITLE : '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME', true) . "',
					ARE_YOU_SURE : '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_ARE_YOU_SURE', true) . "'
				}
				
			});
		});
		";
		
		$document->addScriptDeclaration(implode("\n", $script));
		
		$html = '';
		
		ob_start();
			require_once dirname(__FILE__) . DS . $type . DS . 'tmpl' . DS . 'default.php';
			$html .= ob_get_contents();
		ob_end_clean();
		
		return $JScriptegrator->compressHTML( $html );
	}
}
?>