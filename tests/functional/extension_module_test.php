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
require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_modules.php';

/**
* @group functional
*/
class phpbb_functional_extension_module_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static private $helper;

	static protected $fixtures = array(
		'./',
	);

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);
		self::$helper->copy_ext_fixtures(dirname(__FILE__) . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	public function setUp()
	{
		global $db;

		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();
		$this->phpbb_extension_manager->enable('foo/bar');

		$db = $this->get_db();
		$cache = $this->get_cache_driver();
		$modules = new \phpbb\module\module_manager($cache, $db, $this->phpbb_extension_manager, MODULES_TABLE, dirname(__FILE__) . '/../../phpBB/', 'php');

		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_langname = 'acp'
				AND module_class = 'ACP_CAT_DOT_MODS'";
		$result = $db->sql_query($sql);
		$module_id = (int) $db->sql_fetchfield('module_id');
		$db->sql_freeresult($result);

		$parent_data = array(
			'module_basename'	=> '',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> $module_id,
			'module_class'		=> 'acp',
			'module_langname'	=> 'ACP_FOOBAR_TITLE',
			'module_mode'		=> '',
			'module_auth'		=> '',
		);
		$modules->update_module_data($parent_data);

		$module_data = array(
			'module_basename'	=> 'foo\\bar\\acp\\main_module',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> $parent_data['module_id'],
			'module_class'		=> 'acp',
			'module_langname'	=> 'ACP_FOOBAR_TITLE',
			'module_mode'		=> 'mode',
			'module_auth'		=> '',
		);
		$modules->update_module_data($module_data);

		$parent_data = array(
			'module_basename'	=> '',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> 0,
			'module_class'		=> 'ucp',
			'module_langname'	=> 'UCP_FOOBAR_TITLE',
			'module_mode'		=> '',
			'module_auth'		=> '',
		);
		$modules->update_module_data($parent_data);

		$module_data = array(
			'module_basename'	=> 'foo\\bar\\ucp\\main_module',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> $parent_data['module_id'],
			'module_class'		=> 'ucp',
			'module_langname'	=> 'UCP_FOOBAR_TITLE',
			'module_mode'		=> 'mode',
			'module_auth'		=> '',
		);
		$modules->update_module_data($module_data);

		$this->purge_cache();
	}

	public function test_acp()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=foo%5cbar%5cacp%5cmain_module&mode=mode&sid=' . $this->sid);
		$this->assertContains('Bertie rulez!', $crawler->filter('#main')->text());
	}

	public function test_ucp()
	{
		$this->login();

		$crawler = self::request('GET', 'ucp.php?sid=' . $this->sid);
		$this->assertContains('UCP_FOOBAR_TITLE', $crawler->filter('#tabs')->text());

		$link = $crawler->selectLink('UCP_FOOBAR_TITLE')->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'ucp.')));
		$this->assertContains('UCP Extension Template Test Passed!', $crawler->filter('#content')->text());

		$this->phpbb_extension_manager->purge('foo/bar');
	}
}
