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
 */

if ($_SERVER['argc'] != 4)
{
	echo "Please specify the public key, filename for which the signature should be check, and the signature file, e.g. verify_signature.php superPublicKey path/to/file path/to/signature\n";
	exit(1);
}

$public_key = base64_decode($_SERVER['argv'][1]);
$file_path = $_SERVER['argv'][2];
$signature_path = $_SERVER['argv'][3];

if (!extension_loaded('sodium'))
{
	die('Required sodium extension not loaded');
}

if (!file_exists($file_path))
{
	die('File does not exist');
}

if (!file_exists($signature_path))
{
	die('Signature file does not exist');
}

$hash = hash_file('sha384', $file_path, true);
$signature = base64_decode(file_get_contents($signature_path));

try
{
	if (sodium_crypto_sign_verify_detached($signature, $hash, $public_key))
	{
		echo 'Signature is valid!';
	}
	else
	{
		echo 'Signature is not valid!';
	}
} catch (SodiumException $e)
{
	die('Unable to verify the signature: ' . $e->getMessage() . "\n");
}
