<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_View_Helper_Date {
	
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
/*	
	public function date($date = null,$format = null) {
		if ($date === null) {
			return $this;
		}
		return Bbx_Date::getDate($date,$format);
	}
	
	public function dateTime($dateTime,$format = null) {
		return Bbx_Date::getDateTime($dateTime,$format);
	}
	
	public function time($dateTime,$format = null) {
		return Bbx_Date::getDateTime($dateTime,$format);
	}

	public function range($from, $to, $format = null, $separator = null) {
		return Bbx_Date::getDateRange($dateTime,$format);
	}
*/
	
	public function date($date = null,$format = null) {
		if ($date === null) {
			return $this;
		}
		return Bbx_Date::date($date,$format);
	}
	
	public function __call($method,$arguments) {
		if (method_exists('Bbx_Date',$method)) {
			try {
				return call_user_func_array(array('Bbx_Date',$method),$arguments);
			}
			catch (Exception $e) {
				Bbx_Log::write($e->getMessage());
			}
		}
		else {
			Bbx_Log::write('Bbx_Date has no method "'.$method.'"');
		}
	}

}

?>