<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Default_SearchIndexReport extends Bbx_Model {
	
	protected $_tableName = 'search_index_reports';
	protected $_defaultParams = array('order' => array('completed_at DESC'));
	
	public function start() {
		$this->started_at = date('Y-m-d H:i:s');
		$this->save();
	}
	
	public function complete($links_indexed = 0) {
		$this->links_indexed = $links_indexed;
		$this->completed_at = date('Y-m-d H:i:s');
		$this->save();
	}
	
	public function timeSinceLastIndex() {
		if($r = $this->_getMostRecentReport()) {
			$time = strtotime($r->completed_at);
		}
		else {
			$time = 0;
		}
		return time() - $time;
	}
	
	protected function _getMostRecentReport() {
		$all = Bbx_Model::load('SearchIndexReport')->findAll();
		if (count($all) > 0) {
			return $all->first();
		}
		return false;
	}

}

?>