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

/**
 * Class to manage packages through composer.
 */
class manager implements manager_interface
{
	/**
	 * @var installer Composer packages installer
	 */
	protected $installer;

	/**
	 * @var string Type of packages (phpbb-packages per example)
	 */
	protected $package_type;

	/**
	 * @var string Prefix used for the exception's language string
	 */
	protected $exception_prefix;

	/**
	 * @var array Caches the managed packages list (for the current type)
	 */
	private $managed_packages;

	/**
	 * @var array Caches the managed packages list (for all phpBB types)
	 */
	private $all_managed_packages;

	/**
	 * @var array Caches the available packages list
	 */
	private $available_packages;

	/**
	 * @param installer	$installer			Installer object
	 * @param string	$package_type		Composer type of managed packages
	 * @param string	$exception_prefix	Exception prefix to use
	 */
	public function __construct(installer $installer, $package_type, $exception_prefix)
	{
		$this->installer = $installer;
		$this->package_type = $package_type;
		$this->exception_prefix = $exception_prefix;
	}

	/**
	 * Installs (if necessary) a set of packages
	 *
	 * @param array $packages Packages to install.
	 * 		Each entry may be a name or an array associating a version constraint to a name
	 * @throws runtime_exception
	 */
	public function install(array $packages)
	{
		$packages = $this->normalize_version($packages);

		$already_managed = array_intersect(array_keys($this->get_managed_packages()), array_keys($packages));
		if (count($already_managed) !== 0)
		{
			throw new runtime_exception($this->exception_prefix, 'ALREADY_INSTALLED', [implode('|', $already_managed)]);
		}

		$this->do_install($packages);
	}

	/**
	 * Really install the packages.
	 *
	 * @param array $packages Packages to install.
	 */
	protected function do_install($packages)
	{
		$managed_packages = array_merge($this->get_all_managed_packages(), $packages);
		ksort($managed_packages);

		$this->installer->install($managed_packages, array_keys($packages));

		$this->managed_packages = null;
	}

	/**
	 * Updates or installs a set of packages
	 *
	 * @param array $packages Packages to update.
	 * 		Each entry may be a name or an array associating a version constraint to a name
	 * @throws runtime_exception
	 */
	public function update(array $packages)
	{
		$packages = $this->normalize_version($packages);

		// TODO: if the extension is already enabled, we should disabled and re-enable it
		$not_managed = array_diff_key($packages, $this->get_managed_packages());
		if (count($not_managed) !== 0)
		{
			throw new runtime_exception($this->exception_prefix, 'NOT_MANAGED', [implode('|', array_keys($not_managed))]);
		}

		$managed_packages = array_merge($this->get_all_managed_packages(), $packages);
		ksort($managed_packages);

		$this->installer->install($managed_packages, array_keys($packages));
	}

	/**
	 * Removes a set of packages
	 *
	 * @param array $packages Packages to remove.
	 * 		Each entry may be a name or an array associating a version constraint to a name
	 * @throws runtime_exception
	 */
	public function remove(array $packages)
	{
		$packages = $this->normalize_version($packages);

		// TODO: if the extension is already enabled, we should disabled (with an option for purge)
		$not_managed = array_diff_key($packages, $this->get_managed_packages());
		if (count($not_managed) !== 0)
		{
			throw new runtime_exception($this->exception_prefix, 'NOT_MANAGED', [implode('|', array_keys($not_managed))]);
		}

		$managed_packages = array_diff_key($this->get_all_managed_packages(), $packages);
		ksort($managed_packages);

		$this->installer->install($managed_packages, array_keys($packages));

		$this->managed_packages = null;
	}

	/**
	 * Tells whether or not a package is managed by Composer.
	 *
	 * @param string $package Package name
	 * @return bool
	 */
	public function is_managed($package)
	{
		return array_key_exists($package, $this->get_managed_packages());
	}

	/**
	 * Returns the list of managed packages for the current type
	 *
	 * @return array The managed packages associated to their version.
	 */
	public function get_managed_packages()
	{
		if ($this->managed_packages === null)
		{
			$this->managed_packages = $this->installer->get_installed_packages($this->package_type);
		}

		return $this->managed_packages;
	}

	/**
	 * Returns the list of managed packages for all phpBB types
	 *
	 * @return array The managed packages associated to their version.
	 */
	public function get_all_managed_packages()
	{
		if ($this->all_managed_packages === null)
		{
			$this->all_managed_packages = $this->installer->get_installed_packages(installer::PHPBB_TYPES);
		}

		return $this->all_managed_packages;
	}

	/**
	 * Returns the list of available packages
	 *
	 * @return array The name of the available packages, associated to their definition. Ordered by name.
	 */
	public function get_available_packages()
	{
		if ($this->available_packages === null)
		{
			$this->available_packages = $this->installer->get_available_packages($this->package_type);
		}

		return $this->available_packages;
	}

	protected function normalize_version($packages)
	{
		$normalized_packages = [];

		foreach ($packages as $package => $version)
		{
			if (is_numeric($package))
			{
				if (strpos($version, ':') !== false)
				{
					$parts = explode(':', $version);
					$normalized_packages[$parts[0]] = $parts[1];
				}
				else
				{
					$normalized_packages[$version] = '*';
				}
			}
			else
			{
				$normalized_packages[$package] = $version;
			}
		}

		return $normalized_packages;
	}
}
