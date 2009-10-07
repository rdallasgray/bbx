<?php

class Bbx_Date {
	
	public static function fixFormat($str,$format = "ISO_8601") {
		
		$parts = explode('-',$str);
		$parts = array_pad($parts,3,"00");
		
		if (preg_match('/^[1-9][0-9][0-9][0-9]$/',$parts[0]) === 0) {
			$parts[0] = "0000";
		}
		if (preg_match('/^[0-9][0-9]$/',$parts[1]) === 0) {
			$parts[1] = "00";
		}
		if (preg_match('/^[0-9][0-9]$/',$parts[2]) === 0) {
			$parts[2] = "00";
		}
		
		return implode('-',$parts);
	}
	
}

?>