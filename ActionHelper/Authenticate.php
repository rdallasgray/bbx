<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Authenticate extends Zend_Controller_Action_Helper_Abstract {
	
	protected $_authenticated;
	protected $_resolver;
	
	public function direct() {
		if ($this->_authenticated === true) {
			return true;
		}
		
		$config = array(
			'accept_schemes' => 'digest',
			'realm'          => Bbx_Config::get()->site->location,
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
		$this->_authenticated = true;
	}
	
	public function getUser() {
		return $this->_resolver->getUser();
	}

}

?>