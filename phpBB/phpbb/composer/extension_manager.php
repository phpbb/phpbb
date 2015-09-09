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

namespace phpbb\composer;

use phpbb\composer\exception\runtime_exception;
use phpbb\extension\manager as ext_manager;

/**
 * Class to safely manage extensions through composer.
 */
class extension_manager extends manager
{
	/**
	 * @var \phpbb\extension\manager
	 */
	protected $extension_manager;

	/**
	 * @param installer		$installer			Installer object
	 * @param ext_manager	$extension_manager	phpBB extension manager
	 * @param string		$package_type		Composer type of managed packages
	 * @param string		$exception_prefix	Exception prefix to use
	 */
	public function __construct(installer $installer, ext_manager $extension_manager, $package_type, $exception_prefix)
	{
		$this->extension_manager = $extension_manager;

		parent::__construct($installer, $package_type, $exception_prefix);
	}

	/**
	 * {@inheritdoc}
	 */
	public function install(array $packages)
	{
		$packages = $this->normalize_version($packages);

		$already_managed = array_intersect(array_keys($this->get_managed_packages()), array_keys($packages));
		if (count($already_managed) !== 0)
		{
			throw new runtime_exception($this->exception_prefix, 'ALREADY_INSTALLED', [implode('|', $already_managed)]);
		}

		$installed_manually = array_intersect(array_keys($this->extension_manager->all_available()), array_keys($packages));
		if (count($installed_manually) !== 0)
		{
			throw new runtime_exception($this->exception_prefix, 'ALREADY_INSTALLED_MANUALLY', [implode('|', array_keys($installed_manually))]);
		}

		$this->do_install($packages);
	}
}
