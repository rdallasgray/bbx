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

function encode_email($email) {
    $encoded = bin2hex($email);
    $encoded = chunk_split($encoded, 2, '%');
    $encoded = '%' . substr($encoded, 0, strlen($encoded) - 1);
    return $encoded;
}

function bbx_escape($string) {
	return htmlspecialchars((string)$string,ENT_COMPAT,Bbx_Config::get()->locale->charset);
}

function bbx_smart_escape($string) {
	$string = str_replace('&','&#38;',$string);
	$string = SmartyPants($string,2);
	return $string;
}

function h($string) {
	return bbx_escape((string)$string);
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