<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_ControllerPlugin_ExtContext extends Zend_Controller_Plugin_Abstract {

	public function routeStartup(Zend_Controller_Request_Abstract $request) {
		
		$uri = $request->getRequestUri();
		
		if (strpos($uri,'.') === false) {
			return;
		}
		
		$querySplit = explode('?',$uri);
		$pathSplit = explode('/',$querySplit[0]);
		$pathLength = count($pathSplit);
		$resource = $pathSplit[$pathLength-1];
		
		$extSplit = explode('.',$resource);
		
		if (count($extSplit) > 1) {

			$ext = array_pop($extSplit);
			$request->setParam('format',$ext);
			$resource = implode('.',$extSplit);
			
			$pathSplit[$pathLength-1] = $resource;
			$path = implode('/',$pathSplit);
			if (count($querySplit) > 1) {
				$path = $path.'?'.end($querySplit);
			}
			
//			$request->setRequestUri($path);
			
		}
	}

}

?>