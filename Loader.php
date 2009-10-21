<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Loader extends Zend_Loader {

	public static function loadClass($class,$dirs = null) {
		parent::loadClass($class,$dirs);
	}

	public static function autoload($class) {
		
		if (substr($class,-10) === 'Controller') {
			try {
				self::loadClass($class,array(
					SITE_ROOT.'/application/modules/'.MODULE_NAME.'/controllers'
				));
				return $class;
			} 
			catch (Exception $e) {
			}
		}
		
		try {
			self::loadClass($class,array(
				SHARED_LIB.'/Bbx/Vendor',
				SITE_ROOT.'/library',
				SHARED_LIB,
				SITE_ROOT.'/application/modules/'.MODULE_NAME.'/models'
			));
			return $class;
		} 
		catch (Exception $e) {
			return false;
		}
	}

}

?>