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

	/**
	* This should only be called once before the tests are run.
	* This is used to copy the fixtures to the phpBB install
	*/
	public function copy_ext_fixtures($fixtures_dir, $fixtures)
	{
		global $phpbb_root_path;

		if (file_exists($phpbb_root_path . 'ext/'))
		{
			// First, move any extensions setup on the board to a temp directory
			$this->copy_dir($phpbb_root_path . 'ext/', $phpbb_root_path . 'store/temp_ext/');

			// Then empty the ext/ directory on the board (for accurate test cases)
			$this->empty_dir($phpbb_root_path . 'ext/');
		}

		// Copy our ext/ files from the test case to the board
		foreach ($fixtures as $fixture)
		{
			$this->copy_dir($fixtures_dir . $fixture, $phpbb_root_path . 'ext/' . $fixture);
		}
	}

	/**
	* This should only be called once after the tests are run.
	* This is used to remove the fixtures from the phpBB install
	*/
	public function restore_original_ext_dir()
	{
		global $phpbb_root_path;

		// Remove all of the files we copied from test ext -> board ext
		$this->empty_dir($phpbb_root_path . 'ext/');

		// Copy back the board installed extensions from the temp directory
		if (file_exists($phpbb_root_path . 'store/temp_ext/'))
		{
			$this->copy_dir($phpbb_root_path . 'store/temp_ext/', $phpbb_root_path . 'ext/');

			// Remove all of the files we copied from board ext -> temp_ext
			$this->empty_dir($phpbb_root_path . 'store/temp_ext/');
		}

		if (file_exists($phpbb_root_path . 'store/temp_ext/'))
		{
			$this->empty_dir($phpbb_root_path . 'store/temp_ext/');
		}
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
		// PHP bug #55124 (fixed in 5.4.0)
		$path = str_replace('/./', '/', $path);

		mkdir($path, 0777, true);
	}

	static public function get_test_config()
	{
		$config = array();

		if (extension_loaded('sqlite') && version_compare(PHPUnit_Runner_Version::id(), '3.4.15', '>='))
		{
			$config = array_merge($config, array(
				'dbms'		=> '\phpbb\db\driver\sqlite',
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

			if (!function_exists('phpbb_convert_30_dbms_to_31'))
			{
				require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
			}

			$config = array_merge($config, array(
				'dbms'		=> phpbb_convert_30_dbms_to_31($dbms),
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

			if (isset($phpbb_redis_host))
			{
				$config['redis_host'] = $phpbb_redis_host;
			}
			if (isset($phpbb_redis_port))
			{
				$config['redis_port'] = $phpbb_redis_port;
			}
		}

		if (isset($_SERVER['PHPBB_TEST_DBMS']))
		{
			if (!function_exists('phpbb_convert_30_dbms_to_31'))
			{
				require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
			}

			$config = array_merge($config, array(
				'dbms'		=> isset($_SERVER['PHPBB_TEST_DBMS']) ? phpbb_convert_30_dbms_to_31($_SERVER['PHPBB_TEST_DBMS']) : '',
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

		if (isset($_SERVER['PHPBB_TEST_REDIS_HOST']))
		{
			$config['redis_host'] = $_SERVER['PHPBB_TEST_REDIS_HOST'];
		}

		if (isset($_SERVER['PHPBB_TEST_REDIS_PORT']))
		{
			$config['redis_port'] = $_SERVER['PHPBB_TEST_REDIS_PORT'];
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
