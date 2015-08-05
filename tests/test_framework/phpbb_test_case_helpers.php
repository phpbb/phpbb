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

use Symfony\Component\DependencyInjection\ContainerInterface;

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


		if (extension_loaded('sqlite3'))
		{
			$config = array_merge($config, array(
				'dbms'		=> 'phpbb\db\driver\sqlite3',
				'dbhost'	=> dirname(__FILE__) . '/../phpbb_unit_tests.sqlite3', // filename
				'dbport'	=> '',
				'dbname'	=> '',
				'dbuser'	=> '',
				'dbpasswd'	=> '',
			));
		}
		else if (extension_loaded('sqlite'))
		{
			$config = array_merge($config, array(
				'dbms'		=> 'phpbb\db\driver\sqlite',
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

		$config_php_file = new \phpbb\config_php_file('', '');

		if (file_exists($test_config))
		{
			$config_php_file->set_config_file($test_config);
			extract($config_php_file->get_all());

			$config = array_merge($config, array(
				'dbms'		=> $config_php_file->convert_30_dbms_to_31($dbms),
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

			if (isset($fulltext_sphinx_id))
			{
				$config['fulltext_sphinx_id'] = $fulltext_sphinx_id;
			}
		}

		if (isset($_SERVER['PHPBB_TEST_DBMS']))
		{
			$config = array_merge($config, array(
				'dbms'		=> isset($_SERVER['PHPBB_TEST_DBMS']) ? $config_php_file->convert_30_dbms_to_31($_SERVER['PHPBB_TEST_DBMS']) : '',
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

	/**
	* Set working instances of the text_formatter.* services
	*
	* If no container is passed, the global $phpbb_container will be used and/or
	* created if applicable
	*
	* @param  ContainerInterface $container   Service container
	* @param  string             $fixture     Path to the XML fixture
	* @param  string             $styles_path Path to the styles dir
	* @return ContainerInterface
	*/
	public function set_s9e_services(ContainerInterface $container = null, $fixture = null, $styles_path = null)
	{
		static $first_run;
		global $phpbb_container, $phpbb_dispatcher, $phpbb_root_path, $phpEx, $user;

		$cache_dir = __DIR__ . '/../tmp/';

		// Remove old cache files on first run
		if (!isset($first_run))
		{
			$first_run = 1;

			array_map('unlink', array_merge(
				glob($cache_dir . 'data_s9e_*'),
				glob($cache_dir . 's9e_*')
			));
		}

		if (!isset($container))
		{
			if (!isset($phpbb_container))
			{
				$phpbb_container = new phpbb_mock_container_builder;
			}

			$container = $phpbb_container;
		}

		if (!isset($fixture))
		{
			$fixture = __DIR__ . '/../text_formatter/s9e/fixtures/default_formatting.xml';
		}

		if (!isset($styles_path))
		{
			$styles_path = $phpbb_root_path . 'styles/';
		}

		$dataset = new DOMDocument;
		$dataset->load($fixture);

		$tables = array(
			'phpbb_bbcodes' => array(),
			'phpbb_smilies' => array(),
			'phpbb_styles'  => array(),
			'phpbb_words'   => array()
		);
		foreach ($dataset->getElementsByTagName('table') as $table)
		{
			$name = $table->getAttribute('name');
			$columns = array();

			foreach ($table->getElementsByTagName('column') as $column)
			{
				$columns[] = $column->textContent;
			}

			foreach ($table->getElementsByTagName('row') as $row)
			{
				$values = array();

				foreach ($row->getElementsByTagName('value') as $value)
				{
					$values[] = $value->textContent;
				}

				$tables[$name][] = array_combine($columns, $values);
			}
		}

		// Set up a default style if there's none set
		if (empty($tables['phpbb_styles']))
		{
			$tables['phpbb_styles'][] = array(
				'style_id' => 1,
				'style_path' => 'prosilver',
				'bbcode_bitfield' => 'kNg='
			);
		}

		// Mock the DAL, make it return data from the fixture
		$mb = $this->test_case->getMockBuilder('phpbb\\textformatter\\data_access');
		$mb->setMethods(array('get_bbcodes', 'get_censored_words', 'get_smilies', 'get_styles'));
		$mb->setConstructorArgs(array(
			$this->test_case->getMock('phpbb\\db\\driver\\driver'),
			'phpbb_bbcodes',
			'phpbb_smilies',
			'phpbb_styles',
			'phpbb_words',
			$styles_path
		));

		$dal = $mb->getMock();
		$container->set('text_formatter.data_access', $dal);

		$dal->expects($this->test_case->any())
		    ->method('get_bbcodes')
		    ->will($this->test_case->returnValue($tables['phpbb_bbcodes']));
		$dal->expects($this->test_case->any())
		    ->method('get_smilies')
		    ->will($this->test_case->returnValue($tables['phpbb_smilies']));
		$dal->expects($this->test_case->any())
		    ->method('get_styles')
		    ->will($this->test_case->returnValue($tables['phpbb_styles']));
		$dal->expects($this->test_case->any())
		    ->method('get_censored_words')
		    ->will($this->test_case->returnValue($tables['phpbb_words']));

		// Cache the parser and renderer with a key based on this method's arguments
		$cache = new \phpbb\cache\driver\file($cache_dir);
		$prefix = '_s9e_' . md5(serialize(func_get_args()));
		$cache_key_parser = $prefix . '_parser';
		$cache_key_renderer = $prefix . '_renderer';
		$container->set('cache.driver', $cache);

		if (!$container->isFrozen())
		{
			$container->setParameter('cache.dir', $cache_dir);
		}

		// Create a path_helper
		if (!$container->has('path_helper') || $container->getDefinition('path_helper')->isSynthetic())
		{
			$path_helper = $this->test_case->getMockBuilder('phpbb\\path_helper')
				->disableOriginalConstructor()
				->setMethods(array('get_web_root_path'))
				->getMock();
			$path_helper->expects($this->test_case->any())
				->method('get_web_root_path')
				->will($this->test_case->returnValue('phpBB/'));

			$container->set('path_helper', $path_helper);
		}
		else
		{
			$path_helper = $container->get('path_helper');
		}

		// Create an event dispatcher
		if ($container->has('dispatcher'))
		{
			$dispatcher = $container->get('dispatcher');
		}
		else if (isset($phpbb_dispatcher))
		{
			$dispatcher = $phpbb_dispatcher;
		}
		else
		{
			$dispatcher = new phpbb_mock_event_dispatcher;
		}
		if (!isset($phpbb_dispatcher))
		{
			$phpbb_dispatcher = $dispatcher;
		}

		// Set up the a minimum config
		if ($container->has('config'))
		{
			$config = $container->get('config');
		}
		else
		{
			$config = new \phpbb\config\config(array());
		}
		$default_config = array(
			'allow_nocensors' => false,
			'allowed_schemes_links' => 'http,https,ftp',
			'smilies_path' => 'images/smilies',
		);
		foreach ($default_config as $config_name => $config_value)
		{
			if (!isset($config[$config_name]))
			{
				$config[$config_name] = $config_value;
			}
		}

		// Create and register the text_formatter.s9e.factory service
		$factory = new \phpbb\textformatter\s9e\factory($dal, $cache, $dispatcher, $config, $cache_dir, $cache_key_parser, $cache_key_renderer);
		$container->set('text_formatter.s9e.factory', $factory);

		// Create a user if none was provided, and add the common lang strings
		if ($container->has('user'))
		{
			$user = $container->get('user');
		}
		else
		{
			$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
			$lang = new \phpbb\language\language($lang_loader);

			$user = $this->test_case->getMockBuilder('\phpbb\user')
					->setConstructorArgs(array($lang, '\phpbb\datetime'))
					->setMethods(array('format_date'))
					->getMock();
			$user->expects($this->test_case->any())
			     ->method('format_date')
			     ->will($this->test_case->returnCallback(__CLASS__ . '::format_date'));

			$user->date_format = 'Y-m-d H:i:s';
			$user->optionset('viewcensors', true);
			$user->optionset('viewflash', true);
			$user->optionset('viewimg', true);
			$user->optionset('viewsmilies', true);
			$user->timezone = new \DateTimeZone('UTC');
			$container->set('user', $user);
		}
		$user->add_lang('common');

		if (!isset($user->style))
		{
			$user->style = array('style_id' => 1);
		}

		// Create and register a quote_helper
		$quote_helper = new \phpbb\textformatter\s9e\quote_helper(
			$container->get('user'),
			$phpbb_root_path,
			$phpEx
		);
		$container->set('text_formatter.s9e.quote_helper', $quote_helper);

		// Create and register the text_formatter.s9e.parser service and its alias
		$parser = new \phpbb\textformatter\s9e\parser(
			$cache,
			$cache_key_parser,
			$factory,
			$dispatcher
		);
		$container->set('text_formatter.parser', $parser);
		$container->set('text_formatter.s9e.parser', $parser);

		// Create and register the text_formatter.s9e.renderer service and its alias
		$renderer = new \phpbb\textformatter\s9e\renderer(
			$cache,
			$cache_dir,
			$cache_key_renderer,
			$factory,
			$dispatcher
		);

		// Calls configured in services.yml
		$auth = ($container->has('auth')) ? $container->get('auth') : new \phpbb\auth\auth;
		$renderer->configure_quote_helper($quote_helper);
		$renderer->configure_smilies_path($config, $path_helper);
		$renderer->configure_user($user, $config, $auth);

		$container->set('text_formatter.renderer', $renderer);
		$container->set('text_formatter.s9e.renderer', $renderer);

		// Create and register the text_formatter.s9e.utils service and its alias
		$utils = new \phpbb\textformatter\s9e\utils;
		$container->set('text_formatter.utils', $utils);
		$container->set('text_formatter.s9e.utils', $utils);

		return $container;
	}

	/**
	* Mocked replacement for \phpbb\user::format_date()
	*
	* @param  integer $gmepoch unix timestamp
	* @return string
	*/
	static public function format_date($gmepoch)
	{
		return gmdate('Y-m-d H:i:s', $gmepoch);
	}
}
