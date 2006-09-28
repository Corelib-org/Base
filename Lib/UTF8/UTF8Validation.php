<?php
/**
 *	Corelib UTF8 helper class
 * 
 * Tools for locating / replacing bad bytes in UTF-8 strings
 * The Original Code is Mozilla Communicator client code.
 * The Initial Developer of the Original Code is
 * Netscape Communications Corporation.
 * Portions created by the Initial Developer are Copyright (C) 1998
 * the Initial Developer. All Rights Reserved.
 * Ported to PHP by Henri Sivonen (http://hsivonen.iki.fi)
 * Slight modifications to fit with Bravura CoreLib library by Anders Møller
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
 * 				LGPL (http://www.gnu.org/copyleft/lesser.html)
 * 
 *	@see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUTF8ToUnicode.cpp
 *  @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUnicodeToUTF8.cpp
 *  @see http://hsivonen.iki.fi/php-utf8/
 *	@author Anders Møller <anders@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.gnu.org/copyleft/lesser.html
 *	@package corelib
 *	@subpackage UTF8
 *	@link http://www.bravura.dk/
 */

class UTF8Validation extends UTF8Core {

	/**
	* Tests a string as to whether it's valid UTF-8 and supported by the
	* Unicode standard
	* Note: this function has been modified to simple return true or false
	* @author <hsivonen@iki.fi>
	* @param string UTF-8 encoded string
	* @return boolean true if valid
	* @see http://hsivonen.iki.fi/php-utf8/
	*/
	public static function is_valid($str) {
	    
	    $mState = 0;     // cached expected number of octets after the current octet
	                     // until the beginning of the next UTF8 character sequence
	    $mUcs4  = 0;     // cached Unicode character
	    $mBytes = 1;     // cached expected number of octets in the current sequence
	    
	    $len = strlen($str);
	    
	    for($i = 0; $i < $len; $i++) {
	        
	        $in = ord($str{$i});
	        
	        if ( $mState == 0) {
	            
	            // When mState is zero we expect either a US-ASCII character or a
	            // multi-octet sequence.
	            if (0 == (0x80 & ($in))) {
	                // US-ASCII, pass straight through.
	                $mBytes = 1;
	                
	            } else if (0xC0 == (0xE0 & ($in))) {
	                // First octet of 2 octet sequence
	                $mUcs4 = ($in);
	                $mUcs4 = ($mUcs4 & 0x1F) << 6;
	                $mState = 1;
	                $mBytes = 2;
	                
	            } else if (0xE0 == (0xF0 & ($in))) {
	                // First octet of 3 octet sequence
	                $mUcs4 = ($in);
	                $mUcs4 = ($mUcs4 & 0x0F) << 12;
	                $mState = 2;
	                $mBytes = 3;
	                
	            } else if (0xF0 == (0xF8 & ($in))) {
	                // First octet of 4 octet sequence
	                $mUcs4 = ($in);
	                $mUcs4 = ($mUcs4 & 0x07) << 18;
	                $mState = 3;
	                $mBytes = 4;
	                
	            } else if (0xF8 == (0xFC & ($in))) {
	                /* First octet of 5 octet sequence.
	                *
	                * This is illegal because the encoded codepoint must be either
	                * (a) not the shortest form or
	                * (b) outside the Unicode range of 0-0x10FFFF.
	                * Rather than trying to resynchronize, we will carry on until the end
	                * of the sequence and let the later error handling code catch it.
	                */
	                $mUcs4 = ($in);
	                $mUcs4 = ($mUcs4 & 0x03) << 24;
	                $mState = 4;
	                $mBytes = 5;
	                
	            } else if (0xFC == (0xFE & ($in))) {
	                // First octet of 6 octet sequence, see comments for 5 octet sequence.
	                $mUcs4 = ($in);
	                $mUcs4 = ($mUcs4 & 1) << 30;
	                $mState = 5;
	                $mBytes = 6;
	                
	            } else {
	                /* Current octet is neither in the US-ASCII range nor a legal first
	                 * octet of a multi-octet sequence.
	                 */
	                return FALSE;
	                
	            }
	        
	        } else {
	            
	            // When mState is non-zero, we expect a continuation of the multi-octet
	            // sequence
	            if (0x80 == (0xC0 & ($in))) {
	                
	                // Legal continuation.
	                $shift = ($mState - 1) * 6;
	                $tmp = $in;
	                $tmp = ($tmp & 0x0000003F) << $shift;
	                $mUcs4 |= $tmp;
	            
	                /**
	                * End of the multi-octet sequence. mUcs4 now contains the final
	                * Unicode codepoint to be output
	                */
	                if (0 == --$mState) {
	                    
	                    /*
	                    * Check for illegal sequences and codepoints.
	                    */
	                    // From Unicode 3.1, non-shortest form is illegal
	                    if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
	                        ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
	                        ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
	                        (4 < $mBytes) ||
	                        // From Unicode 3.2, surrogate characters are illegal
	                        (($mUcs4 & 0xFFFFF800) == 0xD800) ||
	                        // Codepoints outside the Unicode range are illegal
	                        ($mUcs4 > 0x10FFFF)) {
	                        
	                        return FALSE;
	                        
	                    }
	                    
	                    //initialize UTF8 cache
	                    $mState = 0;
	                    $mUcs4  = 0;
	                    $mBytes = 1;
	                }
	            
	            } else {
	                /**
	                *((0xC0 & (*in) != 0x80) && (mState != 0))
	                * Incomplete multi-octet sequence.
	                */
	                
	                return FALSE;
	            }
	        }
	    }
	    return TRUE;
	}

	/**
	* Tests whether a string complies as UTF-8. This will be much
	* faster than utf8_is_valid but will pass five and six octet
	* UTF-8 sequences, which are not supported by Unicode and
	* so cannot be displayed correctly in a browser. In other words
	* it is not as strict as utf8_is_valid but it's faster. If you use
	* is to validate user input, you place yourself at the risk that
	* attackers will be able to inject 5 and 6 byte sequences (which
	* may or may not be a significant risk, depending on what you are
	* are doing)
	* @see http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
	* @param string UTF-8 string to check
	* @return boolean TRUE if string is valid UTF-8
	*/
	public static function compliant($str) {
	    if ( strlen($str) == 0 ) {
	        return TRUE;
	    }
	    // If even just the first character can be matched, when the /u
	    // modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
	    // invalid, nothing at all will match, even if the string contains
	    // some valid sequences
	    return (preg_match('/^.{1}/us',$str,$ar) == 1);
	}

	
	/**
	* Tests whether a string contains only 7bit ASCII bytes.
	* You might use this to conditionally check whether a string
	* needs handling as UTF-8 or not, potentially offering performance
	* benefits by using the native PHP equivalent if it's just ASCII e.g.;
	*
	* <code>
	* <?php
	* if ( utf8_is_ascii($someString) ) {
	*     // It's just ASCII - use the native PHP version
	*     $someString = strtolower($someString);
	* } else {
	*     $someString = UTF8::strtolower($someString);
	* }
	* ?>
	* </code>
	* 
	* @param string
	* @return boolean TRUE if it's all ASCII
	*/
	public static function is_ascii($str) {
	    if ( strlen($str) > 0 ) {
	        // Search for any bytes which are outside the ASCII range...
	        return (preg_match('/[^\x00-\x7F]/',$str) !== 1);
	    }
	    return FALSE;
	}
	
	/**
	* Tests whether a string contains only 7bit ASCII bytes with device
	* control codes omitted. The device control codes can be found on the
	* second table here: http://www.w3schools.com/tags/ref_ascii.asp
	* 
	* @param string
	* @return boolean TRUE if it's all ASCII without device control codes
	*/
	public static function is_ascii_ctrl($str) {
	    if ( strlen($str) > 0 ) {
	        // Search for any bytes which are outside the ASCII range,
	        // or are device control codes
	        return (preg_match('/[^\x09\x0A\x0D\x20-\x7E]/',$str) !== 1);
	    }
	    return FALSE;
	}

	/**
	* Strip out all non-7bit ASCII bytes
	* If you need to transmit a string to system which you know can only
	* support 7bit ASCII, you could use this function.
	* @param string
	* @return string with non ASCII bytes removed
	*/
	public static function strip_non_ascii($str) {
	    ob_start();
	    while ( preg_match(
	        '/^([\x00-\x7F]+)|([^\x00-\x7F]+)/S',
	            $str, $matches) ) {
	        if ( !isset($matches[2]) ) {
	            echo $matches[0];
	        }
	        $str = substr($str, strlen($matches[0]));
	    }
	    $result = ob_get_contents();
	    ob_end_clean();
	    return $result;
	}

	/**
	* Strip out all non 7bit ASCII bytes and ASCII device control codes.
	* For a list of ASCII device control codes see the 2nd table here:
	* http://www.w3schools.com/tags/ref_ascii.asp
	* 
	* @param string
	* @return boolean TRUE if it's all ASCII
	*/
	public static function strip_non_ascii_ctrl($str) {
	    ob_start();
	    while ( preg_match(
	        '/^([\x09\x0A\x0D\x20-\x7E]+)|([^\x09\x0A\x0D\x20-\x7E]+)/S',
	            $str, $matches) ) {
	        if ( !isset($matches[2]) ) {
	            echo $matches[0];
	        }
	        $str = substr($str, strlen($matches[0]));
	    }
	    $result = ob_get_contents();
	    ob_end_clean();
	    return $result;
	}	
	
	
}