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

abstract class phpbb_database_test_case extends PHPUnit_Extensions_Database_TestCase
{
	static private $already_connected;

	private $db_connections;

	protected $test_case_helpers;

	protected $fixture_xml_data;

	static protected $schema_file;

	static protected $phpbb_schema_copy;

	static protected $install_schema_file;

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->backupStaticAttributesBlacklist += array(
			'PHP_CodeCoverage' => array('instance'),
			'PHP_CodeCoverage_Filter' => array('instance'),
			'PHP_CodeCoverage_Util' => array('ignoredLines', 'templateMethods'),
			'PHP_Timer' => array('startTimes',),
			'PHP_Token_Stream' => array('customTokens'),
			'PHP_Token_Stream_CachingFactory' => array('cache'),

			'phpbb_database_test_case' => array('already_connected'),
		);

		$this->db_connections = array();
	}

	/**
	* @return array List of extensions that should be set up
	*/
	static protected function setup_extensions()
	{
		return array();
	}

	static public function setUpBeforeClass()
	{
		global $phpbb_root_path, $phpEx;

		$setup_extensions = static::setup_extensions();

		$finder = new \phpbb\finder(new \phpbb\filesystem\filesystem(), $phpbb_root_path, null, $phpEx);
		$finder->core_path('phpbb/db/migration/data/');
		if (!empty($setup_extensions))
		{
			$finder->set_extensions($setup_extensions)
				->extension_directory('/migrations');
		}
		$classes = $finder->get_classes();

		$schema_sha1 = sha1(serialize($classes));
		self::$schema_file = __DIR__ . '/../tmp/' . $schema_sha1 . '.json';
		self::$install_schema_file = __DIR__ . '/../../phpBB/install/schemas/schema.json';

		if (!file_exists(self::$schema_file))
		{

			global $table_prefix;

			$db = new \phpbb\db\driver\sqlite();
			$factory = new \phpbb\db\tools\factory();
			$db_tools = $factory->get($db, true);

			$schema_generator = new \phpbb\db\migration\schema_generator($classes, new \phpbb\config\config(array()), $db, $db_tools, $phpbb_root_path, $phpEx, $table_prefix);
			file_put_contents(self::$schema_file, json_encode($schema_generator->get_schema()));
		}

		copy(self::$schema_file, self::$install_schema_file);

		parent::setUpBeforeClass();
	}

	static public function tearDownAfterClass()
	{
		if (file_exists(self::$install_schema_file))
		{
			unlink(self::$install_schema_file);
		}

		parent::tearDownAfterClass();
	}

	protected function tearDown()
	{
		parent::tearDown();

		// Close all database connections from this test
		if (!empty($this->db_connections))
		{
			foreach ($this->db_connections as $db)
			{
				$db->sql_close();
			}
		}
	}

	protected function setUp()
	{
		parent::setUp();

		// Resynchronise tables if a fixture was loaded
		if (isset($this->fixture_xml_data))
		{
			$config = $this->get_database_config();
			$manager = $this->create_connection_manager($config);
			$manager->connect();
			$manager->post_setup_synchronisation($this->fixture_xml_data);
		}
	}

	/**
	* Performs synchronisations for a given table/column set on the database
	*
	* @param	array	$table_column_map		Information about the tables/columns to synchronise
	*
	* @return null
	*/
	protected function database_synchronisation($table_column_map)
	{
		$config = $this->get_database_config();
		$manager = $this->create_connection_manager($config);
		$manager->connect();
		$manager->database_synchronisation($table_column_map);
	}

	public function createXMLDataSet($path)
	{
		$this->fixture_xml_data = parent::createXMLDataSet($path);
		return $this->fixture_xml_data;
	}

	public function get_test_case_helpers()
	{
		if (!$this->test_case_helpers)
		{
			$this->test_case_helpers = new phpbb_test_case_helpers($this);
		}

		return $this->test_case_helpers;
	}

	public function get_database_config()
	{
		$config = phpbb_test_case_helpers::get_test_config();

		if (!isset($config['dbms']))
		{
			$this->markTestSkipped('Missing test_config.php: See first error.');
		}

		return $config;
	}

	public function getConnection()
	{
		$config = $this->get_database_config();

		$manager = $this->create_connection_manager($config);

		if (!self::$already_connected)
		{
			$manager->recreate_db();
		}

		$manager->connect();

		if (!self::$already_connected)
		{
			$manager->load_schema($this->new_dbal());
			self::$already_connected = true;
		}

		return $this->createDefaultDBConnection($manager->get_pdo(), 'testdb');
	}

	public function new_dbal()
	{
		$config = $this->get_database_config();

		$db = new $config['dbms']();
		$db->sql_connect($config['dbhost'], $config['dbuser'], $config['dbpasswd'], $config['dbname'], $config['dbport']);

		$this->db_connections[] = $db;

		return $db;
	}

	public function assertSqlResultEquals($expected, $sql, $message = '')
	{
		$db = $this->new_dbal();

		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$this->assertEquals($expected, $rows, $message);
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$this->get_test_case_helpers()->setExpectedTriggerError($errno, $message);
	}

	protected function create_connection_manager($config)
	{
		return new phpbb_database_test_connection_manager($config);
	}

	public function assert_array_content_equals($one, $two)
	{
		// http://stackoverflow.com/questions/3838288/phpunit-assert-two-arrays-are-equal-but-order-of-elements-not-important
		// but one array_diff is not enough!
		if (sizeof(array_diff($one, $two)) || sizeof(array_diff($two, $one)))
		{
			// get a nice error message
			$this->assertEquals($one, $two);
		}
		else
		{
			// increase assertion count
			$this->assertTrue(true);
		}
	}
}
