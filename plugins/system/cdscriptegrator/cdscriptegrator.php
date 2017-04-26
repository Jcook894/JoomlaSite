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

// no direct access
defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.environment.response');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.utilities.string');

/**
 * Core Design Scriptegrator plugin
 *
 * @author		Daniel Rataj <info@greatjoomla.com>
 * @package		Core Design
 * @subpackage	System
 */
class PlgSystemCDScriptegrator extends JPlugin
{
    /**
     * Plugin status
     * @var bool
     */
    private	$enabled	= 	false;

    /**
     * JApplication object
     * @var JApplication
     */
    private $japplication = null;

    /**
     * Object Constructor.
     *
     * @access	public
     * @param	object	The object to observe -- event dispatcher.
     * @param	object	The configuration object for the plugin.
     * @return	void
     * @since	1.0
     */
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * Load JScriptegrator application
     * and reset the document
     *
     * @return void
     */
    public function onAfterInitialise()
    {
        // disable plugin for non-HTML interface
        if (JFactory::getDocument()->getType() !== 'html')
        {
            $this->enabled = false;
            return false;
        }

        // load the language 
        $this->loadLanguage();

        // register JScriptegrator class
        JLoader::register('JScriptegrator' , dirname(__FILE__) . DS . 'includes' . DS . 'JScriptegrator.php');

        $this->enabled = true;

        $this->japplication = JFactory::getApplication();

        JFactory::$document = null; // document reset for other then html output (feed etc.)
    }

    /**
     * Organize Scriptegrator plugin files
     *
     * @return boolean
     */
    public function onAfterRender()
    {
        if (!$this->enabled) return false;
        $this->enabled = false; // re-false value

        $buffer = JResponse::getBody();

        $scriptegrator_scripts = array();
        $loaded_libraries_from_scriptegrator = array();

        // PHPJS - must be first
        if (JString::strpos($buffer, $this->_name . '/libraries/phpjs/') !== false)
        {
            preg_match_all('#<script src=".*?' . $this->_name . '/libraries/phpjs/.*?" type="text/javascript"></script>#i', $buffer, $phpjs);
            if (isset($phpjs[0]))
            {
                $this->enabled = true;

                $scriptegrator_scripts = array_merge($scriptegrator_scripts, $phpjs[0]);
                $loaded_libraries_from_scriptegrator[] = 'phpjs';
                unset($phpjs);
            }
        }

        // jQuery JS
        if (JString::strpos($buffer, $this->_name . '/libraries/jquery/') !== false)
        {
            preg_match_all('#<script src=".*?' . $this->_name . '/libraries/jquery/.*?" type="text/javascript"></script>#i', $buffer, $jQuery);
            if (isset($jQuery[0]))
            {
                $this->enabled = true;

                $scriptegrator_scripts = array_merge($scriptegrator_scripts, $jQuery[0]);
                $loaded_libraries_from_scriptegrator[] = 'jquery';
                unset($jQuery);
            }
        }

        // jQuery UI
        if (JString::strpos($buffer, $this->_name . '/libraries/jqueryui/') !== false)
        {
            preg_match_all('#<script src=".*?' . $this->_name . '/libraries/jqueryui/.*?" type="text/javascript"></script>#i', $buffer, $jQueryUI);
            if (isset($jQueryUI[0]))
            {
                $this->enabled = true;

                $scriptegrator_scripts = array_merge($scriptegrator_scripts, $jQueryUI[0]);
                $loaded_libraries_from_scriptegrator[] = 'jqueryui';
                unset($jQueryUI);
            }
        }

        // JScriptegrator JavaScript
        if (JString::strpos($buffer, $this->_name . '/libraries/jscriptegrator/') !== false)
        {
            preg_match_all('#<script src=".*?' . $this->_name . '/libraries/jscriptegrator/.*?" type="text/javascript"></script>#i', $buffer, $JScriptegrator);
            if (isset($JScriptegrator[0]))
            {
                $this->enabled = true;

                $scriptegrator_scripts = array_merge($scriptegrator_scripts, $JScriptegrator[0]);
                $loaded_libraries_from_scriptegrator[] = 'jscriptegrator';
                unset($JScriptegrator);
            }
        }

        if ($loaded_libraries_from_scriptegrator)
        {
            // get all other scripts and order it properly if required
            preg_match_all('#<script src=".*?' . $this->_name . '/libraries/(?!' . implode('|', $loaded_libraries_from_scriptegrator) . ').*?" type="text/javascript"></script>#i', $buffer, $other_scriptegrator_scripts);

            if (isset($other_scriptegrator_scripts[0]))
            {
                $this->enabled = true;

                $scriptegrator_scripts = array_merge($scriptegrator_scripts, $other_scriptegrator_scripts[0]);
                $loaded_libraries_from_scriptegrator[] = '';
                unset($other_scriptegrator_scripts);
            }
        }

        if (count($scriptegrator_scripts))
        {
            foreach($scriptegrator_scripts as $scriptegrator_script)
            {
                $buffer = str_ireplace(
                    array(
                        $scriptegrator_script . "\n", $scriptegrator_script
                    ), '', $buffer);
            }

            $scriptegrator_scripts = implode("\n", $scriptegrator_scripts) . "\n<script";
            $search = '#<script#i';
            $count = 1;

            $buffer = preg_replace($search, $scriptegrator_scripts, $buffer, $count);
            unset($scriptegrator_scripts);
        }

        if (!$this->enabled) return false;

        // cleaners, only if JScriptegrator class has been called from some other extension
        if((int) $this->params->get( 'enable_cleaners', 0 ) and class_exists( 'JScriptegrator'))
        {
            // auto cleaners
            $cleaners_dir = dirname(__FILE__) . DS . 'cleaners';

            $cleaners = JFolder::files($cleaners_dir, "\.xml$", false, true);

            $paths = array();
            if($cleaners)
            {
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
                        $this->japplication->enqueueMessage($e->getMessage(), 'error');
                        return false;
                    }

                    foreach($xml->libraries as $xml_library)
                    {
                        foreach($xml_library as $xml_library_name=>$expression)
                        {
                            foreach($expression as $expression_part)
                            {
                                // check if library comes from Scriptegrator - exception in case we're testing some piece of custom script declaration
                                if($loaded_libraries_from_scriptegrator and !in_array($xml_library_name, $loaded_libraries_from_scriptegrator) and $xml_library_name !== 'scriptDeclaration')
                                {
                                    continue;
                                }
                                if($expression_part)
                                {
                                    $paths[$xml_library_name ][] = (string)$expression_part;
                                }
                            }
                        }
                    }
                }
            }

