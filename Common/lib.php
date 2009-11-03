<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



require('utf8.php');
require('smartypants.php');
require('markdown.php');

function encode_email($email) {
    $encoded = bin2hex($email);
    $encoded = chunk_split($encoded, 2, '%');
    $encoded = '%' . substr($encoded, 0, strlen($encoded) - 1);
    return $encoded;
}

function bbx_escape($string) {
	return htmlentities($string,ENT_COMPAT,Bbx_Config::get()->env->locale->charset);
}

function h($string) {
	return bbx_escape($string);
}

function hh($string) {
	return bbx_text_format($string);
}

function bbx_smart_escape($string) {
	return bbx_smart_format(bbx_escape($string));
}

function bbx_text_escape($string) {
	return bbx_smart_format(bbx_text_format($string));
}

function bbx_smart_format($string) {
	$string = str_replace('&','&#38;',$string);
	$string = SmartyPants($string,2);
	return $string;
}

function bbx_text_format($string) {
	return bbx_smart_format(MarkDown($string));
}

function entity($ent) {
	return html_entity_decode($ent,ENT_COMPAT,$cfg->locale->charset);
}

function sort_by_prop($array,$prop,$direction = SORT_ASC) {
	$sortArr = array();
	foreach ($array as $key=>$obj) {
		$sortArr[$key] = $obj->$prop;
	}
	array_multisort($sortArr,$direction,$array);
	return $array;
}

function sort_by_method($array,$method,$direction = SORT_ASC) {
	$sortArr = array();
	foreach ($array as $key=>$obj) {
		$sortArr[$key] = $obj->$method();
	}
	array_multisort($sortArr,$direction,$array);
	return $array;
}

function genpassword($length = 8){
	$password = '';
	srand((double)microtime()*1000000);

	$vowels = array("a", "e", "i", "o", "u");
	$cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr",
	"cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");

	$num_vowels = count($vowels);
	$num_cons = count($cons);

	for($i = 0; $i < $length; $i++){
		$password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)];
	}

	return substr($password, 0, $length);
}

function balance_tags($text) {
    $tagstack = array();
    $stacksize = 0;
    $tagqueue = '';
    $newtext = '';
 
    # b2 bug fix for comments - in case you REALLY meant to type '< !--'
    $text = str_replace('< !--', '<    !--', $text);
 
    # b2 bug fix for LOVE <3 (and other situations with '<' before a number)
    $text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);
 
    while( preg_match("/<(\/?\w*)\s*([^>]*)>/", $text, $regex) )
    {
        $newtext = $newtext . $tagqueue;
 
        $i = strpos($text,$regex[0]);
        $l = strlen($tagqueue) + strlen($regex[0]);
 
        // clear the shifter
        $tagqueue = '';
 
        // Pop or Push
        if( substr($regex[1],0,1) == '/' )
        { // End Tag
            $tag = strtolower(substr($regex[1],1));
 
            // if too many closing tags
            if($stacksize <= 0) {
                $tag = '';
                //or close to be safe $tag = '/' . $tag;
            }
            // if stacktop value = tag close value then pop
            else if ($tagstack[$stacksize - 1] == $tag) { // found closing tag
                $tag = '</' . $tag . '>'; // Close Tag
                // Pop
                array_pop ($tagstack);
                $stacksize--;
            } else { // closing tag not at top, search for it
                for ($j=$stacksize-1;$j>=0;$j--) {
                    if ($tagstack[$j] == $tag) {
                    // add tag to tagqueue
                        for ($k=$stacksize-1;$k>=$j;$k--){
                            $tagqueue .= '</' . array_pop ($tagstack) . '>';
                            $stacksize--;
                        }
                        break;
                    }
                }
                $tag = '';
            }
        }
        else
        { // Begin Tag
            $tag = strtolower($regex[1]);
 
            // Tag Cleaning
 
            // Push if not img or br or hr
            if($tag != 'br' && $tag != 'img' && $tag != 'hr') {
                $stacksize = array_push ($tagstack, $tag);
            }
 
            // Attributes
            // $attributes = $regex[2];
            $attributes = $regex[2];
            if($attributes) {
                $attributes = ' '.$attributes;
            }
 
            $tag = '<'.$tag.$attributes.'>';
        }
 
        $newtext .= substr($text,0,$i) . $tag;
        $text = substr($text,$i+$l);
    }
 
    // Clear Tag Queue
    $newtext = $newtext . $tagqueue;
 
    // Add Remaining text
    $newtext .= $text;
 
    // Empty Stack
    while($x = array_pop($tagstack)) {
        $newtext = $newtext . '</' . $x . '>'; // Add remaining tags to close
    }
 
    # b2 fix for the bug with HTML comments
    $newtext = str_replace( '< !--', '<'.'!--', $newtext ); // the concatenation is needed to work around some strange parse error in PHP 4.3.1
    $newtext = str_replace( '<    !--', '< !--', $newtext );
 
    return $newtext;
}


?>