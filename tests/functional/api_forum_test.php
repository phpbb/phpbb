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

	public function setUp()
	{
		parent::setUp();

		$db = $this->get_db();

		$db->sql_query("UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'allow_api'");

		$result = $db->sql_query("SELECT auth_option_id
		FROM phpbb_acl_options
		WHERE auth_option = 'u_api'");

		$id = $db->sql_fetchrow($result);

		$db->sql_query("INSERT INTO phpbb_acl_users
			VALUES (1, 0, " . (int) $id->auth_option_id . ", 5, 1)");

	}

	public function test_all_forums_guest_json()
	{
		$crawler = $this->request('GET', 'app.php?controller=api/forums', array(), false);

		$decoded = json_decode($crawler->text());
		$this->assertEquals(200, $decoded->status);
		$this->assertEquals(1, $decoded->data[0]->forum_id);
		$this->assertEquals(0, $decoded->data[0]->parent_id);
		$this->assertEquals('Your first category', $decoded->data[0]->forum_name);
		$this->assertEmpty($decoded->data[0]->forum_desc);
		$this->assertEmpty($decoded->data[0]->forum_link);
		$this->assertEmpty($decoded->data[0]->forum_image);
		$this->assertEmpty($decoded->data[0]->forum_rules);
		$this->assertEmpty($decoded->data[0]->forum_rules_link);
		$this->assertEquals(0, $decoded->data[0]->forum_type);
		$this->assertEquals(0, $decoded->data[0]->forum_posts_approved);
		$this->assertEquals(0, $decoded->data[0]->forum_topics_approved);
		$this->assertEquals(1, $decoded->data[0]->forum_last_post_id);
		$this->assertEquals(2, $decoded->data[0]->forum_last_poster_id);
		$this->assertEmpty($decoded->data[0]->forum_last_post_subject);
		$this->assertEquals('admin', $decoded->data[0]->forum_last_poster_name);
		$this->assertEquals('aa0000', $decoded->data[0]->forum_last_poster_colour);
		$this->assertNotNull($decoded->data[0]->subforums);

		$this->assertEquals(2, $decoded->data[0]->subforums[0]->forum_id);
		$this->assertEquals(1, $decoded->data[0]->subforums[0]->parent_id);
		$this->assertEquals('Your first forum', $decoded->data[0]->subforums[0]->forum_name);
		$this->assertEquals('Description of your first forum.', $decoded->data[0]->subforums[0]->forum_desc);
		$this->assertEmpty($decoded->data[0]->subforums[0]->forum_link);
		$this->assertEmpty($decoded->data[0]->subforums[0]->forum_image);
		$this->assertEmpty($decoded->data[0]->subforums[0]->forum_rules);
		$this->assertEmpty($decoded->data[0]->subforums[0]->forum_rules_link);
		$this->assertEquals(1, $decoded->data[0]->subforums[0]->forum_type);
		$this->assertEquals(1, $decoded->data[0]->subforums[0]->forum_posts_approved);
		$this->assertEquals(1, $decoded->data[0]->subforums[0]->forum_topics_approved);
		$this->assertEquals(1, $decoded->data[0]->subforums[0]->forum_last_post_id);
		$this->assertEquals(2, $decoded->data[0]->subforums[0]->forum_last_poster_id);
		$this->assertEquals('Welcome to phpBB3', $decoded->data[0]->subforums[0]->forum_last_post_subject);
		$this->assertEquals('admin', $decoded->data[0]->subforums[0]->forum_last_poster_name);
		$this->assertEquals('aa0000', $decoded->data[0]->subforums[0]->forum_last_poster_colour);
		$this->assertNull($decoded->data[0]->subforums[0]->subforums);
	}

	public function test_single_forum_guest_json()
	{
		$crawler = $this->request('GET', 'app.php?controller=api/forums/2', array(), false);

		$decoded = json_decode($crawler->text());

		$this->assertEquals(200, $decoded->status);
		$this->assertEquals(2, $decoded->data[0]->forum_id);
		$this->assertEquals(1, $decoded->data[0]->parent_id);
		$this->assertEquals('Your first forum', $decoded->data[0]->forum_name);
		$this->assertEquals('Description of your first forum.', $decoded->data[0]->forum_desc);
		$this->assertEmpty($decoded->data[0]->forum_link);
		$this->assertEmpty($decoded->data[0]->forum_image);
		$this->assertEmpty($decoded->data[0]->forum_rules);
		$this->assertEmpty($decoded->data[0]->forum_rules_link);
		$this->assertEquals(1, $decoded->data[0]->forum_type);
		$this->assertEquals(1, $decoded->data[0]->forum_posts_approved);
		$this->assertEquals(1, $decoded->data[0]->forum_topics_approved);
		$this->assertEquals(1, $decoded->data[0]->forum_last_post_id);
		$this->assertEquals(2, $decoded->data[0]->forum_last_poster_id);
		$this->assertEquals('Welcome to phpBB3', $decoded->data[0]->forum_last_post_subject);
		$this->assertEquals('admin', $decoded->data[0]->forum_last_poster_name);
		$this->assertEquals('aa0000', $decoded->data[0]->forum_last_poster_colour);
		$this->assertNull($decoded->data[0]->subforums);
	}
}
