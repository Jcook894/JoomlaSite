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
		
		$.fn.selectarticle = function(options) {

			// set defaults
			var defaults = {
					scriptURL : '',
					language : {
					}
			},
			opts = $.extend(defaults, options),
			$PHP_JS = new PHP_JS();
			
			return this.each(function() {
				var $this = $(this);
				
				if (typeof opts.scriptURL !== 'string' && !opts.scriptURL) return false;
				
				$this.autocomplete({
					appendTo: $this.parent(),
					source : function(request, response) {
						var data = {
								term : request.term,
								selectarticle_task : 'getArticleListAutocomplete'
						};
						
						$.ajax({
							type: 'POST',
							url : opts.scriptURL,
							//data: $.param(data) + '&' + getParam('token') + '=1',
							data: $.param(data),
							cache: true,
							async: true,
							beforeSend: function() {},
							success: function(result) {
								response( $.map( $PHP_JS.explode('||', result), function( item ) {
									if (result) {
										item = $PHP_JS.explode('::', item),
										value = item[0],
										label = item[1];
										
										return {
											value: value,
											label: label
										};
									} else {
										return null;
									}
									
								}));
								
								return true;
							},
							complete: function() { }
						});
					},
					open: function() {
						$(this).autocomplete('widget').css({
							'overflow-y' : 'auto',
							'overflow-x' : 'hidden',
							'max-height' : '200px'
						});
					},
					focus: function( event, ui ) {
						//$(this).val( ui.item.label.replace(/^(\[.*?\]\s)/, '') );
						return false;
					},
					select: function(event, ui) {
						$(this).val( ui.item.label.replace(/^(\[.*?\]\s)/, '') );
						$(this).autocomplete('widget').prev(':hidden').val(ui.item.value);
						return false;
					}
				});
			});
			
		};
		
	})(jQuery);
}