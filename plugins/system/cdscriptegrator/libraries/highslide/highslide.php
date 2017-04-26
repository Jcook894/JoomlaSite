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
 * Highslide class
 * 
 * @author Daniel Rataj
 */
class PlgSystemCDScriptegratorLibraryHighslide
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
		if ($instance == null)
		{
			$instance = new PlgSystemCDScriptegratorLibraryHighslide();
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
			'js/highslide-full.min.js',
			'css/highslide.css'
		);
	}
	
	/**
	 * Script declaration
	 * 
	 * @return string
	 */
	public function scriptDeclaration()
	{
		$JScriptegrator = JScriptegrator::getInstance();
		
		// define database parameters
		$outlineType = $this->plg_params->get('outlineType', 'rounded-white');
		$outlineWhileAnimating = (int) $this->plg_params->get('outlineWhileAnimating', 1);
		$showCredits = (int) $this->plg_params->get('showCredits', 1);
		$expandDuration = (int) $this->plg_params->get('expandDuration', 250);
		$anchor = $this->plg_params->get('anchor', 'auto');
		$align = $this->plg_params->get('align', 'auto');
		$transitions = $this->plg_params->get('transitions', 'expand');
		$dimmingOpacity = $this->plg_params->get('dimmingOpacity', '0');
		// end

		// define script parameters
		switch ($outlineWhileAnimating)
		{
			case 1:
				$outlineWhileAnimating = 'true';
				break;
			case 0:
				$outlineWhileAnimating = 'false';
				break;
			default:
				$outlineWhileAnimating = 'true';
				break;
		}
		
		if ($showCredits)
		{
			$showCredits = 'true';
		} else {
			$showCredits = 'false';
		}

		switch ($transitions)
		{
			case 'expand':
				$transitions = '["expand"]';
				break;
			case 'fade':
				$transitions = '["fade"]';
				break;
			case 'expand+fade':
				$transitions = '["expand", "fade"]';
				break;
			case 'fade+expand':
				$transitions = '["fade", "expand"]';
				break;
			default:
				$transitions = '["expand"]';
				break;
		}
		// end
		
		$script = "
		<!--
		hs.graphicsDir = '" . $JScriptegrator->folderPath() . "/libraries/highslide/graphics/';
    	hs.outlineType = '" . $outlineType . "';
    	hs.outlineWhileAnimating = " . $outlineWhileAnimating . ";
    	hs.showCredits = " . $showCredits . ";
    	hs.expandDuration = " . $expandDuration . ";
		hs.anchor = '" . $anchor . "';
		hs.align = '" . $align . "';
		hs.transitions = " . $transitions . ";
		hs.dimmingOpacity = " . $dimmingOpacity . ";
		hs.lang = {
		   loadingText :     '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_LOADING', true) . "',
		   loadingTitle :    '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_CANCELCLICK', true) . "',
		   focusTitle :      '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FOCUSCLICK', true) . "',
		   fullExpandTitle : '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FULLEXPANDTITLE', true) . "',
		   fullExpandText :  '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FULLEXPANDTEXT', true) . "',
		   creditsText :     '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_CREDITSTEXT', true) . "',
		   creditsTitle :    '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_CREDITSTITLE', true) . "',
		   previousText :    '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_PREVIOUSTEXT', true) . "',
		   previousTitle :   '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_PREVIOUSTITLE', true) . "',
		   nextText :        '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_NEXTTEXT', true) . "',
		   nextTitle :       '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_NEXTTITLE', true) . "',
		   moveTitle :       '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_MOVETITLE', true) . "',
		   moveText :        '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_MOVETEXT', true) . "',
		   closeText :       '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_CLOSETITLE', true) . "',
		   closeTitle :      '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_CLOSETEXT', true) . "',
		   resizeTitle :     '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_RESIZETITLE', true) . "',
		   playText :        '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_PLAYTEXT', true) . "',
		   playTitle :       '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_PLAYTITLE', true) . "',
		   pauseText :       '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_PAUSETEXT', true) . "',
		   pauseTitle :      '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_PAUSETITLE', true) . "',   
		   number :          '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_NUMBER', true) . "',
		   restoreTitle :    '" . JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_RESTORETITLE', true) . "'
		};
		//-->
		";
		return $script;
	}
}

?>