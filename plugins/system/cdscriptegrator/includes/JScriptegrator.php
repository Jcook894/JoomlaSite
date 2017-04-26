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

jimport('joomla.filesystem.folder');

/**
 * JScripetgrator class
 * 
 * @author Daniel Rataj
 * 
 */
class JScriptegrator
{
	public 			$plg_name 				= 	'cdscriptegrator';
	public 			$plg_version 			= 	'';
	public 			$path_to_folder 		= 	'/plugins/system/cdscriptegrator';
	
	/** Scriptegrator plugin parameters */
	private static	$plugin_params			=	array();
	
	/** Available libraries */
	private static	$available_libraries	= 	array();
	
	/** Errors in Scriptegrator plugin, like wrong version or missing JS libraries... */
	public 			$hasError				= 	array();
	
	/**
	 * Constructor
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		self::$available_libraries = JFolder::folders($this->folderPath(true) . DS . 'libraries');
		
		// plugin version
		$xml = JFactory::getXML(dirname(dirname(__FILE__)) . DS . $this->plg_name . '.xml');
		
		// assign version
		$this->plg_version = (string) $xml->version->data();
		
		// assign plugin params
		self::$plugin_params = $this->scriptegratorPluginParams();
	}
	
	/**
	 * Get instance of JScriptegrator
	 * 
	 * @param	string		$id
	 * @param	string		$requirePluginVersion
	 * @return	instance
	 */
	public static function getInstance($requirePluginVersion = '', $id = '')
	{
		static $instances;
		
		if (!isset($instances))
		{
			$instances = array();
		}
		
		$instancename = md5($requirePluginVersion . $id);
		
		if (empty($instances[$instancename]))
		{
			$JScriptegrator = new JScriptegrator();
			$instances[$instancename] = $JScriptegrator;
		}
		
		// check required version
		if ($requirePluginVersion and !$instances[$instancename]->versionRequire($requirePluginVersion))
		{
			$instances[$instancename]->setError(JText::sprintf('PLG_SYSTEM_CDSCRIPTEGRATOR_ERROR_REQUIRE_VERSION', $requirePluginVersion));
		}
		
		return $instances[$instancename];
	}
	
	/**
	 * Library JS loader
	 * 
	 * @param	array	$libraries
	 * @return 	void
	 */
	public function importLibrary($libraries = array())
	{
		// make sure is array, if not, convert it
		if (!is_array($libraries))
		{
			$libraries = array($libraries);
		}
		
		$document = JFactory::getDocument();
		
		foreach($libraries as $library => $library_params)
		{
			// no parameters, so the library params is also the library name
			if (is_int($library))
			{
				$library = $library_params;
				$library_params = array();
			}
			
			$library = strtolower( $library );
			
			$library_classname = $library;
			switch ( $library )
			{
				case 'cleditor':
					$library_classname = 'CLEditor';
					break;
				case 'jquery':
					$library_classname = 'jQuery';
					break;
				case 'jqueryui':
					$library_classname = 'jQueryUI';
					break;
				case 'jscriptegrator':
					$library_classname = 'JScriptegrator';
					break;
				case 'highslide':
					$library_classname = 'Highslide';
					break;
			}
			
			// check if library is allowed
			if ($library and !in_array($library, self::$available_libraries))
			{
				$this->setError(JText::sprintf('PLG_SYSTEM_CDSCRIPTEGRATOR_ERROR_LIBRARY_DOES_NOT_EXISTS', $library));
				continue;
			}
			
			// define helper path
	        $library_class_path = $this->folderPath(true) . DS . 'libraries' . DS . $library . DS . $library . '.php';
	        
	        // load & register class file if exists
	        if (JFile::exists($library_class_path))
	        {
	        	$class_name = ucfirst( 'plgSystemCDScriptegratorLibrary' . $library_classname );
	        	JLoader::register($class_name, $library_class_path);
	        	
	        	$libraryInstance = call_user_func(array($class_name, 'getInstance'), $library_params);
	        	
	        	$files = array();
	        	
	        	// import header files
	        	if (is_callable(array($libraryInstance, 'importFiles')))
	        	{
	        		$importFiles = call_user_func(array($libraryInstance, 'importFiles'));
	        		if (is_array($importFiles))
	        		{
	        			$files = array_merge($files, array_values($importFiles));
	        		} else {
	        			// some error
	        			$this->setError($importFiles);
	        			continue;
	        		}
	        		unset($importFiles);
	        	}
	        	
	        	// add script declaration, only ONCE
	        	if (is_callable(array($libraryInstance, 'scriptDeclaration')))
	        	{
	        		static $addScriptDeclaration = array();
	        		
	        		if (!in_array(get_class($libraryInstance), $addScriptDeclaration) and $script_declaration = call_user_func(array($libraryInstance, 'scriptDeclaration')))
	        		{
	        			$document->addScriptDeclaration($script_declaration);
		        		$addScriptDeclaration[]= get_class($libraryInstance);
	        		}
	        	}
	        	
				if ((int) self::$plugin_params->get('compression', 0))
				{
					$js_files = array();
					$css_files = array();
					
					foreach($files as $file)
					{
						$extension = JFile::getExt($file);
						switch ($extension)
						{
							case 'js':
								$js_files []= $file;
								break;
							case 'css':
								$css_files []= $file;
								break;
							default: break;
						}
					}
					
					if ($js_files) {
						$js_files = implode('&amp;', array_map(array($this, 'arrayToUrl'), $js_files));
					}
					if ($css_files) {
						$css_files = implode('&amp;', array_map(array($this, 'arrayToUrl'), $css_files));
					}
					
					if (!is_array($js_files))
					{
						$loader_filepath = $this->folderPath(true) . DS . 'libraries' . DS . $library . DS . 'gzip' . DS . 'js.php';
						
						if (JFile::exists($loader_filepath))
						{
							$document->addScript($this->folderPath() . "/libraries/$library/gzip/js.php?" . $js_files);
						}
					}
					
					if (!is_array($css_files))
					{
						$loader_filepath = $this->folderPath(true) . DS . 'libraries' . DS . $library . DS . 'gzip' . DS . 'css.php';
						
						if (JFile::exists($loader_filepath))
						{
							$document->addStyleSheet($this->folderPath() . "/libraries/$library/gzip/css.php?" . $css_files);
						}
					}
					
				} else
				{
					if (is_array($files) and $files)
					{
						foreach ($files as $file)
						{
							$extension = JFile::getExt($file);
							$path = $this->folderPath() . "/libraries/$library/$file";
							
							switch ($extension)
							{
								case 'js':
									$document->addScript($path);
									break;
								case 'css':
									$document->addStyleSheet($path, 'text/css');
									break;
								default: break;
							}
						}
					}
				}
	        }
		}
	}
	
