#!/usr/bin/env php
<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
* USAGE:
* 
*/

use phpseclib\Crypt\RSA;

if ($_SERVER['argc'] < 3)
{
	die("Please specify a path to private key and a path to the package.");
}

$rsa = new RSA();

// load private key (first argument)
$rsa->loadKey(file_get_contents($_SERVER['argv'][1]), RSA::PRIVATE_FORMAT_PKCS1);
$rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);

// create a hash of a new package (provided as second argument)
$hash = sha1_file($_SERVER['argv'][2]);

// sign hash of the file and output the signature
echo $rsa->sign($hash);
