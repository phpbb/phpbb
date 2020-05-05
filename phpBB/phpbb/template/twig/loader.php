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

namespace phpbb\template\twig;

/**
* Twig Template loader
*/
class loader extends \Twig\Loader\FilesystemLoader
{
	protected $safe_directories = array();

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * Constructor
	 *
	 * @param \phpbb\filesystem\filesystem_interface $filesystem
	 * @param string|array	$paths
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $filesystem, $paths = array())
	{
		$this->filesystem = $filesystem;

		parent::__construct($paths, __DIR__);
	}

	/**
	* Set safe directories
	*
	* @param array $directories Array of directories that are safe (empty to clear)
	* @return \Twig\Loader\FilesystemLoader
	*/
	public function setSafeDirectories($directories = array())
	{
		$this->safe_directories = array();

		if (!empty($directories))
		{
			foreach ($directories as $directory)
			{
				$this->addSafeDirectory($directory);
			}
		}

		return $this;
	}

	/**
	* Add safe directory
	*
	* @param string $directory Directory that should be added
	* @return \Twig\Loader\FilesystemLoader
	*/
	public function addSafeDirectory($directory)
	{
		$directory = $this->filesystem->realpath($directory);

		if ($directory !== false)
		{
			$this->safe_directories[] = $directory;
		}

		return $this;
	}

	/**
	* Get current safe directories
	*
	* @return array
	*/
	public function getSafeDirectories()
	{
		return $this->safe_directories;
	}

	/**
	* Override for parent::validateName()
	*
	* This is done because we added support for safe directories, and when Twig
	*	findTemplate() is called, validateName() is called first, which would
	*	always throw an exception if the file is outside of the configured
	*	template directories.
	*/
	protected function validateName($name)
	{
		return;
	}

	/**
	 * Adds a realpath call to fix a BC break in Twig 1.26 (https://github.com/twigphp/Twig/issues/2145)
	 *
	 * {@inheritdoc}
	 */
	public function addPath($path, $namespace = self::MAIN_NAMESPACE)
	{
		return parent::addPath($this->filesystem->realpath($path), $namespace);
	}

	/**
	 * Find the template
	 *
	 * Override for \Twig\Loader\FilesystemLoader::findTemplate
	 * to add support for loading from safe directories.
	 */
	protected function findTemplate($name, $throw = true)
	{
		$name = (string) $name;

		// normalize name
		$name = preg_replace('#/{2,}#', '/', strtr($name, '\\', '/'));

		// If this is in the cache we can skip the entire process below
		//	as it should have already been validated
		if (isset($this->cache[$name]))
		{
			return $this->cache[$name];
		}

		// First, find the template name. The override above of validateName
		//	causes the validateName process to be skipped for this call
		$file = parent::findTemplate($name, $throw);

		try
		{
			// Try validating the name (which may throw an exception)
			$this->validateName($name);
		}
		catch (\Twig\Error\LoaderError $e)
		{
			if (strpos($e->getRawMessage(), 'Looks like you try to load a template outside configured directories') === 0)
			{
				// Ok, so outside of the configured template directories, we
				//	can now check if we're within a "safe" directory

				// Find the real path of the directory the file is in
				$directory = $this->filesystem->realpath(dirname($file));

				if ($directory === false)
				{
					// Some sort of error finding the actual path, must throw the exception
					throw $e;
				}

				foreach ($this->safe_directories as $safe_directory)
				{
					if (strpos($directory, $safe_directory) === 0)
					{
						// The directory being loaded is below a directory
						// that is "safe". We're good to load it!
						return $file;
					}
				}
			}

			// Not within any safe directories
			throw $e;
		}

		// No exception from validateName, safe to load.
		return $file;
	}
}
