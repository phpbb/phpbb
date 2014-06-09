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

class phpbb_mock_filesystem_extension_manager extends phpbb_mock_extension_manager
{
	public function __construct($phpbb_root_path)
	{
		$extensions = array();
		$iterator = new DirectoryIterator($phpbb_root_path . 'ext/');
		foreach ($iterator as $fileinfo)
		{
			if ($fileinfo->isDir() && substr($fileinfo->getFilename(), 0, 1) != '.')
			{
				$name = $fileinfo->getFilename();
				$extension = array(
					'ext_name' => $name,
					'ext_active' => true,
					'ext_path' => 'ext/' . $name . '/',
				);
				$extensions[$name] = $extension;
			}
		}
		ksort($extensions);
		parent::__construct($phpbb_root_path, $extensions);
	}
}
