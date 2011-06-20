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
	protected $_maxLinks = 100000;
	protected $_indexed = 0;
	protected $_host;
	protected $_report;
	protected $_client;
	protected $_search;

	public function __construct() {
		Zend_Search_Lucene_Document_Html::setExcludeNoFollowLinks(true);
		$this->_client = new Zend_Http_Client();
		$this->_client->setConfig(array(
			'timeout' 	=> 10,
			'keepalive'	=> true
		));
	}
	
	protected function _search() {
		if (!isset($this->_search)) {
			$this->_search = new Bbx_Search;
		}
		return $this->_search;
	}

	protected function _sanitizeUrl($url) {
		if (empty($url)) {
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
		return array_key_exists($url, $this->_visited);
	}
	
	protected function _getAbsoluteUrl($url) {
		return 'http://' . $this->_host . $url;
	}
	
	public function start($url, $host = null, $reset = false) {

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

		Bbx_Log::write('Starting Spider with url ' . $url, null, Bbx_Search::LOG);
		$this->_report = Bbx_Model::load('SearchIndexReport')->create();
		$this->_report->start();
		if ($reset) {
			Bbx_Log::write('Resetting search index', null, Bbx_Search::LOG);
			$this->_search()->reset();
		}
		$this->_spider($url);
		Bbx_Log::write('Optimizing index', null, Bbx_Search::LOG);
		$this->_search()->optimize();
		Bbx_Log::write('Completing index', null, Bbx_Search::LOG);
		$this->_report->complete($this->_indexed);
		Bbx_Log::write('Spider done', null, Bbx_Search::LOG);
	}
	
	protected function _spider($url) {
		$queue = array();
		$visited = array();
		array_push($queue, $url);
		while(!empty($queue)) {
			$doc = null;
			$url = array_shift($queue);
			if (($url = $this->_sanitizeUrl($url))) {
				if (!in_array($url, $visited)) {
					$visited[] = $url;
					Bbx_Log::write('Spidering url ' . $url, null, Bbx_Search::LOG);
					$cachePath = APPLICATION_PATH . '/../www/cached' . $url . '.html';
					Bbx_Log::write('Checking cache: ' . $cachePath);
					if (file_exists($cachePath)) {
						Bbx_Log::write('Found file in cache', null, Bbx_Search::LOG);
						try {
							$doc = Zend_Search_Lucene_Document_Html::loadHTMLFile($cachePath, false, 'utf-8');
						}
						catch (Exception $e) {
							Bbx_Log::write('Unable to open file: ' . $cachePath, null, Bbx_Search::LOG);
						}
					}
					else {
						$this->_client->setUri($this->_getAbsoluteUrl($url));
						try {
							$response = $this->_client->request();
							$status = $response->getStatus();
							Bbx_Log::write('Client response code ' . $status, null, Bbx_Search::LOG);
							if ($status == '200') {
								$data = $response->getBody();
								$doc = Zend_Search_Lucene_Document_Html::loadHTML($data, false, 'utf-8');
							}
						}
						catch (Exception $e) {
							Bbx_Log::write('Request failed: ' . $e->getMessage(), null, Bbx_Search::LOG);
						}
					}
					if ($doc !== null) {
						$this->_search()->indexDoc($doc, $url);
						$this->_indexed++;
						$links = array_diff($doc->getLinks(), $this->_visited);
						if (count($visited) < $this->_maxLinks) {
							$queue = array_merge($queue, $links);
						}
						else {
							Bbx_Log::write('Reached max number of links (' . $this->_maxLinks . '), exiting', null, Bbx_Search::LOG);
							exit();
						}
					}
				}
			}
		}
	}
	
}

?>