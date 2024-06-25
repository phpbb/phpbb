<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

use Symfony\Component\DependencyInjection\ContainerBuilder;

class mention_helper_test extends phpbb_database_test_case
{
	protected $db, $container, $user, $auth;

	/**
	 * @var \phpbb\textformatter\s9e\mention_helper
	 */
	protected $mention_helper;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/mention.xml');
	}

	public function setUp(): void
	{
		parent::setUp();

		global $auth, $db, $cache, $phpbb_container, $phpEx, $phpbb_root_path;

		// Disable caching for this test class
		$cache = null;

		// Database
		$this->db = $this->new_dbal();
		$db = $this->db;

		// Auth
		$auth = $this->createMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			 ->method('acl_gets')
			 ->with('a_group', 'a_groupadd', 'a_groupdel')
			 ->willReturn(false)
		;

		// Language
		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		// User
		$user = $this->createMock('\phpbb\user', array(), array(
			$lang,
			'\phpbb\datetime'
		));
		$user->ip = '';
		$user->data = array(
			'user_id'       => 2,
			'username'      => 'myself',
			'is_registered' => true,
			'user_colour'   => '',
		);

		// Container
		$phpbb_container = new phpbb_mock_container_builder();

		$phpbb_container->set('dbal.conn', $db);
		$phpbb_container->set('auth', $auth);
		$phpbb_container->set('user', $user);

		$this->get_test_case_helpers()->set_s9e_services($phpbb_container);

		$this->mention_helper = $phpbb_container->get('text_formatter.s9e.mention_helper');
	}

	public function inject_metadata_data()
	{
		return [
			[
				'<r><MENTION user_id="3"><s>[mention=u:3]</s>test<e>[/mention]</e></MENTION></r>',
				'mode=viewprofile&amp;u=3',
			],
			[
				'<r><MENTION group_id="3"><s>[mention=g:3]</s>test<e>[/mention]</e></MENTION></r>',
				'mode=group&amp;g=3',
			],
		];
	}

	/**
	 * @dataProvider inject_metadata_data
	 */
	public function test_inject_metadata($incoming_xml, $expected_profile_substring)
	{
		$result = $this->mention_helper->inject_metadata($incoming_xml);
		$this->assertStringContainsString($expected_profile_substring, $result);
	}

	public function get_mentioned_user_ids_data()
	{
		return [
			[
				'<r><MENTION user_id="3"><s>[mention=u:3]</s>test<e>[/mention]</e></MENTION><MENTION user_id="4"><s>[mention=u:4]</s>test<e>[/mention]</e></MENTION><MENTION user_id="5"><s>[mention=u:5]</s>test<e>[/mention]</e></MENTION></r>',
				[3, 4, 5],
			],
			[
				'<r><MENTION group_id="1"><s>[mention=g:1]</s>test<e>[/mention]</e></MENTION><MENTION group_id="2"><s>[mention=g:2]</s>test<e>[/mention]</e></MENTION><MENTION group_id="3"><s>[mention=g:3]</s>test<e>[/mention]</e></MENTION></r>',
				[4, 2, 6],
			],
		];
	}

	/**
	 * @dataProvider get_mentioned_user_ids_data
	 */
	public function test_get_mentioned_user_ids($incoming_xml, $expected_result)
	{
		$this->assertEqualsCanonicalizing($expected_result, $this->mention_helper->get_mentioned_user_ids($incoming_xml));
	}
}
