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
class phpbb_functional_mcp_main_test extends phpbb_functional_test_case
{
	public function test_create_topics()
	{
		$this->add_lang(['acp/common', 'common']);
		$this->login();
		$this->admin_login();

		// Disable flood intervar to post >1 of topics
		$crawler = self::request('GET', "adm/index.php?i=acp_board&mode=post&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[flood_interval]'	=> 0,
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->text());

		// Create topics to test with
		$post = [];
		$post[] = $this->create_topic(2, 'Test Topic 3', 'Testing forum moderation actions from MCP/View forum page.');
		$crawler = self::request('GET', "viewtopic.php?t={$post[0]['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('Testing forum moderation actions from MCP/View forum page.', $crawler->filter('html')->text());

		$post[] = $this->create_topic(2, 'Topic to merge with', 'Testing merge topics moderation actions from MCP/View forum page.');
		$crawler = self::request('GET', "viewtopic.php?t={$post[1]['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('Testing merge topics moderation actions from MCP/View forum page.', $crawler->filter('html')->text());

		return $post;
}


	/**
	 * @depends test_create_topics
	 */
	public function test_mcp_view_forum($post)
	{
		$this->add_lang(['common']);
		$this->login();

		// Browse MCP main page from forum view (gives &f=2)
		$crawler = self::request('GET', "viewforum.php?f=2&sid={$this->sid}");
		$mcp_link = substr_replace($crawler->selectLink($this->lang('MCP_SHORT'))->attr('href'), '', 0, 2); // Remove leading ./
		$crawler = self::request('GET', $mcp_link);

		// Test forum moderation page has a list of topics to select
		$this->assertGreaterThanOrEqual(3, $crawler->filter('input[type=checkbox]')->count());

		return $post;
	}

	public function mcp_view_forum_actions_data()
	{
		// action, success message, require_confirmation
		return [
			['delete_topic', 'TOPIC_DELETED_SUCCESS', true],
			['restore_topic', 'TOPIC_RESTORED_SUCCESS', true],
			['fork', 'TOPIC_FORKED_SUCCESS', true],
			['lock', 'TOPIC_LOCKED_SUCCESS', true],
			['unlock', 'TOPIC_UNLOCKED_SUCCESS', true],
			['resync', 'TOPIC_RESYNC_SUCCESS', false],
			['make_global', 'TOPIC_TYPE_CHANGED', true],
			['make_announce', 'TOPIC_TYPE_CHANGED', true],
			['make_sticky', 'TOPIC_TYPE_CHANGED', true],
			['make_normal', 'TOPIC_TYPE_CHANGED', true],
			['merge_topics', 'POSTS_MERGED_SUCCESS', true],
			['move', 'TOPIC_MOVED_SUCCESS', true],
		];
	}

	/**
	 * @depends test_mcp_view_forum
	 * @dataProvider mcp_view_forum_actions_data
	 */
	public function test_mcp_view_forum_actions($action, $message, $require_confirmation, $post)
	{
		$topic_id_1 = $post[0]['topic_id'];
		$topic_id_2 = $post[1]['topic_id'];

		$this->add_lang(['common', 'mcp']);
		$this->login();

		$crawler = self::request('GET', "viewforum.php?f=2&sid={$this->sid}");
		$mcp_link = substr_replace($crawler->selectLink($this->lang('MCP_SHORT'))->attr('href'), '', 0, 2); // Remove leading ./
		$crawler = self::request('GET', $mcp_link);

		// Test actions
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form()->disableValidation()->setValues([
			'action' => $action,
			'topic_id_list' => [$action == 'move' ? $topic_id_2 : $topic_id_1], // while moving, topic_id_1 has been already merged into topic_id_2
		]);
		$crawler = self::submit($form);

		if ($require_confirmation)
		{
			if ($action == 'merge_topics')
			{
				// Merge topic_id_1 into topic_id_2
				$select_for_merge_link = substr_replace($crawler->filter('.row a')->reduce(
					function ($node, $i) use ($topic_id_2)
					{
						return (bool) strpos($node->attr('href'), "to_topic_id=$topic_id_2");
					}
				)->attr('href'), '', 0, 2); // Remove leading ./

				$crawler = self::request('GET', $select_for_merge_link);
			}

			$form = $crawler->selectButton($this->lang('YES'))->form();

			if (in_array($action, ['fork', 'move']))
			{
				// Fork or move the topic to the forum id=3 'Download #1'
				$form->setValues(['to_forum_id' => 3]);
			}

			$crawler = self::submit($form);
		}

		$this->assertStringContainsString($this->lang($message), $crawler->filter('#message p')->text());
	}

	/**
	 * @depends test_mcp_view_forum_actions
	 */
	public function test_mcp_view_forum_permanently_delete_topic()
	{
		$this->add_lang(['common', 'mcp']);
		$this->login();

		// Get to the forum id=3 'Download #1' where the topic has been moved to in previous test
		$crawler = self::request('GET', "viewforum.php?f=3&sid={$this->sid}");
		$mcp_link = substr_replace($crawler->selectLink($this->lang('MCP_SHORT'))->attr('href'), '', 0, 2); // Remove leading ./
		$crawler = self::request('GET', $mcp_link);

		// Get topic ids to delete (forked and moved topics in the previous test)
		$topic_link_1 = $crawler->selectLink('Test Topic 3')->attr('href');
		$topic_link_2 = $crawler->selectLink('Topic to merge with')->attr('href');
		$topic_ids = [
			(int) $this->get_parameter_from_link($topic_link_1, 't'),
			(int) $this->get_parameter_from_link($topic_link_2, 't'),
		];

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form()->disableValidation()->setValues([
			'action' => 'delete_topic',
			'topic_id_list' => $topic_ids, // tick both topics in the list
		]);
		$crawler = self::submit($form);

		$form = $crawler->selectButton($this->lang('YES'))->form();
		$form['delete_permanent']->tick();
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('TOPICS_DELETED_SUCCESS'), $crawler->filter('#message p')->text());
	}
}
