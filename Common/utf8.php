<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



function mb_exists() {
	if (Zend_Registry::isRegistered('MB_EXISTS')) {
		return Zend_Registry::get('MB_EXISTS');
	}
	else {
		if (function_exists('mb_substr')) {
			Zend_Registry::set('MB_EXISTS',true);
			return true;
		}
		Zend_Registry::set('MB_EXISTS',false);
		return false;
	}
}

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

function utf8_substr($str,$start) {
	if (mb_exists()) {
		if(func_num_args() >= 3) {
			$end = func_get_arg(2);
			return mb_substr($str,$start,$end);
		}
		else {
			return mb_substr($str,$start);
		}
	}
	preg_match_all("/./u",$str,$ar);

	if(func_num_args() >= 3) {
		$end = func_get_arg(2);
		return join("",array_slice($ar[0],$start,$end));
	}
	else {
		return join("",array_slice($ar[0],$start));
	}
}

function utf8_strlen($str) {
	if (mb_exists()) {
		return mb_strlen($str);
	}
	return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
}

function utf8_ucfirst($str){
	$str=utf8_decode($str);
	$str=ucfirst($str);
	$str=utf8_encode($str);
	return $str;
}

function utf8_ucwords($str){
	if (mb_exists()) {
		return mb_convert_case($str,MB_CASE_TITLE);
	}
	$str=utf8_decode($str);
	$str=ucwords($str);
	$str=utf8_encode($str);
	return $str;
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

function utf8_strtoupper($str){
	if (mb_exists()) {
		return mb_strtoupper($str);
	}
	$string=utf8_decode($str);
	$string=strtoupper($str);
	$string=utf8_encode($str);
	return $string;
}

function utf8_strtolower($str){
	if (mb_exists()) {
		return mb_strtolower($str);
	}
	$string=utf8_decode($str);
	$string=strtolower($str);
	$string=utf8_encode($str);
	return $string;
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

function utf8_excerpt($text,$chars,$balance_tags = true) {
	if (utf8_strlen($text) > $chars) {
		$text = $text." ";
		$text = utf8_substr($text,0,$chars);
		$text = utf8_substr($text,0,utf8_strrpos($text,' '));
		$text = $text." ...";
	}
	if ($balance_tags) {
		$text = balance_tags($text);
	}
	return $text;
}

function utf8_strrpos($text,$str) {
	if (mb_exists()) {
		return mb_strrpos($text,$str);
	}
	$text = utf8_decode($text);
	return strrpos($text,$str);
}


?>