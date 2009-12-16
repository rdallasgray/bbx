<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_Bootstrap {

	public static function run() {

		/**
		* Set up basic ini settings
		*
		*/

		ini_set("display_errors","0");
		ini_set("html_errors","0");
		ini_set("log_errors","1");
		error_reporting(E_ALL);

		ini_set("short_open_tag","0");
		
		set_time_limit(240);

		/**
		* Get config info and set basic include paths 
		*
		*/


		set_include_path(get_include_path().':'.SITE_ROOT.'/library');


		require(SITE_ROOT.'/library/Zend/Loader.php');
		require(SITE_ROOT.'/library/Bbx/Loader.php');

		@Zend_Loader::registerAutoload('Bbx_Loader');
		//TODO THIS NEEDS TO BE CHANGED TO WORK WITH ZEND_LOADER_AUTOLOADER BEFORE V2.0


		/**
		* Set up locale, language and charset 
		*
		*/

		ini_set("default_charset",Bbx_Config::get()->env->locale->charset);
		ini_set("date.timezone","UTC");

		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding(Bbx_Config::get()->env->locale->charset);
		}



		/**
		* Require unclassed library files
		*
		*/

		require(SITE_ROOT.'/library/Bbx/Common/lib.php');
		


		// LOCALE

		//TODO redo locale from scratch


		/**
		* Set up global routes 
		*
		*/

		$front = Zend_Controller_Front::getInstance();

		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		Zend_Registry::set('router',$router);

		include(SITE_ROOT.'/application/modules/'.MODULE_NAME.'/config/routes.php');

		$front->setControllerDirectory(array(
			'default'=>SITE_ROOT.'/application/modules/'.MODULE_NAME.'/controllers',
			'admin'=>SITE_ROOT.'/library/Bbx/Admin/application/controllers'
		));

		$front->setRouter($router);

		/**
		* Set up global Action Helpers 
		*
		*/

		Zend_Controller_Action_HelperBroker::addPath(SITE_ROOT.'/library/Bbx/ActionHelper','Bbx_ActionHelper');

		$front->registerPlugin(new Bbx_ControllerPlugin_Startup);


		/**
		* Set media path
		*
		*/
//TODO better way to find media path -- set as constant or method in Model
		set_include_path(get_include_path().':'.SITE_ROOT.'/application/modules/'.MODULE_NAME.'/media');
		
		
		/**
		* Set up DB
		*
		*/
		
		$db = Zend_Db::factory(Bbx_Config::get()->db->adaptor,array(
			'host'=>Bbx_Config::get()->db->host,
			'username'=>Bbx_Config::get()->db->username,
			'password'=>Bbx_Config::get()->db->password,
			'dbname'=>Bbx_Config::get()->db->dbname,
			'charset'=>Bbx_Config::get()->env->locale->charset,
		));

		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		
		$db->query('SET NAMES utf8');
		
		Zend_Registry::set('db',$db);
		
		
		/**
		* Start Front Controller 
		*
		*/
		if (!defined("NO_DISPATCH")) {
			$front->dispatch();
		}

	}

}

Bbx_Bootstrap::run();

?>