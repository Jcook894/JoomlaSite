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
class JFormFieldUIThemeCombobox extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public	$type 		= 'UIThemeCombobox';
	
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
		
		$attr = ' style="width: 230px;"';
		
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		if ((string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}
		
		// UI theme styling
		$uitheme = 'ui-lightness';
		if (isset($this->element['uitheme']))
		{
			$uitheme = $this->element['uitheme'];
		}
		
		// value
		if ((string)$this->value === '') {
			if (isset($this->element['default'])) {
				$this->value = $this->element['default'];
			} else {
				$this->value = $uitheme;
			}
		}
		
		$uitheme = $this->value;
		
		$JScriptegrator = JScriptegrator::getInstance('2.5.x.2.1.8');
		$JScriptegrator->importLibrary(
		array(
			'jQuery',
			'jQueryUI' => array(
				'uitheme' => (string)$uitheme, // must be presented as string
				'withoutScript' => true
			)
		));
		
		if ($error = $JScriptegrator->getError())
		{
			echo str_replace('%error%', $error, $error_msg_tmpl);
			return false;
		}
		
		// mainCSS
		$mainCSS = 'ui';
		
		// Add the script to the document head.
		$document = JFactory::getDocument();
		
		$document->addScript($JScriptegrator->folderPath() . '/fields/' . strtolower($this->type) . '/js/jquery.dd.js');
		$document->addStyleSheet($JScriptegrator->folderPath() . '/fields/' . strtolower($this->type) . '/css/dd.css');
		
		$options = array();
		
		$options = $JScriptegrator->themeList();
		
		$uithemes = array();
		foreach($options as $option)
		{
			$uithemes[$option] = $option;
		}
		$options = $uithemes;
		unset($uithemes);
		
		// Build the script.
		$script = array();
		
		// Script function
		$script[] = "
		jQuery(document).ready(function($){
			var select = $('select[name=\"$this->name\"]');
			select.children('option').each(function () {
				var src_value = '" . $JScriptegrator->folderPath() . "/libraries/jqueryui/css/' + $(this).val() + '/' + $(this).val() + '.png';
				$(this).attr('title', src_value);
			});
			var msDropDown = select.msDropDown({
				mainCSS : '$mainCSS'
			});
			msDropDown.parent('div').next('div').addClass('$uitheme').children('.ddTitle').addClass('ui-state-default ui-corner-all').hover(
				function() { $(this).addClass('ui-state-hover'); },
				function() { $(this).removeClass('ui-state-hover'); }
			);
			msDropDown.parent('div').next('div').children('.ddChild').children('a').addClass('ui-state-default').hover(
				function() { $(this).addClass('ui-state-hover'); },
				function() { $(this).removeClass('ui-state-hover'); }
			);
		});
		";
		
		$document->addScriptDeclaration(implode("\n", $script));
		
		// Setup variables for display.
		$html = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		$html .= '<div style="padding: 5px 0; clear: both;" />';

		return $JScriptegrator->compressHTML($html);
	}
}
?>