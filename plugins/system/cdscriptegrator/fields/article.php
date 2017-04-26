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
class JFormFieldArticle extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public	$type = 'Article';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$error_msg_tmpl = '<div style="float: left; border: 1px solid left; width: 250px; font-weight: bold; color: red; margin: 0 0 5px 0;">%error%</div>';
		
		if (!JPluginHelper::isEnabled('system', 'cdscriptegrator'))
		{
			echo str_replace('%error%', 'Please install and enable Core Design Scriptegrator plugin.', $error_msg_tmpl);
			return false;
		}
		
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');
		
		$reset = (int)$this->element['reset'] || 0;
		
		// Build the script.
		$script = array();
		$script[] = '	function jSelectArticle_'.$this->id.'(id, title, catid, object) {';
		$script[] = '		document.id("'.$this->id.'_id").value = id;';
		$script[] = '		document.id("'.$this->id.'_name").value = title;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';
		
		// Reset function
		if ($reset)
		{
			$script[] = "
			window.addEvent('domready', function() {
				$$('a#modal_reset_" . $this->id . "').addEvent('click', function(e) {
					new Event(e).stop();
					$$('input#" . $this->id . "_name').set({ value: '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_ARTICLE_SELECT_ARTICLE', true) . "' });
					$$('input#" . $this->id . "_id').set({ value: '0' });
				});
			});
			";
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));


		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_'.$this->id;

		$db	= JFactory::getDBO();
		$db->setQuery(
			'SELECT title' .
			' FROM #__content' .
			' WHERE id = '.(int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
		}

		if (empty($title))
		{
			$title = JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_ARTICLE_SELECT_ARTICLE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
		$html[] = '</div>';

		// The user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '	<a class="modal" title="'.JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_ARTICLE_SELECT_ARTICLE').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_ARTICLE_SELECT_ARTICLE').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';
		
		// Reset button, if required.
		if ($reset)
		{
			$html[] = '<div class="button2-left">';
			$html[] = '  <div class="blank">';
			$html[] = '	<a class="modal_reset" id="modal_reset_' . $this->id . '" title="' . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_ARTICLE_RESET_ARTICLE') . '"  href="javascript:void(0);">' . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_ARTICLE_RESET_ARTICLE') . '</a>';
			$html[] = '  </div>';
			$html[] = '</div>';
		}

		// The active article id field.
		if (0 == (int)$this->value)
		{
			$value = '';
		} else
		{
			$value = (int)$this->value;
		}

		// class='required' for client side validation
		$class = '';
		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return implode("\n", $html);
	}
}
