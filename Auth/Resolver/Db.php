<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Auth_Resolver_Db implements Zend_Auth_Adapter_Http_Resolver_Interface {
	
	protected $_user;
	
	public function resolve($username,$realm) {
		$users = Bbx_Model::load('User');
		$user = $users->find($users->select()->where('username = ?',$username));
		if (!$user instanceof Bbx_Model) {
			return null;
		}
		$this->_user = $user;
		return (md5($username.':'.$realm.':'.$user->password));
	}
	
	public function getUser() {
		return $this->_user;
	}
	
}

?>