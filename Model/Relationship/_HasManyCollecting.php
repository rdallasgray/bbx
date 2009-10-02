<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_HasManyCollecting extends Bbx_Model_Relationship_Abstract {
		
	protected function _findCollection(Zend_Db_Table_Row $parentRow) {
				
		$collected_data = array();
		
		foreach ($this->_collecting as $relationship) {
			foreach (Bbx_Model::load(get_class($this->_parentModel))->find($parentRow->id)->$relationship->getRowset() as $row) {
				$collected_data[] = $row->toArray();
			}
		}
		
		$config = array(
            'table'    => $this->_model()->getTable(),
            'data'     => $collected_data,
            'readOnly' => false,
            'stored'   => true
		);

		$rowset = new Zend_Db_Table_Rowset($config);

		$this->_collections[$parentRow->id] = new Bbx_Model_Collection($rowset,$this);
	}

}

?>