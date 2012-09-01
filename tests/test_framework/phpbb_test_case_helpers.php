<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_test_case_helpers
{
	protected $expectedTriggerError = false;

	protected $test_case;

	public function __construct($test_case)
	{
		$this->test_case = $test_case;
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$exceptionName = '';
		switch ($errno)
		{
			case E_NOTICE:
			case E_STRICT:
				PHPUnit_Framework_Error_Notice::$enabled = true;
				$exceptionName = 'PHPUnit_Framework_Error_Notice';
			break;

			case E_WARNING:
				PHPUnit_Framework_Error_Warning::$enabled = true;
				$exceptionName = 'PHPUnit_Framework_Error_Warning';
			break;

			default:
				$exceptionName = 'PHPUnit_Framework_Error';
			break;
		}
		$this->expectedTriggerError = true;
		$this->test_case->setExpectedException($exceptionName, (string) $message, $errno);
	}

	public function makedirs($path)
	{
		mkdir($path, 0777, true);
	}

	static public function get_test_config()
	{
		$config = array();

		if (extension_loaded('sqlite') && version_compare(PHPUnit_Runner_Version::id(), '3.4.15', '>='))
		{
			$config = array_merge($config, array(
				'dbms'		=> 'sqlite',
				'dbhost'	=> dirname(__FILE__) . '/../phpbb_unit_tests.sqlite2', // filename
				'dbport'	=> '',
				'dbname'	=> '',
				'dbuser'	=> '',
				'dbpasswd'	=> '',
			));
		}

		if (isset($_SERVER['PHPBB_TEST_CONFIG']))
		{
			// Could be an absolute path
			$test_config = $_SERVER['PHPBB_TEST_CONFIG'];
		}
		else
		{
			$test_config = dirname(__FILE__) . '/../test_config.php';
		}

		if (file_exists($test_config))
		{
			include($test_config);

			$config = array_merge($config, array(
				'dbms'		=> $dbms,
				'dbhost'	=> $dbhost,
				'dbport'	=> $dbport,
				'dbname'	=> $dbname,
				'dbuser'	=> $dbuser,
				'dbpasswd'	=> $dbpasswd,
				'custom_dsn'	=> isset($custom_dsn) ? $custom_dsn : '',
			));

			if (isset($phpbb_functional_url))
			{
				$config['phpbb_functional_url'] = $phpbb_functional_url;
			}
		}

		if (isset($_SERVER['PHPBB_TEST_DBMS']))
		{
			$config = array_merge($config, array(
				'dbms'		=> isset($_SERVER['PHPBB_TEST_DBMS']) ? $_SERVER['PHPBB_TEST_DBMS'] : '',
				'dbhost'	=> isset($_SERVER['PHPBB_TEST_DBHOST']) ? $_SERVER['PHPBB_TEST_DBHOST'] : '',
				'dbport'	=> isset($_SERVER['PHPBB_TEST_DBPORT']) ? $_SERVER['PHPBB_TEST_DBPORT'] : '',
				'dbname'	=> isset($_SERVER['PHPBB_TEST_DBNAME']) ? $_SERVER['PHPBB_TEST_DBNAME'] : '',
				'dbuser'	=> isset($_SERVER['PHPBB_TEST_DBUSER']) ? $_SERVER['PHPBB_TEST_DBUSER'] : '',
				'dbpasswd'	=> isset($_SERVER['PHPBB_TEST_DBPASSWD']) ? $_SERVER['PHPBB_TEST_DBPASSWD'] : '',
				'custom_dsn'	=> isset($_SERVER['PHPBB_TEST_CUSTOM_DSN']) ? $_SERVER['PHPBB_TEST_CUSTOM_DSN'] : '',
			));
		}

		if (isset($_SERVER['PHPBB_FUNCTIONAL_URL']))
		{
			$config = array_merge($config, array(
				'phpbb_functional_url'	=> isset($_SERVER['PHPBB_FUNCTIONAL_URL']) ? $_SERVER['PHPBB_FUNCTIONAL_URL'] : '',
			));
		}

		return $config;
	}

	/**
	* Recursive directory copying function
	*
	* @param string $source
	* @param string $dest
	* @return array list of files copied
	*/
	public function copy_dir($source, $dest)
	{
		$source = (substr($source, -1) == '/') ? $source : $source . '/';
		$dest = (substr($dest, -1) == '/') ? $dest : $dest . '/';

		$copied_files = array();

		if (!is_dir($dest))
		{
			$this->makedirs($dest);
		}

		$files = scandir($source);
		foreach ($files as $file)
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}

			if (is_dir($source . $file))
			{
				$created_dir = false;
				if (!is_dir($dest . $file))
				{
					$created_dir = true;
					$this->makedirs($dest . $file);
				}

				$copied_files = array_merge($copied_files, self::copy_dir($source . $file, $dest . $file));

				if ($created_dir)
				{
					$copied_files[] = $dest . $file;
				}
			}
			else
			{
				if (!file_exists($dest . $file))
				{
					copy($source . $file, $dest . $file);

					$copied_files[] = $dest . $file;
				}
			}
		}

		return $copied_files;
	}

	/**
	* Remove files/directories that are listed in an array
	* Designed for use with $this->copy_dir()
	*
	* @param array $file_list
	*/
	public function remove_files($file_list)
	{
		foreach ($file_list as $file)
		{
			if (is_dir($file))
			{
				rmdir($file);
			}
			else
			{
				unlink($file);
			}
		}
	}

	/**
	* Empty directory (remove any subdirectories/files below)
	*
	* @param array $file_list
	*/
	public function empty_dir($path)
	{
		$path = (substr($path, -1) == '/') ? $path : $path . '/';

		$files = scandir($path);
		foreach ($files as $file)
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}

			if (is_dir($path . $file))
			{
				$this->empty_dir($path . $file);

				rmdir($path . $file);
			}
			else
			{
				unlink($path . $file);
			}
		}
	}
}
