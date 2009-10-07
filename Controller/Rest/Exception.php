<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Exception extends Zend_Exception {
	
	public function __construct($message = 'Server Error',$code = 400,$extra = null) {
		
		switch ($code) {
			case 401:
			if (null == $message){
				$message = 'Not Authorised';
			}
			break;
			
			case 403:
			if (null == $message){
				$message = 'Forbidden';
			}
			break;
			
			case 404:
			if (null == $message){
				$message = 'Not Found';
			}
			break;
			
			case 405:
			if (null == $message){
				$message = 'Method Not Allowed';
			}
			Zend_Controller_Front::getInstance()->getResponse()->setHeader('Allow',$extra['allowed_methods']);
			break;
		}
		
		parent::__construct($message,$code);
	}
	
}

?>