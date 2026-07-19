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
* @group functional
*/
class phpbb_functional_extension_acp_test extends phpbb_functional_test_case
{
	private static $helper;

	protected static $fixtures = array(
		'./',
	);

	static public function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(__CLASS__);
		self::$helper->copy_ext_fixtures(__DIR__ . '/../extension/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->purge_cache();

		// Clear the phpbb_ext table
		$this->db->sql_query('DELETE FROM phpbb_ext');

		// Insert our base data
		$insert_rows = array(
			array(
				'ext_name'		=> 'vendor2/foo',
				'ext_active'	=> true,
				'ext_state'		=> 'b:0;',
			),
			array(
				'ext_name'		=> 'vendor/moo',
				'ext_active'	=> false,
				'ext_state'		=> 'b:0;',
			),

			// do not exist
			array(
				'ext_name'		=> 'vendor/test2',
				'ext_active'	=> true,
				'ext_state'		=> 'b:0;',
			),
			array(
				'ext_name'		=> 'vendor/test3',
				'ext_active'	=> false,
				'ext_state'		=> 'b:0;',
			),
		);
		$this->db->sql_multi_insert('phpbb_ext', $insert_rows);

		$this->login();
		$this->admin_login();

		$this->add_lang(['acp/common', 'acp/extensions']);
	}

	/**
	 * Mocks the extensions catalog cache used in phpBB/phpbb/composer/manager.php
	 * with a predefined fixture so no external calls are made.
	 */
	protected function mock_extensions_catalog_cache():void {
		$fixture_file = __DIR__ . '/fixtures/files/extensions_catalog.json';
		$package_type = 'phpbb-extension';

		$available_extensions = json_decode(file_get_contents($fixture_file), true);
		$this->cache->put('_composer_' . $package_type . '_available', $available_extensions, 24*60*60);
	}

