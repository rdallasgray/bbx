<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Search {
	
	protected $_index;
	
	public static function reset() {
		$moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		Zend_Search_Lucene::create(APPLICATION_PATH . '/modules/' . $moduleName . '/search/index');
	}
	
	public function __construct() {
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(
			new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
		$moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		$indexPath = APPLICATION_PATH . '/modules/' . $moduleName . '/search/index';
		if (file_exists($indexPath)) {
			$this->_index = Zend_Search_Lucene::open($indexPath);
		}
		else {
			$this->_index = Zend_Search_Lucene::create($indexPath);
		}
	}
	
	public static function find($query) {
		$search = new Bbx_Search;
		return $search->_doFind($query);
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

	protected function _doFind($query) {
		$this->_query = $query;
		return $this->_prepareResults();
	}
	
	public function indexDoc($doc, $url) {
		$fields = array();
		$fields['url'] = urlencode($url);
		$fields['docId'] = md5($fields['url']);
		if ($oldDoc = $this->_documentExists($fields['docId'])) {
			if (md5($doc->body) == $oldDoc->checkSum) {
				Bbx_Log::debug('No change in document');
				return;
			}
			else {
				$this->_deleteDocument($oldDoc->id);
			}
		}
		$html = $doc->getHTML();
		$contentStart = strpos($html, '<section id="main-content">');
		$contentEnd = strpos($html, '</section>') - $contentStart;
		if ($contentStart !== false) {
			$html = substr($html, $contentStart, $contentEnd);
		}
		$html = str_replace(array('</', '<br'), array(' </', ' <br'), $html);
		$html = strip_tags($html);
		$contentSample = preg_replace('/[\t\s\n]+/', ' ', $html);
		$fields['contentSample'] = utf8_excerpt(trim($contentSample), 128, false);
		$this->_indexDocument($doc, $fields);
	}
	
	protected function _indexDocument($doc, $fields) {		
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('checkSum',md5($doc->body)));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('lastIndexed',time()));
		foreach ($fields as $name => $value) {
			$doc->addField(Zend_Search_Lucene_Field::Text($name, $value));
		}
		$this->_index->addDocument($doc);
		Bbx_Log::debug('Added ' . urldecode($doc->url) . ' to index');
	}
	
	protected function _deleteDocument($id) {
		Bbx_Log::debug('Deleting document id '.$id);
		$this->_index->delete($id);
	}
	
	protected function _documentExists($docId) {
		@$hits = $this->_index->find('docId:' . $docId);
		foreach ($hits as $hit) {
			if ($hit->docId == $docId) {
				Bbx_Log::debug('Found existing document: ' . urldecode($hit->url));
				return $hit;
			}
		}
		return false;
	}
	
	protected function _prepareResults() {
		$results = $this->_index->find($this->_query);
		$preparedResults = array();
		foreach ($results as $result) {
			$url = urldecode($result->url);
			if (!array_key_exists($url, $preparedResults)) {
				$preparedResults[$url] = array(
					'title'=>$result->title,
					'contentSample'=>$result->contentSample,
					'score'=>$result->score,
					'docId'=>$result->docId,
				);
			}
		}
		return $preparedResults;
	}
	
}

?>