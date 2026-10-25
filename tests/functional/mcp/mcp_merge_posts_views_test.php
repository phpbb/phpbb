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
class phpbb_functional_mcp_merge_posts_views_test extends phpbb_functional_test_case
{
	public function test_merge_posts_carries_over_topic_views()
	{
		$this->add_lang(['common', 'mcp']);
		$this->login();

		$source = $this->create_topic(2, 'Merge views source topic', 'Source topic for the topic views merge test.');
		$target = $this->create_topic(2, 'Merge views target topic', 'Target topic for the topic views merge test.');

		$db = $this->get_db();

		$sql = 'SELECT post_id
			FROM phpbb_posts
			WHERE topic_id = ' . (int) $source['topic_id'];
		$result = $db->sql_query($sql);
		$post_id_list = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$post_id_list[] = (int) $row['post_id'];
		}
		$db->sql_freeresult($result);
		$this->assertCount(1, $post_id_list);

		// Open the source topic's MCP topic view and start merging all its posts
		$crawler = self::request('GET', "viewtopic.php?t={$source['topic_id']}&sid={$this->sid}");
		$crawler = self::$client->click($crawler->selectLink($this->lang('MCP_SHORT'))->link());

		// Give both topics distinct view counts; set after the viewtopic request
		// so the counts cannot be bumped by our own page views
		$db->sql_query('UPDATE phpbb_topics SET topic_views = 7 WHERE topic_id = ' . (int) $source['topic_id']);
		$db->sql_query('UPDATE phpbb_topics SET topic_views = 3 WHERE topic_id = ' . (int) $target['topic_id']);

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form()->disableValidation()->setValues([
			'action'		=> 'merge_posts',
			'post_id_list'	=> $post_id_list,
		]);
		$crawler = self::submit($form);

		// Select the target topic to merge into
		$crawler = self::$client->click($crawler->selectLink($this->lang('SELECT_TOPIC'))->link());

		$to_topic_id = (int) $target['topic_id'];
		$select_for_merge_link = $crawler->selectLink($this->lang('SELECT_MERGE'))->reduce(
			function ($node, $i) use ($to_topic_id)
			{
				return (bool) strpos($node->attr('href'), "to_topic_id=$to_topic_id");
			}
		)->link();
		$crawler = self::$client->click($select_for_merge_link);
		$this->assertEquals($to_topic_id, (int) $crawler->filter('#to_topic_id')->attr('value'));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form()->disableValidation()->setValues([
			'post_id_list'	=> $post_id_list,
		]);
		$crawler = self::submit($form);

		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('POSTS_MERGED_SUCCESS'), $crawler->filter('#message p')->text());

		// The fully merged source topic is gone and its higher view count
		// has been carried over to the target topic
		$sql = 'SELECT COUNT(topic_id) AS num_topics
			FROM phpbb_topics
			WHERE topic_id = ' . (int) $source['topic_id'];
		$result = $db->sql_query($sql);
		$this->assertEquals(0, (int) $db->sql_fetchfield('num_topics'));
		$db->sql_freeresult($result);

		$sql = 'SELECT topic_views
			FROM phpbb_topics
			WHERE topic_id = ' . (int) $target['topic_id'];
		$result = $db->sql_query($sql);
		$this->assertEquals(7, (int) $db->sql_fetchfield('topic_views'));
		$db->sql_freeresult($result);
	}
}
