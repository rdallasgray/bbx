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
		$this->model = $this->_helper->Model->getModel();
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
			$request
				->setControllerName($params['rel'])
				->setParams(array(
					'rel'=>null,
					'rel_id'=>null,
					'controller'=>$params['rel'],
					'id'=>$rel_id,
					'parentModel'=>$this->model
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

		$collectionName = Inflector::tableize(get_class($this->model));
		if (!isset($this->collection)) {
			$this->view->$collectionName = $this->model->findAll($params);
		}
		else {
			$this->view->$collectionName = $this->collection;
		}
		
		$this->_setEtag($this->view->$collectionName->etag($this->_helper->contextSwitch()->getCurrentContext()));
		
		if ($this->_context === 'json' || $this->_context === 'csv') {
			$options = ($this->_context === 'json') ? array('deep' => true) : null;
			$this->view->assign($this->view->$collectionName->toArray($options));
			unset($this->view->$collectionName);
		}
	}

	public function showAction() {
		if (isset($this->model)) {
			$modelName = Inflector::underscore(get_class($this->model));
			$this->view->$modelName = $this->model;
		
			$this->_setEtag($this->view->$modelName->etag($this->_helper->contextSwitch()->getCurrentContext()));
		
			if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
				$this->view->assign($this->view->$modelName->toArray());
				unset($this->view->$modelName);
			}
		}
	}
	
	public function newAction() {
		$this->_authenticate();
		if (isset($this->model) && $this->model instanceof Bbx_Model) {		
			$this->view->assign($this->model->schema());
		}
		else {
			throw new Bbx_Controller_Rest_Exception(null,404);
		}
	}

	protected function _post() {
		$model = isset($this->collection) ? $this->collection : $this->model;
		$new_model = $model->create($this->_getBodyData());
		
		$this->getResponse()->setHttpResponseCode(201)->setHeader('Location',$new_model->url(true));
		
		$this->_forward('show',null,null,array('format' => 'json','id' => $new_model->id,'final' => true));
	}

	protected function _put() {
		if (!$this->_hasParam('id')) {
			$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'GET,POST'));
			throw $e;
		}
		$this->model->update($this->_getBodyData());
	}

	protected function _delete() {
		if (!$this->_hasParam('id')) {
			$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'GET,POST'));
			throw $e;
		}
		$model = isset($this->collection) ? $this->collection : $this->model;
		$model->delete($this->model->id);
		unset($this->model);
		$this->getResponse()->setHttpResponseCode(204);
	}
	
	protected function _head() {
	}
	
	protected function _options() {
//TODO set header for allowed methods etc.
	}

}

?>