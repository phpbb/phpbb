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
		$this->config = new \phpbb\config\config(array(
			'version'		=> '3.1.0',
		));
		$this->db = $this->new_dbal();
		$this->db_tools = new \phpbb\db\tools($this->db);
		$this->phpbb_root_path = dirname(__FILE__) . '/';
		$this->phpEx = 'php';
		$this->user = new \phpbb\user();
		$this->table_prefix = 'phpbb_';

		$this->template = new \phpbb\template\twig\twig(
			new \phpbb\path_helper(
				new \phpbb\symfony_request(
					new phpbb_mock_request()
				),
				new \phpbb\filesystem(),
				$this->phpbb_root_path,
				$this->phpEx
			),
			$this->config,
			$this->user,
			new \phpbb\template\context()
		);

		$this->migrator = new \phpbb\db\migrator(
			$this->config,
			$this->db,
			$this->db_tools,
			'phpbb_migrations',
			$this->phpbb_root_path,
			'php',
			$this->table_prefix,
			array(),
			new \phpbb\db\migration\helper()
		);
		$container = new phpbb_mock_container_builder();
		$container->set('migrator', $migrator);

		$this->extension_manager = new \phpbb\extension\manager(
			$container,
			$this->db,
			$this->config,
			new \phpbb\filesystem(),
			$this->user,
			'phpbb_ext',
			$this->phpbb_root_path,
			$this->phpEx,
			$this->cache
		);
	}

	// Should fail from missing composer.json
	public function test_bar()
	{
		$ext_name = 'vendor3/bar';

		$manager = $this->get_metadata_manager($ext_name);

		try
		{
			$manager->get_metadata();
		}
		catch(\phpbb\extension\exception $e){}

		$this->assertEquals((string) $e, $this->user->lang('FILE_NOT_FOUND', $this->phpbb_root_path . $this->extension_manager->get_extension_path($ext_name) . 'composer.json'));
	}

	// Should be the same as a direct json_decode of the composer.json file
	public function test_foo()
	{
		$ext_name = 'vendor2/foo';

		$manager = $this->get_metadata_manager($ext_name);

		try
		{
			$metadata = $manager->get_metadata();
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->fail($e);
		}

		$json = json_decode(file_get_contents($this->phpbb_root_path . 'ext/vendor2/foo/composer.json'), true);

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
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_NOT_SET', 'name'));
		}

		try
		{
			$manager->validate('type');

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_NOT_SET', 'type'));
		}

		try
		{
			$manager->validate('license');

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_NOT_SET', 'license'));
		}

		try
		{
			$manager->validate('version');

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_NOT_SET', 'version'));
		}

		try
		{
			$manager->validate_authors();

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_NOT_SET', 'authors'));
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
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_NOT_SET', 'author name'));
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
			'license'	=> '',
			'version'	=> '',
		));

		try
		{
			$manager->validate('name');

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_INVALID', 'name'));
		}

		try
		{
			$manager->validate('type');

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_INVALID', 'type'));
		}

		try
		{
			$manager->validate('license');

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_INVALID', 'license'));
		}

		try
		{
			$manager->validate('version');

			$this->fail('Exception not triggered');
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->assertEquals((string) $e, $this->user->lang('META_FIELD_INVALID', 'version'));
		}
	}

	public function test_validator_valid()
	{
		$ext_name = 'validator';

		$manager = $this->get_metadata_manager($ext_name);

		// Valid data
		$manager->set_metadata(array(
			'name'		=> 'test/foo',
			'type'		=> 'phpbb-extension',
			'license'	=> 'GPL v2',
			'version'	=> '1.0.0',
		));

		try
		{
			$this->assertEquals(true, $manager->validate('enable'));
		}
		catch(\phpbb\extension\exception $e)
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
				'phpbb/phpbb'		=> '3.2.0', // config is set to 3.1.0
			),
		));

		try
		{
			//$this->assertEquals(false, $manager->validate_require_php());
			//$this->assertEquals(false, $manager->validate_require_phpbb());
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->fail($e);
		}


		// Too high of requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '5.3.0',
				'phpbb/phpbb'		=> '3.1.0-beta', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->fail($e);
		}


		// Too high of requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '>' . phpversion(),
				'phpbb/phpbb'		=> '>3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			//$this->assertEquals(false, $manager->validate_require_php());
			//$this->assertEquals(false, $manager->validate_require_phpbb());
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->fail($e);
		}


		// Too high of current install
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '<' . phpversion(),
				'phpbb/phpbb'		=> '<3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			//$this->assertEquals(false, $manager->validate_require_php());
			//$this->assertEquals(false, $manager->validate_require_phpbb());
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->fail($e);
		}


		// Matching requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> phpversion(),
				'phpbb/phpbb'		=> '3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->fail($e);
		}


		// Matching requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '>=' . phpversion(),
				'phpbb/phpbb'		=> '>=3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(\phpbb\extension\exception $e)
		{
			$this->fail($e);
		}


		// Matching requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> '<=' . phpversion(),
				'phpbb/phpbb'		=> '<=3.1.0', // config is set to 3.1.0
			),
		));

		try
		{
			$this->assertEquals(true, $manager->validate_require_php());
			$this->assertEquals(true, $manager->validate_require_phpbb());
		}
		catch(\phpbb\extension\exception $e)
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
			$this->user,
			$this->phpbb_root_path
		);
	}
}
