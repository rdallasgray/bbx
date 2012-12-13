<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



abstract class Bbx_Media_Abstract {
	
	protected function __construct($sourcePath) {
		$this->_sourcePath = $sourcePath;
	}

  protected function _checkPath($savePath) {
    $pathName = dirname($savePath)
    if (!file_exists($pathName)) {
      mkdir($pathName, 0770, true);
    }
  }
	
}

?>