            // manually
            if($custom_cleaners = trim( (string) $this->params->get( 'custom_cleaners', '')) )
            {
                $paths = array_merge($paths, array_map( 'trim', (array) explode( "\n", $custom_cleaners)));
            }

            if($paths )
            {
                // script path
                $tmp_paths = array();
                foreach($paths as $library => $path)
                {
                    // custom script declaration, for example jQuery.noConflict();
                    if ($library === 'scriptDeclaration')
                    {
                        if($path and is_array($path)) {
                            foreach($path as $library_script)
                            {
                                $search = '#' . preg_quote($library_script) . '#is';
                                $replace = '';
                                $count = -1;
                                $buffer = preg_replace($search, $replace, $buffer, $count);
                            }
                        }
                    } else
                    {
                        // example: multiple paths to jQuery script
                        if(is_array($path))
                        {
                            $tmp_paths = array_merge($tmp_paths, $path);
                        } else
                        {
                            // only one library
                            $tmp_paths[] = $path;
                        }

                    }

                    unset($paths);
                }

                if($tmp_paths and is_array($tmp_paths))
                {
                    foreach($tmp_paths as $tmp_path)
                    {
                        $search = '#<script[^>]*' . $tmp_path . '[^>]*></script>#i';
                        $replace = '';
                        $count = -1;
                        $buffer = preg_replace($search, $replace, $buffer, $count);
                    }
                    unset($tmp_paths);
                }

            }
        }

        JResponse::setBody($buffer);

        return true;
    }
}
?>