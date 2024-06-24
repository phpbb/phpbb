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

use PHPUnit\DbUnit\TestCase;

abstract class phpbb_database_test_case extends TestCase
{
	private static $already_connected;

	private $db_connections;

	protected $test_case_helpers;

	protected $fixture_xml_data;

	protected static $schema_file;

	protected static $phpbb_schema_copy;

	protected static $install_schema_file;

	/**
	 * @var \Doctrine\DBAL\Connection[]
	 */
	private $db_connections_doctrine;

	public function __construct($name = NULL, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->backupStaticAttributesExcludeList += [
			'SebastianBergmann\CodeCoverage\CodeCoverage' => ['instance'],
			'SebastianBergmann\CodeCoverage\Filter' => ['instance'],
			'SebastianBergmann\CodeCoverage\Util' => ['ignoredLines', 'templateMethods'],
			'SebastianBergmann\Timer\Timer' => ['startTimes'],
			'PHP_Token_Stream' => ['customTokens'],
			'PHP_Token_Stream_CachingFactory' => ['cache'],

			'phpbb_database_test_case' => ['already_connected'],
		];

		$this->db_connections = [];
		$this->db_connections_doctrine = [];
	}

	/**
	* @return array List of extensions that should be set up
	*/
	protected static function setup_extensions()
	{
		return array();
	}

	public static function setUpBeforeClass(): void
	{
		global $phpbb_root_path, $phpEx;

		$setup_extensions = static::setup_extensions();

		$finder = new \phpbb\finder\finder(null, false, $phpbb_root_path, $phpEx);
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

			$db = new \phpbb\db\driver\sqlite3();
			$doctrine = \phpbb\db\doctrine\connection_factory::get_connection(new phpbb_mock_config_php_file());
			$factory = new \phpbb\db\tools\factory();
			$db_tools = $factory->get($doctrine, true);

			$schema_generator = new \phpbb\db\migration\schema_generator($classes, new \phpbb\config\config(array()), $db, $db_tools, $phpbb_root_path, $phpEx, $table_prefix, self::get_core_tables());
			file_put_contents(self::$schema_file, json_encode($schema_generator->get_schema()));
		}

		copy(self::$schema_file, self::$install_schema_file);

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass(): void
	{
		if (file_exists(self::$install_schema_file))
		{
			unlink(self::$install_schema_file);
		}

		parent::tearDownAfterClass();
	}

	protected function tearDown(): void
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

