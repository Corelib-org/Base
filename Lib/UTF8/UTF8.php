<?php
/**
 *	Corelib UTF8 helper class
 *
 *	LICENSE:
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
 * 				LGPL (http://www.gnu.org/copyleft/lesser.html
 * 
 *	@author Anders Møller <anders@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.gnu.org/copyleft/lesser.html
 *	@package corelib
 *	@subpackage UTF8
 *	@link http://www.bravura.dk/
 */


class UTF8 extends UTF8Core {

	
	/**
	* UTF-8 aware replacement for explode
	*
	* @TODO support third limit arg
	* @param string
	* @param string
	* @return array of strings
	* @see http://www.php.net/explode;
	*/
	public static function explode($sep, $str) {
	  if ( $sep == '' ) {
	  	throw new UTF8Exception('explode: Empty delimiter');
	    return false;
	  }
	  return preg_split('!'.preg_quote($sep,'!').'!u',$str);
	}
		
	/**
	* UTF-8 aware alternative to ucfirst
	* Make a string's first character uppercase
	* Note: requires utf8_strtoupper
	* @param string
	* @return string with first character as upper case (if applicable)
	* @see http://www.php.net/ucfirst
	*/
	public static function ucfirst($str){
	    switch (UTF8::strlen($str) ) {
	        case 0:
	            return '';
	        break;
	        case 1:
	            return utf8_strtoupper($str);
	        break;
	        default:
	            preg_match('/^(.{1})(.*)$/us', $str, $matches);
	            return UTF8::strtoupper($matches[1]).$matches[2];
	        break;
	    }
	}

