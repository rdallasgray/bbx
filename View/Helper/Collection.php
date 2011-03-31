<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_View_Helper_Collection extends Bbx_View_Helper_Model {

	public function linkList() {
		$a = array();
		foreach ($this->_model as $model) {
			if ($model->isLinkable()) {
				$a[] = '<a href="' . $model->url() . '">' . (string) $model . '</a>';
			}
			else {
				$a[] = (string) $model;
			}
		}
		return implode(', ', $a);
	}
	
	public function flatList() {
		$a = $this->_model->toArray();
		return implode(', ', $a);
	}
	
	public function columnize($num_cols) {
		$count = count($this->_model);
		$cols = array();
		$items_remaining = $count;
		for ($i = ($num_cols - 1); $i > -1; $i--) {
			$items_todo = (int) ($items_remaining / ($i + 1));
			$cols[$i] = array();
			for ($j = ($items_remaining - $items_todo); $j < ($items_remaining); $j++) {
				$cols[$i][] = $this->_model[$j];
			}
			$items_remaining -= $items_todo;
		}
		return $cols;
	}
	
}

?>