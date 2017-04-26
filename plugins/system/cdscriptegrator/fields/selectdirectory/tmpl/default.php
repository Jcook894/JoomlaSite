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
<div class="selectdirectory_controlpanel">
	<a href="#/" class="selectdirectory_home"><?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_SELECT_DIRECTORY_BUTTON_HOME'); ?></a>
	<?php if ($directory): ?>
		<?php
		$back_rel = str_replace(JPATH_BASE . DS, '', dirname($called_path));
		if (str_replace(JPATH_BASE . DS, '', dirname($called_path)) === JPATH_BASE)
		{
			$back_rel = DS;
		}
		?>
		<a href="#<?php echo $back_rel; ?>" class="selectdirectory_back" rel="<?php echo $back_rel; ?>"><?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_SELECT_DIRECTORY_BUTTON_BACK'); ?></a>
	<?php endif; ?>
</div>
<div class="selectdirectory_called_path">
	<?php echo str_replace(JPATH_BASE, '', $called_path); ?>
</div>
<?php if ($folders_in_directory): ?>
	<?php foreach($folders_in_directory as $key=>$folder): ?>
		<?php
		$folder = str_replace(JPATH_BASE, '', $folder);
		$folder = str_replace('\\\\', '\\', $folder);
		
		$folder_select = $folder;
		if (strpos($folder_select, DS) === 0)
		{
			$folder_select = substr($folder_select, 1);
		}
		?>
		<div class="selectdirectory_container ui-widget-content">
			<a href="#<?php echo $folder; ?>" class="selectdirectory_getdir" rel="<?php echo $folder; ?>"><?php echo basename($folder); ?></a>
			<a href="#select" class="selectdirectory_selectdir" rel="<?php echo $folder_select; ?>">(select)</a>
			<div style="clear: both;"></div>
		</div>
	<?php endforeach; ?>
<?php else: ?>
	<?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_SELECT_DIRECTORY_NO_DIRECTORIES'); ?>
<?php endif; ?>