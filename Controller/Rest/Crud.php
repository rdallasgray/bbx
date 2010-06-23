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
		if ($this->_getParam('final')) {
			return;
		}
		$params = $this->_getAllParams();
		$request = $this->getRequest();

		if (isset($params['rel'])) {
			$initialRequest = clone $request;
			$initialRequest->setDispatched(true);
			
			$rel_id = isset($params['rel_id']) ? $params['rel_id'] : null;
			$userParams = $this->_helper->Model->parseParams($params);
			$parentModel = $this->_helper->Model->getModel();
			
			$newParams = array_merge(
				array(
					'rel'            => null,
					'rel_id'         => null,
					'id'             => $rel_id,
					'parentModel'    => $parentModel,
					'controller'     => $params['rel'],
					'initialRequest' => $initialRequest
				),
				$userParams
			);
			
			return $this->_forward(
				$request->getActionName(),
				$params['rel'],
				$request->getModuleName(),
				$newParams
			);
		}
		
		if ($this->_context === 'csv') {
			$this->_helper->authenticate();
		}

		if (@$params['download'] === "true") {
			$this->_helper->authenticate();
			$this->_helper->download($this->_helper->Model->getModel());
		}
	}
	
	public function postDispatch() {
		if ($this->_hasParam('initialRequest')) {
			$r = $this->_getParam('initialRequest');
			Zend_Controller_Front::getInstance()->setRequest($r);
		}
	}
	
	protected function _doRequestMethod() {
		$method = '_' . strtolower($this->getRequest()->getMethod());
		if (method_exists($this, $method)) {
			$this->$method();
		}
	}

	public function indexAction() {
		$this->_doRequestMethod();
		if ($this->getRequest()->isHead()) {
			return;
		}
		$collection = $this->_getIndexData();
		$this->_setEtag($collection->etag($this->_helper->contextSwitch()->getCurrentContext()));
		$collectionName = Inflector::tableize($collection->getModelName());
		$this->view->$collectionName = $collection;


		if ($this->_context === 'json' || $this->_context === 'csv') {
			$options = ($this->_context === 'json') ? array('deep' => true) : null;
			$this->view->assign($this->view->$collectionName->toArray($options));
			unset($this->view->$collectionName);
		}
	}
	
	protected function _getIndexData() {
		return $this->_helper->Model->getCollection();
	}
	
	protected function _getShowData() {
		return $this->_helper->Model->getModel();
	}
	
	protected function _htmlIndexRel() {
		$request = $this->getRequest();
		$action = $request->getActionName();
		if ($action !== 'index' && $action !== 'show') {
			if (method_exists($this, $action . 'Action')) {
				return;
			}
		}
		
		if (!$this->_hasParam('parentModel')) {
			throw new Zend_View_Exception('Not Found');
		}
		$subject = $this->_getParam('parentModel');
		$subjectType = Inflector::underscore(get_class($subject));
		$controller = Inflector::interscore(Inflector::pluralize($subjectType));
		$action = $this->_getParam('controller');
		
		$controllerClass = ucfirst($controller) . 'Controller';
		if (class_exists($controllerClass)) {
			$actionName = $action . 'Action';
			if (method_exists($controllerClass, $actionName)) {
				return $this->_forward(
					$action,
					$controller,
					$request->getModuleName(),
					array('id' => $subject->id)
				);
			}
		}

		$this->view->$subjectType = $subject;
		
		$this->_helper->viewRenderer($controller.'/'.$action,  null, true);
	}

	public function showAction() {
		$this->_doRequestMethod();
		if ($this->getRequest()->isHead()) {
			return;
		}
		$model = $this->_getShowData();
	
		$this->_setEtag($model->etag($this->_helper->contextSwitch()->getCurrentContext()));

		$modelName = Inflector::underscore(get_class($model));
		$this->view->$modelName = $model;

		if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
			$this->view->assign($this->view->$modelName->toArray());
			unset($this->view->$modelName);
		}
	}
	
	public function newAction() {
		$this->_helper->authenticate();
		if ($this->getRequest()->isHead()) {
			return;
		}
		$model = $this->_helper->Model->getModel();
		if ($model instanceof Bbx_Model) {		
			$this->view->assign($model->newModel());
		}
		else {
			throw new Bbx_Controller_Rest_Exception(null,404);
		}
	}

	protected function _post() {
		$this->_helper->authenticate();
		$model = $this->_helper->Model->getModel();
		
		$new_model = $model->create($this->_getBodyData());
		
		$this->getResponse()->setHttpResponseCode(201)
			->setHeader('Location',$new_model->url(true))
			->setBody(Zend_Json::encode($new_model->toArray()))
			->sendResponse();
		//TODO if we exit, we can't use postDispatch hooks ...	also, this presumes we always want to respond with JSON
		exit();
	}

	protected function _put($data = null) {
		$this->_helper->authenticate();
		if (!$this->_hasParam('id')) {
			$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'GET,POST'));
			throw $e;
		}
		if ($data === null) {
			$data = $this->_getBodyData();
		}
		$this->_helper->Model->getModel()->update($data);
	}

	protected function _delete() {
		$this->_helper->authenticate();
		if (!$this->_hasParam('id')) {
			$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'GET,POST'));
			throw $e;
		}
		$model = $this->_helper->Model->getModel();
		$model->delete($this->_getParam('id'));
		$this->getResponse()->setHttpResponseCode(204)->sendResponse();
		exit();
	}

}

?>