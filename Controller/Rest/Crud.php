<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Crud extends Bbx_Controller_Rest {

	public function preDispatch() {
		parent::preDispatch();
		$request = $this->getRequest();
		if (($rel = $request->getParam('rel'))) {
			if (method_exists($this, Inflector::camelize($rel) . 'Action') && !$request->getParam('final'))  {
				$request->setActionName($rel)->setParam('final', true)->setDispatched(false);
				return;
			}
			Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer')->setScriptAction($rel);
		}
		$this->_doRequestMethod();
	}
	
	public function indexAction() {
		$collection = $this->_getIndexData();
		$this->_assign($collection);
	}

	public function showAction() {
		$model = $this->_getShowData();
		$this->_assign($model);
	}
	
	public function newAction() {
		$this->_helper->authenticate();
		$model = $this->_helper->Model->getModel();
		$this->view->assign($model->newModel());
	}
	
	protected function _getIndexData() {
		return $this->_helper->Model->getCollection();
	}
	
	protected function _getShowData() {
		return $this->_helper->Model->getModel();
	}
	
	protected function _doRequestMethod() {
		$method = '_' . strtolower($this->getRequest()->getMethod());
		if (method_exists($this, $method)) {
			$this->$method();
		}
	}

	protected function _assign($model) {
		if ($this->getRequest()->isHead()) {
			Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer')->setNoRender(true);
			return;
		}
		$request = $this->getRequest();
		$this->_setEtag($model->etag($this->_context));

		$modelName = $model instanceof Bbx_Model ? 
				Inflector::underscore(get_class($model)) : Inflector::tableize($model->getModelName());
	
		if ($request->getParam('list') === 'true') {
			$model->renderAsList();
		}		
		if ($this->_context === 'csv') {
			$this->_helper->authenticate();
		}
		if ($request->getParam('download') == 'true') {
			$this->_helper->authenticate();
			$this->_helper->download($model);
		}

		if ($this->_context === 'json') {
			$options = ($this->_context === 'json') ? array('deep' => true) : null;
			$this->view->assign($model->toArray($options));
		}
		else {
			$this->view->$modelName = $model;
		}
	}

	protected function _post() {
		$this->_helper->authenticate();
		$collection = $this->_helper->Model->getCollection();
		if ($collection instanceof Bbx_Model) {
			// It's a hasOne-type model
			throw new Bbx_Controller_Rest_Exception(null, 405, array('allowed_methods' => 'GET,PUT'));
		}
		$model = Bbx_Model::load($collection->getModelName());
		if ($model instanceof Bbx_Model_Default_Media) {
			$new_model = $this->_helper->Media->handleUpload($collection);
		}
		else {
			$new_model = $collection->create($this->_getBodyData());
		}
		
		Bbx_CacheManager::clean($this->getRequest(), 'post');
		
		$this->getResponse()->setHttpResponseCode(201)
			->setHeader('Location',$new_model->url(true))
			->setBody(Zend_Json::encode($new_model->toArray()))
			->sendResponse();
		//TODO if we exit, we can't use postDispatch hooks ...	also, this presumes we always want to respond with JSON
		exit();
	}

	protected function _put($data = null) {
		$this->_helper->authenticate();
		try {
			$model = $this->_helper->Model->getModel();
			$method = ($model->isEmpty()) ? 'create' : 'update';
			if ($data === null) {
				$data = $this->_getBodyData();
			}
			$model->$method($data);
			Bbx_CacheManager::clean($this->getRequest(), 'put');
		}
		catch (Exception $e) {
			Bbx_Log::write('Unable to _put to model: ' . $e->getMessage());
			throw new Bbx_Controller_Rest_Exception(null, 405, array('allowed_methods' => 'GET,POST'));
		}
	}

	protected function _delete() {
		$this->_helper->authenticate();
		if (!$this->_hasParam('id')) {
			$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'GET,POST'));
			throw $e;
		}
		$model = $this->_helper->Model->getModel();
		$model->delete($this->_getParam('id'));
		Bbx_CacheManager::clean($this->getRequest(), 'delete');
		$this->getResponse()->setHttpResponseCode(204)->sendResponse();
		exit();
	}

}

?>