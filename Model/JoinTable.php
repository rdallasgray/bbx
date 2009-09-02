<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_JoinTable {
	
	public static function find($parentName,$childName) {
		$tableName = self::findName($parentName,$childName);
		return self::_getTable($tableName);
	}
	
	public static function findName($parentName,$childName) {
		$tables = Zend_Registry::get('db')->listTables();
		$searchNames = create_function(
			'$table',
			'$tableKeys = explode("_",$table);
			 $parentKeys = explode("_","'.$parentName.'");
			 $childKeys = explode("_","'.$childName.'");
			 $allKeys = array_merge($parentKeys,$childKeys);
			 return count(array_intersect_key(array_flip($allKeys),array_flip($tableKeys))) == count($allKeys);'
		);
		$candidateTables = array_values(array_filter($tables,$searchNames));
		if (empty($candidateTables)) {
			throw new Zend_Exception('Unable to find intersection table: '.$parentName.'->'.$childName);
		}
		if (count($candidateTables) == 1) {
			$candidateTables[0];
		}
		$byStringLength = create_function(
			'$a,$b',
			'return strlen($a) - strlen($b);'
		);
		usort($candidateTables,$byStringLength);
		return $candidateTables[0];
	}
	
	protected static function _getTable($tableName) {
		return new Bbx_Db_Table(array('name'=>$tableName));
	}
	
}

?>