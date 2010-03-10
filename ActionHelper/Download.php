<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Download extends Zend_Controller_Action_Helper_Abstract {
	
	public function direct(Bbx_Model $model) {

		if ($model instanceof Bbx_Model_Default_Media) {
			$extension = $model->getExtension();
		}
		else {
			$extension = $this->getActionController()->getContext();
		}
		$this->getResponse()->setHeader('Content-disposition','attachment; filename='
			.Bbx_ActionHelper_Filename::fromUrl($this->getRequest()->getRequestUri()).'.'.$extension, true);
	}

}

?>