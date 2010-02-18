<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Config {

	public $db;
	public $env;
	private static $_instance = null;
	
	private function __construct() {
		$this->env = new Zend_Config_Ini(SITE_ROOT.'/application/modules/'.MODULE_NAME.'/config/env.ini',APP_MODE);
		$this->db = new Zend_Config_Ini(SITE_ROOT.'/application/modules/'.MODULE_NAME.'/config/db.ini',APP_MODE);
	}

	public static function get() {
		if (self::$_instance === null) {
			self::$_instance = new Bbx_Config();
		}
		return self::$_instance;
	}

//TODO deal with this later	
/*
	public static function locale() {
		if (isset($this->env->locale)) {
			if (!Zend_Registry::isRegistered('locale')) {
				return Site_Config::$lang[Site_Config::$lang['default']]['locale'].'.'.Site_Config::$lang['charset'];
			}
			$locale = Zend_Registry::get('locale');
			if ($locale->getLanguage() == Site_Config::$lang['default'] || in_array($locale->getLanguage(),Site_Config::$lang['translations'])) {
				return $locale->toString();
			}
			return Site_Config::$lang[Site_Config::$lang['default']]['locale'].'.'.Site_Config::$lang['charset'];
		}
		return Site_Config::$lang['locale'].'.'.Site_Config::$lang['charset'];
	}
*/
	
}

?>