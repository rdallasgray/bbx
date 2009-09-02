<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Default_AdminSession extends Bbx_Model {
	
	protected $_tableName = 'admin_sessions';
	
	protected function _initRelationships() {
		$this->belongsTo('user');
	}
	
	protected function _beforeSave() {
		if ($this->timein == '' | $this->timein == '0000-00-00 00:00:00') {
			$this->setTimeIn();
		}
	}
	
	public function setTimeIn($time = null) {
		if (!$time) {
			$time = date('Y-m-d H:i:s');
		}
		$this->timein = $time;
	}
	
	public function setTimeOut($time = null) {
		if (!$time) {
			$time = date('Y-m-d H:i:s');
		}
		$this->timeout = $time;
	}
	
	public function close() {
		$this->setTimeOut();
		$this->save();
	}

}

?>