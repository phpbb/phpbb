<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_notification_test extends phpbb_database_test_case
{
	protected $notifications;
	static private $copied_files = array();
	static private $helper;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/notification.xml');
	}

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

	protected function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $db, $phpEx;

		if (!function_exists('set_var'))
		{
			include($phpbb_root_path . 'includes/functions.' . $phpEx);
		}

		$db = $this->new_dbal();
		$config = new phpbb_config(array());
		$user = new phpbb_mock_user();

		$this->notifications = new phpbb_notification_manager(
			$db,
			new phpbb_mock_cache(),
			new phpbb_template($phpbb_root_path, $phpEx, $config, $user, new phpbb_style_resource_locator()),
			new phpbb_mock_extension_manager($phpbb_root_path,
				array(
					'test' => array(
						'ext_name' => 'test',
						'ext_active' => '1',
						'ext_path' => 'ext/test/',
					),
				)
			),
			$user,
			new phpbb_auth(),
			$config,
			$phpbb_root_path,
			$phpEx
		);
	}

	public function test_get_subscription_types()
	{
		$this->assertArrayHasKey('ext_test-test', $this->notifications->get_subscription_types());
		$this->assertArrayHasKey('moderation_queue', $this->notifications->get_subscription_types());
		$this->assertArrayHasKey('bookmark', $this->notifications->get_subscription_types());
		$this->assertArrayHasKey('pm', $this->notifications->get_subscription_types());
		$this->assertArrayHasKey('post', $this->notifications->get_subscription_types());
		$this->assertArrayHasKey('quote', $this->notifications->get_subscription_types());
		$this->assertArrayHasKey('topic', $this->notifications->get_subscription_types());

		//get_subscription_types
		//get_subscription_methods
	}

	public function test_subscriptions()
	{
		$this->notifications->add_subscription('post', 0, '');
		$this->notifications->add_subscription('post', 0, '', 1);
		$this->notifications->add_subscription('quote', 0, '', 1);

		$this->notifications->add_subscription('post', 0, '', 2);
		$this->notifications->add_subscription('post', 0, 'email', 2);
		$this->notifications->add_subscription('post', 0, 'jabber', 2);
		$this->notifications->add_subscription('post', 1, '', 2);
		$this->notifications->add_subscription('post', 1, 'email', 2);
		$this->notifications->add_subscription('post', 1, 'jabber', 2);
		$this->notifications->add_subscription('post', 2, '', 2);
		$this->notifications->add_subscription('post', 2, 'email', 2);
		$this->notifications->add_subscription('post', 2, 'jabber', 2);

		$this->assertEquals(array(
			array(
				'item_type'		=> 'post',
				'item_id'		=> 0,
				'user_id'		=> 0,
				'method'		=> '',
			),
		), $this->notifications->get_subscriptions());

		$this->assertEquals(array(
			array(
				'item_type'		=> 'post',
				'item_id'		=> 0,
				'user_id'		=> 1,
				'method'		=> '',
			),
			array(
				'item_type'		=> 'quote',
				'item_id'		=> 0,
				'user_id'		=> 1,
				'method'		=> '',
			),
		), $this->notifications->get_subscriptions(1));

		$this->assertEquals(array(
			array(
				'item_type'		=> 'post',
				'item_id'		=> 0,
				'user_id'		=> 2,
				'method'		=> '',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 0,
				'user_id'		=> 2,
				'method'		=> 'email',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 0,
				'user_id'		=> 2,
				'method'		=> 'jabber',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 1,
				'user_id'		=> 2,
				'method'		=> '',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 1,
				'user_id'		=> 2,
				'method'		=> 'email',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 1,
				'user_id'		=> 2,
				'method'		=> 'jabber',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 2,
				'user_id'		=> 2,
				'method'		=> '',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 2,
				'user_id'		=> 2,
				'method'		=> 'email',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 2,
				'user_id'		=> 2,
				'method'		=> 'jabber',
			),
		), $this->notifications->get_subscriptions(2));

		$this->assertEquals(array(
			'post' => array(
				'',
				'email',
				'jabber',
			),
		), $this->notifications->get_subscriptions(2, true));

		$this->notifications->delete_subscription('post', 0, '', 2);
		$this->notifications->delete_subscription('post', 1, 'email', 2);
		$this->notifications->delete_subscription('post', 2, 'jabber', 2);

		$this->assertEquals(array(
			array(
				'item_type'		=> 'post',
				'item_id'		=> 0,
				'user_id'		=> 2,
				'method'		=> 'email',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 0,
				'user_id'		=> 2,
				'method'		=> 'jabber',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 1,
				'user_id'		=> 2,
				'method'		=> '',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 1,
				'user_id'		=> 2,
				'method'		=> 'jabber',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 2,
				'user_id'		=> 2,
				'method'		=> '',
			),
			array(
				'item_type'		=> 'post',
				'item_id'		=> 2,
				'user_id'		=> 2,
				'method'		=> 'email',
			),
		), $this->notifications->get_subscriptions(2));
	}

	public function test_notifications()
	{
		$this->assertEquals(array(
				'notifications'		=> array(),
				'unread_count'		=> 0,
		), $this->notifications->load_notifications(array(
			'count_unread'	=> true,
		)));

		$this->notifications->add_notifications('ext_test-test', array(
			'post_id'		=> '1',
			'topic_id'		=> '1',
			'post_time'		=> 1349413321,
		));

		$notifications = $this->notifications->load_notifications(array(
			'count_unread'	=> true,
		));

		$this->assertEquals(1, $notifications['unread_count']);

		$notifications = $notifications['notifications'];

		$expected = array(
			1 => array(
				'item_type'			=> 'ext_test-test',
				'item_id'			=> 1,
				'item_parent_id'	=> 1,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413321,
				'data'				=> array(),
			),
		);

		$i = 0;
		foreach ($expected as $notification_id => $notification_data)
		{
			//echo $notifications[$i];

			$this->assertEquals($notification_id, $notifications[$i]->notification_id);

			foreach ($notification_data as $key => $value)
			{
				$this->assertEquals($value, $notifications[$i]->$key);
			}

			$i++;
		}




/*
		$notification = $this->notifications->get_item_type_class('phpbb_ext_test_notification_type_test');
		$notification->set_initial_data(array(
			'notification_id'	=> '1',
		));
		$notification->create_insert_array($notification_data);

		$this->dump($this->notifications->load_notifications());
		$this->dump(array('notifications' => $notification));

		$this->assertEquals(array(
				'notifications'		=> array(
					$notification,
				),
				'unread_count'		=> 0,
		), $this->notifications->load_notifications());*/
	}

	private function dump($array, $pre = '')
	{
		echo ($pre == '') ? "\n------------------------------------------------\n" : '';

		foreach ($array as $key => $value)
		{
			echo $pre . $key . ' => ';

			if (is_array($value))
			{
				echo "\n";

				$this->dump($value, $pre . "\t");
			}
			else
			{
				echo (string) $value;

				echo "\n";
			}
		}
	}
}
