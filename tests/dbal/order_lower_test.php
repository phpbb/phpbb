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

class phpbb_dbal_order_lower_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/styles.xml');
	}

	public function test_order_lower()
	{
		$db = $this->new_dbal();

		if (strpos($db->get_sql_layer(), 'mysql') === 0 && version_compare($db->sql_server_info(true, false), '5.6', '>='))
		{
			$this->markTestSkipped('MySQL 5.6 fails to order things correctly. See also: http://tracker.phpbb.com/browse/PHPBB3-11571 http://bugs.mysql.com/bug.php?id=69005');
		}

		// http://tracker.phpbb.com/browse/PHPBB3-10507
		// Test ORDER BY LOWER(style_name)
		$db->sql_return_on_error(true);

		$sql = 'SELECT * FROM phpbb_styles ORDER BY LOWER(style_name)';
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
				array(
					'style_id' 			=> 1,
					'style_name'		=> 'prosilver',
					'style_copyright'	=> '&copy; phpBB Limited',
					'style_active'		=> 1,
					'style_path'		=> 'prosilver',
					'bbcode_bitfield'	=> 'kNg=',
					'style_parent_id'	=> 0,
					'style_parent_tree'	=> '',
				),
				array(
					'style_id' 			=> 3,
					'style_name'		=> 'Prosilver1',
					'style_copyright'	=> '&copy; phpBB Limited',
					'style_active'		=> 0,
					'style_path'		=> 'prosilver1',
					'bbcode_bitfield'	=> 'kNg=',
					'style_parent_id'	=> 1,
					'style_parent_tree'	=> 'prosilver',
				),
				array(
					'style_id' 			=> 2,
					'style_name'		=> 'prosilver2',
					'style_copyright'	=> '&copy; phpBB Limited',
					'style_active'		=> 0,
					'style_path'		=> 'prosilver2',
					'bbcode_bitfield'	=> 'kNg=',
					'style_parent_id'	=> 0,
					'style_parent_tree'	=> '',
				)
			),
			$db->sql_fetchrowset($result)
		);
	}
}
