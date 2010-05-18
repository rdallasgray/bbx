<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Search_Spider {

	protected $_visited = array();
	protected $_maxLinks = 1000;
	protected $_client;
	protected $_search;

	public function __construct() {
	}
	
	protected function _search() {
		if (!isset($this->_search)) {
			$this->_search = new Bbx_Search;
		}
		return $this->_search;
	}

	protected function _sanitizeUrl($url) {
		if ($url == '') {
			return false;
		}
		$schemeCheck = explode(':', $url);
		if (count($schemeCheck) > 1) {
			return false; 
		}
		if (strpos($url, '//') !== false) {
			return false;
		}
		
		$link = explode('?', $url);
		
		$link = explode('#', $link[0]);
		
		$extCheck = explode('.', $link[0]);
		$forbidden = array('jpg', 'gif', 'png', 'mp3', 'pdf');
		if (in_array(end($extCheck), $forbidden)) {
			return false;
		}

		return $link[0];
	}

	protected function _isVisited($url) {
		return in_array($url, $this->_visited);
	}
	
	protected function _getAbsoluteUrl($url) {
		return 'http://' . $_SERVER['HTTP_HOST'] . $url;
	}

	public function start($url) {
		if (empty($url)) {
			return;
		}
		Bbx_Log::debug('Starting Spider with url ' . $url);
		$this->_spider($url);
		$this->_search()->optimize();
		Bbx_Log::debug('Spider done');
	}
	
	protected function _spider($url) {
		if ($url = $this->_sanitizeUrl($url)) {
			if (!$this->_isVisited($url)) {
				$doc = Zend_Search_Lucene_Document_Html::loadHTMLFile($this->_getAbsoluteUrl($url), false, 'utf-8');
				$this->_search()->indexDoc($doc, $url);
				$this->_visited[] = $url;
				$links = array_diff($doc->getLinks(), $this->_visited);
				foreach ($links as $link) {
					if (count($this->_visited) < $this->_maxLinks) {
						Bbx_Log::debug('Spidering url ' . $link);
						$this->_spider($link);
					}
					else {
						Bbx_Log::debug('Reached max number of links (' . $this->_maxLinks . '), returning');
					}
				}
			}
		}
	}
	
}

?>