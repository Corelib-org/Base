<?php
/**
 *	Corelib UTF8 helper class
 *
 *	<i>
 * 		This source file is divide work!
 * 		Parts of the code in this class come from other places, under different licenses.
 * 		Main author's:
 * 			Andreas Gohr <andi@splitbrain.org>
 * 				LGPL (http://www.gnu.org/copyleft/lesser.html)
 * 			Henri Sivonen (http://hsivonen.iki.fi/php-utf8/)
 * 				MPL/LGPL (He ported a Unicode / UTF-8 converter from the Mozilla codebase to PHP, which is re-used in pats in this class.)
 * 			harryf (http://phputf8.sourceforge.net/)
 * 				LGPL
 * 			Anders Møller <anders@bravura.dk>
 * 				LGPL (http://www.gnu.org/copyleft/lesser.html)
 * </i>
 *
 *	LICENSE: This source file is subject to version 1.0 or any later version of the 
 *	Bravura Corelib license that is available through the 
 *	world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 *	If you did not receive a copy of the Bravura Corelib License and are
 *	unable to obtain it through the web, please send a note to 
 *	license@bravura.dk so we can mail you a copy immediately.
 *
 *	@author Anders Møller <anders@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.bravura.dk/licence/corelib_1_0/
 *	@package corelib
 *	@subpackage UTF8
 *	@link http://www.bravura.dk/
 */

if(!defined('UTF8_MBSTRING')){
  if(function_exists('mb_substr') && !defined('UTF8_NOMBSTRING')){
  	define('UTF8_MBSTRING',1);
    mb_internal_encoding('UTF-8');
  }else{
    define('UTF8_MBSTRING',0);
  }
}

class UTF8Core {
	
	/**
	* Wrapper round mb_strlen
	* Assumes you have mb_internal_encoding to UTF-8 already
	* Note: this function does not count bad bytes in the string - these
	* are simply ignored
	* @param string UTF-8 string
	* @return int number of UTF-8 characters in string
	*/
	public static function strlen($str){
		if(UTF8_MBSTRING){
			return mb_strlen($str);
		} else {
			 throw new UTF8Exception('Internal error: mbstring extension not loaded');
		}
	}
	
	/**
	* Assumes mbstring internal encoding is set to UTF-8
	* Wrapper around mb_strpos
	* Find position of first occurrence of a string
	* @param string haystack
	* @param string needle (you should validate this with utf8_is_valid)
	* @param integer offset in characters (from left)
	* @return mixed integer position or FALSE on failure
	*/
	public static function strpos($str, $search, $offset = FALSE){
		if(UTF8_MBSTRING){
		    if ( $offset === FALSE ) {
		        return mb_strpos($str, $search);
		    } else {
		        return mb_strpos($str, $search, $offset);
		    }
		} else {
			 throw new UTF8Exception('Internal error: mbstring extension not loaded');
		}	    
	}
	
	/**
	* Assumes mbstring internal encoding is set to UTF-8
	* Wrapper around mb_strrpos
	* Find position of last occurrence of a char in a string
	* @param string haystack
	* @param string needle (you should validate this with utf8_is_valid)
	* @param integer (optional) offset (from left)
	* @return mixed integer position or FALSE on failure
	*/
	public static function strrpos($str, $search, $offset = FALSE){
		if(UTF8_MBSTRING){
		    if ( $offset === FALSE ) {
		        # Emulate behaviour of strrpos rather than raising warning
		        if ( empty($str) ) {
		            return FALSE;
		        }
		        return mb_strrpos($str, $search);
		    } else {
		        if ( !is_int($offset) ) {
		             throw new UTF8Exception('strrpos expects parameter 3 to be long');
		            return FALSE;
		        }
		        
		        $str = mb_substr($str, $offset);
		        
		        if ( FALSE !== ( $pos = mb_strrpos($str, $search) ) ) {
		            return $pos + $offset;
		        }
		        
		        return FALSE;
		    }
		} else {
			 throw new UTF8Exception('Internal error: mbstring extension not loaded');
		}		    
	}


	/**
	* Assumes mbstring internal encoding is set to UTF-8
	* Wrapper around mb_substr
	* Return part of a string given character offset (and optionally length)
	* @param string
	* @param integer number of UTF-8 characters offset (from left)
	* @param integer (optional) length in UTF-8 characters from offset
	* @return mixed string or FALSE if failure
	*/
	public static function substr($str, $offset, $length = FALSE){
		if(UTF8_MBSTRING){
		    if ( $length === FALSE ) {
		        return mb_substr($str, $offset);
		    } else {
		        return mb_substr($str, $offset, $length);
		    }
		} else {
			 throw new UTF8Exception('Internal error: mbstring extension not loaded');
		}	    
	}

	/**
	* Assumes mbstring internal encoding is set to UTF-8
	* Wrapper around mb_strtolower
	* Make a string lowercase
	* Note: The concept of a characters "case" only exists is some alphabets
	* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	* not exist in the Chinese alphabet, for example. See Unicode Standard
	* Annex #21: Case Mappings
	* @param string
	* @return mixed either string in lowercase or FALSE is UTF-8 invalid
	*/
	public static function strtolower($str){
		if(UTF8_MBSTRING){
			return mb_strtolower($str);
		} else {
			 throw new UTF8Exception('Internal error: mbstring extension not loaded');
		}	    
	}

	/**
	* Assumes mbstring internal encoding is set to UTF-8
	* Wrapper around mb_strtoupper
	* Make a string uppercase
	* Note: The concept of a characters "case" only exists is some alphabets
	* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	* not exist in the Chinese alphabet, for example. See Unicode Standard
	* Annex #21: Case Mappings
	* @param string
	* @return mixed either string in lowercase or FALSE is UTF-8 invalid
	*/
	public static function strtoupper($str){
		if(UTF8_MBSTRING){
			 return mb_strtoupper($str);
		} else {
			 throw new UTF8Exception('Internal error: mbstring extension not loaded');
		}    
	}
}
?>