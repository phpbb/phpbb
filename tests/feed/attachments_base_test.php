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

require_once(__DIR__ . '/attachments_mock_feed.php');

class phpbb_feed_attachments_base_test extends phpbb_database_test_case
{
	/** @var \phpbb_feed_attachments_mock_feed */
	protected $attachments_mocks_feed;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../extension/fixtures/extensions.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$config = new \phpbb\config\config(array());
		$path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$this->createMock('\phpbb\request\request'),
			$phpbb_root_path,
			'php'
		);
		$user = new \phpbb\user(
			new \phpbb\language\language(
				new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)
			),
			'\phpbb\datetime'
		);
		$container = new phpbb_mock_container_builder();
		$this->get_test_case_helpers()->set_s9e_services($container);
		$container->set('feed.quote_helper', new \phpbb\feed\quote_helper($user, $phpbb_root_path, 'php'));
		$db = $this->new_dbal();
		$cache = new \phpbb_mock_cache();
		$auth = new \phpbb\auth\auth();
		$feed_helper = new \phpbb\feed\helper($auth, $config, $container, $path_helper, $container->get('text_formatter.renderer'), $user);
		$content_visibility = new \phpbb\content_visibility(
			$auth,
			$config,
			new \phpbb_mock_event_dispatcher(),
			$db,
			$user,
			$phpbb_root_path,
			$phpEx,
			FORUMS_TABLE,
			POSTS_TABLE,
			TOPICS_TABLE,
			USERS_TABLE
		);

		$this->attachments_mocks_feed = new \phpbb_feed_attachments_mock_feed(
			$feed_helper,
			$config,
			$db,
			$cache,
			$user,
			$auth,
			$content_visibility,
			new \phpbb_mock_event_dispatcher(),
			$phpEx
		);
	}

	public function data_fetch_attachments()
	{
		return array(
			array(array(0), array(0)),
			array(array(), array(1)),
			array(array(), array(), 'RuntimeException')
		);
	}

	/**
	 * @dataProvider data_fetch_attachments
	 */
	public function test_fetch_attachments($post_ids, $topic_ids, $expected_exception = false)
	{
		$this->attachments_mocks_feed->post_ids = $post_ids;
		$this->attachments_mocks_feed->topic_ids = $topic_ids;

		if ($expected_exception !== false)
		{
			$this->expectException($expected_exception);

			$this->attachments_mocks_feed->get_sql();
		}
		else
		{
			$this->assertTrue($this->attachments_mocks_feed->get_sql());
		}
	}
}
