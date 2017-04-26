<?php
/**
 *  $Id: SRBridgeDatabaseJoomla10.php 67 2009-04-10 05:16:29Z rowan $
 *
 * 	Copyright (C) 2005-2009  Rowan Youngson
 * 
 *	This file is part of Simple Review.
 *
 *	Simple Review is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.

 *  Simple Review is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with Simple Review.  If not, see <http://www.gnu.org/licenses/>.
*/
class SRBridgeDatabaseTable extends mosDBTable
{
	function InitTable($tableName, $keyColumnName, $database)
	{
		$this->mosDBTable($tableName, $keyColumnName, $database );
	}
	
	function load( $oid=null )
	{
		if($oid != null)
		{
			$oid = intval($oid);
		}
	  	parent::load($oid);
	}	
}

class SRBridgeDatabase extends SRBridgeDatabaseBase
{
	function _QueryBatch(&$database, $query, $useTransaction=false)
	{
		return $database->query_batch(true, $useTransaction);
	}	
}

?>