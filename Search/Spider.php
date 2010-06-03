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
	protected $_indexed = 0;
	protected $_host;
	protected $_report;
	protected $_client;
	protected $_search;

	public function __construct() {
		Zend_Search_Lucene_Document_Html::setExcludeNoFollowLinks(true);
		$this->_client = new Zend_Http_Client;
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
		$forbidden = array('jpg', 'gif', 'png', 'mp3', 'pdf', 'swf');
		if (in_array(end($extCheck), $forbidden)) {
			return false;
		}
		if (!strpos($link[0], '/') === 1) {
			return false;
		}
		return $link[0];
	}

	protected function _isVisited($url) {
		return in_array($url, $this->_visited);
	}
	
	protected function _getAbsoluteUrl($url) {
		return 'http://' . $this->_host . $url;
	}

	public function start($url, $host = null) {

		if (empty($url)) {
			return;
		}
		
		if (array_key_exists('HTTP_HOST', $_SERVER)) {
			$this->_host = $_SERVER['HTTP_HOST'];
		}
		else if ($host !== null) {
			$this->_host = $host;
		}
		else {
			throw new Zend_Exception('No host set for Spider');
		}

		Bbx_Log::debug('Starting Spider with url ' . $url);
		$this->_report = Bbx_Model::load('SearchIndexReport')->create();
		$this->_report->start();
		$this->_spider($url);
		$this->_search()->optimize();
		$this->_report->complete($this->_indexed);
		Bbx_Log::debug('Spider done');
	}
	
	protected function _spider($url = '/', $reset = false) {
		if ($reset) {
			$this->_search->reset();
		}
		if ($url = $this->_sanitizeUrl($url)) {
			if (!$this->_isVisited($url)) {
				$this->_client->setUri($this->_getAbsoluteUrl($url));
				$response = $this->_client->request();
				$status = $response->getStatus();
				Bbx_Log::write('Client response code ' . $status);
				if ($status == '200') {
					$data = $response->getBody();
					$doc = Zend_Search_Lucene_Document_Html::loadHTML($data, false, 'utf-8');
					$this->_search()->indexDoc($doc, $url);
					$this->_indexed++;
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
				$this->_visited[] = $url;
			}
		}
	}
	
}

?>