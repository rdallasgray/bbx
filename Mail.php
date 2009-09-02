<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

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

	static public function instance($args = null) {
		$args = isset($args) ? func_get_args() : array();
		if (self::$_instance === null) {
			$transport = new Zend_Mail_Transport_Smtp(
				Site_Config::$site['mail']['smtp_server'],
				array(
					'auth'=>'login',
					'username'=>Site_Config::$site['mail']['smtp_username'],
					'password'=>Site_Config::$site['mail']['smtp_password']
				)
			);
			Zend_Mail::setDefaultTransport($transport);
			$mail = new ReflectionClass('Bbx_Mail');
			self::$_instance = $mail->newInstanceArgs($args);
		}
		return self::$_instance;
	}

}

?>