	/**
	 * Return Scriptegrator folder path
	 * 
	 * @param 	boolean	$absolute
	 * @return 	string
	 */
	public function folderPath($absolute = false)
	{
		$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		$path = JURI::root(true) . $this->path_to_folder;
		if ($absolute) {
			$path = JPath::clean($root . $this->path_to_folder);
		}

		return $path;
	}

	/**
	 * Check version compatibility
	 * 
	 * @param	string	$min_version
	 * @return 	boolean
	 */
	public function versionRequire($min_version)
	{
		return (version_compare( $this->plg_version, $min_version, '>=' ) == 1);
	}
	
	/**
	 * Return list of available themes
	 * 
	 * @return array
	 */
	public function themeList()
	{
		jimport('joomla.filesystem.folder');
		$path = $this->folderPath(true) . DS . 'libraries' . DS . 'jqueryui' . DS . 'css';
		$files = array();
		$files = JFolder::folders($path, '.', false, false);
		return $files;
	}
	
	/**
	 * Get Scriptegrator plugin parameters
	 * 
	 * @param	string	$param
	 * @param	string	$default
	 * @return 	mixed	Array or string.
	 */
	public function scriptegratorPluginParams($param = '', $default = '')
	{
    	$plugin = JPluginHelper::getPlugin('system', $this->plg_name);
    	
    	jimport('joomla.registry.registry');
    	
        $params =  new JRegistry($plugin->params);
        
        // no defined param, just return all of them
        if (!$param) return $params;
        
        return $params->get($param, $default);
	}
	
	/**
     * Modify URL array
     * 
     * @param	string	$value
     * @param 	string	$name
     * @return 	array
     */
	private function arrayToUrl($value = '', $name = 'files')
	{
		return $name . '[]=' . $value;
	}
	
	/**
	 * Set error message
	 * 
	 * @param 	string		$error
	 * @return	boolean		True if error is set.
	 */
	private function setError($error = '')
	{
		if (is_string($error) and $error)
		{
			array_push($this->hasError, $error);
			return true;
		}
		return false;
	}
	
	/**
	 * Return JScriptegrator plugin
	 * 
	 * @return		string		Error message.
	 */
	public function getError()
	{
		if (is_array($this->hasError) and $this->hasError)
		{
			return implode("<br />", $this->hasError);
		}
		return '';
	}
	
