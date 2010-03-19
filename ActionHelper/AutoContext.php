<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_AutoContext extends Zend_Controller_Action_Helper_Abstract {
	
	public function init() {
		$req = $this->getRequest();
		$accept = $req->getHeader('Accept');
		$types = explode(',',$accept);
		$mainType = trim($types[0]);
		$mime = explode('/',$mainType);
				
		if($mime[1] == '' || $mime[1] == '*') {
			return;
		}
		
		try {
			if ($this->getRequest()->getParam('format') == '') {
				$this->getRequest()->setParam('format',$mime[1]);
			}
		}
		catch (Exception $e) {
		}
	}

}

?>