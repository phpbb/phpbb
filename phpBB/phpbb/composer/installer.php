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

use Composer\Composer;
use Composer\DependencyResolver\Request as composer_request;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Package\BasePackage;
use Composer\Package\CompletePackage;
use Composer\Repository\ComposerRepository;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Util\HttpDownloader;
use phpbb\composer\io\null_io;
use phpbb\config\config;
use phpbb\exception\runtime_exception;
use phpbb\filesystem\filesystem;
use phpbb\request\request;
use Seld\JsonLint\ParsingException;
use phpbb\filesystem\helper as filesystem_helper;

/**
 * Class to install packages through composer while freezing core dependencies.
 */
class installer
{
	const PHPBB_TYPES = 'phpbb-extension,phpbb-style,phpbb-language';

	/**
	 * @var array Repositories to look packages from
	 */
	protected $repositories = [];

	/**
	 * @var bool Indicates whether packagist usage is allowed or not
	 */
	protected $packagist = false;

	/**
	 * @var string Composer filename used to manage the packages
	 */
	protected $composer_filename = 'composer-ext.json';

	/**
	 * @var string Directory where to install packages vendors
	 */
	protected $packages_vendor_dir = 'vendor-ext/';

	/**
	 * @var string Minimum stability
	 */
	protected $minimum_stability = 'stable';

	/**
	 * @var string phpBB root path
	 */
	protected $root_path;

	/**
	 * @var string|null Stores the original working directory in case it has been changed through move_to_root()
	 */
	private $original_cwd;

	/**
	 * @var array|null Stores the content of the ext json file before generate_ext_json_file() overrides it
	 */
	private $ext_json_file_backup;

	/**
	 * @var request phpBB request object
	 */
	private $request;

	/**
	 * @var filesystem phpBB filesystem
	 */
	private $filesystem;

	/**
	 * @param string		$root_path	phpBB root path
	 * @param filesystem	$filesystem	Filesystem object
	 * @param request		$request	phpBB request object
	 * @param config|null		$config		Config object
	 */
	public function __construct($root_path, filesystem $filesystem, request $request, config $config = null)
	{
		if ($config)
		{
			$repositories = json_decode($config['exts_composer_repositories'], true);

			if (is_array($repositories) && !empty($repositories))
			{
				$this->repositories = (array) $repositories;
			}

			$this->packagist			= (bool) $config['exts_composer_packagist'];
			$this->composer_filename	= $config['exts_composer_json_file'];
			$this->packages_vendor_dir	= $config['exts_composer_vendor_dir'];
			$this->minimum_stability	= $config['exts_composer_minimum_stability'];
		}

		$this->root_path = $root_path;
		$this->request = $request;
		$this->filesystem = $filesystem;

		putenv('COMPOSER_HOME=' . filesystem_helper::realpath($root_path) . '/store/composer');
	}

	/**
	 * Update the current installed set of packages
	 *
	 * @param array $packages Packages to install.
	 *        Each entry may be a name or an array associating a version constraint to a name
	 * @param array $whitelist White-listed packages (packages that can be installed/updated/removed)
	 * @param IOInterface|null $io IO object used for the output
	 *
	 * @throws runtime_exception
	 */
	public function install(array $packages, $whitelist, IOInterface $io = null)
	{
		$this->wrap(function() use ($packages, $whitelist, $io) {
			$this->do_install($packages, $whitelist, $io);
		});
	}

	/**
	 * Update the current installed set of packages
	 *
	 * /!\ Doesn't change the current working directory
	 *
	 * @param array $packages Packages to install.
	 *        Each entry may be a name or an array associating a version constraint to a name
	 * @param array $whitelist White-listed packages (packages that can be installed/updated/removed)
	 * @param IOInterface|null $io IO object used for the output
	 *
	 * @throws runtime_exception
	 */
	protected function do_install(array $packages, $whitelist, IOInterface $io = null)
	{
		if (!$io)
		{
			$io = new null_io();
		}

		$this->generate_ext_json_file($packages);

		$composer = Factory::create($io, $this->get_composer_ext_json_filename(), false);
		$install = \Composer\Installer::create($io, $composer);

		$composer->getInstallationManager()->setOutputProgress(false);

		$install
			->setVerbose(true)
			->setPreferSource(false)
			->setPreferDist(true)
			->setDevMode(false)
			->setUpdate(true)
			->setUpdateAllowList($whitelist)
			->setUpdateAllowTransitiveDependencies(composer_request::UPDATE_ONLY_LISTED)
			->setIgnorePlatformRequirements(false)
			->setOptimizeAutoloader(true)
			->setDumpAutoloader(true)
			->setPreferStable(true)
			->setRunScripts(false)
			->setDryRun(false);

		try
		{
			$result = $install->run();
		}
		catch (\Exception $e)
		{
			$this->restore_ext_json_file();

			throw new runtime_exception('COMPOSER_CANNOT_INSTALL', [], $e);
		}

		if ($result !== 0)
		{
			$this->restore_ext_json_file();

			throw new runtime_exception($io->get_composer_error(), []);
		}
	}

