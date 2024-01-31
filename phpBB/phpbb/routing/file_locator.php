<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license       GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\routing;

use Symfony\Component\Config\FileLocator;
use phpbb\filesystem\helper as filesystem_helper;

class file_locator extends FileLocator
{
	public function __construct($paths = [])
	{
		$paths = (array) $paths;
		$absolute_paths = [];

		foreach ($paths as $path)
		{
			$path = filesystem_helper::realpath($path);
			if ($path !== false)
			{
				$absolute_paths[] = $path;
			}
		}

		parent::__construct($absolute_paths);
	}
}
