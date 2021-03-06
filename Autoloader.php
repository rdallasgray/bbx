<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Autoloader implements Zend_Loader_Autoloader_Interface {

	public function autoload($class) {

		if (substr($class, 0, 10) === 'ViewHelper') {
			
			$class = substr($class, 11);
			$path = APPLICATION_PATH . '/modules/'.MODULE_NAME.'/views/helpers/'.$class.'.php';

			if (Zend_Loader::isReadable($path)) {
				@include $path;
				return true;
			}
		}

		if (substr($class,-10) === 'Controller') {
			
			$path = APPLICATION_PATH . '/modules/'.MODULE_NAME.'/controllers/'.$class.'.php';

			if (Zend_Loader::isReadable($path)) {
				@include $path;
				return true;
			}
		}
		
		if (substr($class,-4) === 'Form') {
			
			$path = APPLICATION_PATH . '/modules/'.MODULE_NAME.'/forms/'.$class.'.php';

			if (Zend_Loader::isReadable($path)) {
				@include $path;
				return true;
			}
		}
		
		$paths = array(
			APPLICATION_PATH . '/modules/'.MODULE_NAME.'/models',
			APPLICATION_PATH . '/../library/Bbx/Vendor',
		);
		
		foreach ($paths as $p) {
			$classPath = $p.'/'.$class.'.php';
			if (Zend_Loader::isReadable($classPath)) {
				@include $classPath;
				return true;
			}
		}
		
		return false;
	}

}

?>