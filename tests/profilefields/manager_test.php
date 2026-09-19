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

class manager_test extends phpbb_database_test_case
{
	/** @var \phpbb\di\service_collection */
	protected $collection;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb_mock_container_builder */
	protected $container_mock;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \Doctrine\DBAL\Connection */
	protected $db_doctrine;

	/** @var \phpbb\db\tools\doctrine */
	protected $db_tools;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\profilefields\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string Table prefix */
	protected $table_prefix;

	/**
	 * {@inheritdoc}
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/manager.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx, $table_prefix;
		global $cache, $user;

		$cache = new phpbb_mock_cache();
		$this->db			= $this->new_dbal();
		$this->db_doctrine	= $this->new_doctrine_dbal();
		$this->db_tools		= $this->getMockBuilder('\phpbb\db\tools\doctrine')
			->setConstructorArgs([$this->db_doctrine])
			->getMock();
		$this->config_text	= new \phpbb\config\db_text($this->db, $table_prefix . 'config_text');
		$this->table_prefix	= $table_prefix;

		$this->container_mock	= new phpbb_mock_container_builder();
		$dispatcher	= new phpbb_mock_event_dispatcher();

		$this->request	= $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->template	= $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();

		$auth		= new \phpbb\auth\auth();
		$language	= new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->collection = new \phpbb\di\service_collection($this->container_mock);
		$this->user		= new \phpbb\user($language, '\phpbb\datetime');
		$this->user->data['user_id'] = 2;
		$this->user->ip = '';
		$user = $this->user;

		$this->log	= new \phpbb\log\log($this->db, $this->user, $auth, $dispatcher, $phpbb_root_path, 'adm/', $phpEx, $table_prefix . 'log');

		$this->manager = new \phpbb\profilefields\manager(
			$auth,
			$this->config_text,
			$this->db,
			$this->db_tools,
			$dispatcher,
			$language,
			$this->log,
			$this->template,
			$this->collection,
			$this->user,
			$table_prefix . 'profile_fields',
			$table_prefix . 'profile_fields_data',
			$table_prefix . 'profile_fields_lang',
			$table_prefix . 'profile_lang'
		);
	}

	public function test_disable_profilefields()
	{
		// Disable the profile field type
		$this->manager->disable_profilefields('foo_bar_type');

		$sql = 'SELECT field_id, field_ident
			FROM ' . $this->table_prefix . "profile_fields
			WHERE field_active = 1
				AND field_type = 'foo_bar_type'";
		$this->assertSqlResultEquals([], $sql, 'All profile fields should be disabled');

		// Test that the config entry exists
		$saved = $this->config_text->get('foo_bar_type.saved');
		$saved = (array) json_decode($saved, true);
		$this->assertEquals([
			1	=> 'pf_1',
			2	=> 'pf_2',
		], $saved, 'All disable profile fields should be saved');
	}

	public function test_enable_profilefields()
	{
		// Enable the profile field type
		$this->manager->enable_profilefields('foo_bar_type');

		$sql = 'SELECT field_id
			FROM ' . $this->table_prefix . "profile_fields
			WHERE field_active = 1
				AND field_type = 'foo_bar_type'
			ORDER BY field_id ASC";
		$this->assertSqlResultEquals([
			['field_id' => '1'],
			['field_id' => '2'],
		], $sql, 'All profile fields should be enabled');

		// Test that the config entry was removed
		$saved = $this->config_text->get('foo_bar_type.saved');
		$this->assertEquals($saved, null, 'All disable profile fields should be removed');
	}

	public function test_purge_profilefields()
	{
		$this->db_tools
			->expects($this->exactly(2))
			->method('sql_column_remove')
			->with(
				$this->table_prefix . 'profile_fields_data',
				$this->stringStartsWith('pf_')
			);

		// Get the field identifiers
		$sql = 'SELECT field_id
			FROM ' . $this->table_prefix . "profile_fields
			WHERE field_type = 'foo_bar_type'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$field_ids = array_map('intval', array_column($rowset, 'field_id'));

		// Purge the profile field type
		$this->manager->purge_profilefields('foo_bar_type');

		// Test all the profile field tables
		$sql = 'SELECT field_id
			FROM ' . $this->table_prefix . "profile_fields
			WHERE field_type = 'foo_bar_type'";
		$this->assertSqlResultEquals([], $sql, 'All profile fields should be removed');

		$sql = 'SELECT field_id
			FROM ' . $this->table_prefix . "profile_fields_lang
			WHERE field_type = 'foo_bar_type'";
		$this->assertSqlResultEquals([], $sql, 'All profile fields lang should be removed');

		$sql = 'SELECT lang_name
			FROM ' . $this->table_prefix . 'profile_lang
			WHERE ' . $this->db->sql_in_set('field_id', $field_ids);
		$this->assertSqlResultEquals([], $sql, 'All profile fields lang should be removed');

		$sql = 'SELECT field_id, field_order
			FROM ' . $this->table_prefix . 'profile_fields
			ORDER BY field_id ASC';
		$this->assertSqlResultEquals([
			[
				'field_id'		=> '3',
			 	'field_order'	=> '1'
			]
		], $sql, 'Profile fields order should be recalculated, starting by 1');

		// Test that the config entry was removed
		$saved = $this->config_text->get('foo_bar_type.saved');
		$this->assertEquals($saved, null, 'All disable profile fields should be removed');
	}

	public function test_generate_profile_fields_template_data_simple()
	{
		$profile_field_string = new \phpbb\profilefields\type\type_string($this->request, $this->template, $this->user);
		$this->container_mock->set('profile_field_string', $profile_field_string);

		$this->collection->add('profile_field_string');

		$profile_row = [
			'username' => ['data' => ['field_type' => 'profile_field_string', 'lang_name' => 'user', 'field_contact_desc' => '', 'field_is_contact' => false, 'field_contact_url' => ''], 'value' => 'John Doe'],
		];

		$result = $this->manager->generate_profile_fields_template_data($profile_row, false);

		$this->assertArrayHasKey('row', $result);
		$this->assertArrayHasKey('blockrow', $result);

		$this->assertEquals([
			'PROFILE_USERNAME_IDENT'		=> 'username',
			'PROFILE_USERNAME_VALUE'		=> 'John Doe',
			'PROFILE_USERNAME_VALUE_RAW'	=> 'John Doe',
			'PROFILE_USERNAME_CONTACT'		=> '',
			'PROFILE_USERNAME_DESC'			=> '',
			'PROFILE_USERNAME_TYPE'			=> 'profile_field_string',
			'PROFILE_USERNAME_NAME'			=> 'user',
			'PROFILE_USERNAME_EXPLAIN'		=> null,
			'S_PROFILE_USERNAME_CONTACT'	=> false,
			'S_PROFILE_USERNAME'			=> true
			],
			$result['row']
		);

		$this->assertEquals([
				'PROFILE_FIELD_IDENT'		=> 'username',
				'PROFILE_FIELD_VALUE'		=> 'John Doe',
				'PROFILE_FIELD_VALUE_RAW'	=> 'John Doe',
				'PROFILE_FIELD_CONTACT'		=> '',
				'PROFILE_FIELD_DESC'			=> '',
				'PROFILE_FIELD_TYPE'			=> 'profile_field_string',
				'PROFILE_FIELD_NAME'			=> 'user',
				'PROFILE_FIELD_EXPLAIN'		=> null,
				'S_PROFILE_CONTACT'	=> false,
				'S_PROFILE_USERNAME'			=> true
			],
			$result['blockrow'][0]
		);
	}

	public function test_generate_profile_fields_template_data_invalid_contact_field()
	{
		$profile_field_string = new \phpbb\profilefields\type\type_string($this->request, $this->template, $this->user);
		$this->container_mock->set('profile_field_string', $profile_field_string);

		$this->collection->add('profile_field_string');

		$profile_row = [
			'username' => ['data' => ['field_type' => 'profile_field_string', 'lang_name' => 'user', 'field_contact_desc' => '', 'field_is_contact' => true, 'field_contact_url' => '%s'], 'value' => 'John Doe'],
		];

		$result = $this->manager->generate_profile_fields_template_data($profile_row);

		$this->assertArrayHasKey('row', $result);
		$this->assertArrayHasKey('blockrow', $result);

		$this->assertEquals([
			'PROFILE_USERNAME_IDENT'		=> 'username',
			'PROFILE_USERNAME_VALUE'		=> 'John Doe',
			'PROFILE_USERNAME_VALUE_RAW'	=> 'John Doe',
			'PROFILE_USERNAME_CONTACT'		=> '',
			'PROFILE_USERNAME_DESC'			=> '',
			'PROFILE_USERNAME_TYPE'			=> 'profile_field_string',
			'PROFILE_USERNAME_NAME'			=> 'user',
			'PROFILE_USERNAME_EXPLAIN'		=> null,
			'S_PROFILE_USERNAME_CONTACT'	=> false,
			'S_PROFILE_USERNAME'			=> true
		],
			$result['row']
		);

		$this->assertEquals([
			'PROFILE_FIELD_IDENT'		=> 'username',
			'PROFILE_FIELD_VALUE'		=> 'John Doe',
			'PROFILE_FIELD_VALUE_RAW'	=> 'John Doe',
			'PROFILE_FIELD_CONTACT'		=> '',
			'PROFILE_FIELD_DESC'		=> '',
			'PROFILE_FIELD_TYPE'		=> 'profile_field_string',
			'PROFILE_FIELD_NAME'		=> 'user',
			'PROFILE_FIELD_EXPLAIN'		=> null,
			'S_PROFILE_CONTACT'			=> false,
			'S_PROFILE_USERNAME'		=> true
		],
			$result['blockrow'][0]
		);
	}

	public function test_generate_profile_fields_template_data_valid_contact_field()
	{
		$profile_field_string = new \phpbb\profilefields\type\type_string($this->request, $this->template, $this->user);
		$this->container_mock->set('profile_field_string', $profile_field_string);

		$this->collection->add('profile_field_string');

		$profile_row = [
			'username' => ['data' => ['field_type' => 'profile_field_string', 'lang_name' => 'user', 'field_contact_desc' => '', 'field_is_contact' => true, 'field_contact_url' => 'http://foo.bar/%s'], 'value' => 'John_Doe'],
		];

		$result = $this->manager->generate_profile_fields_template_data($profile_row);

		$this->assertArrayHasKey('row', $result);
		$this->assertArrayHasKey('blockrow', $result);

		$this->assertEquals([
			'PROFILE_USERNAME_IDENT'		=> 'username',
			'PROFILE_USERNAME_VALUE'		=> 'John_Doe',
			'PROFILE_USERNAME_VALUE_RAW'	=> 'John_Doe',
			'PROFILE_USERNAME_CONTACT'		=> 'http://foo.bar/John_Doe',
			'PROFILE_USERNAME_DESC'			=> '',
			'PROFILE_USERNAME_TYPE'			=> 'profile_field_string',
			'PROFILE_USERNAME_NAME'			=> 'user',
			'PROFILE_USERNAME_EXPLAIN'		=> null,
			'S_PROFILE_USERNAME_CONTACT'	=> true,
			'S_PROFILE_USERNAME'			=> true
		],
			$result['row']
		);

		$this->assertEquals([
			'PROFILE_FIELD_IDENT'		=> 'username',
			'PROFILE_FIELD_VALUE'		=> 'John_Doe',
			'PROFILE_FIELD_VALUE_RAW'	=> 'John_Doe',
			'PROFILE_FIELD_CONTACT'		=> 'http://foo.bar/John_Doe',
			'PROFILE_FIELD_DESC'		=> '',
			'PROFILE_FIELD_TYPE'		=> 'profile_field_string',
			'PROFILE_FIELD_NAME'		=> 'user',
			'PROFILE_FIELD_EXPLAIN'		=> null,
			'S_PROFILE_CONTACT'			=> true,
			'S_PROFILE_USERNAME'		=> true
		],
			$result['blockrow'][0]
		);
	}
}
