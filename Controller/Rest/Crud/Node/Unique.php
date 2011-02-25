<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Crud_Node_Unique extends Bbx_Controller_Rest_Crud_Node {

	public function indexAction() {
		$this->getRequest()->setActionName('show');
		$this->showAction();
	}

	public function showAction() {
		$this->_doRequestMethod();
		$model = $this->_helper->Model->getModel();
		$modelName = Inflector::underscore(get_class($model));
			
		$this->view->$modelName = $model;
		
		if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
			$this->view->assign($this->view->$modelName->toArray());
			unset($this->view->$modelName);
		}

	}

	protected function _post() {
		$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'PUT'));
		throw $e;
	}

	protected function _put() {
		$this->_helper->authenticate();
		$model = $this->_helper->Model->getModel();
		if ($model instanceof Bbx_Model) {
			$model->update($this->_getBodyData());
		}
		else {
			$model->create($this->_getBodyData());
		}
	}

	protected function _delete() {
		$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'PUT'));
		throw $e;
	}

}

?>