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
<div id="<?php echo $type; ?>" class="<?php echo $uitheme; ?>">
	<div class="ui-widget-header ui-corner-all <?php echo $type; ?>_adminbuttons">
		<a class="<?php echo $this->id; ?>" href="#install" rel="install"><?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME'); ?></a>
		<a class="<?php echo $this->id; ?>" href="#uninstall" rel="uninstall"><?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME'); ?></a>
		<div style="clear: both;"></div>
	</div>
	<div>
		<div class="<?php echo $type; ?>_formtemplate formtemplate_installation">
			<p style="font-style: italic; font-size: 90%;"><?php echo JText::_( 'PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_COMPATIBILITY_NOTE' ); ?></p>
			<!--
			<form action="<?php echo $JScriptegrator->folderPath() . '/fields/' . $type . "/application.php"; ?>" method="post" enctype="multipart/form-data">
				<div class="ui-widget-content">
					<div><input type="file" name="<?php echo $type; ?>_uploadfile" /></div>
					<div class="<?php echo $type; ?>_submitbutton"><button type="submit"><?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_INSTALL_UITHEME_SUBMIT'); ?></button></div>
					<input type="hidden" name="task" value="install" />
					<input type="hidden" name="return" value="<?php echo base64_encode(JURI::getInstance()->toString(array('path', 'query'))); ?>" />
				</div>
			</form>
			-->
		</div>
	</div>
	<?php if ($uithemes = $JScriptegrator->themeList()): ?>
		<p><br /></p>
		<div>
			<div class="<?php echo $type; ?>_formtemplate formtemplate_showcase">
				<!--
				<form action="<?php echo $JScriptegrator->folderPath() . '/fields/' . $type . "/application.php"; ?>" method="post">
					<?php foreach($uithemes as $uitheme): ?>
						<div class="ui-widget-content ui-corner-all" style="float: left; margin: 5px; width: 90px;">
							<div><img alt="<?php echo $uitheme; ?>" title="<?php echo $uitheme; ?>" src="<?php echo $JScriptegrator->folderPath() . '/libraries/jqueryui/css/' . $uitheme . '/' . $uitheme . '.png'; ?>" /></div>
							<div class="<?php echo $type; ?>_submitbutton">
								<a rel="<?php echo $uitheme; ?>" title="<?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME_SUBMIT') . ' - ' . $uitheme; ?>"><?php echo JText::_('PLG_SYSTEM_CDSCRIPTEGRATOR_FORM_FIELD_UITHEMEMANAGER_UNINSTALL_UITHEME_SUBMIT'); ?></a>
							</div>
							<div style="clear: both;"></div>
						</div>
					<?php endforeach; ?>
					<input type="hidden" name="uitheme" value="" />
					<input type="hidden" name="task" value="uninstall" />
					<input type="hidden" name="return" value="<?php echo base64_encode(JURI::getInstance()->toString(array('path', 'query'))); ?>" />
				</form>
				-->
			</div>
		</div>
	<?php endif; ?>
</div>