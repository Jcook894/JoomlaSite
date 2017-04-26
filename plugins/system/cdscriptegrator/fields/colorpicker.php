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
class JFormFieldColorpicker extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public	$type 		= 'Colorpicker';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		jimport('joomla.plugin.helper');
		
		$error_msg_tmpl = '<div style="float: left; border: 1px solid left; width: 250px; font-weight: bold; color: red; margin: 5px 0;">%error%</div>';
		
		if (!JPluginHelper::isEnabled('system', 'cdscriptegrator'))
		{
			echo str_replace('%error%', 'Please install and enable Core Design Scriptegrator plugin.', $error_msg_tmpl);
			return false;
		}
		
		$attr = '';
		
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		if ((string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}
		
		$JScriptegrator = JScriptegrator::getInstance('2.5.x.2.1.8');
		$JScriptegrator->importLibrary(
		array(
			'jQuery',
			'jQueryUI' => array( 'withoutTheme', true )
		));
		
		if ($error = $JScriptegrator->getError())
		{
			echo str_replace('%error%', $error, $error_msg_tmpl);
			return false;
		}
		
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		
		// Add the script to the document head.
		$document = JFactory::getDocument();
		
		$document->addScript($JScriptegrator->folderPath() . '/fields/' . strtolower($this->type) . '/js/colorpicker.js');
		$document->addStyleSheet($JScriptegrator->folderPath() . '/fields/' . strtolower($this->type) . '/css/colorpicker.css');
		
		$options = array();
		
		// Build the script.
		$script = array();
		
		// Script function
		$script[] = "
		jQuery(document).ready(function($){
			var input = $('input[name=\"$this->name\"]');
			input.ColorPicker({
				color : '" . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . "',
				flat : false,
				livePreview : true,
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex);
					$(el).ColorPickerHide();
				},
				onShow: function (colpkr) {
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					input.val('#' + hex);
				}
				
			});
		});
		";
		
		$document->addScriptDeclaration(implode("\n", $script));
		
		// Setup variables for display.
		$html = '';
		
		$html .='<input type="text" name="'.$this->name.'" id="'.$this->id.'"' .
				' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .
				$class.$size.$disabled.$readonly.$maxLength.'/>';

		return $html;
	}
}
?>