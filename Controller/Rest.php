<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest extends Zend_Controller_Action {
	
	protected $_resolver;
	protected $_lastModified;
	protected $_context;
	
	public $contexts = array(
		'index' => array('json','csv'),
		'show'  => array('json','csv'),
		'new'   => array('json')
	);

	public function init() {
		$this->_helper->contextSwitch()->initContext();
		$this->_context = $this->_helper->contextSwitch()->getCurrentContext();
	}

	protected function _setEtag($etag) {
		if (($ifNoneMatch = $this->getRequest()->getHeader('If-None-Match'))) {
			if ($ifNoneMatch === $etag) {
				$this->getResponse()->setHttpResponseCode(304)->sendResponse();
				exit();
			}
		}
		
		$this->getResponse()->setHeader('Etag',$etag);
	}
	
	protected function _authenticate() {
		$config = array(
			'accept_schemes' => 'digest',
			'realm'          => Bbx_Config::get()->env->site->location,
			'digest_domains' => '/',
			'nonce_timeout'  => 3600,
		);
		$adaptor = new Zend_Auth_Adapter_Http($config);
		$this->_resolver = new Bbx_Auth_Resolver_Db;
		$adaptor->setDigestResolver($this->_resolver);
		
		$adaptor->setRequest($this->getRequest());
		$adaptor->setResponse($this->getResponse());

		$result = $adaptor->authenticate();
		
		if (!$result->isValid()) {
			throw new Bbx_Controller_Rest_Exception(null,401);
		}
	}
	
	protected function _getBodyData() {
		return Zend_Json::decode($this->getRequest()->getRawBody());
	}

}

?>