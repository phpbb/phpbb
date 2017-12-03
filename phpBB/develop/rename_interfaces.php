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

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

$code_dir = realpath(__DIR__ . '/../');
$test_dir = realpath(__DIR__ . '/../../tests/');
$iterator = new \AppendIterator();
$iterator->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($code_dir)));
$iterator->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($test_dir)));

$map = array(
	'phpbb\request\request_interface' => 'phpbb\request\request_interface',
	'phpbb\auth\provider\provider_interface' => 'phpbb\auth\provider\provider_interface',
	'phpbb\avatar\driver\driver_interface' => 'phpbb\avatar\driver\driver_interface',
	'phpbb\cache\driver\driver_interface' => 'phpbb\cache\driver\driver_interface',
	'phpbb\db\migration\tool\tool_interface' => 'phpbb\db\migration\tool\tool_interface',
	'phpbb\extension\extension_interface' => 'phpbb\extension\extension_interface',
	'phpbb\groupposition\groupposition_interface' => 'phpbb\groupposition\groupposition_interface',
	'phpbb\log\log_interface' => 'phpbb\log\log_interface',
	'phpbb\notification\method\method_interface' => 'phpbb\notification\method\method_interface',
	'phpbb\notification\type\type_interface' => 'phpbb\notification\type\type_interface',
	'phpbb\request\request_interface' => 'phpbb\request\request_interface',
	'phpbb\tree\tree_interface' => 'phpbb\tree\tree_interface',
);

foreach ($iterator as $file)
{
	if ($file->getExtension() == 'php')
	{
		$code = file_get_contents($file->getPathname());

		foreach ($map as $orig => $new)
		{
			$code = preg_replace("#([^a-z0-9_\$])$orig([^a-z0-9_])#i", '\\1' . $new . '\\2', $code);
		}
		file_put_contents($file->getPathname(), $code);
	}
}
