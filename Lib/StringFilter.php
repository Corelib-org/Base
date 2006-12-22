<?php
/**
 *	StringFilter class
 *
 *  <i>No Description</i>
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
 *	@subpackage Base
 *	@link http://www.bravura.dk/
 */
class StringFilter {
    /**
     * Options for isHostname() that specify which types of hostnames
     * to allow.
     *
     * HOST_ALLOW_DNS:   Allows Internet domain names (e.g.,
     *                   example.com).
     * HOST_ALLOW_IP:    Allows IP addresses.
     * HOST_ALLOW_LOCAL: Allows local network names (e.g., localhost,
     *                   www.localdomain) and Internet domain names.
     * HOST_ALLOW_ALL:   Allows all of the above types of hostnames.
     */
    const HOST_ALLOW_DNS   = 1;
    const HOST_ALLOW_IP    = 2;
    const HOST_ALLOW_LOCAL = 4;
    const HOST_ALLOW_ALL   = 7;

    public static function getAlpha($value){
        return preg_replace('/[^[:alpha:]]/', '', $value);
    }
    
    public static function getAlnum($value) {
        return preg_replace('/[^[:alnum:]]/', '', $value);
    }
    
    public static function getDigits($value) {
        return preg_replace('/[^\d]/', '', $value);
    }
    
    public static function getInt($value) {
        return (int) $value;
    }
       
    public static function getDir($value) {
        return dirname($value);
    }
    
    public static function getPath($value) {
        return realpath($value);
    }

    public static function isAlnum($value) {
        return ctype_alnum($value);
    }

    public static function isAlpha($value) {
        return ctype_alpha($value);
    }
    
    public static function isDigits($value) {
        return ctype_digit($value);
    }
    
    public static function isInt($value) {
        $locale = localeconv();

        $value = str_replace($locale['decimal_point'], '.', $value);
        $value = str_replace($locale['thousands_sep'], '', $value);

        return (strval(intval($value)) == $value);
    }
    public static function isFloat($value) {
        $locale = localeconv();

        $value = str_replace($locale['decimal_point'], '.', $value);
        $value = str_replace($locale['thousands_sep'], '', $value);

        return (strval(floatval($value)) == $value);
    }
       
    public static function isHex($value) {
        return ctype_xdigit($value);
    }    
    
    public static function isBetween($value, $min, $max, $inc = true) {
        if ($value > $min && $value < $max) {
            return true;
        }
        if ($value >= $min && $value <= $max && $inc) {
            return true;
        }
        return false;
    }

    public static function isGreaterThan($value, $min) {
        return ($value > $min);
    }    
    
    public static function isLessThan($value, $max) {
        return ($value < $max);
    }    

    /**
     * Returns $value if it is a valid date, FALSE otherwise. The
     * date is required to be in ISO 8601 format (YYYY-MM-DD). 
     */
    public static function isDate($value) {
        list($year, $month, $day) = sscanf($value, '%d-%d-%d');
        return checkdate($month, $day, $year);
    }

    /**
     * Returns $value if it is a valid date, FALSE otherwise. The
     * date is required to be in UNIX timestamp. 
     */
    public static function isDateTimestamp($value) {
    	return self::isDate(date(DATE_ISO8601,$value));
    }    
    
    public static function isIp($value) {
        return (bool) ip2long($value);
    }
    
    public static function isEmail($value) {
        /**
         * @todo RFC 2822 (http://www.ietf.org/rfc/rfc2822.txt)
         */
        return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $value));
    }
    
    public static function isRegex($value, $pattern = NULL) {
        return (bool) preg_match($pattern, $value);
    }

    public static function isUri($value) {
		return(preg_match("/^(http|https|ftp):\/\/[a-z0-9\/:_\-_\.\?\$,~=#&%\+]+$/i",$value));
    }    
    
    /**
     * Returns value if it is a valid hostname, FALSE otherwise.
     * Depending upon the value of $allow, Internet domain names, IP
     * addresses, and/or local network names are considered valid.
     * The default is HOST_ALLOW_ALL, which considers all of the
     * above to be valid.
     *
     * @param mixed $value
     * @param integer $allow bitfield for HOST_ALLOW_DNS, HOST_ALLOW_IP, HOST_ALLOW_LOCAL
     * @throws StringFilterException
     * @return mixed
     */
    public static function isHostname($value, $allow = self::HOST_ALLOW_ALL)
    {
        if (!is_numeric($allow) || !is_int($allow)) {
            throw new StringFilterException('Illegal value for $allow; expected an integer');
        }

        if ($allow < self::HOST_ALLOW_DNS || self::HOST_ALLOW_ALL < $allow) {
            throw new StringFilterException('Illegal value for $allow; expected integer between ' .
                                            self::HOST_ALLOW_DNS . ' and ' . self::HOST_ALLOW_ALL);
        }

        // determine whether the input is formed as an IP address
        $status = self::isIp($value);

        // if the input looks like an IP address
        if ($status) {
            // if IP addresses are not allowed, then fail validation
            if (($allow & self::HOST_ALLOW_IP) == 0) {
                return FALSE;
            }

            // IP passed validation
            return TRUE;
        }

        // check input against domain name schema
        $status = @preg_match('/^(?:[^\W_](?:[^\W_]|-){0,61}[^\W_]\.)+[a-zA-Z]{2,6}\.?$/', $value);
        if ($status === false) {
            throw new StringFilterException('Internal error: DNS validation failed');
        }

        // if the input passes as an Internet domain name, and domain names are allowed, then the hostname
        // passes validation
        if ($status == 1 && ($allow & self::HOST_ALLOW_DNS) != 0) {
            return TRUE;
        }

        // if local network names are not allowed, then fail validation
        if (($allow & self::HOST_ALLOW_LOCAL) == 0) {
            return FALSE;
        }

        // check input against local network name schema; last chance to pass validation
        $status = @preg_match('/^(?:[^\W_](?:[^\W_]|-){0,61}[^\W_]\.)*(?:[^\W_](?:[^\W_]|-){0,61}[^\W_])\.?$/',
                              $value);
        if ($status === FALSE) {
            throw new StringFilterException('Internal error: local network name validation failed');
        }

        if ($status == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    public static function isOneOf($value, $allowed = NULL) {
        if(is_array($allowed)) {
        	return in_array($value, $allowed);
        } else {
        	$allowed = split($allowed,',');
        	return in_array($value, $allowed);
        }
    }
    
	/**
	 *	Check if string contains http:// or https://
	 *
	 *	@param string $str subject, string to test whether or not it contains http:// or https://
	 *	@return boolean returns true if $str contains http:// or https://, else return false
	 */
	public static function ContainsHTTP($str){
		return (preg_match('(^(http:\/\/))', $str) || preg_match('(^(https:\/\/))', $str));
	}    
}



class StringFilterException extends BaseException {
}