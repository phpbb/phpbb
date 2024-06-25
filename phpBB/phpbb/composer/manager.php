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
	 * @var driver_interface Cache instance
	 */
	protected $cache;

	/**
	 * @var string Type of packages (phpbb-packages per example)
	 */
	protected $package_type;

	/**
	 * @var string Prefix used for the exception's language string
	 */
	protected $exception_prefix;

	/**
	 * @var array|null Caches the managed packages list (for the current type)
	 */
	private $managed_packages;

	/**
	 * @var array|null Caches the managed packages list (for all phpBB types)
	 */
	private $all_managed_packages;

	/**
	 * @var array|null Caches the available packages list
	 */
	private $available_packages;

	/**
	 * @param installer			$installer			Installer object
	 * @param driver_interface	$cache				Cache object
	 * @param string			$package_type		Composer type of managed packages
	 * @param string			$exception_prefix	Exception prefix to use
	 */
	public function __construct(installer $installer, driver_interface $cache, $package_type, $exception_prefix)
	{
		$this->installer = $installer;
		$this->cache = $cache;
		$this->package_type = $package_type;
		$this->exception_prefix = $exception_prefix;
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

		$this->pre_install($packages, $io);

		$managed_packages = array_merge($this->get_all_managed_packages(), $packages);
		ksort($managed_packages);

		$this->installer->install($managed_packages, [], $io);

		$this->post_install($packages, $io);

		$this->managed_packages = null;
	}

	/**
	 * Hook called before installing the packages
	 *
	 * @param array $packages Packages to update.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface|null $io IO object used for the output
	 */
	protected function pre_install(array $packages, IOInterface $io = null)
	{
	}

	/**
	 * Hook called after installing the packages
	 *
	 * @param array $packages Packages to update.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface|null $io IO object used for the output
	 */
	protected function post_install(array $packages, IOInterface $io = null)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function update(array $packages, IOInterface $io = null)
	{
		$packages = $this->normalize_version($packages);

		$not_managed = array_diff_key($packages, $this->get_managed_packages());
		if (count($not_managed) !== 0)
		{
			throw new runtime_exception($this->exception_prefix, 'NOT_MANAGED', [implode('|', array_keys($not_managed))]);
		}

		$this->pre_update($packages, $io);

		$managed_packages = array_merge($this->get_all_managed_packages(), $packages);
		ksort($managed_packages);

		$this->installer->install($managed_packages, array_keys($packages), $io);

		$this->post_update($packages, $io);
	}

	/**
	 * Hook called before updating the packages
	 *
	 * @param array $packages Packages to update.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface|null $io IO object used for the output
	 */
	protected function pre_update(array $packages, IOInterface $io = null)
	{
	}

	/**
	 * Hook called after updating the packages
	 *
	 * @param array $packages Packages to update.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface|null $io IO object used for the output
	 */
	protected function post_update(array $packages, IOInterface $io = null)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(array $packages, IOInterface $io = null)
	{
		$packages = $this->normalize_version($packages);

		$not_managed = array_diff_key($packages, $this->get_managed_packages());
		if (count($not_managed) !== 0)
		{
			throw new runtime_exception($this->exception_prefix, 'NOT_MANAGED', [implode('|', array_keys($not_managed))]);
		}

		$this->pre_remove($packages, $io);

		$managed_packages = array_diff_key($this->get_all_managed_packages(), $packages);
		ksort($managed_packages);

		$this->installer->install($managed_packages, array_keys($packages), $io);

		$this->post_remove($packages, $io);

		$this->managed_packages = null;
	}

	/**
	 * Hook called before removing the packages
	 *
	 * @param array $packages Packages to update.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface|null $io IO object used for the output
	 */
	protected function pre_remove(array $packages, IOInterface $io = null)
	{
	}

	/**
	 * Hook called after removing the packages
	 *
	 * @param array $packages Packages to update.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface|null $io IO object used for the output
	 */
	protected function post_remove(array $packages, IOInterface $io = null)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_managed($package)
	{
		return array_key_exists($package, $this->get_managed_packages());
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function get_all_managed_packages()
	{
		if ($this->all_managed_packages === null)
		{
			$this->all_managed_packages = $this->installer->get_installed_packages(explode(',', installer::PHPBB_TYPES));
		}

		return $this->all_managed_packages;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_available_packages()
	{
		if ($this->available_packages === null)
		{
			$this->available_packages = $this->cache->get('_composer_' . $this->package_type . '_available');

			if (!$this->available_packages)
			{
				$this->available_packages = $this->installer->get_available_packages($this->package_type);
				$this->cache->put('_composer_' . $this->package_type . '_available', $this->available_packages, 24*60*60);
			}
		}

		return $this->available_packages;
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset_cache()
	{
		$this->cache->destroy('_composer_' . $this->package_type . '_available');

		$this->available_packages = null;
		$this->managed_packages = null;
		$this->all_managed_packages = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function start_managing($package, $io)
	{
		throw new \phpbb\exception\runtime_exception('COMPOSER_UNSUPPORTED_OPERATION', (array) $this->package_type);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements()
	{
		return $this->installer->check_requirements();
	}

	/**
	 * Normalize a packages/version array. Every entry can have 3 different forms:
	 *  - $package => $version
	 *  - $indice => $package:$version
	 *  - $indice => $package
	 * They are converted to he form:
	 *  - $package => $version ($version is set to '*' for the third form)
	 *
	 * @param array $packages
	 *
	 * @return array
	 */
	protected function normalize_version(array $packages)
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
