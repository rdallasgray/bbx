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
			$logFilePath = APPLICATION_PATH . '/../logs/' . $log;
			if(!file_exists($logFilePath)) {
				touch($logFilePath);
			}
			else if (filesize($logFilePath) > 100000000) {
				exec('tail -n 50000 ' . $logFilePath . ' > ' . $logFilePath . '__new');
				copy($logFilePath . '__new', $logFilePath);
				unlink($logFilePath . '__new');
			}
			$writer = new Zend_Log_Writer_Stream($logFilePath);
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
		return Bbx_Log::write('DEBUG: ' . $str, $type, $log);
	}

}

?>