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

class phpbb_installer_create_search_index_test extends phpbb_test_case
{
	public static function post_row_data()
	{
		$row = array('post_id' => '42', 'post_text' => 'Message', 'post_subject' => 'Subject', 'poster_id' => '7', 'forum_id' => '2');
		return array(array($row), array(array_change_key_case($row, CASE_UPPER)));
	}

	/**
	 * @dataProvider post_row_data
	 */
	public function test_index_post_row($row)
	{
		$indexer = $this->getMockBuilder(\phpbb\search\backend\fulltext_native::class)
			->disableOriginalConstructor()
			->onlyMethods(array('index'))
			->getMock();
		$indexer->expects($this->once())->method('index')->with('post', 42, 'Message', 'Subject', 7, 2);
		$class = new \ReflectionClass(\phpbb\install\module\install_data\task\create_search_index::class);
		$task = $class->newInstanceWithoutConstructor();
		$property = $class->getProperty('search_indexer');
		$property->setAccessible(true);
		$property->setValue($task, $indexer);
		$method = $class->getMethod('execute_step');
		$method->setAccessible(true);
		$method->invoke($task, 0, $row);
	}
}
