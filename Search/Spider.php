<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Search_Spider {

	protected $_visited = array();
	protected $_maxLinks = 500;
	protected $_languages;
	protected $_currentLanguage;
	protected $_indexLevel;
	protected $_search;
	protected $_reporter;

	public function __construct($startUrl = '',$level = 0) {
		set_time_limit(6000);
		$this->_search = Zend_Registry::get('searchManager');
		$this->_reporter = Zend_Registry::get('searchIndexReporter');
		$this->_client = new Zend_Http_Client;
		$this->_client->setConfig(array('keepalive'=>true));
		$this->_client->setParameterGet(array('spider'=>'true'));
		$this->_indexLevel = $level;
		$this->_reporter->report("Spidering at level ".$level."\n");
		if (isset(Site_Config::$lang['multi']) && Site_Config::$lang['multi']) {
			$this->_languages = array_merge(array(Site_Config::$lang['default']),Site_Config::$lang['translations']);
			foreach ($this->_languages as $lang) {
				$this->_currentLanguage = $lang;
				$this->_spider($this->_getLinks($this->_getUrl($startUrl,'?lang='.$lang)));
			}
		}
		else {
			$this->_spider($this->_getLinks($this->_getUrl($startUrl)));
		}
	}

	protected function _checkLink($link) {
		if (empty($link)) {
//			$this->_reporter->report($link.': Empty url, returning');
			return false;
		}
		$schemeChecked = explode(':',$link);
		if (isset($schemeChecked[1])) {
//			$this->_reporter->report($link.': url has scheme, returning');
			return false; 
		}
		if (strpos($link,'//') !== false) {
//			$this->_reporter->report($link.': url has scheme, returning');
			return false;
		}
		$slashed = explode('/',$link);
		if (in_array($slashed[0],Site_Structure::$noIndex)) {
//			$this->_reporter->report($link.': url set as noIndex, returning');
			return false;
		}
		$link = explode('#',$link);
		return $link[0];
	}

	protected function _getUrl($link = '') {
		$rawUrl = Site_Config::site_base().$link;
		$splitUrl = explode('?',$rawUrl);
		$url = $splitUrl[0];
		if (isset($this->_currentLanguage)) {
			$url .= '?lang='.$this->_currentLanguage;
		}
		return $url;
	}

	protected function _getLink($url = '') {
		return str_replace(Site_Config::site_base(),'',$url);
	}

	protected function _getLinks($url) {
		$links = array();
		if (!$this->_isVisited($url)) {
			$this->_reporter->report($url.': Getting links');
			$docLinks = $this->_visit($url);
			$links = array();
			foreach ($docLinks as $link) {
				if ($this->_checkLink($link)) {
					$link = $this->_checkLink($link);
					$url = $this->_getUrl($link);
					if ($link && !$this->_isVisited($url) && $this->_hasLevel($url)) {
						$links[] = $this->_getUrl($link);
					}
				}
			}
		}
		else {
//			$this->_reporter->report($url.': Already visited, returning');
		}
		return $links;
	}

	protected function _hasLevel($url) {
		if (!isset(Site_Structure::$indexLevels)) {
			return true;
		}
		if ($this->_indexLevel >= count(Site_Structure::$indexLevels)) {
			return true;
		}
		for ($i = 0; $i <= $this->_indexLevel; $i++) {
			if (!isset(Site_Structure::$indexLevels[$i])) {
//				$this->_reporter->report($url.': below indexLevel, returning');
				return false;
			}
			foreach (Site_Structure::$indexLevels[$i] as $str) {
				if (strpos($url,$str) !== false) {
					return true;
				}
			}
		}
//		$this->_reporter->report($url.': below indexLevel, returning');
		return false;
	}

	protected function _visit($url) {
		$this->_client->setUri($url);
		try {
			$response = $this->_client->request();
		}
		catch (Exception $e) {
			return array();
		}
		$docContents = $response->getBody();
		$domDoc = new DOMDocument;
		$domDoc->loadHTML($docContents);
		$searchDoc = Zend_Search_Lucene_Document_Html::loadHTML($docContents);
		if (isset($this->_currentLanguage)) {
			if (!isset($this->_visited[$this->_currentLanguage])) {
				$this->_visited[$this->_currentLanguage] = array();
			}
			$this->_visited[$this->_currentLanguage][] = $url;
		}
		else {
			$this->_visited[] = $url;
		}
		$this->_index($searchDoc,$domDoc,$this->_getLink($url));
		return $searchDoc->getLinks();
	}
	
	protected function _index($searchDoc,$domDoc,$url) {
		$this->_reporter->report($url.': Indexing ...');
		ob_start();
		$this->_search->index($searchDoc,$domDoc,$url);
		$this->_reporter->report(ob_get_clean());
	}

	protected function _isVisited($url) {
		if (isset($this->_currentLanguage)) {
			return isset($this->_visited[$this->_currentLanguage]) ? in_array($url,$this->_visited[$this->_currentLanguage]) : false;
		}
		return in_array($url,$this->_visited);
	}

	protected function _countVisited() {
		if (isset($this->_currentLanguage)) {
			return isset($this->_visited[$this->_currentLanguage]) ? count($this->_visited[$this->_currentLanguage]) : 0;
		}
		return count($this->_visited);
	}

	protected function _spider($links) {
		if (empty($links)) {
			return;
		}
		foreach ($links as $link) {
			if ($this->_countVisited() < $this->_maxLinks) {
				$nextLinks = $this->_getLinks($link);
				$this->_spider($nextLinks);
			}
			else {
				$this->_reporter->report('Reached max number of links ('.$this->_maxLinks.'), returning');
			}
		}
	}

}

?>