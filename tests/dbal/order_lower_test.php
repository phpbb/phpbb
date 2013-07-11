<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

		if (strpos($db->sql_layer, 'mysql') === 0 && version_compare($db->sql_server_info(true, false), '5.6', '>='))
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
					'style_copyright'	=> '&copy; phpBB Group',
					'style_active'		=> 1,
					'template_id'		=> 1,
					'theme_id'			=> 1,
					'imageset_id'		=> 1
				),
				array(
					'style_id' 			=> 3,
					'style_name'		=> 'Prosilver1',
					'style_copyright'	=> '&copy; phpBB Group',
					'style_active'		=> 0,
					'template_id'		=> 3,
					'theme_id'			=> 3,
					'imageset_id'		=> 3
				),
				array(
					'style_id' 			=> 2,
					'style_name'		=> 'prosilver2',
					'style_copyright'	=> '&copy; phpBB Group',
					'style_active'		=> 0,
					'template_id'		=> 2,
					'theme_id'			=> 2,
					'imageset_id'		=> 2
				)
			),
			$db->sql_fetchrowset($result)
		);
	}
}
