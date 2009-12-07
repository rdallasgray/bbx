<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Default_User extends Bbx_Model {
	
	protected $_tableName = 'users';

	protected function _initRelationships() {
		$this->hasMany('admin_sessions');
		$this->hasOne('last_admin_session')->source('admin_sessions')->select(array('order'=>'id DESC','limit'=>1));
		$this->hasOne('current_admin_session')->source('admin_sessions')
			->select(array('order'=>'id DESC','where'=>"timein < NOW() AND timeout = '0000-00-00 00:00:00'",'limit'=>1));
	}
	
	protected function _afterCreate() {
		$this->password = md5($this->username.':'.Bbx_Config::get()->env->site->location.':'.$this->password);
	}
	
	protected function _beforeSave() {
		if (!empty($this->_oldData) && $this->password !== $this->_oldData['password']) {
			$this->password = md5($this->username.':'.Bbx_Config::get()->env->site->location.':'.$this->password);
		}
	}
}

?>