	/**
	 * Clean content from "ob_get_contents()" function
	 * 
	 * @param	string	$string
	 * @return 	string
	 */
	public function compressHTML( $string = '' )
	{
		$search = array(
		'/\n/',			// replace end of line by a space
		'/\>[^\S ]+/s',		// strip whitespaces after tags, except space
		'/[^\S ]+\</s',		// strip whitespaces before tags, except space
	 	'/(\s)+/s',		// shorten multiple whitespace sequences
		'#(?://)?<!\[CDATA\[(.*?)(?://)?\]\]>#s' //leave CDATA alone
	  );
 
		 $replace = array(
			'',
			'>',
		 	'<',
		 	'\\1',
		   	"//&lt;![CDATA[\n".'\1'."\n//]]>"
		  );
	 
		$string = preg_replace($search, $replace, $string);
		return $string;
	}
	
	/**
	 * Create a random string
	 *
	 * @param $length
	 * @return string		Generated hash.
	 */
	public function randomString( $length = 10 )
	{
		$alphanum = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$var_random = '';
		mt_srand(10000000 * (double)microtime());
		for ($i = 0; $i < (int)$length; $i++)
		{
			$var_random .= $alphanum[mt_rand(0, strlen($alphanum) - 1)];
		}
		return $var_random;
	}
	
	/**
	 * Sanitize request data
	 * 
	 * @param	array	$data
	 * @param	string	$method
	 * @param	string	$separator
	 * @return 	array
	 */
	public function sanitizeRequest($data = array(), $method = 'post', $separator = '-')
	{
		$data = JFactory::getApplication()->input->$method->getArray($data);
		
		if (!$data) return array();
		
		foreach ($data as $key=>$value)
		{
			$name = explode($separator, $key);
			unset($name[0]);
			
			$data[implode($separator, $name)] = $value;
			
			unset($data[$key]);
		}
		
		return $data;
	}
	
	/**
	 * Build current URL
	 * 
	 * @param string $context
	 * @param boolean $absolute
	 * @param array|string $del_vars
	 * @param array $custom_vars
	 * @return string
	 */
	public function buildURL($context = 'html', $absolute = false, $del_vars = array(), $custom_vars = array())
	{
		$uri = clone(JURI::getInstance());
		
		$vars = $uri->getQuery(true);
		
		// delete variables
		if ($del_vars)
		{
			foreach($vars as $name=>$value)
			{
				// is string, perform a regular expression
				if (is_string($del_vars))
				{
					if (preg_match('#' . $del_vars . '#', $name))
					{
						$uri->delVar($name);
					}
				}
				
				// is array
				if(is_array($del_vars) and in_array($name, $del_vars) !== false)
				{
					$uri->delVar($name);
				}
			}
		}
		
		$parts = array();
		
		// append custom variables
		if ($custom_vars)
		{
			foreach($custom_vars as $var=>$val)
			{
				$uri->setVar($var, $val);
			}
		}
		
		if ($absolute)
		{
			$parts[] = 'scheme';
			$parts[] = 'user';
			$parts[] = 'pass';
			$parts[] = 'host';
			$parts[] = 'port';
		}
		$parts[] = 'path';
		$parts[] = 'query';
		
		// no fragment part in URL allowed here
		// $parts[] = 'fragment';
		
		$url = $uri->toString(array('path', 'query'));
		switch($context)
		{
			case 'url':
				$url = $uri->toString($parts);
			break;
			
			case 'html':
			default:
				$url = str_replace('&', '&amp;', $uri->toString($parts));
				break;
		}
		
		return $url;
	}
	
	/**
	 * Check if Email Cloak content plugin is ordered before specified element
	 * $param	$element	string
	 * $param	$type		string
	 * @return 	boolean
	 */
	public function isEmailCloakPluginBefore($element = '', $type = 'plugin')
	{
		if (!$element) return false;
		if (!$type) return false;
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$user = JFactory::getUser();
		$levels = implode(',', $user->getAuthorisedViewLevels());
		
		$query->select('element')
			->from('#__extensions')
			->where('element LIKE ' . $db->quote('emailcloak') . ' OR element LIKE ' . $db->quote($element))
			->where('type LIKE ' . $db->quote($type))
			->where('state >= 0')
			->where('enabled >= 1')
			->order('ordering ASC')->order('extension_id ASC')
			->where('access IN (' . $levels . ')');
		
			$db->setQuery($query);
			
		if ($db->query() === false)
		{
			$db->setError($db->getErrorMsg());
			return false;
		}

		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}
		
		$result = $db->loadResultArray();
		
		// make sure we're talking about two lenght array result
		if (!is_array($result) or count($result) !== 2) return false;
		
		// first one MUST BE "Email Cloak plugin"
		if ($result[0] !== 'emailcloak') return false;
		
		// correct ordering
		return true;
	}
}
?>