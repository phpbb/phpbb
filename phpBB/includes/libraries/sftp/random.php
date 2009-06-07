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
* Copyright 2007-2009 TerraFrost <terrafrost@php.net>
* Copyright 2009+ phpBB
*
* @package sftp
* @author  TerraFrost <terrafrost@php.net>
*/

/**
 * Generate a random value.  Feel free to replace this function with a cryptographically secure PRNG.
 *
 * @param optional Integer $min
 * @param optional Integer $max
 * @param optional String $randomness_path
 * @return Integer
 * @access public
 */
function crypt_random($min = 0, $max = 0x7FFFFFFF, $randomness_path = '/dev/urandom')
{
	static $seeded = false;

	if (!$seeded)
	{
		$seeded = true;
		if (file_exists($randomness_path))
		{
			$fp = fopen($randomness_path, 'r');
			$temp = unpack('Nint', fread($fp, 4));
			mt_srand($temp['int']);
			fclose($fp);
		}
		else
		{
			list($sec, $usec) = explode(' ', microtime());
			mt_srand((float) $sec + ((float) $usec * 100000));
		}
	}

	return mt_rand($min, $max);
}
?>