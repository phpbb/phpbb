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
		$this->add_lang(['acp/common', 'acp/forums', 'common']);
		$this->login();
		$this->admin_login();

		$this->set_flood_interval(0);

		// Create a forum to move topics around
		$forum_name = 'MCP Test #1';
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('CREATE_FORUM'))->form([
			'forum_name'	=> $forum_name,
		]);
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'forum_parent_id'	=> 1,
			'forum_perm_from'	=> 2,
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('FORUM_CREATED', $crawler->text());

		// Create topics to test with
		$post = [];
		$post[] = $this->create_topic(2, 'Test Topic 3', 'Testing forum moderation actions from MCP/View forum page.');
		$crawler = self::request('GET', "viewtopic.php?t={$post[0]['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('Testing forum moderation actions from MCP/View forum page.', $crawler->filter('html')->text());

		$post[] = $this->create_topic(2, 'Topic to merge with', 'Testing merge topics moderation actions from MCP/View forum page.');
		$crawler = self::request('GET', "viewtopic.php?t={$post[1]['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('Testing merge topics moderation actions from MCP/View forum page.', $crawler->filter('html')->text());

		$this->set_flood_interval(15);

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
		$crawler = self::$client->click($crawler->selectLink($this->lang('MCP_SHORT'))->link());

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
		$crawler = self::$client->click($crawler->selectLink($this->lang('MCP_SHORT'))->link());

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
				$select_for_merge_link = $crawler->selectLink($this->lang('SELECT_MERGE'))->reduce(
					function ($node, $i) use ($topic_id_2)
					{
						return (bool) strpos($node->attr('href'), "to_topic_id=$topic_id_2");
					}
				)->link();

				$crawler = self::$client->click($select_for_merge_link);
			}

			$form = $crawler->selectButton($this->lang('YES'))->form();

			if (in_array($action, ['fork', 'move']))
			{
				// Fork or move the topic to the 'MCP Test #1'
				$forum_id = $crawler->filter('select > option')->reduce(
					function ($node, $i)
					{
						return (bool) strpos($node->text(), 'MCP Test #1');
					}
				)->attr('value');
				$form['to_forum_id']->select($forum_id);
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

		// Get to the forum 'MCP Test #1' where the topic has been moved to in previous test
		$crawler = self::request('GET', "index.php?sid={$this->sid}");
		$crawler = self::$client->click($crawler->selectLink('MCP Test #1')->link());
		$crawler = self::$client->click($crawler->selectLink($this->lang('MCP_SHORT'))->link());

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

	public function mcp_view_topic_actions_data()
	{
		// action, success message, require_confirmation
		return [
			['lock_post', 'POSTS_LOCKED_SUCCESS', true],
			['unlock_post', 'POSTS_UNLOCKED_SUCCESS', true],
			['resync', 'TOPIC_RESYNC_SUCCESS', false],
			['split_all', 'TOPIC_SPLIT_SUCCESS', true],
			['split_beyond', 'TOPIC_SPLIT_SUCCESS', true],
			['merge_posts', 'POSTS_MERGED_SUCCESS', true],
			['delete_post', 'POSTS_DELETED_SUCCESS', true],
		];
	}

	public function test_create_topic_with_replies()
	{
		$this->login();

		// Create topic and replies to test with
		$post = [];
		$post[] = $this->create_topic(2, 'Test Topic 4', 'Testing topic moderation actions from MCP/View topic page.');
		$crawler = self::request('GET', "viewtopic.php?t={$post[0]['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('Testing topic moderation actions from MCP/View topic page.', $crawler->filter('html')->text());

		// Create replies. Flood control was disabled above
		for ($i = 1; $i <= 15; $i++)
		{
			sleep(1);
			$post_text = "This is reply number $i to the Test Topic 4 to test moderation actions from MCP/View topic page.";
			$post[$i] = $this->create_post(2, $post[0]['topic_id'], 'Re: Test Topic 4', $post_text);
			$crawler = self::request('GET', "viewtopic.php?p={$post[$i]['post_id']}&sid={$this->sid}#p{$post[$i]['post_id']}");
			$this->assertStringContainsString($post_text, $crawler->filter('html')->text());
		}

		return $post;
	}

	/**
	 * @depends test_create_topic_with_replies
	 * @dataProvider mcp_view_topic_actions_data
	 */
	public function test_mcp_view_topic_actions($action, $message, $require_confirmation, $post)
	{
		$this->add_lang(['common', 'mcp']);
		$this->login();

		$crawler = self::request('GET', "viewtopic.php?t={$post[0]['topic_id']}&sid={$this->sid}");
		$crawler = self::$client->click($crawler->selectLink($this->lang('MCP_SHORT'))->link());
		$this->assertLessThanOrEqual(count($post), $crawler->filter('input[type=checkbox]')->count());

		// Test actions
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();

		// Set posts to select for actions
		$post_id_list = [];
		switch ($action)
		{
			case 'lock_post':
			case 'unlock_post':
				$post_id_list = [$post[1]['post_id'], $post[2]['post_id']];
			break;

			case 'split_all':
				$post_id_list = [$post[13]['post_id'], $post[14]['post_id'], $post[15]['post_id']]; // Split last 3 replies
				$subject = '[Split] Topic 1';
			break;

			case 'split_beyond':
				$post_id_list = [$post[10]['post_id']]; // Split from 10th reply
				$subject = '[Split] Topic 2';
			break;

			case 'merge_posts':
				$post_id_list = [$post[7]['post_id'], $post[8]['post_id'], $post[9]['post_id']]; // Split replies 7, 8, 9
			break;

			case 'delete_post':
				$post_id_list = [$post[4]['post_id'], $post[5]['post_id'], $post[6]['post_id']]; // Delete posts 4, 5, 6
			break;

			default:
			break;
		}

		$form->disableValidation()->setValues([
			'action' => $action,
			'post_id_list' => $post_id_list, // tick post ids
		]);
		$crawler = self::submit($form);

		if ($require_confirmation)
		{
			if ($action == 'merge_posts')
			{
				// Merge posts into '[Split] Topic 1'
				// Get topics list to select from
				$crawler = self::$client->click($crawler->selectLink($this->lang('SELECT_TOPIC'))->link());

				// Get '[Split] Topic 1' topic_id
				$to_topic_link = $crawler->selectLink('[Split] Topic 1')->attr('href');
				$to_topic_id = (int) $this->get_parameter_from_link($to_topic_link, 't');
				
				// Select '[Split] Topic 1'
				$select_for_merge_link = $crawler->selectLink($this->lang('SELECT_MERGE'))->reduce(
					function ($node, $i) use ($to_topic_id)
					{
						return (bool) strpos($node->attr('href'), "to_topic_id=$to_topic_id");
					}
				)->link();

				$crawler = self::$client->click($select_for_merge_link);

				$this->assertEquals($to_topic_id, (int) $crawler->filter('#to_topic_id')->attr('value'));

				// Reselect post ids to move
				$form = $crawler->selectButton($this->lang('SUBMIT'))->form()->disableValidation()->setValues(['post_id_list' => $post_id_list]);
				$crawler = self::submit($form);
			}

			if (in_array($action, ['split_all', 'split_beyond']))
			{
				$form = $crawler->selectButton($this->lang('SUBMIT'))->form()->disableValidation()->setValues([
					'subject' => $subject,
					'post_id_list' => $post_id_list, // tick post ids
					'to_forum_id' => 2,
				]);
				$crawler = self::submit($form);	
			}

			$form = $crawler->selectButton($this->lang('YES'))->form();
			$crawler = self::submit($form);
		}

		$this->assertStringContainsString($this->lang($message), $crawler->filter('#message p')->text());
	}
}
