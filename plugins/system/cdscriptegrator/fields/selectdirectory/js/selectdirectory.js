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
		
		$.fn.selectdirectory = function(options) {

			// set defaults
			var defaults = {
					input : null,
					uitheme : 'smoothness',
					scriptURL : '',
					language : {
						DIALOG_TITLE : 'Select directory'
					}
			},
			selectdirectory_dialog = null,
			opts = $.extend(defaults, options);
			
			return this.each(function() {
				var $this = $(this);
				
				if (typeof opts.input !== 'object') return false;
				if (typeof opts.scriptURL !== 'string' && !opts.scriptURL) return false;
				
				$this.click(function(e) {
					e.preventDefault();
					
					if ($('#selectdirectory', 'body').length) return false;
					
					selectdirectory_dialog = $('<div />', { id : 'selectdirectory' });
					
					selectdirectory_dialog.dialog({
						title : opts.language.DIALOG_TITLE,
						width : 300,
						height : 200,
						position : {
							of : $this,
							my : 'right top',
							at : 'right bottom',
							offset : '0 5'
						},
						draggable : false,
						resizable : false,
						open : function() {
							var dialog = $(this);
							
							// wrap with UI Theme
							dialog.closest('.ui-dialog').wrap($('<div />', {
								'class' : opts.uitheme
							}));
							
							// get list of directories
							getListOfDirectories(opts.input.val());
							
						},
						close : function() {
							selectdirectory_dialog.dialog('destroy');
							selectdirectory_dialog.prev('.' + opts.uitheme).remove();
							selectdirectory_dialog.remove();
							selectdirectory_dialog = null;
						}
					});
					
				});
			});
			
			/**
			 * Get list of directories via Ajax request
			 * 
			 * @param	string
			 * @return	void
			 */
			function getListOfDirectories(dir) {
				if (typeof dir === 'undefined') dir = '';
				
				$.ajax({
					type: 'POST',
					url : opts.scriptURL,
					data: 'directory=' + dir,
					cache: true,
					async: true,
					beforeSend: function() {
						selectdirectory_dialog.html($('<div />', {
							'class' : 'selectdirectory_loading'
						}));
					},
					success: function(result) {
						selectdirectory_dialog.append(result);
						
						// control panel
						$('.selectdirectory_controlpanel a', selectdirectory_dialog).button();
						
						// home directory
						$('.selectdirectory_controlpanel a.selectdirectory_home', selectdirectory_dialog)
						.button('option', {
							icons : {
								primary : 'ui-icon-home'
							},
							text : false
						}).click(function() {
							// go home directory
							getListOfDirectories();
						});
						
						// back
						$('.selectdirectory_controlpanel a.selectdirectory_back', selectdirectory_dialog)
						.button('option', {
							icons : {
								primary : 'ui-icon-arrowreturnthick-1-w'
							},
							text : false
							})
						.click(function(e) {
							// go home directory
							getListOfDirectories($(this).attr('rel'));
						});
						
						// click element to select directory and re-send request
						$('a.selectdirectory_getdir', selectdirectory_dialog).click(function(e) {
							getListOfDirectories($(this).attr('rel'));
						});
						
						// select
						$('a.selectdirectory_selectdir').click(function(e) {
							e.preventDefault();
							var selected_dir = $(this).attr('rel');
							opts.input.val(selected_dir);
							selectdirectory_dialog.dialog('close');
							window.location.hash = selected_dir;
						});
						
						// hover effect
						$('.selectdirectory_container', selectdirectory_dialog).hover(
							function() {
								$(this).addClass('ui-state-hover');
							},
							function() {
								$(this).removeClass('ui-state-hover');
							}
						);
					},
					complete : function() {
						// remove loading
						$('.selectdirectory_loading', selectdirectory_dialog).remove();
					}
				});
			};
			
		};
		
	})(jQuery);
}