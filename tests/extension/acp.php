<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class acp_test extends phpbb_functional_test_case
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

		// First, move any extensions setup on the board to a temp directory
		self::$copied_files = self::$helper->copy_dir($phpbb_root_path . 'ext/', $phpbb_root_path . 'store/temp_ext/');

		// Then empty the ext/ directory on the board (for accurate test cases)
		self::$helper->empty_dir($phpbb_root_path . 'ext/');

		// Copy our ext/ files from the test case to the board
		self::$copied_files = array_merge(self::$copied_files, self::$helper->copy_dir(dirname(__FILE__) . '/ext/', $phpbb_root_path . 'ext/'));
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

		// Copy back the board installed extensions from the temp directory
		self::$helper->copy_dir($phpbb_root_path . 'store/temp_ext/', $phpbb_root_path . 'ext/');

		self::$copied_files[] = $phpbb_root_path . 'store/temp_ext/';

		// Remove all of the files we copied around (from board ext -> temp_ext, from test ext -> board ext)
		self::$helper->remove_files(self::$copied_files);
	}

	public function test_list()
	{
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);

        $this->assertCount(1, $crawler->filter('.ext_enabled'));
        $this->assertCount(4, $crawler->filter('.ext_disabled'));

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

        for ($i = 0; $i < $crawler->filter('dl')->count(); $i++)
        {
        	$text = $crawler->filter('dl')->eq($i)->text();

        	switch (true)
        	{
        		case (strpos($text, $this->lang('DISPLAY_NAME')) === 0):
        			$this->assertContains('phpBB Foo Extension', $text);
        		break;

        		case (strpos($text, $this->lang('CLEAN_NAME')) === 0):
        			$this->assertContains('foo/example', $text);
        		break;

        		case (strpos($text, $this->lang('DESCRIPTION')) === 0):
        			$this->assertContains('An example/sample extension to be used for testing purposes in phpBB Development.', $text);
        		break;

        		case (strpos($text, $this->lang('VERSION')) === 0):
        			$this->assertContains('1.0.0', $text);
        		break;

        		case (strpos($text, $this->lang('TIME')) === 0):
        			$this->assertContains('2012-02-15 01:01:01', $text);
        		break;

        		case (strpos($text, $this->lang('LICENCE')) === 0):
        			$this->assertContains('GNU GPL v2', $text);
        		break;

        		case (strpos($text, $this->lang('PHPBB_VERSION')) === 0):
        			$this->assertContains('3.1.0-dev', $text);
        		break;

        		case (strpos($text, $this->lang('PHP_VERSION')) === 0):
        			$this->assertContains('>=5.3', $text);
        		break;

        		case (strpos($text, $this->lang('AUTHOR_NAME')) === 0):
        			$this->assertContains('Nathan Guse', $text);
        		break;

        		case (strpos($text, $this->lang('AUTHOR_EMAIL')) === 0):
        			$this->assertContains('email@phpbb.com', $text);
        		break;

        		case (strpos($text, $this->lang('AUTHOR_HOMEPAGE')) === 0):
        			$this->assertContains('http://lithiumstudios.org', $text);
        		break;

        		case (strpos($text, $this->lang('AUTHOR_ROLE')) === 0):
        			$this->assertContains('N/A', $text);
        		break;
			}
		}
	}

	public function test_enable_pre()
	{
		// Foo is already enabled (error)
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=foo&sid=' . $this->sid);
        $this->assertContainsLang('EXTENSION_NOT_AVAILABLE', $crawler->filter('html')->text());

        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('ENABLE_CONFIRM', $crawler->filter('html')->text());
	}

	public function test_disable_pre()
	{
        // Moo is not enabled (error)
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('EXTENSION_NOT_AVAILABLE', $crawler->filter('html')->text());

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

	public function test_enable()
	{
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('ENABLE_SUCCESS', $crawler->filter('html')->text());
	}

	public function test_disable()
	{
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('DISABLE_SUCCESS', $crawler->filter('html')->text());
	}

	public function test_purge()
	{
        $crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=purge&ext_name=vendor%2Fmoo&sid=' . $this->sid);
        $this->assertContainsLang('PURGE_SUCCESS', $crawler->filter('html')->text());
	}
}