	/**
	 * Returns the list of currently installed packages
	 *
	 * @param string|array $types Returns only the packages with the given type(s)
	 *
	 * @return array The installed packages associated to their version.
	 *
	 * @throws runtime_exception
	 */
	public function get_installed_packages($types)
	{
		return $this->wrap(function() use ($types) {
			return $this->do_get_installed_packages($types);
		});
	}

	/**
	 * Returns the list of currently installed packages
	 *
	 * /!\ Doesn't change the current working directory
	 *
	 * @param string|array $types Returns only the packages with the given type(s)
	 *
	 * @return array The installed packages associated to their version.
	 */
	protected function do_get_installed_packages($types)
	{
		$types = (array) $types;

		try
		{
			$io = new NullIO();
			$composer = Factory::create($io, $this->get_composer_ext_json_filename(), false);

			$installed = [];

			/** @var \Composer\Package\Link[] $required_links */
			$required_links = $composer->getPackage()->getRequires();
			$installed_packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

			foreach ($installed_packages as $package)
			{
				if (in_array($package->getType(), $types, true))
				{
					$version = array_key_exists($package->getName(), $required_links) ?
						$required_links[$package->getName()]->getPrettyConstraint() : '*';
					$installed[$package->getName()] = $version;
				}
			}

			return $installed;
		}
		catch (\Exception $e)
		{
			return [];
		}
	}

	/**
	 * Gets the list of the available packages of the configured type in the configured repositories
	 *
	 * /!\ Doesn't change the current working directory
	 *
	 * @param string $type Returns only the packages with the given type
	 *
	 * @return array The name of the available packages, associated to their definition. Ordered by name.
	 *
	 * @throws runtime_exception
	 */
	public function get_available_packages($type)
	{
		return $this->wrap(function() use ($type) {
			return $this->do_get_available_packages($type);
		});
	}

	/**
	 * Gets the list of the available packages of the configured type in the configured repositories
	 *
	 * @param string $type Returns only the packages with the given type
	 *
	 * @return array The name of the available packages, associated to their definition. Ordered by name.
	 */
	protected function do_get_available_packages($type)
	{
		try
		{
			$this->generate_ext_json_file($this->do_get_installed_packages(explode(',', self::PHPBB_TYPES)));

			$io = new NullIO();
			$composer = Factory::create($io, $this->get_composer_ext_json_filename(), false);

			/** @var ConstraintInterface $core_constraint */
			$core_constraint = $composer->getPackage()->getRequires()['phpbb/phpbb']->getConstraint();
			$core_stability = $composer->getPackage()->getMinimumStability();

			$available = [];

			$compatible_packages = [];
			$repositories = $composer->getRepositoryManager()->getRepositories();

			/** @var \Composer\Repository\RepositoryInterface $repository */
			foreach ($repositories as $repository)
			{
				try
				{
					if ($repository instanceof ComposerRepository)
					{
						// Special case for packagist which exposes an api to retrieve all packages of a given type.
						// For the others composer repositories with providers we can't do anything. It would be too slow.

						$repositoryReflection = new \ReflectionObject($repository);
						$repo_url = $repositoryReflection->getProperty('url');
						$repo_url->setAccessible(true);

						if ($repo_url->getValue($repository) === 'https://repo.packagist.org')
						{
							$url = 'https://packagist.org/packages/list.json?type=' . $type;
							$composer_config = new \Composer\Config([]);
							$downloader = new HttpDownloader($io, $composer_config);
							$json = $downloader->get($url)->getBody();

							/** @var \Composer\Package\PackageInterface $package */
							foreach (JsonFile::parseJson($json, $url)['packageNames'] as $package)
							{
								$versions            = $repository->findPackages($package);
								$compatible_packages = $this->get_compatible_versions($compatible_packages, $core_constraint, $core_stability, $package, $versions);
							}
						}
					}
					else
					{
						// Pre-filter repo packages by their type
						$packages = [];
						/** @var \Composer\Package\PackageInterface $package */
						foreach ($repository->getPackages() as $package)
						{
							if ($package->getType() === $type)
							{
								$packages[$package->getName()][] = $package;
							}
						}

						// Filter the compatibles versions
						foreach ($packages as $package => $versions)
						{
							$compatible_packages = $this->get_compatible_versions($compatible_packages, $core_constraint, $core_stability, $package, $versions);
						}
					}
				}
				catch (\Exception $e)
				{
					// If a repo fails, just skip it.
					continue;
				}
			}

			foreach ($compatible_packages as $name => $versions)
			{
				// Determine the highest version of the package
				/** @var CompletePackage $highest_version */
				$highest_version = null;

				/** @var CompletePackage $version */
				foreach ($versions as $version)
				{
					if (!$highest_version || version_compare($version->getVersion(), $highest_version->getVersion(), '>'))
					{
						$highest_version = $version;
					}
				}

				// Generates the entry
				$available[$name] = [];
				$available[$name]['name'] = $highest_version->getPrettyName();
				$available[$name]['display_name'] = $highest_version->getExtra()['display-name'];
				$available[$name]['composer_name'] = $highest_version->getName();
				$available[$name]['version'] = $highest_version->getPrettyVersion();

				if ($version instanceof CompletePackage)
				{
					$available[$name]['description'] = $highest_version->getDescription();
					$available[$name]['url'] = $highest_version->getHomepage();
					$available[$name]['authors'] = $highest_version->getAuthors();
				}
				else
				{
					$available[$name]['description'] = '';
					$available[$name]['url'] = '';
					$available[$name]['authors'] = [];
				}
			}

			usort($available, function($a, $b)
			{
				return strcasecmp($a['display_name'], $b['display_name']);
			});

			return $available;
		}
		catch (\Exception $e)
		{
			return [];
		}
	}

