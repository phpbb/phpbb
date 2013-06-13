<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/db/db_tools.php';

class phpbb_extension_metadata_manager_test extends phpbb_database_test_case
{
	protected $class_loader;
	protected $extension_manager;

	protected $cache;
	protected $config;
	protected $db;
	protected $phpbb_root_path;
	protected $phpEx;
	protected $template;
	protected $user;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/extensions.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->cache = new phpbb_mock_cache();
		$this->config = new phpbb_config(array(
			'version'		=> '3.1.0',
		));
		$this->db = $this->new_dbal();
		$this->db_tools = new phpbb_db_tools($this->db);
		$this->phpbb_root_path = dirname(__FILE__) . '/';
		$this->phpEx = 'php';
		$this->user = new phpbb_user();
		$this->table_prefix = 'phpbb_';

		$this->template = new phpbb_template(
			$this->phpbb_root_path,
			$this->phpEx,
			$this->config,
			$this->user,
			new phpbb_style_resource_locator(),
			new phpbb_template_context()
		);

		$this->migrator = new phpbb_db_migrator(
			$this->config,
			$this->db,
			$this->db_tools,
			'phpbb_migrations',
			$this->phpbb_root_path,
			'php',
			$this->table_prefix,
			array()
		);
		$container = new phpbb_mock_container_builder();
		$container->set('migrator', $migrator);

		$this->extension_manager = new phpbb_extension_manager(
			$container,
			$this->db,
			$this->config,
			new phpbb_filesystem(),
			'phpbb_ext',
			$this->phpbb_root_path,
			$this->phpEx,
			$this->cache
		);
	}

	// Should fail from missing composer.json
	public function test_bar()
	{
		$ext_name = 'bar';

		$manager = $this->get_metadata_manager($ext_name);

		try
		{
			$manager->get_metadata();
		}
		catch(phpbb_extension_exception $e){}

		$this->assertEquals((string) $e, 'The required file does not exist: ' . $this->phpbb_root_path . $this->extension_manager->get_extension_path($ext_name) . 'composer.json');
	}

	// Should be the same as a direct json_decode of the composer.json file
	public function test_foo()
	{
		$ext_name = 'foo';

		$manager = $this->get_metadata_manager($ext_name);

		try
		{
			$metadata = $manager->get_metadata();
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}

		$json = json_decode(file_get_contents($this->phpbb_root_path . 'ext/foo/composer.json'), true);

		$this->assertEquals($metadata, $json);
	}

	public function test_validator_non_existant()
	{
		$ext_name = 'validator';

		$manager = $this->get_metadata_manager($ext_name);

		// Non-existant data
		try
		{
			$manager->validate('name');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Required meta field \'name\' has not been set.');
		}

		try
		{
			$manager->validate('type');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Required meta field \'type\' has not been set.');
		}

		try
		{
			$manager->validate('licence');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Required meta field \'licence\' has not been set.');
		}

		try
		{
			$manager->validate('version');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Required meta field \'version\' has not been set.');
		}

		try
		{
			$manager->validate_authors();

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Required meta field \'authors\' has not been set.');
		}

		$manager->merge_metadata(array(
			'authors'	=> array(
				array(),
			),
		));

		try
		{
			$manager->validate_authors();

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Required meta field \'author name\' has not been set.');
		}
	}


	public function test_validator_invalid()
	{
		$ext_name = 'validator';

		$manager = $this->get_metadata_manager($ext_name);

		// Invalid data
		$manager->set_metadata(array(
			'name'		=> 'asdf',
			'type'		=> 'asdf',
			'licence'	=> '',
			'version'	=> '',
		));

		try
		{
			$manager->validate('name');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Meta field \'name\' is invalid.');
		}

		try
		{
			$manager->validate('type');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Meta field \'type\' is invalid.');
		}

		try
		{
			$manager->validate('licence');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Meta field \'licence\' is invalid.');
		}

		try
		{
			$manager->validate('version');

			$this->fail('Exception not triggered');
		}
		catch(phpbb_extension_exception $e)
		{
			$this->assertEquals((string) $e, 'Meta field \'version\' is invalid.');
		}
	}

	public function test_validator_valid()
	{
		$ext_name = 'validator';

		$manager = $this->get_metadata_manager($ext_name);

		// Valid data
		$manager->set_metadata(array(
			'name'		=> 'test/foo',
			'type'		=> 'phpbb3-extension',
			'licence'	=> 'GPL v2',
			'version'	=> '1.0.0',
		));

		try
		{
			$this->assertEquals(true, $manager->validate('enable'));
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}
	}


	public function test_validator_requirements()
	{
		$ext_name = 'validator';

		$manager = $this->get_metadata_manager($ext_name);
		// Too high of requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '10.0.0',
				'phpbb'		=> '3.2.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(false, $manager->validate_require_php());
			$this->assertEquals(false, $manager->validate_require_phpbb());
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}


		// Too high of requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '5.3.0',
				'phpbb'		=> '3.1.0-beta', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}


		// Too high of requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '>' . phpversion(),
				'phpbb'		=> '>3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(false, $manager->validate_require_php());
			$this->assertEquals(false, $manager->validate_require_phpbb());
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}


		// Too high of current install
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '<' . phpversion(),
				'phpbb'		=> '<3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(false, $manager->validate_require_php());
			$this->assertEquals(false, $manager->validate_require_phpbb());
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}


		// Matching requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> phpversion(),
				'phpbb'		=> '3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}


		// Matching requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '>=' . phpversion(),
				'phpbb'		=> '>=3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}


		// Matching requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '<=' . phpversion(),
				'phpbb'		=> '<=3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(phpbb_extension_exception $e)
		{
			$this->fail($e);
		}
	}

	/**
	* Get an instance of the metadata manager
	*
	* @param string $ext_name
	* @return phpbb_mock_metadata_manager
	*/
	private function get_metadata_manager($ext_name)
	{
		return new phpbb_mock_metadata_manager(
			$ext_name,
			$this->config,
			$this->extension_manager,
			$this->template,
			$this->phpbb_root_path
		);
	}
}
