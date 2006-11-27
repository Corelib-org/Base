<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * RFC 4122 A Universally Unique IDentifier (UUID) URN Namespace Generator
 * 
 * <i>No Description</i>
 * 
 * LICENSE: This source file is subject to version 1.0 of the 
 * Bravura Distribution license that is available through the 
 * world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 * If you did not receive a copy of the Bravura License and are
 * unable to obtain it through the web, please send a note to 
 * license@bravura.dk so we can mail you a copy immediately.
 * 
 * @author Steffen Sørensen <steffen@bravura.dk>
 * @copyright Copyright (c) 2006 Bravura ApS
 * @license http://www.bravura.dk/licence/corelib_1_0/
 * @package corelib
 * @subpackage Base
 * @link http://www.bravura.dk/
 * @link http://www.ietf.org/rfc/rfc4122.txt
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//************************ Table Of Content ***********************//
//*****************************************************************//
//**                                                        Line **//
//**    1. RFC4122 Class ...................................     **//
//**        1. generate Method .............................     **//
//**                                                             **//
//*****************************************************************//


//*****************************************************************//
//************************ RFC 4122 Class *************************//
//*****************************************************************//
/**
 * RFC 4122 A Universally Unique IDentifier (UUID) URN Namespace Generator Class
 * 
 * @package corelib
 * @subpackage Base
 */
class RFC4122 {
	/**
	 * RFC 4122 A Universally Unique IDentifier (UUID) URN Namespace Generator
	 * 
	 * Generate a Universally Unique IDentifier (UUID) and return it
	 * 
	 * <code>
	 * <?php
	 * // Provides: f485b374-8e40-46d0-8c67-0da94fb18dd1
	 * $token = RFC4122::generate();
	 * ?>
	 * </code>
	 * This will create a UUID 128 bits long, and can guarantee uniqueness 
	 * across space and time
	 * 
	 * @return string Universally Unique IDentifier (UUID)
	 */
	public static function generate(){
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		               mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		               mt_rand( 0, 0x0fff ) | 0x4000,
		               mt_rand( 0, 0x3fff ) | 0x8000,
		               mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ));
	}
}

echo RFC4122::generate();
?>