<?php
// namespace components\com_jmap;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Sitemap Joomla router
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 */
function JMapBuildRoute(&$query) {
	$config = JFactory::getConfig();
	static $appSuffix;
	if($appSuffix) {
		$config->set('sef_suffix', $appSuffix);
	}
	$componentParams = JComponentHelper::getParams('com_jmap');
	
	// Segments that will be translated and built for this URL, subtracted from $query that will be untranslated and not built
	$segments = array();

	$app = JFactory::getApplication();
	// Get all site menus
	$menus = $app->getMenu('site');

	// Mapping fallback for generic task name = view name if view is not used. View concept is only used wrong way by Joomla menus
	if(!isset($query['view']) && isset($query['task'])) {
		if (strpos($query['task'], '.')) {
			list($controller_name, $controller_task) = explode('.', $query['task']);
		}
		$mappedView = $controller_name; 
	}
	
	// Helper Route here for existing menu item pointing to this $query, so try finding Itemid before all
	if (empty($query['Itemid']) && $app->getClientId() != 1) {
		$component = JComponentHelper::getComponent('com_jmap');
		$menuItems = $menus->getItems('component_id', $component->id);
		if(!empty($menuItems)) {
			// Route helper priority 1: view + dataset
			if(isset($query['dataset'])) {
				foreach ($menuItems as $menuItem) {
					if (isset($menuItem->query) && isset($menuItem->query['dataset'])) {
						if($menuItem->query['dataset'] == $query['dataset']) {
							// Found a link exact match to sitemap view default html format within a site menu, use the Itemid for alias: component/com_jmap=>alias
							$query['Itemid'] = $menuItem->id;
							break;
						}
					}
				}
			}
			
			// Route helper priority 2: view only
			if(empty($query['Itemid'])) {
				foreach ($menuItems as $menuItem) {
					if(isset($menuItem->query['dataset']) && is_numeric($menuItem->query['dataset'])) {
						continue;
					}
					if (isset($menuItem->query) && isset($menuItem->query['view'])) {
						if(isset($query['view']) && $menuItem->query['view'] == $query['view']) {
							// Found a link exact match to sitemap view default html format within a site menu, use the Itemid for alias: component/com_jmap=>alias
							$query['Itemid'] = $menuItem->id;
							break;
						}
			
						if(isset($mappedView) && $menuItem->query['view'] == $mappedView) {
							// Found a link exact match to sitemap view default html format within a site menu, use the Itemid for alias: component/com_jmap=>alias
							$query['Itemid'] = $menuItem->id;
							break;
						}
					}
				}
			}
		}
	}

	// Lookup for an menu itemid in $query, should be helped by route helper if any, for mod_menu links there is always $query = http://domain.com/?Itemid=123, and all is desetted by default
	if (empty($query['Itemid'])) {
		$menuItem = $menus->getActive();
	} else {
		$menuItem = $menus->getItem($query['Itemid']);
	}
	// Store query info for menu, for example view name, for the menu selected fom Itemid or current as fallback
	$mView = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	$mFormat = (empty($menuItem->query['format'])) ? 'html' : $menuItem->query['format'];
	$mDataset = (empty($menuItem->query['dataset'])) ? null : $menuItem->query['dataset'];
	// If this is a link to HTML sitemap format view assigned already to a menu, ensure to unset all by default to leave only menu alias
	if (isset($query['view']) && ($mView == $query['view']) && 
		(!isset($query['format']) || $mFormat == $query['format']) &&
		(!isset($query['dataset']) || $mDataset == $query['dataset'])) {
		unset($query['view']);
		unset($query['format']);
		unset($query['dataset']);
		// Return empty segments ONLY if link has a view specified that match a menu item. Controller.task is always left as a segment because could have specific behavior
		return $segments;
	}

	// Start desetting $query chunks assigning to segments
	// UNSET VIEW
	if (isset($query['view'])) {
		// Store view info for $query link
		$view = $query['view'];
		// Assign and unset
		$segments[] = $query['view'];
		unset($query['view']);
	}

	// UNSET TASK
	if (isset($query['task'])) {
		// Assign and unset
		$segments[] = str_replace('.', ':', $query['task']);
		unset($query['task']);
	}

	// UNSET FORMAT
	if (isset($query['format'])) {
		// Assign and unset
		$segments[] = $query['format'];
		unset($query['format']);
		
		// Manage XML/NOT HTML JDocument format if detected
		$appSuffix = $config->get('sef_suffix');
		$config->set('sef_suffix', false);
	}

	// UNSET XSLT
	$foundXslt = false;
	if (isset($query['xslt'])) {
		// Assign and unset
		$segments[] = $query['xslt'] . ':formatted';
		unset($query['xslt']);
		$foundXslt = true;
	}
	
	// UNSET DATASET
	if (isset($query['dataset'])) {
		// Assign and unset
		if(!$foundXslt) {
			$segments[] = '0:formatted';
		}
		$segments[] = $query['dataset'] . ':dataset';
		unset($query['dataset']);
	}

	// Finally return processed segments
	return $segments;
}

/**
 * Parse the segments of a URL with following shapes:
 * 
 * http://mydomain/component/jmap/view-task/format/xslt
 * 
 * http://mydomain/component/jmap/sitemap
 * http://mydomain/component/jmap/sitemap/xml
 * http://mydomain/component/jmap/sitemap/xml/1
 * http://mydomain/component/jmap/sitemap.display
 * http://mydomain/component/jmap/sitemap.exportxml/xml
 * 
 * component/jmap/ has to be handled through route helper for menu Itemid
 * By convention view based Joomla components are overwritten by mapping viewname = taskname.display ex. view=sitemap is mapped to task=sitemap.display
 * 
 * @param	array	The segments of the URL to parse.
 * @return	array	The URL attributes to be used by the application.
 */
function JMapParseRoute($segments) {
	$vars = array();
	$count = count($segments);
	$app = JFactory::getApplication();
	$componentParams = JComponentHelper::getParams('com_jmap');

	if ($count) {
		$count--;
		// VIEW-TASK is always 1° segment
		$segment = array_shift($segments);
		if (strpos($segment, ':')) {
			$vars['task'] = str_replace(':', '.', $segment);
		} else {
			$vars['view'] = $segment;
		}
	}

	if ($count) {
		$count--;
		// FORMAT is always 2° segment
		$segment = array_shift($segments);
		if ($segment) {
			$vars['format'] = $segment;
		}
	}

	if ($count) {
		$count--;
		// XSLT stylesheet is always 3° segment
		$segment = array_shift($segments);
		if (is_numeric((int) $segment)) {
			$vars['xslt'] = (int) $segment;
		}
	}
	
	if ($count) {
		$count--;
		// Dataset is always 4° segment
		$segment = array_shift($segments);
		if (is_numeric((int) $segment)) {
			$vars['dataset'] = (int) $segment;
		}
	}
	
	// Evaluate a forcing Itemid into vars and JMenu setter, link with no alias substitution, partially SEF from backend
	if(($ItemidByLink = JRequest::getInt('Itemid', null)) && $componentParams->get('sitemap_links_sef', false)) {
		$vars['Itemid'] = $ItemidByLink;
		$menu = $app->getMenu();
		$menu->setActive($vars['Itemid']);
	}

	return $vars;
}
