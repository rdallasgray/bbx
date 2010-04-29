<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Crud_Node_Text extends Bbx_Controller_Rest_Crud_Node {

	public function showAction() {
		$model = $this->_helper->Model->getModel();

		if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
			if ($model instanceof Bbx_Model && isset($model->text)) {
				$this->view->text = $model->text;
			}
			else {
				$this->view->text = "";
			}
		}
		else {
			$modelName = Inflector::underscore(get_class($model));
			$this->view->$modelName = $model;
		}
		$this->_setEtag($model->etag($this->_helper->contextSwitch()->getCurrentContext()));
	}

}

?>