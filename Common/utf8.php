<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/


function utf8_str_replace($search,$replace,$subject) {
	$subject = utf8_decode($subject);
	if (!is_array($search)) {
		$search = array($search);
	}
	if (!is_array($replace)) {
		$replace = array($replace);
	}
	@array_walk($search,'utf8_decode');
	@array_walk($replace,'utf8_decode');
	return utf8_encode(str_replace($search,$replace,$subject));
	
}

function utf8_ucfirst($str){
    $str[0] = mb_strtoupper($str[0]); 
	return $str;
}

function utf8_ucwords($str){
	return mb_convert_case($str,MB_CASE_TITLE);
}

function utf8_titlecase($str){
	$exceptions = array('and','is','the','was','at');
	
	$str = utf8_decode($str);
    $words = split(" ", $str);
    $newwords = array();

    foreach ($words as $word) {
        if (!in_array($word, $exceptions)) {
            $word = strtolower($word);
            $word = ucfirst($word);
        }
        array_push($newwords, $word);
    }

    $str = ucfirst(join(" ", $newwords));
	$str = utf8_encode($str);
	return $str;
}

function utf8_str_pad($input,$pad_length,$pad_string = '',$pad_type = 1,$charset = "UTF-8") {
	$str = '';
	$length = $pad_length - utf8_strlen($input);

	if( $length > 0) {
		if($pad_type == STR_PAD_RIGHT) {
			$str = $input.str_repeat($pad_string,$length);
		}
		else if ($pad_type == STR_PAD_LEFT) {
			$str = str_repeat($pad_string,$length).$input;
		}
		else if ($pad_type == STR_PAD_BOTH) {
			$str = str_repeat($pad_string,floor($length/2));
			$str .= $input;
			$str .= str_repeat($pad_string,ceil($length/2));
		}
		else {
			$str = str_repeat($pad_string,$length).$input;
		}
	}
	else {
		$str = $input;
	}
	return $str;
}

function utf8_excerpt($text, $chars, $balance_tags = true, $middle = false) {
	$len = mb_strlen($text);
	if ($len > $chars) {
		$text = $text . " ";
		if ($middle) {
			$start = (int) ($len - $chars) / 2;
		}
		else {
			$start = 0;
		}
		$text = mb_substr($text, $start, $chars);
		$last_space = mb_strrpos($text, ' ');
		if ($last_space !== false) {
			$text = mb_substr($text, 0, $last_space);
		}
		if ($middle) {
			$first_space = mb_strpos($text, ' ');
			if ($first_space !== false) {
				$text = mb_substr($text, $first_space);
			}
		}
    	if ($balance_tags) {
    		$text = balance_tags($text);
    	}
    	$text = preg_replace("/^[^a-zA-Z0-9]+/", "", $text);
    	return $text . ' …';
	}
	return $text;
}

?>