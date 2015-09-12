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

use Composer\IO\IOInterface;
use phpbb\cache\driver\driver_interface;
use phpbb\composer\exception\managed_with_clean_error_exception;
use phpbb\composer\exception\managed_with_enable_error_exception;
use phpbb\composer\exception\managed_with_error_exception;
use phpbb\composer\exception\runtime_exception;
use phpbb\extension\manager as ext_manager;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem;

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
	 * @var \phpbb\filesystem\filesystem
	 */
	protected $filesystem;

	/**
	 * @param installer			$installer			Installer object
	 * @param driver_interface	$cache				Cache object
	 * @param ext_manager		$extension_manager	phpBB extension manager
	 * @param filesystem		$filesystem			Filesystem object
	 * @param string			$package_type		Composer type of managed packages
	 * @param string			$exception_prefix	Exception prefix to use
	 */
	public function __construct(installer $installer, driver_interface $cache, ext_manager $extension_manager, filesystem $filesystem, $package_type, $exception_prefix)
	{
		$this->extension_manager = $extension_manager;
		$this->filesystem = $filesystem;

		parent::__construct($installer, $cache, $package_type, $exception_prefix);
	}

	/**
	 * {@inheritdoc}
	 */
	public function install(array $packages, IOInterface $io = null)
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

		$this->do_install($packages, $io);
	}

	/**
	 * {@inheritdoc}
	 */
	public function start_managing($package)
	{
		if (!$this->extension_manager->is_available($package))
		{
			throw new runtime_exception($this->exception_prefix, 'NOT_INSTALLED', [$package]);
		}

		if ($this->is_managed($package))
		{
			throw new runtime_exception($this->exception_prefix, 'ALREADY_MANAGED', [$package]);
		}

		$enabled = false;
		if ($this->extension_manager->is_enabled($package))
		{
			$enabled = true;
			$this->extension_manager->disable($package);
		}

		$ext_path = $this->extension_manager->get_extension_path($package);
		$backup_path = rtrim($ext_path, '/') . '__backup__';

		try
		{
			$this->filesystem->rename($ext_path, $backup_path);
		}
		catch (filesystem_exception $e)
		{
			throw new runtime_exception($this->exception_prefix, 'CANNOT_MANAGE_FILESYSTEM_ERROR', [$package], $e);
		}

		try
		{
			$this->install((array) $package);
			$this->filesystem->remove($backup_path);
		}
		catch (runtime_exception $e)
		{
			$this->filesystem->rename($backup_path, $ext_path);
			throw new runtime_exception($this->exception_prefix, 'CANNOT_MANAGE_INSTALL_ERROR', [$package], $e);
		}
		catch (filesystem_exception $e)
		{
			throw new managed_with_clean_error_exception($this->exception_prefix, 'MANAGED_WITH_CLEAN_ERROR', [$package, $backup_path], $e);
		}

		if ($enabled)
		{
			try
			{
				$this->extension_manager->enable($package);
			}
			catch (\Exception $e)
			{
				throw new managed_with_enable_error_exception($this->exception_prefix, 'MANAGED_WITH_ENABLE_ERROR', [$package], $e);
			}
		}
	}
}
