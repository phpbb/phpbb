<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_extension_acp_test extends phpbb_functional_test_case
{
	static private $copied_files = array();
	static private $helper;

	/**
	* This should only be called once before the tests are run.
	* This is used to copy the extensions to the phpBB install
	*/
	static public function setUpBeforeClass()
	{
		global $phpbb_root_path;

		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);

		self::$copied_files = array();

		if (file_exists($phpbb_root_path . 'ext/'))
		{
			// First, move any extensions setup on the board to a temp directory
			self::$copied_files = self::$helper->copy_dir($phpbb_root_path . 'ext/', $phpbb_root_path . 'store/temp_ext/');

			// Then empty the ext/ directory on the board (for accurate test cases)
			self::$helper->empty_dir($phpbb_root_path . 'ext/');
		}

		// Copy our ext/ files from the test case to the board
		self::$copied_files = array_merge(self::$copied_files, self::$helper->copy_dir(dirname(__FILE__) . '/../extension/ext/', $phpbb_root_path . 'ext/'));
	}

	public function setUp()
	{
		parent::setUp();

		$this->get_db();

		// Clear the phpbb_ext table
		$this->db->sql_query('DELETE FROM phpbb_ext');

		// Insert our base data
		$insert_rows = array(
			array(
				'ext_name'		=> 'foo',
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
				'ext_name'		=> 'test2',
				'ext_active'	=> true,
				'ext_state'		=> 'b:0;',
			),
			array(
				'ext_name'		=> 'test3',
				'ext_active'	=> false,
				'ext_state'		=> 'b:0;',
			),
		);
		$this->db->sql_multi_insert('phpbb_ext', $insert_rows);

		$this->login();
		$this->admin_login();

		$this->add_lang('acp/extensions');
	}

	/**
	* This should only be called once after the tests are run.
	* This is used to remove the files copied to the phpBB install
	*/
	static public function tearDownAfterClass()
	{
		global $phpbb_root_path;

		if (file_exists($phpbb_root_path . 'store/temp_ext/'))
		{
			// Copy back the board installed extensions from the temp directory
			self::$helper->copy_dir($phpbb_root_path . 'store/temp_ext/', $phpbb_root_path . 'ext/');
		}

		// Remove all of the files we copied around (from board ext -> temp_ext, from test ext -> board ext)
		self::$helper->remove_files(self::$copied_files);

		if (file_exists($phpbb_root_path . 'store/temp_ext/'))
		{
			self::$helper->empty_dir($phpbb_root_path . 'store/temp_ext/');
		}
	}

	public function test_list()
	{
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);

        $this->assertCount(1, $crawler->filter('.ext_enabled'));
        $this->assertCount(5, $crawler->filter('.ext_disabled'));

        $this->assertContains('phpBB Foo Extension', $crawler->filter('.ext_enabled')->eq(0)->text());
        $this->assertContainsLang('PURGE', $crawler->filter('.ext_enabled')->eq(0)->text());

        $this->assertContains('The "test2" extension is not valid.', $crawler->filter('.ext_disabled')->eq(0)->text());

        $this->assertContains('The "test3" extension is not valid.', $crawler->filter('.ext_disabled')->eq(1)->text());

        $this->assertContains('phpBB Moo Extension', $crawler->filter('.ext_disabled')->eq(2)->text());
        $this->assertContainsLang('DETAILS', $crawler->filter('.ext_disabled')->eq(2)->text());
        $this->assertContainsLang('ENABLE', $crawler->filter('.ext_disabled')->eq(2)->text());
        $this->assertContainsLang('PURGE', $crawler->filter('.ext_disabled')->eq(2)->text());

        $this->assertContains('The "bar" extension is not valid.', $crawler->filter('.ext_disabled')->eq(3)->text());
	}

	public function test_details()
	{
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=foo&sid=' . $this->sid);

        $validation = array(
        	'DISPLAY_NAME'		=> 'phpBB Foo Extension',
        	'CLEAN_NAME'		=> 'foo/example',
        	'DESCRIPTION'		=> 'An example/sample extension to be used for testing purposes in phpBB Development.',
        	'VERSION'	  		=> '1.0.0',
        	'TIME'				=> '2012-02-15 01:01:01',
        	'LICENCE'			=> 'GPL-2.0',
        	'PHPBB_VERSION'		=> '3.1.0-dev',
        	'PHP_VERSION'		=> '>=5.3',
        	'AUTHOR_NAME'		=> 'Nathan Guse',
        	'AUTHOR_EMAIL'		=> 'email@phpbb.com',
        	'AUTHOR_HOMEPAGE'	=> 'http://lithiumstudios.org',
        	'AUTHOR_ROLE'		=> 'N/A',
        );

        for ($i = 0; $i < $crawler->filter('dl')->count(); $i++)
        {
        	$text = $crawler->filter('dl')->eq($i)->text();

        	$match = false;

        	foreach ($validation as $language_key => $expected)
        	{
        		if (strpos($text, $this->lang($language_key)) === 0)
        		{
        			$match = true;

        			$this->assertContains($expected, $text);
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
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=foo&sid=' . $this->sid);
        $this->assertContainsLang('EXTENSION_NAME', $crawler->filter('html')->text());
        $this->assertContainsLang('EXTENSION_OPTIONS', $crawler->filter('html')->text());
        $this->assertContainsLang('EXTENSION_ACTIONS', $crawler->filter('html')->text());

        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('ENABLE_CONFIRM', $crawler->filter('html')->text());
	}

	public function test_disable_pre()
	{
        // Moo is not enabled (redirect to list)
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('EXTENSION_NAME', $crawler->filter('html')->text());
        $this->assertContainsLang('EXTENSION_OPTIONS', $crawler->filter('html')->text());
        $this->assertContainsLang('EXTENSION_ACTIONS', $crawler->filter('html')->text());

        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=foo&sid=' . $this->sid);
        $this->assertContainsLang('DISABLE_CONFIRM', $crawler->filter('html')->text());
	}

	public function test_purge_pre()
	{
        // test2 is not available (error)
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=purge_pre&ext_name=test2&sid=' . $this->sid);
        $this->assertContains('The required file does not exist', $crawler->filter('html')->text());

        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=purge_pre&ext_name=foo&sid=' . $this->sid);
        $this->assertContainsLang('PURGE_CONFIRM', $crawler->filter('html')->text());
	}

	public function test_actions()
	{
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('ENABLE_SUCCESS', $crawler->filter('html')->text());

        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('DISABLE_SUCCESS', $crawler->filter('html')->text());

        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=purge&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('PURGE_SUCCESS', $crawler->filter('html')->text());
	}
}