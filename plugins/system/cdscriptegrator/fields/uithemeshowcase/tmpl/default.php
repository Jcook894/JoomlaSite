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
?>
<div id="<?php echo $type; ?>">
	<h4><?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_SHOWCASE'); ?></h4>
	<div>
		<?php foreach($JScriptegrator->themeList() as $uitheme): ?>
			<?php
			$url = '';
			$content = '';
			
			$folderlist = JFolder::files($JScriptegrator->folderPath(true) . DS . 'libraries' . DS . 'jqueryui' . DS . 'css' . DS . $uitheme, '\.css$', false, true);
			
			if (!isset($folderlist[0])) continue;
			
			$cssfile = $folderlist[0];
			
			if (JFile::exists($cssfile))
			{
				$content = JFile::read($cssfile);
				preg_match('#(http:\/\/jqueryui\.com\/themeroller\/\?.*)#', $content, $url);
				$url = (isset($url[1]) ? $url[1] : '');
			}
			
			unset($folderlist, $content);
			?>
			<?php if (isset($url)): ?>
				<a href="<?php echo $url; ?>" title="<?php echo $uitheme; ?>::<?php echo JText::sprintf('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_SHOWCASE_TOOLTIP_CLICK_TO_PREVIEW', $uitheme); ?>" class="hasTip" target="_blank" rel="nofollow">
			<?php endif; ?>
			<img alt="<?php echo $uitheme; ?>" title="<?php echo $uitheme; ?>" src="<?php echo $JScriptegrator->folderPath() . '/libraries/jqueryui/css/' . $uitheme . '/' . $uitheme . '.png'; ?>" />
			<?php if (isset($url)): ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>