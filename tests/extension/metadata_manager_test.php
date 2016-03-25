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
	protected $db_tools;
	protected $table_prefix;
	protected $phpbb_root_path;
	protected $phpEx;
	protected $migrator;
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
		$factory = new \phpbb\db\tools\factory();
		$this->db_tools = $factory->get($this->db);
		$this->phpbb_root_path = dirname(__FILE__) . '/';
		$this->phpEx = 'php';
		$this->table_prefix = 'phpbb_';

		$container = new phpbb_mock_container_builder();
		$cache_path = $this->phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader(new \phpbb\filesystem\filesystem(), '');
		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$filesystem,
			$this->getMock('\phpbb\request\request'),
			$this->phpbb_root_path,
			$this->phpEx
		);
		$twig = new \phpbb\template\twig\environment(
			$this->config,
			$filesystem,
			$phpbb_path_helper,
			$container,
			$cache_path,
			null,
			$loader,
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);

		$container = new phpbb_mock_container_builder();

		$this->migrator = new \phpbb\db\migrator(
			$container,
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
		$container->set('migrator', $this->migrator);

		$this->extension_manager = new \phpbb\extension\manager(
			$container,
			$this->db,
			$this->config,
			new \phpbb\filesystem\filesystem(),
			'phpbb_ext',
			$this->phpbb_root_path,
			$this->phpEx,
			$this->cache
		);

		global $phpbb_root_path;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $this->phpEx);
		$lang_loader->set_extension_manager($this->extension_manager);
		$lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');

		$this->template = new phpbb\template\twig\twig($phpbb_path_helper, $this->config, $context, $twig, $cache_path, $this->user, array(new \phpbb\template\twig\extension($context, $this->user)));
		$container->set('template.twig.lexer', new \phpbb\template\twig\lexer($twig));
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
		catch (\phpbb\extension\exception $e)
		{
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
			$this->assertEquals($message, $this->user->lang('FILE_NOT_FOUND', $this->phpbb_root_path . $this->extension_manager->get_extension_path($ext_name) . 'composer.json'));
		}
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
		catch (\phpbb\extension\exception $e)
		{
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
			$this->fail($message);
		}

		$json = json_decode(file_get_contents($this->phpbb_root_path . 'ext/vendor2/foo/composer.json'), true);
		array_walk_recursive($json, array($manager, 'sanitize_json'));

		$this->assertEquals($metadata, $json);
	}

	public function validator_non_existing_data()
	{
		return array(
			array('name'),
			array('type'),
			array('license'),
			array('version'),
		);
	}

	/**
	* @dataProvider validator_non_existing_data
	*/
	public function test_validator_non_existing($field_name)
	{
		$manager = $this->get_metadata_manager('validator');
		try
		{
			$manager->validate($field_name);
			$this->fail('Exception not triggered');
		}
		catch (\phpbb\extension\exception $e)
		{
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
			$this->assertEquals($message, $this->user->lang('META_FIELD_NOT_SET', $field_name));
		}
	}

	public function test_validator_non_existing_authors()
	{
		$manager = $this->get_metadata_manager('validator');
		try
		{
			$manager->validate_authors();
			$this->fail('Exception not triggered');
		}
		catch (\phpbb\extension\exception $e)
		{
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
			$this->assertEquals($message, $this->user->lang('META_FIELD_NOT_SET', 'authors'));
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
		catch (\phpbb\extension\exception $e)
		{
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
			$this->assertEquals($message, $this->user->lang('META_FIELD_NOT_SET', 'author name'));
		}
	}

	public function validator_invalid_data()
	{
		return array(
			array('name', 'asdf'),
			array('type', 'asdf'),
			array('license', ''),
			array('version', ''),
		);
	}

	/**
	 * @dataProvider validator_invalid_data
	 */
	public function test_validator_invalid($field_name, $field_value)
	{
		$manager = $this->get_metadata_manager('validator');

		// Invalid data
		$manager->set_metadata(array(
			$field_name		=> $field_value,
		));

		try
		{
			$manager->validate($field_name);
			$this->fail('Exception not triggered');
		}
		catch (\phpbb\extension\exception $e)
		{
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
			$this->assertEquals($message, $this->user->lang('META_FIELD_INVALID', $field_name));
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
		catch (\phpbb\extension\exception $e)
		{
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
		}
	}

	public function validator_requirements_data()
	{
		return array(
			array(
				'10.0.0',
				'100.2.0',
				false,
				false,
				'Versions are not compared at the moment',
			),
			array(
				'5.3.0',
				'3.1.0-beta',
				true,
				true,
			),
			array(
				'>' . phpversion(),
				'>3.1.0',
				false,
				false,
				'Versions are not compared at the moment',
			),
			array(
				'<' . phpversion(),
				'<3.1.0',
				false,
				false,
				'Versions are not compared at the moment',
			),
			array(
				phpversion(),
				'3.1.0',
				true,
				true,
			),
			array(
				'>=' . phpversion(),
				'>=3.1.0',
				true,
				true,
			),
			array(
				'<=' . phpversion(),
				'<=3.1.0',
				true,
				true,
			),
		);
	}

	/**
	* @dataProvider validator_requirements_data
	*/
	public function test_validator_requirements($php_version, $phpbb_version, $expected_php, $expected_phpbb, $incomplete_reason = '')
	{
		if ($incomplete_reason)
		{
			$this->markTestIncomplete($incomplete_reason);
		}

		$ext_name = 'validator';
		$manager = $this->get_metadata_manager($ext_name);
		// Too high of requirements
		$manager->merge_metadata(array(
			'require'		=> array(
				'php'		=> $php_version,
			),
			'extra'		=> array(
				'soft-require'	=> array(
					'phpbb/phpbb'		=> $phpbb_version, // config is set to 3.1.0
				),
			),
		));

		$this->assertEquals($expected_php, $manager->validate_require_php());
		$this->assertEquals($expected_phpbb, $manager->validate_require_phpbb());
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