	/**
	 * Checks the requirements of the manager and returns true if it can be used.
	 *
	 * @return bool
	 */
	public function check_requirements()
	{
		return $this->filesystem->is_writable([
			$this->root_path . $this->composer_filename,
			$this->root_path . $this->packages_vendor_dir,
			$this->root_path . substr($this->composer_filename, 0, -5) . '.lock',
		]);
	}

	/**
	 * Updates $compatible_packages with the versions of $versions compatibles with the $core_constraint
	 *
	 * @param array						$compatible_packages	List of compatibles versions
	 * @param ConstraintInterface	$core_constraint		Constraint against the phpBB version
	 * @param string $core_stability Core stability
	 * @param string					$package_name			Considered package
	 * @param array						$versions				List of available versions
	 *
	 * @return array
	 */
	private function get_compatible_versions(array $compatible_packages, ConstraintInterface $core_constraint, $core_stability, $package_name, array $versions)
	{
		$core_stability_value = BasePackage::$stabilities[$core_stability];

		/** @var \Composer\Package\PackageInterface $version */
		foreach ($versions as $version)
		{
			try
			{
				if (BasePackage::$stabilities[$version->getStability()] > $core_stability_value)
				{
					continue;
				}

				if (array_key_exists('phpbb/phpbb', $version->getRequires()))
				{
					/** @var ConstraintInterface $package_constraint */
					$package_constraint = $version->getRequires()['phpbb/phpbb']->getConstraint();

					if (!$package_constraint->matches($core_constraint))
					{
						continue;
					}
				}

				$compatible_packages[$package_name][] = $version;
			}
			catch (\Exception $e)
			{
				// Do nothing (to log when a true debug logger is available)
			}
		}

		return $compatible_packages;
	}

	/**
	 * Generates and write the json file used to install the set of packages
	 *
	 * @param array $packages Packages to update.
	 *        Each entry may be a name or an array associating a version constraint to a name
	 */
	protected function generate_ext_json_file(array $packages)
	{
		$io = new NullIO();

		$composer = Factory::create($io, null, false);

		$core_packages = $this->get_core_packages($composer);

		// The composer/installers package must be installed on his own and not provided by the existing autoloader
		$core_replace = $core_packages;
		unset($core_replace['composer/installers']);

		$ext_json_data = [
			'require' => array_merge(
				['php' => $this->get_core_php_requirement($composer)],
				$core_packages,
				$this->get_extra_dependencies(),
				$packages),
			'replace' => $core_replace,
			'repositories' => $this->get_composer_repositories(),
			'config' => [
				'vendor-dir'=> $this->packages_vendor_dir,
			],
			'minimum-stability' => $this->minimum_stability,
		];

		$this->ext_json_file_backup = null;
		$json_file = new JsonFile($this->get_composer_ext_json_filename());

		try
		{
			$ext_json_file_backup = $json_file->read();
		}
		catch (ParsingException $e)
		{
			$ext_json_file_backup = '{}';

			$lockFile = new JsonFile(substr($this->get_composer_ext_json_filename(), 0, -5) . '.lock');
			$lockFile->write([]);
		}

		$json_file->write($ext_json_data);
		$this->ext_json_file_backup = $ext_json_file_backup;
	}

