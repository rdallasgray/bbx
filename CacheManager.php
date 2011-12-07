<?php

class Bbx_CacheManager {
	
	public static function clean($request, $method) {
		switch($method) {
			case 'put':
			case 'delete':
			self::_cleanForPut($request);
			break;
			case 'post':
			self::_cleanForPost($request);
			break;
		}
	}
	
	private static function _cache() {
		$cache = Zend_Controller_Front::getInstance()
					->getParam('bootstrap')
					->getResource('cachemanager')
					->getCache('page');
		return $cache;
	}
	
	private static function _clean($tags) {
		$san_tags = array();
		foreach($tags as $k => $v) {
		    $san_tags[$k] = preg_replace('/[^A-Za-z0-9_]/', '', $v);
		}
		Bbx_Log::write('Cleaning tags ' . print_r($san_tags, true));

		self::_cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $san_tags);
	}

	private static function _cleanForPut($request) {
		$tags = array();
		if (($rel = $request->getParam('rel'))) {
			$tag = $request->getControllerName() . '_' . $request->getParam('id');
			$tags[] = $tag;
			$tags[] = $tag . '_' . $rel;
		}
		else {
			$tags[] = $request->getControllerName();
			if (($id = $request->getParam('id'))) {
				$tags[] = $tags[0] . '_' . $id;
			}
		}
		self::_clean($tags);
	}

	private static function _cleanForPost($request) {
		$tags = array();
		if (($rel = $request->getParam('rel'))) {
			$tags[] = $rel;
			$controller = $request->getControllerName();
			$tags[] = $controller . '_' . $rel;
			$tags[] = $controller . '_' . $request->getParam('id') . '_' . $rel;
		}
		else {
			$tags[] = $request->getControllerName();
		}
		self::_clean($tags);
	}
}

?>