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

/**
* This file creates new schema files for every database.
* The filenames will be prefixed with an underscore to not overwrite the current schema files.
*
* If you overwrite the original schema files please make sure you save the file with UNIX linefeeds.
*/

use Symfony\Component\Yaml\Yaml;

$schema_path = __DIR__ . '/../install/schemas/';
$supported_dbms = array(
	'mssql',
	'mysql_41',
	'oracle',
	'postgres',
	'sqlite',
);
$table_prefix = 'phpbb_';

if (!is_writable($schema_path))
{
	die('Schema path not writable');
}

define('IN_PHPBB', true);
$phpbb_root_path = __DIR__ . '/../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'vendor/autoload.php');
include($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();

$finder = new \phpbb\finder\finder(null, false, $phpbb_root_path, $phpEx);
$classes = $finder->core_path('phpbb/')
	->directory('/db/migration/data')
	->get_classes();

$db = new \phpbb\db\driver\sqlite3();

// The database is not used by db\tools when we generate the schema but it requires a doctrine DBAL object
// which always tries to connect to the database in the constructor. Which means if we want a valid doctrine
// Connection object that is not connected to any database we have to do that.
$ref = new ReflectionClass(\Doctrine\DBAL\Connection::class);
$db_doctrine = $ref->newInstanceWithoutConstructor();

// Create a mock SchemaManager that returns an empty array for listTableNames()
$schema_manager_mock = new class extends \Doctrine\DBAL\Schema\SqliteSchemaManager {
	public function listTableNames() {
		return [];
	}

	public function __construct()
	{
		//parent::__construct($connection, $platform);
	}
};

$schema_manager_factory_mock = new class($schema_manager_mock) implements \Doctrine\DBAL\Schema\SchemaManagerFactory {
	private $schema_manager;
	public function __construct($schema_manager) {
		$this->schema_manager = $schema_manager;
	}
	public function createSchemaManager($connection): \Doctrine\DBAL\Schema\AbstractSchemaManager
	{
		return $this->schema_manager;
	}
};

$schema_manager_factory = new \ReflectionProperty($db_doctrine, 'schemaManagerFactory');
$schema_manager_factory->setAccessible(true);
$schema_manager_factory->setValue($db_doctrine, new $schema_manager_factory_mock($schema_manager_mock));

$factory = new \phpbb\db\tools\factory();
$db_tools = $factory->get($db_doctrine, true);
$db_tools->set_table_prefix($table_prefix);

$tables_data = Yaml::parseFile($phpbb_root_path . '/config/default/container/tables.yml', Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
$tables = [];

foreach ($tables_data['parameters'] as $parameter => $table)
{
	$tables[str_replace('tables.', '', $parameter)] = str_replace('%core.table_prefix%', $table_prefix, $table);
}

$schema_generator = new \phpbb\db\migration\schema_generator($classes, new \phpbb\config\config(array()), $db, $db_tools, $phpbb_root_path, $phpEx, $table_prefix, $tables);
$schema_data = $schema_generator->get_schema();

$fp = fopen($schema_path . 'schema.json', 'wb');
fwrite($fp, json_encode($schema_data, JSON_PRETTY_PRINT));
fclose($fp);

echo 'Successfully created schema file';
