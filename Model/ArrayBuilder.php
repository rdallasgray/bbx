<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_ArrayBuilder {

	public static function getArray(Bbx_Model $model,$options) {

		$array = array();

		$cols = $model->columns();
		
		foreach ($cols as &$col) {
			if (!isset($options['only']) || $options['only'] === $col) {
				$array[$col] = $model->$col;
			}
		}
		if (isset($options['include'])) {
			$rd = $model->getRelationshipData();

			foreach ($options['include'] as $key => $options) {
				if (array_key_exists('belongsTo',$rd[$key])) {
					$foreignKey = $key.'_id';
					$array[$foreignKey] = $model->$key->toArray($options);
				}
				else {
					if ($model->$key instanceof Bbx_Model || $model->$key instanceof Bbx_Model_Collection) {
						$array[$key] = $model->$key->toArray($options);
					}
				}
			}

			$new_a = array();

			foreach ($array as $key=>$value) {

				if (@strpos($key,'_id',(strlen($key)-3)) !== false) {
					$key = substr($key,0,-3);
				}

				$new_a[$key] = $value;
			}
			$array = $new_a;
		}

		if (isset($options['methods'])) {
			foreach($options['methods'] as $method) {
				$array[$method] = $model->$method();
			}
		}

 		return $array;
	}

}

?>