	/**
	* UTF-8 aware replacement for ltrim()
	* Note: you only need to use this if you are supplying the charlist
	* optional arg and it contains UTF-8 characters. Otherwise ltrim will
	* work normally on a UTF-8 string
	* @author Andreas Gohr <andi@splitbrain.org>
	* @see http://www.php.net/ltrim
	* @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
	* @return string
	*/
	public static function ltrim( $str, $charlist = FALSE ) {
	    if($charlist === FALSE) return ltrim($str);
	    //quote charlist for use in a characterclass
	    $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);
	    return preg_replace('/^['.$charlist.']+/u','',$str);
	}

	/**
	* UTF-8 aware replacement for rtrim()
	* Note: you only need to use this if you are supplying the charlist
	* optional arg and it contains UTF-8 characters. Otherwise rtrim will
	* work normally on a UTF-8 string
	* @author Andreas Gohr <andi@splitbrain.org>
	* @see http://www.php.net/rtrim
	* @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
	* @return string
	*/
	public static function rtrim( $str, $charlist = FALSE ) {
	    if($charlist === FALSE) return rtrim($str);
	    //quote charlist for use in a characterclass
	    $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);
	    return preg_replace('/['.$charlist.']+$/u','',$str);
	}

	/**
	* UTF-8 aware replacement for trim()
	* Note: you only need to use this if you are supplying the charlist
	* optional arg and it contains UTF-8 characters. Otherwise trim will
	* work normally on a UTF-8 string
	* @author Andreas Gohr <andi@splitbrain.org>
	* @see http://www.php.net/trim
	* @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
	* @return string
	*/
	public static function trim($str, $charlist = FALSE ) {
	    if($charlist === FALSE) return trim($str);
	    return UTF8::ltrim(UTF8::rtrim($str, $charlist), $charlist);
	}	
	
	/**
	* UTF-8 aware substr_replace.
	* @see http://www.php.net/substr_replace
	*/
	public static function substr_replace($str, $repl, $start , $length = NULL ) {
	    preg_match_all('/./us', $str, $ar);
	    preg_match_all('/./us', $repl, $rar);
	    if( $length === NULL ) {
	        $length = UTF8::strlen($str);
	    }
	    array_splice( $ar[0], $start, $length, $rar[0] );
	    return join('',$ar[0]);
	}
	
	
	/**
	* UTF-8 aware alternative to strrev
	* Reverse a string
	* @param string UTF-8 encoded
	* @return string characters in string reverses
	* @see http://www.php.net/strrev
	*/
	public static function strrev($str){
	    preg_match_all('/./us', $str, $ar);
	    return join('',array_reverse($ar[0]));
	}
	
	/**
	* UTF-8 aware alternative to stristr
	* Find first occurrence of a string using case insensitive comparison
	* @param string
	* @param string
	* @return int
	* @see http://www.php.net/stristr
	* @see UTF8::strtolower
	*/
	public static function stristr($str, $search) {
	    if ( strlen($search) == 0 ) {
	        return $str;
	    }
	    $lstr = UTF8::strtolower($str);
	    $lsearch = UTF8::strtolower($search);
	    preg_match('/^(.*)'.preg_quote($lsearch).'/Us',$lstr, $matches);
	    
	    if ( count($matches) == 2 ) {
	        return substr($str, strlen($matches[1]));
	    }
	    return FALSE;
	}
	
	/**
	* UTF-8 aware alternative to strspn
	* Find length of initial segment matching mask
	* @param string
	* @return int
	* @see http://www.php.net/strspn
	*/
	public static function strspn($str, $mask, $start = NULL, $length = NULL) {
	    $mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);
	    if ( $start !== NULL || $length !== NULL ) {
	        $str = UTF8::substr($str, $start, $length);
	    }
	    preg_match('/^['.$mask.']+/u',$str, $matches);
	    
	    if ( isset($matches[0]) ) {
	        return UTF8::strlen($matches[0]);
	    }
	    return 0;
	}
		
	/**
	* UTF-8 aware alternative to strcspn
	* Find length of initial segment not matching mask
	* @param string
	* @return int
	* @see http://www.php.net/strcspn
	* @see UTF8::strlen
	*/
	public static function strcspn($str, $mask, $start = NULL, $length = NULL) {
	    if ( empty($mask) || strlen($mask) == 0 ) {
	        return NULL;
	    }
	    $mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);
	    if ( $start !== NULL || $length !== NULL ) {
	        $str = UTF8::substr($str, $start, $length);
	    }
	    preg_match('/^[^'.$mask.']+/u',$str, $matches);
	    if ( isset($matches[0]) ) {
	        return UTF8::strlen($matches[0]);
	    }
	    return 0;
	}
	
	/**
	* UTF-8 aware alternative to strcasecmp
	* A case insensivite string comparison
	* @param string
	* @param string
	* @return int
	* @see http://www.php.net/strcasecmp
	* @see UTF8::strtolower
	*/
	public static function strcasecmp($strX, $strY) {
	    $strX = UTF8::strtolower($strX);
	    $strY = UTF8::strtolower($strY);
	    return strcmp($strX, $strY);
	}
	
	/**
	* UTF-8 aware alternative to str_split
	* Convert a string to an array
	* @param string UTF-8 encoded
	* @param int number to characters to split string by
	* @return string characters in string reverses
	* @see http://www.php.net/str_split
	* @see UTF8Core::strlen
	*/
	public static function str_split($str, $split_len = 1) {
	    if ( !preg_match('/^[0-9]+$/',$split_len) || $split_len < 1 ) {
	        return FALSE;
	    }
	    $len = UTF8::strlen($str);
	    if ( $len <= $split_len ) {
	        return array($str);
	    }
	    preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);
	    return $ar[0];
	}
	
	/**
	* UTF-8 aware alternative to str_ireplace
	* Case-insensitive version of str_replace
	* Note: it's not fast and gets slower if $search / $replace is array
	* Notes: it's based on the assumption that the lower and uppercase
	* versions of a UTF-8 character will have the same length in bytes
	* which is currently true given the hash table to strtolower
	* @param string
	* @return string
	* @see http://www.php.net/str_ireplace
	* @see UTF8Core::strtolower
	*/
	public static function ireplace($search, $replace, $str, $count = NULL){
	    if ( !is_array($search) ) {
	        $slen = strlen($search);
	        if ( $slen == 0 ) {
	            return $str;
	        }
	        
	        $search = UTF8::strtolower($search);
	        
	        $search = preg_quote($search);
	        $lstr = UTF8::strtolower($str);
	        $i = 0;
	        $matched = 0;
	        while ( preg_match('/(.*)'.$search.'/Us',$lstr, $matches) ) {
	            if ( $i === $count ) {
	                break;
	            }
	            $mlen = strlen($matches[0]);
	            $lstr = substr($lstr, $mlen);
	            $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
	                    $matched += $mlen;
	            $i++;
	        }
	        return $str;
	    } else {
	        foreach ( array_keys($search) as $k ) {
	            if ( is_array($replace) ) {
	                if ( array_key_exists($k,$replace) ) {
	                    $str = UTF8::ireplace($search[$k], $replace[$k], $str, $count);
	                } else {
						$str = UTF8::ireplace($search[$k], '', $str, $count);
	                }
	            } else {
	                $str = UTF8::ireplace($search[$k], $replace, $str, $count);
	            }
	        }
	        return $str;
	    }
	}
	
	
	
	
	
	
	
	
	/**
	* Shortscuts to support functions
	**/
	public static function is_valid($str) {
		return UTF8Validation::is_valid($str);
	}
	public static function compliant($str) {
		return UTF8Validation::compliant($str);
	}
	public static function is_ascii($str) {
		return UTF8Validation::is_ascii($str);
	}
	public static function is_ascii_ctrl($str) {
		return UTF8Validation::is_ascii_ctrl($str);
	}
	public static function strip_non_ascii($str) {
		return UTF8Validation::strip_non_ascii($str);
	}				
	public static function strip_non_ascii_ctrl($str) {
		return UTF8Validation::strip_non_ascii_ctrl($str);
	}
	public static function bad_find($str) {
		return UTF8Bad::bad_find($str);
	}
	public static function bad_findall($str) {
		return UTF8Bad::bad_findall($str);
	}
	public static function bad_strip($str) {
		return UTF8Bad::bad_strip($str);
	}
	public static function bad_replace($str, $replace = '?') {
		return UTF8Bad::bad_replace($str,$replace);
	}
	public static function bad_identify($str, &$i) {
		return UTF8Bad::bad_find($str,$i);
	}
	public static function bad_explain($code) {
		return UTF8Bad::bad_explain($code);
	}						
}
?>