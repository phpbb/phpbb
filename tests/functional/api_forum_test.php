<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @group functional
 */
class phpbb_functional_api_forum_test extends phpbb_functional_test_case
{
	public function test_all_forums_json()
	{
		$crawler = $this->request('GET', 'app.php?controller=api/forums');

		$decoded = json_decode($crawler->text());

		$this->assertEquals(1, $decoded[0]->forum_id);
		$this->assertEquals(0, $decoded[0]->parent_id);
		$this->assertEquals('Your first category', $decoded[0]->forum_name);
		$this->assertEmpty($decoded[0]->forum_desc);
		$this->assertEmpty($decoded[0]->forum_link);
		$this->assertEmpty($decoded[0]->forum_image);
		$this->assertEmpty($decoded[0]->forum_rules);
		$this->assertEmpty($decoded[0]->forum_rules_link);
		$this->assertEquals(0, $decoded[0]->forum_type);
		$this->assertEquals(1, $decoded[0]->forum_posts);
		$this->assertEquals(1, $decoded[0]->forum_topics);
		$this->assertEquals(1, $decoded[0]->forum_topics_real);
		$this->assertEquals(1, $decoded[0]->forum_last_post_id);
		$this->assertEquals(2, $decoded[0]->forum_last_poster_id);
		$this->assertEmpty($decoded[0]->forum_last_post_subject);
		$this->assertEquals('admin', $decoded[0]->forum_last_poster_name);
		$this->assertEquals('aa0000', $decoded[0]->forum_last_poster_colour);
		$this->assertNotNull($decoded[0]->subforums);

		$this->assertEquals(2, $decoded[0]->subforums[0]->forum_id);
		$this->assertEquals(1, $decoded[0]->subforums[0]->parent_id);
		$this->assertEquals('Your first forum', $decoded[0]->subforums[0]->forum_name);
		$this->assertEquals('Description of your first forum.', $decoded[0]->subforums[0]->forum_desc);
		$this->assertEmpty($decoded[0]->subforums[0]->forum_link);
		$this->assertEmpty($decoded[0]->subforums[0]->forum_image);
		$this->assertEmpty($decoded[0]->subforums[0]->forum_rules);
		$this->assertEmpty($decoded[0]->subforums[0]->forum_rules_link);
		$this->assertEquals(1, $decoded[0]->subforums[0]->forum_type);
		$this->assertEquals(1, $decoded[0]->subforums[0]->forum_posts);
		$this->assertEquals(1, $decoded[0]->subforums[0]->forum_topics);
		$this->assertEquals(1, $decoded[0]->subforums[0]->forum_topics_real);
		$this->assertEquals(1, $decoded[0]->subforums[0]->forum_last_post_id);
		$this->assertEquals(2, $decoded[0]->subforums[0]->forum_last_poster_id);
		$this->assertEquals('Welcome to phpBB3', $decoded[0]->subforums[0]->forum_last_post_subject);
		$this->assertEquals('admin', $decoded[0]->subforums[0]->forum_last_poster_name);
		$this->assertEquals('aa0000', $decoded[0]->subforums[0]->forum_last_poster_colour);
		$this->assertNull($decoded[0]->subforums[0]->subforums);
	}

	public function test_single_forum_json()
	{
		$crawler = $this->request('GET', 'app.php?controller=api/forums/2');

		$decoded = json_decode($crawler->text());

		$this->assertEquals(2, $decoded[0]->forum_id);
		$this->assertEquals(1, $decoded[0]->parent_id);
		$this->assertEquals('Your first forum', $decoded[0]->forum_name);
		$this->assertEquals('Description of your first forum.', $decoded[0]->forum_desc);
		$this->assertEmpty($decoded[0]->forum_link);
		$this->assertEmpty($decoded[0]->forum_image);
		$this->assertEmpty($decoded[0]->forum_rules);
		$this->assertEmpty($decoded[0]->forum_rules_link);
		$this->assertEquals(1, $decoded[0]->forum_type);
		$this->assertEquals(1, $decoded[0]->forum_posts);
		$this->assertEquals(1, $decoded[0]->forum_topics);
		$this->assertEquals(1, $decoded[0]->forum_topics_real);
		$this->assertEquals(1, $decoded[0]->forum_last_post_id);
		$this->assertEquals(2, $decoded[0]->forum_last_poster_id);
		$this->assertEquals('Welcome to phpBB3', $decoded[0]->forum_last_post_subject);
		$this->assertEquals('admin', $decoded[0]->forum_last_poster_name);
		$this->assertEquals('aa0000', $decoded[0]->forum_last_poster_colour);
		$this->assertNull($decoded[0]->subforums);
	}
}
