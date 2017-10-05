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

use phpbb\filesystem\filesystem_interface;
use Symfony\Component\Config\FileLocator;

class file_locator extends FileLocator
{
	public function __construct(filesystem_interface $filesystem, $paths = [])
	{
		$paths = (array) $paths;
		$absolute_paths = [];

		foreach ($paths as $path)
		{
			$absolute_paths[] = $filesystem->realpath($path);
		}

		parent::__construct($absolute_paths);
	}
}
