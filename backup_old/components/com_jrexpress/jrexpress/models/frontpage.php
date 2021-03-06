<?php
/*
    JReviews Express - user reviews for Joomla
    Copyright (C) 2009  Alejandro Schmeichler

    JReviews Express is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    JReviews Express is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class FrontpageModel extends MyModel  {
		
	var $name = 'Frontpage';
	
	var $useTable = '#__content_frontpage AS Frontpage';
	
	var $primaryKey = 'Frontpage.content_id';
	
	var $realKey = 'content_id';
	
	var $fields = array(
		'Frontpage.content_id AS `Frontpage.content_id`',
		'Frontpage.ordering AS `Frontpage.ordering`'
		);
}