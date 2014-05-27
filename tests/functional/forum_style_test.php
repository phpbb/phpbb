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
