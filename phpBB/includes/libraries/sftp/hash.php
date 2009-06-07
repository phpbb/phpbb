<?php
/**
*
* @package sftp
* @version $Id$
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Code from http://phpseclib.sourceforge.net/
*
* Modified by phpBB Group to meet our coding standards
* and being able to integrate into phpBB
*
* Pure-PHP implementations of keyed-hash message authentication codes (HMACs) and various cryptographic hashing functions.
*
* Copyright 2007-2009 TerraFrost <terrafrost@php.net>
* Copyright 2009+ phpBB
*
* @package sftp
* @author  TerraFrost <terrafrost@php.net>
*/

/**#@+
 * @access private
 * @see hash::__construct()
 */
/**
 * Toggles the internal implementation
 */
define('CRYPT_HASH_MODE_INTERNAL', 1);
/**
 * Toggles the mhash() implementation, which has been deprecated on PHP 5.3.0+.
 */
define('CRYPT_HASH_MODE_MHASH',	2);
/**
 * Toggles the hash() implementation, which works on PHP 5.1.2+.
 */
define('CRYPT_HASH_MODE_HASH',	 3);
/**#@-*/

/**
 * Pure-PHP implementations of keyed-hash message authentication codes (HMACs) and various cryptographic hashing functions.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.1.0
 * @access  public
 * @package hash
 */
class hash
{
	/**
	 * Byte-length of compression blocks / key (Internal HMAC)
	 *
	 * @see hash::set_algorithm()
	 * @var Integer
	 * @access private
	 */
	var $b;

	/**
	 * Byte-length of hash output (Internal HMAC)
	 *
	 * @see hash::set_hash()
	 * @var Integer
	 * @access private
	 */
	var $l;

	/**
	 * Hash Algorithm
	 *
	 * @see hash::set_hash()
	 * @var String
	 * @access private
	 */
	var $hash;

	/**
	 * Key
	 *
	 * @see hash::setKey()
	 * @var String
	 * @access private
	 */
	var $key = '';

	/**
	 * Outer XOR (Internal HMAC)
	 *
	 * @see hash::setKey()
	 * @var String
	 * @access private
	 */
	var $opad;

	/**
	 * Inner XOR (Internal HMAC)
	 *
	 * @see hash::setKey()
	 * @var String
	 * @access private
	 */
	var $ipad;

	/**
	 * Default Constructor.
	 *
	 * @param optional String $hash
	 * @return hash
	 * @access public
	 */
	function __construct($hash = 'sha1')
	{
		if ( !defined('CRYPT_HASH_MODE') )
		{
			switch (true)
			{
				case extension_loaded('hash'):
					define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_HASH);
					break;
				case extension_loaded('mhash'):
					define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_MHASH);
					break;
				default:
					define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_INTERNAL);
			}
		}

		$this->set_hash($hash);
	}

	/**
	 * Sets the key for HMACs
	 *
	 * Keys can be of any length.
	 *
	 * @access public
	 * @param String $key
	 */
	function set_key($key)
	{
		$this->key = $key;
	}

	/**
	 * Sets the hash function.
	 *
	 * @access public
	 * @param String $hash
	 */
	function set_hash($hash)
	{
		switch ($hash)
		{
			case 'md5-96':
			case 'sha1-96':
				$this->l = 12; // 96 / 8 = 12
				break;
			case 'md5':
				$this->l = 16;
				break;
			case 'sha1':
				$this->l = 20;
		}

		switch (CRYPT_HASH_MODE)
		{
			case CRYPT_HASH_MODE_MHASH:
				switch ($hash)
				{
					case 'md5':
					case 'md5-96':
						$this->hash = MHASH_MD5;
						break;
					case 'sha1':
					case 'sha1-96':
					default:
						$this->hash = MHASH_SHA1;
				}
				return;
			case CRYPT_HASH_MODE_HASH:
				switch ($hash)
				{
					case 'md5':
					case 'md5-96':
						$this->hash = 'md5';
						return;
					case 'sha1':
					case 'sha1-96':
					default:
						$this->hash = 'sha1';
				}
				return;
		}

		switch ($hash)
		{
			case 'md5':
			case 'md5-96':
				 $this->b = 64;
				 $this->hash = 'md5';
				 break;
			case 'sha1':
			case 'sha1-96':
			default:
				 $this->b = 64;
				 $this->hash = 'sha1';
		}

		$this->ipad = str_repeat(chr(0x36), $this->b);
		$this->opad = str_repeat(chr(0x5C), $this->b);
	}

	/**
	 * Compute the HMAC.
	 *
	 * @access public
	 * @param String $text
	 */
	function create($text)
	{
		if (!empty($this->key))
		{
			switch (CRYPT_HASH_MODE)
			{
				case CRYPT_HASH_MODE_MHASH:
					$output = mhash($this->hash, $text, $this->key);
					break;
				case CRYPT_HASH_MODE_HASH:
					$output = hash_hmac($this->hash, $text, $this->key, true);
					break;
				case CRYPT_HASH_MODE_INTERNAL:
					$hash = $this->hash;
					/* "Applications that use keys longer than B bytes will first hash the key using H and then use the
						resultant L byte string as the actual key to HMAC."

						-- http://tools.ietf.org/html/rfc2104#section-2 */
					$key = strlen($this->key) > $this->b ? $hash($this->key) : $this->key;

					$key	= str_pad($key, $this->b, chr(0));// step 1
					$temp   = $this->ipad ^ $key;			 // step 2
					$temp  .= $text;						  // step 3
					$temp   = pack('H*', $hash($temp));	   // step 4
					$output = $this->opad ^ $key;			 // step 5
					$output.= $temp;						  // step 6
					$output = pack('H*', $hash($output));	 // step 7
			}
		}
		else
		{
			switch (CRYPT_HASH_MODE)
			{
				case CRYPT_HASH_MODE_MHASH:
					$output = mhash($this->hash, $text);
					break;
				case CRYPT_HASH_MODE_MHASH:
					$output = hash($this->hash, $text, true);
					break;
				case CRYPT_HASH_MODE_INTERNAL:
					$hash = $this->hash;
					$output = pack('H*', $hash($output));
			}
		}

		return substr($output, 0, $this->l);
	}
}