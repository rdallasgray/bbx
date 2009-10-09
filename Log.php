<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Log {

	public static function write($str, $type = 'info', $log = 'main_log') {

		if (!Zend_Registry::isRegistered($log)) {

			$logFilePath = SITE_ROOT.'/logs';
			if(!file_exists($logFilePath)) {
				mkdir($logFilePath);
			}
			$writer = new Zend_Log_Writer_Stream($logFilePath.'/'.$log);
			$logger = new Zend_Log($writer);
			Zend_Registry::set($log,$logger);
		}

		$logger = Zend_Registry::get($log);
		
		if (method_exists($logger,$type)) {
			return $logger->$type($str);
		}
		
		return $logger->info($str);
	}
	
	public static function debug($str, $type = 'info', $log = 'main_log') {
		if (APP_MODE === 'development') {
			return Bbx_Log::write($str, $type, $log);
		}
		return null;
	}

}

?>