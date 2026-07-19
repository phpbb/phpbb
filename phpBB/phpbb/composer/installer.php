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
use Composer\Filter\PlatformRequirementFilter\PlatformRequirementFilterFactory;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Json\JsonValidationException;
use Composer\Package\BasePackage;
use Composer\Package\CompleteAliasPackage;
use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;
use Composer\PartialComposer;
use Composer\Repository\ComposerRepository;
use Composer\Repository\FilterRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositorySet;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\VersionParser;
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
	 * @var string|null Stores the content of the ext json file before generate_ext_json_file() overrides it
	 */
	private $ext_json_file_backup;

	/**
	 * @var string|null Stores the content of the ext lock file before generate_ext_json_file() overrides it
	 */
	private $ext_lock_file_backup;

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
	public function __construct($root_path, filesystem $filesystem, request $request, config|null $config = null)
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
	public function install(array $packages, $whitelist, IOInterface|null $io = null)
	{
		$this->wrap(function() use ($packages, $whitelist, $io) {
			$lock = @fopen('store/composer-operation.lock', 'c');
			if ($lock === false)
			{
				throw new runtime_exception('COMPOSER_OPERATION_LOCK_FAILED');
			}

			if (!flock($lock, LOCK_EX | LOCK_NB))
			{
				fclose($lock);
				throw new runtime_exception('COMPOSER_OPERATION_IN_PROGRESS');
			}

			try
			{
				$this->do_install($packages, $whitelist, $io);
			}
			finally
			{
				flock($lock, LOCK_UN);
				fclose($lock);
			}
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
	 * @param io\io_interface|null $io IO object used for the output
	 *
	 * @throws runtime_exception
	 * @throws JsonValidationException
	 */
	protected function do_install(array $packages, $whitelist, io\io_interface|null $io = null)
	{
		if (!$io)
		{
			$this->restore_cwd();
			$io = new null_io();
			$this->move_to_root();
		}

		$this->generate_ext_json_file($packages, $io, true);

		$composer = $this->get_composer($this->get_composer_ext_json_filename());

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
			->setPlatformRequirementFilter(PlatformRequirementFilterFactory::fromBoolOrList(false))
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
			$this->restore_cwd();

			throw new runtime_exception('COMPOSER_CANNOT_INSTALL', [], $e);
		}

		if ($result !== 0)
		{
			$this->restore_ext_json_file();
			$this->restore_cwd();

			throw new runtime_exception($io->get_composer_error(), []);
		}

		$this->ext_json_file_backup = null;
		$this->ext_lock_file_backup = null;
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
	 * Returns the exact installed versions of packages of the supplied type(s)
	 *
	 * /!\ Doesn't change the current working directory
	 *
	 * @param string|array $types Returns only the packages with the given type(s)
	 *
	 * @return array The installed packages associated to their normalized version.
	 *
	 * @throws runtime_exception
	 */
	public function get_installed_package_versions($types)
	{
		return $this->wrap(function() use ($types) {
			return $this->do_get_installed_package_versions($types);
		});
	}

	/**
	 * Create instance of composer for supplied config file
	 *
	 * @param string|null $config_file Path to config file relative to phpBB root dir or null
	 *
	 * @return Composer|PartialComposer
	 * @throws JsonValidationException
	 */
	protected function get_composer(string|null $config_file): PartialComposer
	{
		static $composer_factory;
		if (!$composer_factory)
		{
			$composer_factory = new Factory();
		}

		$io = new NullIO();

		return $composer_factory->createComposer(
			$io,
			$config_file,
			false,
			filesystem_helper::realpath('')
		);
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
			$composer = $this->get_composer($this->get_composer_ext_json_filename());

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
	 * Returns the exact installed versions of packages of the supplied type(s)
	 *
	 * @param string|array $types Returns only the packages with the given type(s)
	 *
	 * @return array The installed packages associated to their normalized version.
	 */
	protected function do_get_installed_package_versions($types)
	{
		$types = (array) $types;

		try
		{
			$composer = $this->get_composer($this->get_composer_ext_json_filename());
			$installed = [];

			foreach ($composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages() as $package)
			{
				if (in_array($package->getType(), $types, true))
				{
					$installed[$package->getName()] = $package->getVersion();
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
			$composer = $this->get_composer($this->get_composer_ext_json_filename());

			/** @var ConstraintInterface $core_constraint */
			$core_constraint = $composer->getPackage()->getRequires()['phpbb/phpbb']->getConstraint();
			$core_stability = $composer->getPackage()->getMinimumStability();
			$installers_constraint = $composer->getPackage()->getRequires()['composer/installers']->getConstraint();
			$provided_constraints = $this->get_provided_constraints($composer);
			$platform_constraints = $this->get_platform_constraints($composer);

			$available = [];

			$compatible_packages = [];
			$repositories = $composer->getRepositoryManager()->getRepositories();
			$repository_set = $this->get_repository_set($repositories, $core_stability);

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
							$composer_config = new \Composer\Config();
							$downloader = new HttpDownloader($io, $composer_config);
							$json = $downloader->get($url)->getBody();

							/** @var PackageInterface $package */
							foreach (JsonFile::parseJson($json, $url)['packageNames'] as $package)
							{
								$versions            = $repository->findPackages($package);
								$compatible_packages = $this->get_compatible_versions($compatible_packages, $core_constraint, $core_stability, $installers_constraint, $provided_constraints, $platform_constraints, $repository_set, $package, $versions);
							}
						}
					}
					else
					{
						// Pre-filter repo packages by their type
						$packages = [];
						/** @var PackageInterface $package */
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
							$compatible_packages = $this->get_compatible_versions($compatible_packages, $core_constraint, $core_stability, $installers_constraint, $provided_constraints, $platform_constraints, $repository_set, $package, $versions);
						}
					}
				}
				catch (\Exception $e)
				{
					// If a repo fails, just skip it.
					continue;
				}
			}

			foreach ($compatible_packages as $package_name => $package_versions)
			{
				// Determine the highest version of the package
				/** @var CompletePackage|CompleteAliasPackage $highest_version */
				$highest_version = null;

				// Sort the versions array in descending order
				$this->sort_package_versions($package_versions);

				// The first element in the sorted array is the highest version
				if (!empty($package_versions))
				{
					$highest_version = $package_versions[0];

					// If highest version is a non-numeric dev branch, it's an instance of CompleteAliasPackage,
					// so we need to get the package being aliased in order to show the true non-numeric version.
					if ($highest_version instanceof CompleteAliasPackage)
					{
						$highest_version = $highest_version->getAliasOf();
					}
				}

				// Generates the entry
				$available[$package_name] = [];
				$available[$package_name]['name'] = $highest_version->getPrettyName();
				$available[$package_name]['display_name'] = $highest_version->getExtra()['display-name'];
				$available[$package_name]['composer_name'] = $highest_version->getName();
				$available[$package_name]['version'] = $highest_version->getPrettyVersion();
				$available[$package_name]['normalized_version'] = $highest_version->getVersion();

				if ($highest_version instanceof CompletePackage)
				{
					$available[$package_name]['description'] = $highest_version->getDescription();
					$available[$package_name]['url'] = $highest_version->getHomepage();
					$available[$package_name]['authors'] = $highest_version->getAuthors();
				}
				else
				{
					$available[$package_name]['description'] = '';
					$available[$package_name]['url'] = '';
					$available[$package_name]['authors'] = [];
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
			$this->root_path . 'store/',
		]);
	}

	/**
	 * Updates $compatible_packages with the versions of $versions compatibles with the $core_constraint
	 *
	 * @param array $compatible_packages List of compatibles versions
	 * @param ConstraintInterface $core_constraint Constraint against the phpBB version
	 * @param string $core_stability Core stability
	 * @param ConstraintInterface $installers_constraint Constraint of the composer/installers version used by phpBB
	 * @param array $provided_constraints Versions of packages fixed/provided by phpBB
	 * @param array $platform_constraints Versions of platform packages available to Composer
	 * @param RepositorySet $repository_set Composer repositories in installation priority order
	 * @param string $package_name Considered package
	 * @param array $versions List of available versions
	 *
	 * @return array
	 */
	private function get_compatible_versions(array $compatible_packages, ConstraintInterface $core_constraint, $core_stability, ConstraintInterface $installers_constraint, array $provided_constraints, array $platform_constraints, RepositorySet $repository_set, $package_name, array $versions)
	{
		$version_parser = new VersionParser();

		$core_stability_value = BasePackage::$stabilities[$core_stability];

		/** @var PackageInterface $version */
		foreach ($versions as $version)
		{
			try
			{
				// Check stability first to avoid unnecessary operations
				if (BasePackage::$stabilities[$version->getStability()] > $core_stability_value)
				{
					continue;
				}

				$requires = $version->getRequires();
				$extra = $version->getExtra();
				$conflicts = $version->getConflicts();

				// Extensions must explicitly declare the phpBB versions they support.
				if (!isset($requires['phpbb/phpbb']) && !isset($extra['soft-require']['phpbb/phpbb']))
				{
					continue;
				}

				// Check for compatibility with phpBB if 'phpbb/phpbb' exists in 'requires'
				if (isset($requires['phpbb/phpbb']))
				{
					$package_constraint = $requires['phpbb/phpbb']->getConstraint();
					if (!$package_constraint->matches($core_constraint))
					{
						continue;
					}
				}

				// Check for compatibility with phpBB if 'phpbb/phpbb' exists in 'soft-require'
				if (isset($extra['soft-require']['phpbb/phpbb']))
				{
					$package_constraint = $version_parser->parseConstraints($extra['soft-require']['phpbb/phpbb']);
					if (!$package_constraint->matches($core_constraint))
					{
						continue;
					}
				}

				// Check all platform requirements, including PHP extensions and Composer APIs.
				foreach ($requires as $required_name => $required_link)
				{
					if (PlatformRepository::isPlatformPackage($required_name)
						&& !$this->matches_any_constraint($required_link->getConstraint(), $platform_constraints[$required_name] ?? []))
					{
						continue 2;
					}
				}

				// The extension must support the exact composer/installers version pinned by phpBB.
				if (isset($requires['composer/installers']))
				{
					if (!$requires['composer/installers']->getConstraint()->matches($installers_constraint))
					{
						continue;
					}
				}
				else
				{
					continue;
				}

				// phpBB fixes its core dependency versions in composer-ext.json. Reject extensions whose
				// direct requirements or conflicts cannot coexist with those fixed packages.
				foreach ($requires as $required_name => $required_link)
				{
					if (isset($provided_constraints[$required_name])
						&& !$this->matches_any_constraint($required_link->getConstraint(), $provided_constraints[$required_name]))
					{
						continue 2;
					}

					if (!isset($provided_constraints[$required_name])
						&& !PlatformRepository::isPlatformPackage($required_name)
						&& empty($repository_set->findPackages($required_name, $required_link->getConstraint())))
					{
						continue 2;
					}
				}

				foreach ($conflicts as $conflicted_name => $conflicted_link)
				{
					if (isset($provided_constraints[$conflicted_name])
						&& $this->matches_any_constraint($conflicted_link->getConstraint(), $provided_constraints[$conflicted_name]))
					{
						continue 2;
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
	 * Check whether a requirement/conflict intersects any available exact constraint.
	 *
	 * @param ConstraintInterface $constraint Constraint to test
	 * @param ConstraintInterface[] $available_constraints Available constraints
	 * @return bool
	 */
	private function matches_any_constraint(ConstraintInterface $constraint, array $available_constraints): bool
	{
		foreach ($available_constraints as $available_constraint)
		{
			if ($constraint->matches($available_constraint))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get exact versions of packages provided by phpBB's generated root package.
	 *
	 * @param Composer|PartialComposer $composer Composer instance
	 * @return array<string, ConstraintInterface[]>
	 */
	private function get_provided_constraints($composer): array
	{
		$provided_constraints = [];
		$root_package = $composer->getPackage();

		foreach (array_merge($root_package->getProvides(), $root_package->getReplaces()) as $package => $link)
		{
			$provided_constraints[$package][] = $link->getConstraint();
		}

		foreach ($root_package->getRequires() as $package => $link)
		{
			if (!PlatformRepository::isPlatformPackage($package))
			{
				$provided_constraints[$package][] = $link->getConstraint();
			}
		}

		return $provided_constraints;
	}

	/**
	 * Get exact constraints for platform packages available to Composer.
	 *
	 * @param Composer|PartialComposer $composer Composer instance
	 * @return array<string, ConstraintInterface[]>
	 */
	private function get_platform_constraints($composer): array
	{
		$platform_constraints = [];
		$platform_repository = new PlatformRepository([], $composer->getConfig()->get('platform') ?: []);

		foreach ($platform_repository->getPackages() as $package)
		{
			$platform_constraints[$package->getName()][] = new Constraint('==', $package->getVersion());
		}

		return $platform_constraints;
	}

	/**
	 * Create a repository set matching Composer's installation priority behavior.
	 *
	 * @param array $repositories Composer repositories
	 * @param string $minimum_stability Minimum package stability
	 * @return RepositorySet
	 */
	private function get_repository_set(array $repositories, string $minimum_stability): RepositorySet
	{
		$repository_set = new RepositorySet($minimum_stability);

		foreach ($repositories as $repository)
		{
			$repository_set->addRepository($repository);
		}

		return $repository_set;
	}

	/**
	 * Sort packages the same way as a prefer-stable Composer update.
	 *
	 * @param PackageInterface[] $package_versions Package versions to sort in place
	 */
	private function sort_package_versions(array &$package_versions): void
	{
		usort($package_versions, function (PackageInterface $a, PackageInterface $b)
		{
			$a_priority = BasePackage::$stabilities[$a->getStability()];
			$b_priority = BasePackage::$stabilities[$b->getStability()];
			$stable_priority = BasePackage::STABILITY_STABLE;

			if ($a_priority !== $b_priority && ($a_priority > $stable_priority || $b_priority > $stable_priority))
			{
				return $a_priority <=> $b_priority;
			}

			return version_compare($b->getVersion(), $a->getVersion());
		});
	}

	/**
	 * Generates and write the json file used to install the set of packages
	 *
	 * @param array $packages Packages to update.
	 *        Each entry may be a name or an array associating a version constraint to a name
	 * @param io\io_interface|null $io Composer output
	 * @param bool $skip_unavailable_repositories Whether unavailable custom repositories may be skipped
	 * @throws JsonValidationException
	 */
	protected function generate_ext_json_file(array $packages, io\io_interface|null $io = null, bool $skip_unavailable_repositories = false)
	{
		$composer = $this->get_composer(null);

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
				'vendor-dir'	=> $this->packages_vendor_dir,
				'secure-http' => true,
				'preferred-install' => 'dist',
				'allow-plugins'	=> [
					'composer/installers' => true,
				]
			],
			'minimum-stability' => $this->minimum_stability,
		];

		$this->ext_json_file_backup = null;
		$ext_lock_file_backup = @file_get_contents(substr($this->get_composer_ext_json_filename(), 0, -5) . '.lock');
		$this->ext_lock_file_backup = $ext_lock_file_backup === false ? null : $ext_lock_file_backup;
		$json_file = new JsonFile($this->get_composer_ext_json_filename());
		$ext_json_file_backup = @file_get_contents($this->get_composer_ext_json_filename());
		if ($ext_json_file_backup === false)
		{
			$ext_json_file_backup = "{}\n";
		}

		try
		{
			$json_file->read();
		}
		catch (ParsingException $e)
		{
			$lockFile = new JsonFile(substr($this->get_composer_ext_json_filename(), 0, -5) . '.lock');
			$lockFile->write([]);
		}

		// First pass write: base file with requested packages as provided
		$json_file->write($ext_json_data);
		$this->ext_json_file_backup = $ext_json_file_backup;

		if ($skip_unavailable_repositories && $this->packagist && !empty($ext_json_data['repositories']))
		{
			$ext_composer = $this->get_composer($this->get_composer_ext_json_filename());
			$repositories = $this->filter_unavailable_repositories(
				$ext_json_data['repositories'],
				$ext_composer->getRepositoryManager()->getRepositories(),
				array_keys($packages),
				$io ?? new null_io()
			);

			if ($repositories !== $ext_json_data['repositories'])
			{
				$ext_json_data['repositories'] = $repositories;
				$json_file->write($ext_json_data);
			}
		}

		// Second pass: resolve and pin the highest compatible versions for unconstrained requested packages
		try
		{
			// Build a list of requested packages without explicit constraints
			$unconstrained = [];
			foreach ($packages as $name => $constraint)
			{
				// The $packages array can be either ['vendor/package' => '^1.2'] or ['vendor/package'] (numeric keys).
				if (is_int($name))
				{
					// Numeric key means just a name
					$package_name = $constraint;
					$unconstrained[$package_name] = true;
				}
				else
				{
					// If constraint is empty or '*' treat as unconstrained
					if ($constraint === '' || $constraint === '*' || $constraint === null)
					{
						$unconstrained[$name] = true;
					}
				}
			}

			if (!empty($unconstrained))
			{
				// Load composer on the just-written file so repositories and core constraints are available
				$ext_composer = $this->get_composer($this->get_composer_ext_json_filename());

				/** @var ConstraintInterface $core_constraint */
				$core_constraint = $ext_composer->getPackage()->getRequires()['phpbb/phpbb']->getConstraint();
				$core_stability = $ext_composer->getPackage()->getMinimumStability();

				// Resolve highest compatible versions for each unconstrained package
				$pins = $this->resolve_highest_versions(array_keys($unconstrained), $ext_composer, $core_constraint, $core_stability);

				if (!empty($pins))
				{
					// Merge pins into require section, overwriting unconstrained entries
					foreach ($pins as $pkg => $version)
					{
						$ext_json_data['require'][$pkg] = $version;
					}

					// Rewrite composer-ext.json with pinned versions
					$json_file->write($ext_json_data);
				}
			}
		}
		catch (\Exception $e)
		{
			// If resolution fails for any reason, keep the first-pass file intact (Composer will still resolve).
			// Intentionally swallow to avoid breaking installation flow.
		}
	}

	/**
	 * Remove temporarily unavailable configured repositories when Packagist can provide a fallback.
	 *
	 * @param array $repository_configs Composer repository configuration
	 * @param array $repositories Instantiated Composer repositories
	 * @param array $package_names Package names involved in the operation
	 * @param io\io_interface $io Composer output
	 * @return array Available repository configuration
	 */
	protected function filter_unavailable_repositories(array $repository_configs, array $repositories, array $package_names, io\io_interface $io): array
	{
		$available = [];
		foreach ($repository_configs as $repository_config)
		{
			if (isset($repository_config['url']))
			{
				$available[rtrim($repository_config['url'], '/')] = true;
			}
		}

		$probe_package = reset($package_names) ?: 'phpbb/phpbb';

		foreach ($repositories as $repository)
		{
			$composer_repository = $repository instanceof FilterRepository ? $repository->getRepository() : $repository;
			if (!$composer_repository instanceof ComposerRepository)
			{
				continue;
			}

			$repository_config = $composer_repository->getRepoConfig();
			$repository_url = isset($repository_config['url']) ? rtrim($repository_config['url'], '/') : '';
			if (!isset($available[$repository_url]))
			{
				continue;
			}

			try
			{
				$repository->findPackages($probe_package);
			}
			catch (\Exception $e)
			{
				$available[$repository_url] = false;
				/** @psalm-suppress InvalidArgument phpBB IO accepts translated message tuples. */
				$io->writeError([['COMPOSER_REPOSITORY_UNAVAILABLE', [$repository_config['url']], 3]]);
			}
		}

		return array_values(array_filter($repository_configs, function(array $repository_config) use ($available)
		{
			return !isset($repository_config['url']) || $available[rtrim($repository_config['url'], '/')];
		}));
	}

	/**
	 * Resolve the highest compatible versions for the given package names
	 * based on repositories and phpBB/PHP constraints from the provided Composer instance.
	 *
	 * @param array $package_names list of package names to resolve
	 * @param Composer|PartialComposer $composer Composer instance configured with repositories
	 * @param ConstraintInterface $core_constraint phpBB version constraint
	 * @param string $core_stability minimum stability
	 * @return array [packageName => prettyVersion]
	 */
	protected function resolve_highest_versions(array $package_names, $composer, ConstraintInterface $core_constraint, $core_stability): array
	{
		$compatible_packages = [];
		$repositories = $composer->getRepositoryManager()->getRepositories();
		$repository_set = $this->get_repository_set($repositories, $core_stability);
		$installers_constraint = $composer->getPackage()->getRequires()['composer/installers']->getConstraint();
		$provided_constraints = $this->get_provided_constraints($composer);
		$platform_constraints = $this->get_platform_constraints($composer);

		foreach ($repositories as $repository)
		{
			try
			{
				if ($repository instanceof ComposerRepository)
				{
					foreach ($package_names as $name)
					{
						$versions = $repository->findPackages($name);
						if (!empty($versions))
						{
							$compatible_packages = $this->get_compatible_versions($compatible_packages, $core_constraint, $core_stability, $installers_constraint, $provided_constraints, $platform_constraints, $repository_set, $name, $versions);
						}
					}
				}
				else
				{
					// Preload and filter by name for non-composer repositories
					$package_name = [];
					foreach ($repository->getPackages() as $package)
					{
						$name = $package->getName();
						if (in_array($name, $package_names, true))
						{
							$package_name[$name][] = $package;
						}
					}

					foreach ($package_name as $name => $versions)
					{
						$compatible_packages = $this->get_compatible_versions($compatible_packages, $core_constraint, $core_stability, $installers_constraint, $provided_constraints, $platform_constraints, $repository_set, $name, $versions);
					}
				}
			}
			catch (\Exception $e)
			{
				// If a repo fails, just skip it.
				continue;
			}
		}

		$pins = [];
		foreach ($package_names as $name)
		{
			if (empty($compatible_packages[$name]))
			{
				continue;
			}

			$package_versions = $compatible_packages[$name];

			$this->sort_package_versions($package_versions);

			$highest = $package_versions[0];
			if ($highest instanceof CompleteAliasPackage)
			{
				$highest = $highest->getAliasOf();
			}

			// Pin to the resolved highest compatible version using its pretty version
			$pins[$name] = $highest->getPrettyVersion();
		}

		return $pins;
	}

	/**
	 * Restore the json file overridden by generate_ext_json_file()
	 */
	protected function restore_ext_json_file()
	{
		$restore_failed = false;

		if ($this->ext_json_file_backup !== null)
		{
			if (@file_put_contents($this->get_composer_ext_json_filename(), $this->ext_json_file_backup, LOCK_EX) === false)
			{
				$restore_failed = true;
			}

			$this->ext_json_file_backup = null;
		}

		if ($this->ext_lock_file_backup !== null)
		{
			if (@file_put_contents(substr($this->get_composer_ext_json_filename(), 0, -5) . '.lock', $this->ext_lock_file_backup, LOCK_EX) === false)
			{
				$restore_failed = true;
			}

			$this->ext_lock_file_backup = null;
		}

		if ($restore_failed)
		{
			throw new runtime_exception('COMPOSER_CANNOT_RESTORE');
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
			$parts = is_string($repository) ? parse_url($repository) : false;
			if ($parts !== false
				&& ($parts['scheme'] ?? '') === 'https'
				&& !empty($parts['host'])
				&& !isset($parts['user'])
				&& !isset($parts['pass'])
				&& preg_match('#^' . get_preg_expression('url') . '$#iu', $repository))
			{
				$repositories[] = [
					'type' => 'composer',
					'url' => $repository,
					// When Packagist is enabled, allow dependencies missing from or outdated in this repository
					// to be selected from Packagist. Without Packagist, keep configured repositories canonical.
					'canonical' => !$this->packagist,
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
