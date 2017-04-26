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

// register JScriptegrator object
if( typeof JScriptegrator === 'undefined' ) {
	var JScriptegrator = {};
}
(function(JScriptegrator){
	
	/**
	 * Storage wrapper
	 */
	JScriptegrator.storage = {
			type : 'local',
		
			/**
			 * Store value
			 */
			storeValue : function(key, value)
			{
				var use_cookie = false;
				switch(this.type)
				{
					case 'session':
					default:
						if (typeof sessionStorage === 'object')
						{
							sessionStorage.setItem(key, value);
						}
						else
						{
							use_cookie = true;
						}
					break;
					
					case 'local':
						if (typeof localStorage === 'object')
						{
							localStorage.setItem(key, value);
						}
						else
						{
							use_cookie = true;
						}
						break;
				}
				
				if (use_cookie)
				{
					this.cookie(key, value, { expires: 365 });
				}
				return true;
			},
			
			/**
			 * Remove value
			 */
			removeValue : function(key)
			{
				var use_cookie = false;
				switch(this.type)
				{
					case 'session':
					default:
						if (typeof sessionStorage === 'object')
						{
							sessionStorage.removeItem(key);
						}
						else
						{
							use_cookie = true;
						}
					break;
					
					case 'local':
						if (typeof localStorage === 'object')
						{
							localStorage.removeItem(key);
						}
						else
						{
							use_cookie = true;
						}
						break;
				}
				
				if (use_cookie)
				{
					this.cookie(key, null);
				}
				return true;
			},
			
			/**
			 * Get value
			 */
			getValue : function(key, def)
			{
				def = (typeof def === 'undefined' ? 0 : def);
				
				var value = '',
				use_cookie = false;
				
				switch(this.type)
				{
					case 'session':
					default:
						if (typeof sessionStorage === 'object')
						{
							value = sessionStorage.getItem(key);
						}
						else
						{
							use_cookie = true;
						}
					break;
					
					case 'local':
						if (typeof localStorage === 'object')
						{
							value = localStorage.getItem(key);
						}
						else
						{
							use_cookie = true;
						}
						break;
				}
				
				if (use_cookie)
				{
					value = this.cookie(key);
				}
				return value || def;
			},
			
			/**
			 * Save cookie
			 */
			cookie : function(key,value,options){if(arguments.length>1&&String(value)!=="[object Object]"){options=jQuery.extend({},options);if(value===null||value===undefined){options.expires=-1}if(typeof options.expires==='number'){var days=options.expires,t=options.expires=new Date();t.setDate(t.getDate()+days)}value=String(value);return(document.cookie=[encodeURIComponent(key),'=',options.raw?value:encodeURIComponent(value),options.expires?'; expires='+options.expires.toUTCString():'',options.path?'; path='+options.path:'',options.domain?'; domain='+options.domain:'',options.secure?'; secure':''].join(''))}options=value||{};var result,decode=options.raw?function(s){return s}:decodeURIComponent;return(result=new RegExp('(?:^|; )'+encodeURIComponent(key)+'=([^;]*)').exec(document.cookie))?decode(result[1]):null}
	};
})(JScriptegrator);