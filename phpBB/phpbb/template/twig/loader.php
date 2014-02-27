<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\template\twig;

/**
* Twig Template loader
* @package phpBB3
*/
class loader extends \Twig_Loader_Filesystem
{
	protected $safe_directories = array();

	/**
	* Set safe directories
	*
	* @param array $directories Array of directories that are safe (empty to clear)
	* @return Twig_Loader_Filesystem
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
	* @return Twig_Loader_Filesystem
	*/
	public function addSafeDirectory($directory)
	{
		$directory = phpbb_realpath($directory);

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
	* Find the template
	*
	* Override for Twig_Loader_Filesystem::findTemplate to add support
	*	for loading from safe directories.
	*/
	protected function findTemplate($name)
	{
		$name = (string) $name;

		// normalize name
		$name = preg_replace('#/{2,}#', '/', strtr($name, '\\', '/'));

		// If this is in the cache we can skip the entire process below
		//	as it should have already been validated
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		// First, find the template name. The override above of validateName
		//	causes the validateName process to be skipped for this call
		$file = parent::findTemplate($name);

		try
		{
			// Try validating the name (which may throw an exception)
			parent::validateName($name);
		}
		catch (Twig_Error_Loader $e)
		{
			if (strpos($e->getRawMessage(), 'Looks like you try to load a template outside configured directories') === 0)
			{
				// Ok, so outside of the configured template directories, we
				//	can now check if we're within a "safe" directory

				// Find the real path of the directory the file is in
				$directory = phpbb_realpath(dirname($file));

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
