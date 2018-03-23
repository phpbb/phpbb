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

namespace phpbb\routing\resources_locator;

use phpbb\filesystem\filesystem_interface;

/**
 * Locates the yaml routing resources taking update directories into consideration
 */
class installer_resources_locator implements resources_locator_interface
{
	/**
	 * phpBB's filesystem handler
	 *
	 * @var filesystem_interface
	 */
	protected $filesystem;

	/**
	 * phpBB root path
	 *
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Name of the current environment
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * Construct method
	 *
	 * @param filesystem_interface	$filesystem			phpBB's filesystem handler
	 * @param string				$phpbb_root_path	phpBB root path
	 * @param string				$environment		Name of the current environment
	 */
	public function __construct(filesystem_interface $filesystem, $phpbb_root_path, $environment)
	{
		$this->filesystem			= $filesystem;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->environment			= $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function locate_resources()
	{
		if ($this->filesystem->exists($this->phpbb_root_path . 'install/update/new/config'))
		{
			$resources = array(
				array('install/update/new/config/' . $this->environment . '/routing/environment.yml', 'yaml')
			);
		}
		else
		{
			$resources = array(
				array('config/' . $this->environment . '/routing/environment.yml', 'yaml')
			);
		}

		return $resources;
	}
}
