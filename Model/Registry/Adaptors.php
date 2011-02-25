<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Registry_Adaptors extends Bbx_Model_Registry_Abstract {

	public function getAdaptor(Bbx_Model $parentModel, $adName) {
		$parentModelName = get_class($parentModel);
		$this->endCurrentRegistration();

		if (!$this->_isRegistered($parentModelName, $adName)) {
			throw new Zend_Exception('No adaptor registered for '.$parentModelName.'-'.$adName);
		}
		if (!$this->_isInstantiated($parentModelName, $adName)) {
			$this->_instantiate($parentModelName, $adName);
		}
		return $this->_data[$parentModelName][$adName]['adaptor'];
	}
	
	protected function _instantiate($parentModelName, $adName) {
		$class = 'Bbx_Model_Adaptor_'.ucwords($adName);
		$adap = new $class($this->_data[$parentModelName][$adName]);
		$this->_data[$parentModelName][$adName]['adaptor'] = $adap;
	}
	
	protected function _isInstantiated($parentModelName, $adName) {
		return array_key_exists('adaptor', $this->_data[$parentModelName][$adName]);
	}
}

?>