	public function test_list()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);

		$this->assertCount(1, $crawler->filter('.ext_enabled'));
		$this->assertCount(3, $crawler->filter('.ext_disabled'));
		$this->assertCount(4, $crawler->filter('.ext_not_installed'));

		$this->assertStringContainsString('phpBB Foo Extension', $crawler->filter('.ext_enabled')->eq(0)->text());
		$this->assertContainsLang('EXTENSION_DISABLE', $crawler->filter('.ext_enabled')->eq(0)->text());

		$this->assertStringContainsString('phpBB Moo Extension', $crawler->filter('.ext_disabled')->eq(2)->text());
		$this->assertContainsLang('DETAILS', $crawler->filter('.ext_disabled')->eq(2)->text());
		$this->assertContainsLang('EXTENSION_ENABLE', $crawler->filter('.ext_disabled')->eq(2)->text());
		$this->assertContainsLang('EXTENSION_DELETE_DATA', $crawler->filter('.ext_disabled')->eq(2)->text());

		$this->assertStringContainsString('The “vendor/test2” extension is not valid.', $crawler->filter('.ext_disabled')->eq(0)->text());

		$this->assertStringContainsString('The “vendor/test3” extension is not valid.', $crawler->filter('.ext_disabled')->eq(1)->text());

		$this->assertStringContainsString('phpBB Bar Extension', $crawler->filter('.ext_not_installed')->eq(0)->text());
		$this->assertContainsLang('DETAILS', $crawler->filter('.ext_not_installed')->eq(0)->text());
		$this->assertContainsLang('EXTENSION_ENABLE', $crawler->filter('.ext_not_installed')->eq(0)->text());

		// Check that invalid extensions are not listed.
		$this->assertStringNotContainsString('phpBB BarFoo Extension', $crawler->filter('.table1')->text());
		$this->assertStringNotContainsString('barfoo', $crawler->filter('.table1')->text());

		$this->assertStringNotContainsString('vendor3/bar', $crawler->filter('.table1')->text());
	}

	public function test_details()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=vendor2%2Ffoo&sid=' . $this->sid);

		$validation = array(
			'DISPLAY_NAME'		=> 'phpBB Foo Extension',
			'CLEAN_NAME'		=> 'vendor2/foo',
			'TYPE'				=> 'phpbb-extension',
			'DESCRIPTION'		=> 'An example/sample extension to be used for testing purposes in phpBB Development.',
			'VERSION'	  		=> '1.0.0',
			'TIME'				=> '2012-02-15 01:01:01',
			'LICENSE'			=> 'GPL-2.0',
			'PHPBB_VERSION'		=> '3.1.*@dev',
			'PHP_VERSION'		=> '>=5.3',
			'AUTHOR_NAME'		=> 'John Smith',
			'AUTHOR_EMAIL'		=> 'email@phpbb.com',
			'AUTHOR_HOMEPAGE'	=> 'http://phpbb.com',
			'AUTHOR_ROLE'		=> 'N/A',
		);

		for ($i = 0; $i < $crawler->filter('dl')->count(); $i++)
		{
			$text = trim($crawler->filter('dl')->eq($i)->text());

			$match = false;

			foreach ($validation as $language_key => $expected)
			{
				if (strpos($text, $this->lang($language_key)) === 0)
				{
					$match = true;

					$this->assertStringContainsString($expected, $text);
				}
			}

			if (!$match)
			{
				$this->fail('Unexpected field: "' . $text . '"');
			}
		}
	}

	public function test_enable_pre()
	{
		// Foo is already enabled (redirect to list)
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor2%2Ffoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_NAME', $crawler->filter('div.main thead')->text());
		$this->assertContainsLang('EXTENSION_OPTIONS', $crawler->filter('div.main thead')->text());
		$this->assertContainsLang('EXTENSION_ACTIONS', $crawler->filter('div.main thead')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('EXTENSION_ENABLE_CONFIRM', 'phpBB Moo Extension'), $crawler->filter('#main')->text());

		// Correctly submit the enable form, default not enableable message
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor3%2Ffoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_NOT_ENABLEABLE', $crawler->filter('.errorbox')->text());

		// Custom reason messages returned by not enableable extension
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor5%2Ffoo&sid=' . $this->sid);
		$this->assertStringContainsString('Reason 1', $crawler->filter('.errorbox')->text());
		$this->assertStringContainsString('Reason 2', $crawler->filter('.errorbox')->text());
	}

	public function test_disable_pre()
	{
		// Moo is not enabled (redirect to list)
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_NAME', $crawler->filter('div.main thead')->text());
		$this->assertContainsLang('EXTENSION_OPTIONS', $crawler->filter('div.main thead')->text());
		$this->assertContainsLang('EXTENSION_ACTIONS', $crawler->filter('div.main thead')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=vendor2%2Ffoo&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('EXTENSION_DISABLE_CONFIRM', 'phpBB Foo Extension'), $crawler->filter('#main')->text());
	}

	public function test_delete_data_pre()
	{
		// test2 is not available (error)
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=delete_data_pre&ext_name=test2&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('FILE_NOT_FOUND', ''), $crawler->filter('.errorbox')->text());

		// foo is not disabled (redirect to list)
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=delete_data_pre&ext_name=vendor2%2Ffoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_NAME', $crawler->filter('div.main thead')->text());
		$this->assertContainsLang('EXTENSION_OPTIONS', $crawler->filter('div.main thead')->text());
		$this->assertContainsLang('EXTENSION_ACTIONS', $crawler->filter('div.main thead')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=delete_data_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertStringContainsString('Are you sure that you wish to delete the data associated with “phpBB Moo Extension”?', $crawler->filter('.errorbox')->text());
	}

	public function test_actions()
	{
		// Access enable page without hash
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('FORM_INVALID', $crawler->filter('.errorbox')->text());

		// Correctly submit the enable form
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$form = $crawler->selectButton('enable')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSION_ENABLE_SUCCESS', $crawler->filter('.successbox')->text());

		// Access disable page without hash
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('FORM_INVALID', $crawler->filter('.errorbox')->text());

		// Correctly submit the disable form
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$form = $crawler->selectButton('disable')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSION_DISABLE_SUCCESS', $crawler->filter('.successbox')->text());

		// Access delete_data page without hash
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=delete_data&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('FORM_INVALID', $crawler->filter('.errorbox')->text());

		// Correctly submit the delete data form
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=delete_data_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$form = $crawler->selectButton('delete_data')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSION_DELETE_DATA_SUCCESS', $crawler->filter('.successbox')->text());

		// Attempt to enable invalid extension
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=barfoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_DIR_INVALID', $crawler->filter('.errorbox')->text());

		// Test installing/uninstalling extension altogether
		$this->logout();
		$this->install_ext('vendor/moo');
		$this->uninstall_ext('vendor/moo');
	}

	public function test_extensions_catalog()
	{
		// Access extensions catalog main page
		$this->mock_extensions_catalog_cache();
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=catalog&sid=' . $this->sid);
		$this->assertContainsLang('ACP_EXTENSIONS_CATALOG', $this->get_content());

		$this->assertContainsLang('BROWSE_EXTENSIONS_DATABASE', $crawler->filter('fieldset[class="quick quick-left"] > span > a')->eq(0)->text());
		$this->assertContainsLang('SETTINGS', $crawler->filter('fieldset[class="quick quick-left"] > span > a')->eq(1)->text());

		$restore_repositories = $crawler->filter('#restore_default_repositories');
		$this->assertCount(1, $restore_repositories);
		$this->assertSame($this->lang('RESTORE_DEFAULT_REPOSITORIES'), $restore_repositories->attr('value'));
		$this->assertSame([
			'https://satis.phpbb.com/',
			'https://www.phpbb.com/customise/db/composer/40/',
		], json_decode($restore_repositories->attr('data-default-repositories'), true));

		$form = $crawler->selectButton('Submit')->form();
		$form['minimum_stability']->select('dev');
		$form['repositories'] = 'https://satis.phpbb.com/';
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

		// Revisit extensions catalog main page after configuration change
		$this->mock_extensions_catalog_cache();
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=catalog&sid=' . $this->sid);
		$this->assertContainsLang('ACP_EXTENSIONS_CATALOG', $this->get_content());

		// Ensure catalog has any records in extensions list
		$this->assertGreaterThan(0, $crawler->filter('tbody > tr > td > strong')->count());
	}

	public function test_extensions_catalog_cancelling_install()
	{
		$this->mock_extensions_catalog_cache();
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=catalog&action=install&extension=phpbb%2Fviglink&sid=' . $this->sid);
		$this->assertContainsLang('CONFIRM', $crawler->filter('form#confirm h1')->text());

		$form = $crawler->selectButton($this->lang('NO'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('ACP_EXTENSIONS_CATALOG', $this->get_content());
		$this->assertGreaterThan(0, $crawler->selectLink($this->lang('INSTALL'))->count());

		// A cancelled action originating in the manager returns to the manager
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=catalog&action=install&origin=manager&extension=phpbb%2Fviglink&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('NO'))->form();
		$crawler = self::submit($form);
		$this->assertStringContainsString('mode=main', $crawler->getUri());
		$this->assertGreaterThan(0, $crawler->filter('.ext_enabled')->count());

		// Arbitrary return URLs are not accepted
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=catalog&action=install&origin=https%3A%2F%2Fexample.com&extension=phpbb%2Fviglink&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('NO'))->form();
		$crawler = self::submit($form);
		$this->assertStringContainsString('mode=catalog', $crawler->getUri());
		$this->assertStringNotContainsString('example.com', $crawler->getUri());
	}

	public function test_extensions_catalog_rejects_package_outside_catalog()
	{
		$this->mock_extensions_catalog_cache();
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=catalog&action=install&extension=example%2Funtrusted&sid=' . $this->sid);

		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSIONS_ACTION_NOT_ALLOWED', $crawler->filter('.errorbox')->text());
		$this->assertStringContainsString('mode=main', $crawler->selectLink($this->lang('RETURN_TO_EXTENSION_MANAGER'))->link()->getUri());
		$this->assertStringContainsString('mode=catalog', $crawler->selectLink($this->lang('RETURN_TO_EXTENSION_CATALOG'))->link()->getUri());
	}

	public function test_extensions_catalog_installing_extension()
	{
		// Let's check the overview, multiple packages should be listed
		$this->mock_extensions_catalog_cache();
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=catalog&sid=' . $this->sid);
		$this->assertContainsLang('ACP_EXTENSIONS_CATALOG', $this->get_content());
		$this->assertGreaterThan(1, $crawler->filter('tr')->count());
		$this->assertGreaterThan(1, $crawler->selectLink($this->lang('INSTALL'))->count());

		$pages = 1;
		$pagination = $crawler->filter('div.pagination li:nth-last-child(2) a');
		if ($pagination->count() > 0) {
			$pages = (int) $pagination->first()->text();
		}

		// Get Install links for both extensions
		$extension_filter = function($crawler, $extension_name, &$install_link)
		{
			$extension_filter = $crawler->filter('tr')->reduce(
				function ($node, $i) use ($extension_name)
				{
					return strpos($node->text(), $extension_name) !== false;
				}
			);

			if ($extension_filter->count())
			{
				$install_link = $extension_filter->selectLink($this->lang('INSTALL'))->link();
			}
		};

		for ($i = 0; $i < $pages; $i++)
		{
			if ($i != 0)
			{
				$this->mock_extensions_catalog_cache();
				$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&start=' . $i * 20 . '&mode=catalog&sid=' . $this->sid);
			}

			$extension_filter($crawler, 'VigLink', $viglink_install_link);
		}

		if (!isset($viglink_install_link))
		{
			$this->fail('Failed acquiring install links for test extensions');
		}

		// Attempt to install phpbb/viglink extension
		$crawler = self::$client->click($viglink_install_link);
		$this->assertContainsLang('CONFIRM', $crawler->filter('form#confirm h1')->text());
		$this->assertStringContainsString('phpbb/viglink', $crawler->filter('fieldset > p')->text());
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertGreaterThan(0, $crawler->filter('.successbox > p')->count(), $crawler->filter('body')->text());
		$this->assertContainsLang('EXTENSIONS_INSTALLED', $crawler->filter('.successbox > p')->text());
		$back_links = $crawler->filter('.successbox > p a');
		$this->assertContainsLang('RETURN_TO_EXTENSION_CATALOG', $back_links->eq(0)->text());
		$this->assertContainsLang('RETURN_TO_EXTENSION_MANAGER', $back_links->eq(1)->text());
		$this->assertStringContainsString('mode=catalog', $back_links->eq(0)->link()->getUri());
		$this->assertStringContainsString('mode=main', $back_links->eq(1)->link()->getUri());
		// Assert there's console log output
		$this->assertStringContainsString('Locking phpbb/viglink', $crawler->filter('.console-output > pre')->text());

		// Ensure installed extension appears in available extensions list
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$this->assertStringContainsString('VigLink', $crawler->filter('strong[title="phpbb/viglink"]')->text());
	}

	public function test_extensions_catalog_hides_update_for_current_extension()
	{
		// Enable 'VigLink' extension installed earlier
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$extension_enable_link = $crawler->filter('tr')->reduce(
			function ($node, $i)
			{
				return (bool) (strpos($node->text(), 'VigLink') !== false);
			}
		)->selectLink($this->lang('EXTENSION_ENABLE'))->link();
		$crawler = self::$client->click($extension_enable_link);
		$form = $crawler->selectButton($this->lang('EXTENSION_ENABLE'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSION_ENABLE_SUCCESS', $crawler->filter('.successbox')->text());

		// The installed version is the current catalog version, so no update is offered
		$this->mock_extensions_catalog_cache();
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$viglink_row = $crawler->filter('tr')->reduce(
			function ($node, $i)
			{
				return (bool) (strpos($node->text(), 'VigLink') !== false);
			}
		);

		$this->assertCount(0, $viglink_row->selectLink($this->lang('EXTENSION_UPDATE')));
		$this->assertCount(1, $viglink_row->selectLink($this->lang('EXTENSION_REMOVE')));
	}

	public function test_extensions_catalog_removing_extension()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);

		// Check if both enabled and disabled extensions have 'Remove' action available
		$viglink_remove_link = $crawler->filter('tr')->reduce(
			function ($node, $i)
			{
				return (bool) (strpos($node->text(), 'VigLink') !== false);
			}
		)->selectLink($this->lang('EXTENSION_REMOVE'))->link();

		// Test extensions removal
		// Remove 'VigLink' enabled extension
		$crawler = self::$client->click($viglink_remove_link);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSIONS_REMOVED', $crawler->filter('.successbox > p')->text());
		$back_links = $crawler->filter('.successbox > p a');
		$this->assertContainsLang('RETURN_TO_EXTENSION_MANAGER', $back_links->eq(0)->text());
		$this->assertContainsLang('RETURN_TO_EXTENSION_CATALOG', $back_links->eq(1)->text());
		$this->assertStringContainsString('mode=main', $back_links->eq(0)->link()->getUri());
		$this->assertStringContainsString('mode=catalog', $back_links->eq(1)->link()->getUri());
		// Assert there's console log output
		$this->assertStringContainsString('Removing phpbb/viglink', $crawler->filter('.console-output > pre')->text());

		// Ensure removed extensions do not appear in available extensions list
		self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$this->assertStringNotContainsString('VigLink', $this->get_content());
	}
}
