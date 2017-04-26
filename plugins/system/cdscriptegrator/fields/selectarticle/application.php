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

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'application.php';

if (!JRequest::get('post')) jexit();

$articles = array();

jimport('joomla.application.component.model');

JModel::addIncludePath(dirname(__FILE__) . DS . 'models');

$model = JModel::getInstance('Articles', 'PlgSystemCdScriptegratorContentModel', array('ignore_request' => true));

$model->setState('filter.search', JRequest::getString('term', '', 'post'));
$model->setState('list.ordering', 'a.ordering');
$model->setState('list.direction', 'ASC');

$articles = $model->getItems();

$tmp_articles = array();
foreach( $articles as $key=>$article )
{
	$tmp_articles[] = (int) $article->id . '::' . '[' . (string) $article->category_title . '] ' . $article->title;
}

$html = implode('||', $tmp_articles);

jexit($html);
?>