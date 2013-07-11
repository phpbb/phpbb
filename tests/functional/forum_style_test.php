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
	public function test_default_forum_style()
	{
		$crawler = self::request('GET', 'viewtopic.php?t=1&f=2');
		$this->assertContains('styles/prosilver/', $crawler->filter('head > link[rel=stylesheet]')->attr('href'));

		$crawler = self::request('GET', 'viewtopic.php?t=1');
		$this->assertContains('styles/prosilver/', $crawler->filter('head > link[rel=stylesheet]')->attr('href'));

		$crawler = self::request('GET', 'viewtopic.php?t=1&view=next');
		$this->assertContains('styles/prosilver/', $crawler->filter('head > link[rel=stylesheet]')->attr('href'));
	}

	public function test_custom_forum_style()
	{
		$db = $this->get_db();
		$this->add_style(2, 'test_style');
		$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET forum_style = 2 WHERE forum_id = 2');

		$crawler = self::request('GET', 'viewtopic.php?t=1&f=2');
		$this->assertContains('styles/test_style/', $crawler->filter('head > link[rel=stylesheet]')->attr('href'));

		$crawler = self::request('GET', 'viewtopic.php?t=1');
		$this->assertContains('styles/test_style/', $crawler->filter('head > link[rel=stylesheet]')->attr('href'));

		$crawler = self::request('GET', 'viewtopic.php?t=1&view=next');
		$this->assertContains('styles/test_style/', $crawler->filter('head > link[rel=stylesheet]')->attr('href'));

		$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET forum_style = 0 WHERE forum_id = 2');
		$this->delete_style(2, 'test_style');
	}
}
