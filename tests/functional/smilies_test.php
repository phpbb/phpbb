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
class phpbb_functional_smilies_test extends phpbb_functional_test_case
{
	public function test_smilies_mode()
	{
		$this->login();

		// Get smilies data
		$db = $this->get_db();
		$sql_ary = [
			'SELECT'	=> 's.smiley_url, MIN(s.emotion) AS emotion, MIN(s.code) AS code, s.smiley_width, s.smiley_height, MIN(s.smiley_order) AS min_smiley_order',
			'FROM'		=> [
				SMILIES_TABLE => 's',
			],
			'GROUP_BY'	=> 's.smiley_url, s.smiley_width, s.smiley_height',
			'ORDER_BY'	=> $db->sql_quote('min_smiley_order'),
		];
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		$smilies = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		// Visit smilies page
		$crawler = self::request('GET', 'posting.php?mode=smilies');
		foreach ($smilies as $index => $smiley)
		{
			$this->assertStringContainsString($smiley['smiley_url'],
				$crawler->filter('div[class="inner"] > a > img')->eq($index)->attr('src')
			);
		}
	}
}
