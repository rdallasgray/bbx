<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Registry  {
	
	protected function __construct() {
	}
	
	public static function get($registryName) {
		if (!Zend_Registry::isRegistered('Model_Registry')) {
			$registry = new stdClass();
			Zend_Registry::set('Model_Registry',$registry);
		}
		$registry = Zend_Registry::get('Model_Registry');
		if (!isset($registry->$registryName)) {
			$className = 'Bbx_Model_Registry_'.$registryName;
			try {
				$registry->$registryName = new $className;
			}
			catch (Exception $e) {
				throw new Zend_Exception('Registry type does not exist: '.$registryName);
			}
		}
		return $registry->$registryName;
	}

}

?>