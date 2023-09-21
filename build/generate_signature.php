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

if ($_SERVER['argc'] != 3)
{
	echo "Please specify the secret key and filename for which the signature should be created, e.g. generate_signature.php mySecretSecret path/to/file\n";
	exit(1);
}

$secret_key = base64_decode($_SERVER['argv'][1]);
$file_path = $_SERVER['argv'][2];

if (!extension_loaded('sodium'))
{
	die('Required sodium extension not loaded');
}

if (!file_exists($file_path))
{
	die('File does not exist');
}

$hash = hash_file('sha384', $file_path, true);
try
{
	$signature = sodium_crypto_sign_detached($hash, $secret_key);
}
catch (SodiumException $e)
{
	$keypair = sodium_crypto_sign_keypair();

	$secret_key = base64_encode(sodium_crypto_sign_secretkey($keypair));
	$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));
	echo 'Unable to create the signature: ' . $e->getMessage() . "\n";
	echo "Maybe use these keys:\nPublic key: {$public_key}\nSecret key: {$secret_key}\n";
	die();
}

$signature = base64_encode($signature);

file_put_contents($file_path . '.sig', $signature);
