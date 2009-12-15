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
		
//		set_time_limit(240); For testing CSV/other long jobs

		/**
		* Get config info and set basic include paths 
		*
		*/


		set_include_path(get_include_path().':'.SHARED_LIB);


		require(SHARED_LIB.'/Zend/Loader.php');
//		require(SHARED_LIB.'/Zend/Loader/AutoLoader.php');
		require(SHARED_LIB.'/Bbx/Loader.php');

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

		require(SHARED_LIB.'/Bbx/Common/lib.php');
		
		

		/**
		* Register global log, caches, view, db, dict, mail
		*
		*/

		// SESSION

		Zend_Session::setOptions(array(
			'strict'=>true,
			'name'=>md5(Bbx_Config::get()->env->site->location)
		));

		// CACHE

		$regExps = array('admin'=>array('cache'=>false));

		$page_cache = Zend_Cache::factory(
			'Page',
			'File',
		array(
			'lifetime' => 120,
			'debug_header' => (APP_MODE !== 'production'),
			'regexps' => $regExps
			),
		array(
			'cache_dir' => SITE_ROOT.'/cache/'
			)
			);
		if (APP_MODE === 'production') {
			$page_cache->start();
		}

		$db_cache_lifetime = (APP_MODE === 'production') ? 300 : null;

		$db_cache = Zend_Cache::factory(
			'Core',
			'File',
		array(
			'lifetime' => $db_cache_lifetime,
			'automatic_serialization' => true
			),
		array(
			'cache_dir' => SITE_ROOT.'/cache/'
			)
			);

		$output_cache_lifetime = (APP_MODE === 'production') ? 300 : null;

		$output_cache = Zend_Cache::factory(
			'Output',
			'File',
		array(
			'lifetime' => $output_cache_lifetime,
			),
		array(
			'cache_dir' => SITE_ROOT.'/cache/'
			)
			);

		$table_cache_lifetime = (APP_MODE === 'production') ? 300 : null;

		$table_cache = Zend_Cache::factory(
			'Core',
			'File',
		array(
			'lifetime' => $table_cache_lifetime,
			'automatic_serialization' => true
			),
		array(
			'cache_dir' => SITE_ROOT.'/cache/'
			)
			);

		Zend_Db_Table_Abstract::setDefaultMetadataCache($table_cache);

		Zend_Registry::set('page_cache',$page_cache);
		Zend_Registry::set('db_cache',$db_cache);
		Zend_Registry::set('output_cache',$output_cache);
		Zend_Registry::set('table_cache',$table_cache);


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
			'admin'=>SHARED_LIB.'/Bbx/Admin/application/controllers'
		));

		$front->setRouter($router);

		/**
		* Set up global Action Helpers 
		*
		*/

		Zend_Controller_Action_HelperBroker::addPath(SHARED_LIB.'/Bbx/ActionHelper','Bbx_ActionHelper');

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