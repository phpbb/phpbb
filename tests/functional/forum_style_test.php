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
class phpbb_functional_forum_style_test extends phpbb_functional_test_case
{
	public function test_forum_style()
	{
		// Test with default style
		$crawler = $this->request('GET', 'viewtopic.php?t=1&f=2');
		$this->assert_response_success();
		$this->assertContains('styles/prosilver/theme/print.css', $this->client->getResponse()->getContent());

		$crawler = $this->request('GET', 'viewtopic.php?t=1&f=2&view=next');
		$this->assert_response_success();
		$this->assertContains('styles/prosilver/theme/print.css', $this->client->getResponse()->getContent());

		// Insert new style and change forum style
		$db = $this->get_db();
		$db->sql_multi_insert(STYLES_TABLE, array(
			'style_id' => 2,
			'style_name' => 'test_style',
			'style_copyright' => '',
			'style_active' => 1,
			'style_path' => 'test_style',
			'bbcode_bitfield' => 'kNg=',
			'style_parent_id' => 1,
			'style_parent_tree' => 'prosilver',
		));
		$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET forum_style = 2 WHERE forum_id = 2');

		// Test with custom style
		$crawler = $this->request('GET', 'viewtopic.php?t=1&f=2');
		$this->assert_response_success();
		$this->assertContains('styles/test_style/theme/print.css', $this->client->getResponse()->getContent());

		$crawler = $this->request('GET', 'viewtopic.php?t=1&f=2&view=next');
		$this->assert_response_success();
		$this->assertContains('styles/test_style/theme/print.css', $this->client->getResponse()->getContent());

		// Undo changes
		$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET forum_style = 0 WHERE forum_id = 2');
		$db->sql_query('DELETE FROM ' . STYLES_TABLE . ' WHERE style_id = 2');
	}
}
