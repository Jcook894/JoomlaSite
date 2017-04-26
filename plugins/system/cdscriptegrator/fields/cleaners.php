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
jimport('joomla.filesystem.folder');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldCleaners extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public	$type 		= 'Cleaners';
	
	
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
		$html = '';
        $error_msg_tmpl = '<div style="float: left; border: 1px solid left; width: 250px; font-weight: bold; color: red; margin: 5px 0;">%error%</div>';
		
		// cleaners dir
		$cleaners_dir = dirname(dirname(__FILE__)) . DS . 'cleaners';
		
		$cleaners = JFolder::files($cleaners_dir, "\.xml$", false, true);

        if ($cleaners)
        {
            if (!class_exists('JScriptegrator'))
            {
                echo str_replace('%error%', 'Please install and enable Core Design Scriptegrator plugin.', $error_msg_tmpl);
                return false;
            }

            $JScriptegrator = JScriptegrator::getInstance('2.5.x.2.2.5');
            $JScriptegrator->importLibrary(
                array(
                    'jQuery'
                ));

            if ($error = $JScriptegrator->getError())
            {
                echo str_replace('%error%', $error, $error_msg_tmpl);
                return false;
            }
            $document = JFactory::getDocument();
            $document->addScript($JScriptegrator->folderPath() . '/fields/' . strtolower($this->type) . '/js/' . strtolower($this->type) . '.js');
            $document->addStyleSheet($JScriptegrator->folderPath() . '/fields/' . strtolower($this->type) . '/css/' . strtolower($this->type) . '.css');

            $html .= '<div class="' . strtolower($this->type) . '">';
            $html .= '<div style="clear: both;"></div>';
            $html .= '<p style="font-style:italic; font-size: 95%;">';
            $html .= JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_CLEANERS_DESCRIPTION');
            $html .= '</p>';
            $html .= '<h4 class="' . strtolower($this->type) . '_header">';
                $html .= '<a href="#">';
                    $html .= JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_CLEANERS_SUPPORTED_EXTENSIONS');
                $html .= '</a>';
            $html .= '</h4>';

            $html .= '<div class="' . strtolower($this->type) . '_display_none">';
            $html .= '<table border="0" cellspacing="5" cellpadding="3">';
            $html .= '<tr>';
            $html .= '<th style="font-size: 110%;">';
            $html .= JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_CLEANERS_EXTENSION');
            $html .= '</th>';
            $html .= '<th>';
            $html .= JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_CLEANERS_VERSION');
            $html .= '</th>';
            $html .= '<th>';
            $html .= JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_CLEANERS_NOTE');
            $html .= '</th>';
            $html .= '</tr>';

            foreach($cleaners as $cleaner)
            {
                $cleaner = JPath::clean($cleaner);

                $xml = JFactory::getXML($cleaner, true);

                try
                {
                    if (!$xml)
                    {
                        throw new Exception(JText::sprintf('JLIB_INSTALLER_ERROR_LOAD_XML', $cleaner), 500);
                    }
                }
                catch (Exception $e)
                {
                    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    return false;
                }

                $html .= '<tr>';
                $html .= '<td>';
                $html .= (string)$xml->name;
                $html .= '</td>';
                $html .= '<td>';
                $html .= (string)$xml->version;
                $html .= '</td>';
                if ( isset( $xml->note ) and $xml->note ) {
                    $html .= '<td style="font-style: italic; font-size: 90%;">';
                    $html .= (string)$xml->note;
                    $html .= '</td>';
                } else {
                    $html .= '<td></td>';
                }
                $html .= '</tr>';

            }
            $html .= '</table>';
            $html .= '</div>';
            $html .= '</div>';
        }
        return $html;
	}
}
?>