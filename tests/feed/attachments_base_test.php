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

require_once(dirname(__FILE__) . '/attachments_mock_feed.php');

class phpbb_feed_attachments_base_test extends phpbb_database_test_case
{
	protected $filesystem;

	/** @var \phpbb_feed_attachments_mock_feed */
	protected $attachments_mocks_feed;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../extension/fixtures/extensions.xml');
	}

	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		$this->filesystem = new \phpbb\filesystem();
		$config = new \phpbb\config\config(array());
		$user = new \phpbb\user(
			new \phpbb\language\language(
				new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)
			),
			'\phpbb\datetime'
		);
		$feed_helper = new \phpbb\feed\helper($config, $user, $phpbb_root_path, $phpEx);
		$db = $this->new_dbal();
		$cache = new \phpbb_mock_cache();
		$auth = new \phpbb\auth\auth();
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
			$this->setExpectedException($expected_exception);

			$this->attachments_mocks_feed->get_sql();
		}
		else
		{
			$this->assertTrue($this->attachments_mocks_feed->get_sql());
		}
	}
}