		if (!empty($this->db_connections_doctrine))
		{
			foreach ($this->db_connections_doctrine as $db)
			{
				$db->close();
			}
		}
	}

	protected function setUp(): void
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

	/**
	 * Create xml data set for insertion into database
	 *
	 * @param string $path Path to fixture XML
	 * @return PHPUnit\DbUnit\DataSet\DefaultDataSet|PHPUnit\DbUnit\DataSet\XmlDataSet
	 */
	public function createXMLDataSet($path)
	{
		$this->fixture_xml_data = parent::createXMLDataSet($path);

		// Extend XML data set on MSSQL
		if (strpos($this->get_database_config()['dbms'], 'mssql') !== false)
		{
			$newXmlData = new PHPUnit\DbUnit\DataSet\DefaultDataSet([]);
			$db = $this->new_dbal();
			foreach ($this->fixture_xml_data as $key => $value)
			{
				/** @var PHPUnit\DbUnit\DataSet\DefaultTableMetaData $tableMetaData */
				$tableMetaData = $value->getTableMetaData();
				$columns = $tableMetaData->getColumns();
				$primaryKeys = $tableMetaData->getPrimaryKeys();

				$sql = "SELECT COLUMN_NAME AS identity_column
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE COLUMNPROPERTY(object_id(TABLE_SCHEMA + '.' + TABLE_NAME), COLUMN_NAME, 'IsIdentity') = 1
						AND TABLE_NAME = '$key'
					ORDER BY TABLE_NAME";
				$result = $db->sql_query($sql);
				$identity_columns = $db->sql_fetchrowset($result);
				$has_default_identity = false;
				$add_primary_keys = false;

				// Iterate over identity columns to check for missing primary
				// keys in data set and special identity column 'mssqlindex'
				// that might have been added when no default identity column
				// exists in the current table.
				foreach ($identity_columns as $column)
				{
					if (in_array($column['identity_column'], $columns) && !in_array($column['identity_column'], $primaryKeys))
					{
						$primaryKeys[] = $column['identity_column'];
						$add_primary_keys = true;
					}

					if ($column['identity_column'] === 'mssqlindex')
					{
						$has_default_identity = true;
						break;
					}
				}

				if ($has_default_identity || $add_primary_keys)
				{
					// Add default identity column to columns list
					if ($has_default_identity)
					{
						$columns[] = 'mssqlindex';
					}

					$newMetaData = new PHPUnit\DbUnit\DataSet\DefaultTableMetaData($key, $columns, $primaryKeys);
					$newTable = new PHPUnit\DbUnit\DataSet\DefaultTable($newMetaData);
					for ($i = 0; $i < $value->getRowCount(); $i++)
					{
						$dataRow = $value->getRow($i);
						if ($has_default_identity)
						{
							$dataRow['mssqlindex'] = $i + 1;
						}
						$newTable->addRow($dataRow);
					}
					$newXmlData->addTable($newTable);
				}
				else
				{
					$newXmlData->addTable($value);
				}
			}

			$this->fixture_xml_data = $newXmlData;
		}
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
			$manager->load_schema($this->new_dbal(), $this->new_doctrine_dbal());
			self::$already_connected = true;
		}

		return $this->createDefaultDBConnection($manager->get_pdo(), 'testdb');
	}

	public function new_dbal() : \phpbb\db\driver\driver_interface
	{
		$config = $this->get_database_config();

		/** @var \phpbb\db\driver\driver_interface $db */
		$db = new $config['dbms']();
		$db->sql_connect($config['dbhost'], $config['dbuser'], $config['dbpasswd'], $config['dbname'], $config['dbport']);

		$this->db_connections[] = $db;

		return $db;
	}

	public function new_doctrine_dbal(): \Doctrine\DBAL\Connection
	{
		$config = $this->get_database_config();

		$db = \phpbb\db\doctrine\connection_factory::get_connection_from_params($config['dbms'], $config['dbhost'], $config['dbuser'], $config['dbpasswd'], $config['dbname'], $config['dbport']);
		$this->db_connections_doctrine[] = $db;

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

	/** array_diff() does not corretly compare multidimensionsl arrays
	 *  This solution used for that https://www.codeproject.com/Questions/780780/PHP-Finding-differences-in-two-multidimensional-ar
	 */
	function array_diff_assoc_recursive($array1, $array2)
	{
		$difference = array();
		foreach ($array1 as $key => $value)
		{
			if (is_array($value))
			{
				if (!isset($array2[$key]))
				{
					$difference[$key] = $value;
				}
				else if (!is_array($array2[$key]))
				{
					$difference[$key] = $value;
				}
				else
				{
					$new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
					if (!empty($new_diff))
					{
						$difference[$key] = $new_diff;
					}
				}
			}
			else if (!isset($array2[$key]) || $array2[$key] != $value)
			{
				$difference[$key] = $value;
			}
		}
		return $difference;
	}

	public function assert_array_content_equals($one, $two)
	{
		// one-way comparison is not enough!
		if (count($this->array_diff_assoc_recursive($one, $two)) || count($this->array_diff_assoc_recursive($two, $one)))
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

	public static function get_core_tables() : array
	{
		global $phpbb_root_path, $table_prefix;

		static $core_tables = [];

		if (empty($core_tables))
		{
			$tables_yml_data = \Symfony\Component\Yaml\Yaml::parseFile($phpbb_root_path . '/config/default/container/tables.yml');

			foreach ($tables_yml_data['parameters'] as $parameter => $table)
			{
				$core_tables[str_replace('tables.', '', $parameter)] = str_replace('%core.table_prefix%', $table_prefix, $table);
			}
		}

		return $core_tables;
	}
}
