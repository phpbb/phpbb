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

class phpbb_installer_language_tasks_test extends phpbb_test_case
{
	protected function set_property($object, $name, $value)
	{
		$class = new \ReflectionClass($object);
		while (!$class->hasProperty($name))
		{
			$class = $class->getParentClass();
		}
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

	public static function profile_row_data()
	{
		$row = array('field_id' => '8', 'field_name' => 'phpbb_location');
		return array(array($row), array(array_change_key_case($row, CASE_UPPER)));
	}

	/**
	 * @dataProvider profile_row_data
	 */
	public function test_profile_language_row($row)
	{
		$statement = $this->createMock(\Doctrine\DBAL\Statement::class);
		$task = $this->getMockBuilder(\phpbb\install\module\install_data\task\setup_languages::class)
			->disableOriginalConstructor()->onlyMethods(array('exec_prepared_stmt'))->getMock();
		$task->expects($this->once())->method('exec_prepared_stmt')->with($statement, array('field_id' => '8', 'lang_id' => 1, 'lang_name' => 'LOCATION'));
		$this->set_property($task, 'stmt', $statement);
		$this->set_property($task, 'installed_languages', array(1));
		$method = new \ReflectionMethod($task, 'execute_step');
		$method->setAccessible(true);
		$method->invoke($task, 0, $row);
	}

	public function test_language_id_uses_unique_iso_lookup()
	{
		$statement = $this->createMock(\Doctrine\DBAL\Statement::class);
		$connection = $this->createMock(\Doctrine\DBAL\Connection::class);
		$connection->expects($this->never())->method('lastInsertId');
		$connection->expects($this->once())->method('fetchOne')
			->with('SELECT lang_id FROM phpbb_lang WHERE lang_iso = ?', array('en'))->willReturn('42');
		$config = $this->createMock(\phpbb\install\helper\config::class);
		$config->method('get')->with('installed_languages', array())->willReturn(array(5));
		$config->expects($this->once())->method('set')->with('installed_languages', array(5, 42));
		$task = $this->getMockBuilder(\phpbb\install\module\install_data\task\add_languages::class)
			->disableOriginalConstructor()->onlyMethods(array('exec_prepared_stmt'))->getMock();
		$task->expects($this->once())->method('exec_prepared_stmt');
		foreach (array('stmt' => $statement, 'conn' => $connection, 'config' => $config, 'lang_table' => 'phpbb_lang') as $name => $value)
		{
			$this->set_property($task, $name, $value);
		}
		$method = new \ReflectionMethod($task, 'execute_step');
		$method->setAccessible(true);
		$method->invoke($task, 0, array('iso' => 'en', 'name' => 'English', 'local_name' => 'English', 'author' => 'Fixture'));
	}
}