	/**
	 * Restore the json file overridden by generate_ext_json_file()
	 */
	protected function restore_ext_json_file()
	{
		if ($this->ext_json_file_backup)
		{
			try
			{
				$json_file = new JsonFile($this->get_composer_ext_json_filename());
				$json_file->write($this->ext_json_file_backup);
			}
			catch (\Exception $e)
			{
			}

			$this->ext_json_file_backup = null;
		}
	}

	/**
	 * Get the core installed packages
	 *
	 * @param Composer $composer Composer object to load the dependencies
	 * @return array The core packages with their version
	 */
	protected function get_core_packages(Composer $composer)
	{
		$core_deps = [];
		$packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

		foreach ($packages as $package)
		{
			$core_deps[$package->getName()] = $package->getPrettyVersion();
		}

		$core_deps['phpbb/phpbb'] = PHPBB_VERSION;

		return $core_deps;
	}

	/**
	 * Get the PHP version required by the core
	 *
	 * @param Composer $composer Composer object to load the dependencies
	 * @return string The PHP version required by the core
	 */
	protected function get_core_php_requirement(Composer $composer)
	{
		return $composer->getLocker()->getLockData()['platform']['php'];
	}

	/**
	 * Generate the repositories entry of the packages json file
	 *
	 * @return array repositories entry
	 */
	protected function get_composer_repositories()
	{
		$repositories = [];

		if (!$this->packagist)
		{
			$repositories[]['packagist'] = false;
		}

		foreach ($this->repositories as $repository)
		{
			if (preg_match('#^' . get_preg_expression('url') . '$#iu', $repository))
			{
				$repositories[] = [
					'type' => 'composer',
					'url' => $repository,
				];
			}
		}

		return $repositories;
	}

	/**
	 * Get the name of the json file used for the packages.
	 *
	 * @return string The json filename
	 */
	protected function get_composer_ext_json_filename()
	{
		return $this->composer_filename;
	}

	/**
	 * Get extra dependencies required to install the packages
	 *
	 * @return array Array of composer dependencies
	 */
	protected function get_extra_dependencies()
	{
		return [];
	}

	/**
	 * Sets the customs repositories
	 *
	 * @param array $repositories An array of composer repositories to use
	 */
	public function set_repositories(array $repositories)
	{
		$this->repositories = $repositories;
	}

	/**
	 * Allow or disallow packagist
	 *
	 * @param boolean $packagist
	 */
	public function set_packagist($packagist)
	{
		$this->packagist = $packagist;
	}

	/**
	 * Sets the name of the managed packages' json file
	 *
	 * @param string $composer_filename
	 */
	public function set_composer_filename($composer_filename)
	{
		$this->composer_filename = $composer_filename;
	}

	/**
	 * Sets the location of the managed packages' vendors
	 *
	 * @param string $packages_vendor_dir
	 */
	public function set_packages_vendor_dir($packages_vendor_dir)
	{
		$this->packages_vendor_dir = $packages_vendor_dir;
	}

	/**
	 * Sets the phpBB root path
	 *
	 * @param string $root_path
	 */
	public function set_root_path($root_path)
	{
		$this->root_path = $root_path;
	}

	/**
	 * Change the current directory to phpBB root
	 */
	protected function move_to_root()
	{
		if ($this->original_cwd === null)
		{
			$this->original_cwd = getcwd();
			chdir($this->root_path);
		}
	}

	/**
	 * Restore the current working directory if move_to_root() have been called
	 */
	protected function restore_cwd()
	{
		if ($this->original_cwd)
		{
			chdir($this->original_cwd);
			$this->original_cwd = null;
		}
	}

	/**
	 * Wraps a callable in order to adjust the context needed by composer
	 *
	 * @param callable $callable
	 *
	 * @return mixed
	 */
	protected function wrap(callable $callable)
	{
		// The composer installers works with a path relative to the current directory
		$this->move_to_root();

		// The composer installers uses some super globals
		$super_globals = $this->request->super_globals_disabled();
		$this->request->enable_super_globals();

		try
		{
			return $callable();
		}
		finally
		{
			$this->restore_cwd();

			if ($super_globals)
			{
				$this->request->disable_super_globals();
			}
		}
	}
}
