<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

include_once(__DIR__ . '/../../phpBB/includes/functions_content.php');
include_once(__DIR__ . '/../../phpBB/includes/functions_display.php');
include_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');

class phpbb_user_lang_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/user_loader.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$this->phpbb_root_path = __DIR__ . '/../../phpBB/';
		$this->php_ext = 'php';

		$this->user_loader = new phpbb_user_loader($this->db, $this->phpbb_dispatcher, $this->phpbb_root_path, $this->php_ext, 'phpbb_users');

	}

	public function test_user_loader()
	{
		$this->user_loader->load_users(array(2));

		$user = $this->user_loader->get_user(1);
		$this->assertEquals(1, $user['user_id']);
		$this->assertEquals('Guest', $user['username']);

		$user = $this->user_loader->get_user(2);
		$this->assertEquals(2, $user['user_id']);
		$this->assertEquals('Admin', $user['username']);

		// Not loaded
		$user = $this->user_loader->get_user(3);
		$this->assertEquals(1, $user['user_id']);
		$this->assertEquals('Guest', $user['username']);

		$user = $this->user_loader->get_user(3, true);
		$this->assertEquals(3, $user['user_id']);
		$this->assertEquals('Test', $user['username']);

		$user_id = $this->user_loader->load_user_by_username('Test');
		$user = $this->user_loader->get_user($user_id);
		$this->assertEquals(3, $user['user_id']);
		$this->assertEquals('Test', $user['username']);

		// Test loading an additional field
		$this->assertFalse(isset($user['user_password']));
		$this->user_loader->load_users(array($user_id), array('u.user_password'));
		$user = $this->user_loader->get_user($user_id);
		$this->assertTrue(isset($user['user_password']));

		// Test flushing
		$this->user_loader->flush();
		$user = $this->user_loader->get_user(2);
		$this->assertEquals(ANONYMOUS, $user['user_id']);
	}

	public function test_helpers()
	{
		// Globals needed for get_username_string, get_user_rank
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx, $user, $auth, $cache, $db;

		$phpbb_dispatcher = $this->phpbb_dispatcher;
		$phpbb_root_path = $this->phpbb_root_path;
		$phpEx = $this->php_ext;
		$user = new phpbb_mock_user();
		$auth = new phpbb_auth();
		$cache = new phpbb_cache_service(new phpbb_cache_driver_null());
		$db = $this->db;

		$this->user_loader->load_users(array(2));
		$user = $this->user_loader->get_user(2);

		$username = get_username_string('full',
			$user['user_id'],
			$user['username'],
			$user['user_colour']);
		$this->assertEquals($username, $this->user_loader->get_username(2, 'full'));

		$avatar = get_user_avatar($user['user_avatar'],
			$user['user_avatar_type'],
			$user['user_avatar_width'],
			$user['user_avatar_height']);
		$this->assertEquals($avatar, $this->user_loader->get_avatar(2));

		$rank = array(
			'rank_title' => '',
			'rank_img' => '',
			'rank_img_src' => '',
		);

		get_user_rank($user['user_rank'],
			(($user['user_id'] == ANONYMOUS) ? false : $user['user_posts']),
			$rank['rank_title'],
			$rank['rank_img'],
			$rank['rank_img_src']);
		$this->assertEquals($rank, $this->user_loader->get_rank(2));
	}
}
