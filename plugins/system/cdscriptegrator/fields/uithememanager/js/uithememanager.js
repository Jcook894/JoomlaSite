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

if (typeof(jQuery) == 'function') {
	
	(function($) {
		
		$.fn.uithememanager = function(options) {

			// set defaults
			var defaults = {
					fncname : 'uithememanager',
					uitheme : 'smoothness',
					language : {
						INSTALL_DIALOG_TITLE : 'Install new UI Theme',
						UNINSTALL_DIALOG_TITLE : 'Uninstall UI Theme',
						ARE_YOU_SURE : 'Are you sure?'
					}
			},
			opts = $.extend(defaults, options),
			dialog = null,
			$PHP_JS = new PHP_JS();
			
			return this.each(function() {
				var $this = $(this),
				modal = $.support.changeBubbles, // remove modal in IE
				generalDialogOptions = {
					modal : modal,
					draggable : false,
					resizable : false,
					close : function() {
						dialog.dialog('destroy');
						
						// modal
						if(modal) {
							dialog.prev('.' + opts.uitheme).remove();
						}
						
						dialog.prev('.' + opts.uitheme).remove();
						
						dialog.remove();
						dialog = null;
						
						// enable button
						$this.button('enable');
					}
				};
				
				switch($this.attr('rel')) {
					case 'install':
						$this.button({
							icons : {
								primary : 'ui-icon-circle-plus'
							}
						}).click(function(e) {
							e.preventDefault();
							
							if ($('#' + opts.fncname + '_dialog', 'body').length) {
								dialog.dialog('close');
							}
							
							dialog = $('<div />', { id : opts.fncname + '_dialog' });
							
							dialog.dialog({
								title : opts.language.INSTALL_DIALOG_TITLE,
								width : 350,
								height : 200,
								draggable : generalDialogOptions.draggable,
								resizable : generalDialogOptions.resizable,
								modal : generalDialogOptions.modal,
								close : generalDialogOptions.close,
								open : function() {
									
									// disable button
									$this.button('disable');
									
									// wrap modal with UI Theme
									if(modal) {
										$(this).closest('.ui-dialog').next('.ui-widget-overlay').wrap($('<div />', {
											'class' : opts.uitheme
										}));
									}
									
									// wrap with UI Theme
									$(this).closest('.ui-dialog').wrap($('<div />', {
										'class' : opts.uitheme,
										'style' : 'position: absolute;'
									}));
									
									// re-position
									$(this).dialog('option', {
										position : {
											of : $this,
											my : 'right top',
											at : 'right bottom',
											offset : '0 5'
										}
									});
									
									// insert form
									var content = $('.formtemplate_installation', $this.closest('.' + opts.uitheme));
									
									var content_html = content.html();
									content_html = $PHP_JS.str_replace('<!--', '', content_html);
									content_html = $PHP_JS.str_replace('-->', '', content_html);
									
									$(this).append(content_html);
									
									// make submit button UI-able
									$('button[type="submit"]', $(this)).button({
										icons : {
											primary : 'ui-icon-disk'
										}
									});
									
									// prevent empty form submission
									$('form', $(this)).submit(function(e) {
										if ($('input[type="file"]', $(this)).val() === '') {
											// empty field
											e.preventDefault();
										}
									});
									
									var install_submit_button = $('button', $(this));
									install_submit_button.button('disable');
									
									$('form input[type="file"]', $(this)).change(function(e) {
										// check if empty or not ZIP file
										if ($(this).val() === '' || $PHP_JS.strtolower($(this).val().replace(/^.*?\.([a-zA-Z0-9]+)$/, "$1")) !== 'zip') {
											// empty field
											install_submit_button.button('disable');
										} else {
											install_submit_button.button('enable');
										}
									});
									
								}
							});
							
						});
						break;
						
					case 'uninstall':
						$this.button({
							icons : {
								primary : 'ui-icon-circle-minus'
							}
						}).click(function(e) {
							e.preventDefault();
							
							if ($('#' + opts.fncname + '_dialog', 'body').length) {
								dialog.dialog('close');
							}
							
							dialog = $('<div />', { id : opts.fncname + '_dialog' });
							
							dialog.dialog({
								title : opts.language.UNINSTALL_DIALOG_TITLE,
								width : 380,
								height : 250,
								draggable : generalDialogOptions.draggable,
								resizable : generalDialogOptions.resizable,
								modal : generalDialogOptions.modal,
								close : generalDialogOptions.close,
								open : function() {
									
									var uninstall_dialog = $(this);
									
									// disable button
									$this.button('disable');
									
									// wrap modal with UI Theme
									if(modal) {
										uninstall_dialog.closest('.ui-dialog').next('.ui-widget-overlay').wrap($('<div />', {
											'class' : opts.uitheme
										}));
									}
									
									// wrap with UI Theme
									uninstall_dialog.closest('.ui-dialog').wrap($('<div />', {
										'class' : opts.uitheme,
										'style' : 'position: absolute;'
									}));
									
									// re-position
									uninstall_dialog.dialog('option', {
										position : {
											of : $this,
											my : 'right top',
											at : 'right bottom',
											offset : '0 5'
										}
									});
									
									// insert form
									var content = $('.formtemplate_showcase', $this.closest('.' + opts.uitheme));
									
									var content_html = content.html();
									content_html = $PHP_JS.str_replace('<!--', '', content_html);
									content_html = $PHP_JS.str_replace('-->', '', content_html);
									
									uninstall_dialog.append(content_html);
									
									// make submit button UI-able
									$('a', uninstall_dialog).button({
										icons : {
											primary : 'ui-icon-circle-minus'
										}
									}).click(function(e) {
										e.preventDefault();
										
										var confirmation = confirm(opts.language.ARE_YOU_SURE);
										
										if (confirmation) {
											$('input[name="uitheme"]', uninstall_dialog).val($(this).attr('rel'));
											$('form', uninstall_dialog).submit();
											return true;
										}
										return false;
									});
								}
							});
							
						});
						break;
				}
				
			});
		};
		
	})(jQuery);
}