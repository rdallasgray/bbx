<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Search {
	
	protected $_front;
	protected $_index;
	protected $_fields = array();
	protected $_indexInterval = 21600;
	protected $_docId;
	protected $_document;
	protected $_query;
	protected $_indexLevels;
	
	public static function reset() {
		Zend_Search_Lucene::create(SITE_ROOT.'/search/index');
	}
	
	public static function find($query) {
		$search = new Bbx_Search;
		return $search->_doFind($query);
	}
	
	public function __construct() {
//		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());
		$this->_getIndex();
	}
		
	public function optimize() {
		$this->_index->optimize();
	}
	
	public function size() {
		return $this->_index->maxDoc();
	}
	
	public function count() {
		return $this->_index->numDocs();
	}
	
	protected function _report($str) {
		echo $str."\n";
	}

	protected function _doFind($query) {
		$this->_query = $query;
		return $this->_prepareResults();
	}
	
	public function index($newDoc,$domDoc,$url) {
		$this->_fields['url'] = urlencode($url);
		$docId = $this->_fields['url'];
		if (Zend_Registry::isRegistered('locale') && $lang = $domDoc->documentElement->getAttribute('lang')) {
			$this->_report('Setting language to '.$lang);
			$this->_fields['lang'] = $lang;
			$docId .= urlencode('?lang='.$this->_fields['lang']);
		}
		$this->_fields['docId'] = md5($docId);
		if ($oldDoc = $this->_checkDocumentExists($this->_fields['docId'])) {
			if (md5($newDoc->body) == $oldDoc->checkSum) {
				$this->_report('No change in document');
				return;
			}
			else {
				$this->_deleteDocument($oldDoc->id);
			}
		}
		$this->_indexDocument($newDoc,$domDoc);
	}
	
	protected function _indexDocument($searchDoc,$domDoc) {
		$contentSample = @$domDoc->getElementById('main')->getElementsByTagName('p')->item(0)->textContent;
		$contentHead1 = @$domDoc->getElementById('main')->getElementsByTagName('h1')->item(0)->textContent;
		$contentHead2 = @$domDoc->getElementById('main')->getElementsByTagName('h2')->item(0)->textContent;
		
		$searchDoc->addField(Zend_Search_Lucene_Field::UnIndexed('checkSum',md5($searchDoc->body)));
		$searchDoc->addField(Zend_Search_Lucene_Field::UnIndexed('lastIndexed',time()));
		$searchDoc->addField(Zend_Search_Lucene_Field::UnIndexed('contentSample',rawurlencode($contentSample)));
		$searchDoc->addField(Zend_Search_Lucene_Field::UnIndexed('contentHead1',rawurlencode($contentHead1)));
		$searchDoc->addField(Zend_Search_Lucene_Field::UnIndexed('contentHead2',rawurlencode($contentHead2)));
		foreach ($this->_fields as $field=>$value) {
			$searchDoc->addField(Zend_Search_Lucene_Field::Text($field,$value));
		}
		$this->_index->addDocument($searchDoc);
		$this->_report('Added '.urldecode($searchDoc->url).' to index');
	}
	
	protected function _deleteDocument($id) {
		$this->_report('Deleting document id '.$id);
		$this->_index->delete($id);
	}
	
	protected function _getIndex() {
		$index = SITE_ROOT.'/search/index';
		try {
			$this->_index = Zend_Search_Lucene::open($index);
		}
		catch (Exception $e) {
			$this->_index = Zend_Search_Lucene::create($index);
		}
	}
	
	protected function _checkDocumentExists($docId) {
		$hits = $this->_index->find('docId:'.$docId);
		foreach ($hits as $hit) {
			if ($hit->docId == $docId) {
				$this->_report('Found existing document: '.urldecode($this->_fields['url']));
				return $hit;
			}
		}
		return false;
	}
	
	protected function _prepareResults() {
		$results = $this->_index->find($this->_query);
		$preparedResults = array();
		foreach ($results as $result) {
			$url = urldecode(reset(explode(urlencode('?'),$result->url)));
			if (!isset($preparedResults[$url]) && $result->contentSample != '') {
				$preparedResults[$url] = array(
					'title'=>$result->title,
					'lang'=>$result->lang,
					'score'=>$result->score,
					'contentSample'=>rawurldecode($result->contentSample),
					'contentHead1'=>rawurldecode($result->contentHead1),
					'contentHead2'=>rawurldecode($result->contentHead2),
					'docId'=>$result->docId,
				);
			}
		}
		return $preparedResults;
	}
	
}

?>