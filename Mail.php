<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Mail extends Zend_Mail {

	protected static $_instance = null;	

	protected function _encodeHeader($value) {
		if (Zend_Mime::isPrintable($value)) {
			return $value;
		} 
		else {
			$quotedValue = Zend_Mime::encodeQuotedPrintable($value);
			$quotedValue = str_replace(array('?', ' '), array('=3F', '=20'), $quotedValue);
			$quotedValue = rawurlencode($quotedValue);
			$quotedValue = str_replace('%3D%0A','',$quotedValue);
			$quotedValue = rawurldecode($quotedValue);
			$quotedValue = '=?' . $this->_charset . '?Q?' . $quotedValue . '?=';
		}
		return $quotedValue;
	}

	static public function instance() {
		$args = func_get_args();
		if (self::$_instance === null) {
			$options = array();
			if (isset(Bbx_Config::get()->site->smtp_username)) {
				$options = 	array(
					'auth'=>'login',
					'username'=>Bbx_Config::get()->site->smtp_username,
					'password'=>Bbx_Config::get()->site->smtp_password
				);
			}
			$transport = new Zend_Mail_Transport_Smtp(Bbx_Config::get()->site->smtp_server,$options);
			Zend_Mail::setDefaultTransport($transport);
			self::$_instance = new Bbx_Mail();
		}
		return self::$_instance;
	}

}

?>