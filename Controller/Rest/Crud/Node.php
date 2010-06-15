<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Crud_Node extends Bbx_Controller_Rest_Crud {

	protected function _createModel() {
		$className = Inflector::classify($this->getRequest()->getControllerName());
		return Bbx_Model::load($className);
	}

	public function indexAction() {
		$this->getRequest()->setActionName('show');
		$this->showAction();
	}

	public function showAction() {
		
		$model = $this->_helper->Model->getModel();
		if ($model instanceof Bbx_Model_Collection) {
			$model = $model->current();
		}
		if ($model instanceof Bbx_Model) {
			$this->_setEtag($model->etag($this->_helper->contextSwitch()->getCurrentContext()));
			$modelName = Inflector::underscore(get_class($model));
		}
		else {
			$modelName = Inflector::underscore($this->getRequest()->getControllerName());
		}
			
		$this->view->$modelName = $model;
		
		if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
			if ($this->view->$modelName instanceof Bbx_Model) {
				$this->view->assign($this->view->$modelName->toArray());
			}
			else {
				$this->view->assign($this->_createModel()->newModel());
			}
			unset($this->view->$modelName);
		}

	}

	protected function _post() {
		$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'PUT'));
		throw $e;
	}

	protected function _put() {
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
	
	protected function _options() {
//TODO set header for allowed methods etc.
	}

}

?>