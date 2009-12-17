<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Crud extends Bbx_Controller_Rest {

	public function init() {
		parent::init();
	}

	public function preDispatch() {
		if ($this->_getParam('final')) {
			return;
		}
		$params = $this->_getAllParams();
		$request = $this->getRequest();

		if (isset($params['rel'])) {
			$rel_id = isset($params['rel_id']) ? $params['rel_id'] : null;
			$userParams = $this->_helper->Model->parseParams($params);
			$parentModel = $this->_helper->Model->getModel();
			$request
				->setControllerName($params['rel'])
				->setParams(array(
					'rel'=>null,
					'rel_id'=>null,
					'controller'=>$params['rel'],
					'id'=>$rel_id,
					'parentModel'=>$parentModel
				))
				->setDispatched(false);
				
			foreach($userParams as $key=>$val) {
				$request->setParam($key,$val);
			}
			
			return;
		}
		
		if ($this->_context === 'csv') {
			$this->_authenticate();
		}
		
		switch ($request->getMethod()) {
			case 'GET':
			break;
			
			case 'POST':
			case 'PUT':
			case 'DELETE':
			case 'OPTIONS':
			$this->_authenticate();
			
			$method = '_'.Inflector::underscore($request->getMethod());
			$this->$method();
		}
	}

	public function indexAction() {
		$params = $this->_helper->Model->parseParams($this->_getAllParams());
		$collection = $this->_helper->Model->getModel();
		
		if (!$collection instanceof Bbx_Model_Collection) {
			$collection = $collection->findAll($params);
		}

		$collectionName = Inflector::tableize(get_class($collection));
		$this->view->$collectionName = $collection;
		
		$this->_setEtag($this->view->$collectionName->etag($this->_helper->contextSwitch()->getCurrentContext()));
		
		if ($this->_context === 'json' || $this->_context === 'csv') {
			$options = ($this->_context === 'json') ? array('deep' => true) : null;
			$this->view->assign($this->view->$collectionName->toArray($options));
			unset($this->view->$collectionName);
		}
	}

	public function showAction() {
		$model = $this->_helper->Model->getModel();
		
		if($model instanceof Bbx_Model) {
			$modelName = Inflector::underscore(get_class($model));
			$this->view->$modelName = $model;
	
			$this->_setEtag($this->view->$modelName->etag($this->_helper->contextSwitch()->getCurrentContext()));
	
			if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
				$this->view->assign($this->view->$modelName->toArray());
				unset($this->view->$modelName);
			}
		}
		else {
			throw new Bbx_Controller_Rest_Exception(null,404);
		}
	}
	
	public function newAction() {
		$this->_authenticate();
		$model = $this->_helper->Model->getModel();
		if ($model instanceof Bbx_Model) {		
			$this->view->assign($model->schema());
		}
		else {
			throw new Bbx_Controller_Rest_Exception(null,404);
		}
	}

	protected function _post() {
		$model = $this->_helper->Model->getModel();
		
		$new_model = $model->create($this->_getBodyData());
		
		$this->getResponse()->setHttpResponseCode(201)->setHeader('Location',$new_model->url(true));
		
		$this->_forward('show',null,null,array('format' => 'json','id' => $new_model->id,'final' => true));
	}

	protected function _put() {
		if (!$this->_hasParam('id')) {
			$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'GET,POST'));
			throw $e;
		}
		$this->_helper->Model->getModel()->update($this->_getBodyData());
	}

	protected function _delete() {
		if (!$this->_hasParam('id')) {
			$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'GET,POST'));
			throw $e;
		}
		$model = $this->_helper->Model->getModel();
		$model->delete($this->_getParam('id'));
		$this->getResponse()->setHttpResponseCode(204)->sendResponse();
		exit();
	}
	
	protected function _head() {
	}
	
	protected function _options() {
//TODO set header for allowed methods etc.
	}

}

?>