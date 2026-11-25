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

require_once __DIR__ . '/base.php';

/**
* @group functional
*/
class phpbb_functional_search_sphinx_test extends phpbb_functional_search_base
{
	protected $search_backend = 'phpbb\search\backend\fulltext_sphinx';
	protected const TEST_SPHINX_ID = 'saw9zf2fdhp1goue';

	protected function setUp(): void
	{
		parent::setUp();

		if (!extension_loaded('pdo_mysql') && !extension_loaded('mysqli'))
		{
			$this->markTestSkipped('Neither pdo_mysql nor mysqli extension is loaded.');
		}

		$socket = @fsockopen('127.0.0.1', 9306, $errno, $errstr, 1.0);
		if ($socket === false)
		{
			$this->markTestSkipped('Sphinx searchd mysql41 listener is not running on 127.0.0.1:9306');
		}
		fclose($socket);

		$db = $this->get_db();
		$defaults = array(
			'fulltext_sphinx_id'			=> self::TEST_SPHINX_ID,
			'fulltext_sphinx_host'			=> '127.0.0.1',
			'fulltext_sphinx_port'			=> '9306',
			'fulltext_sphinx_data_path'		=> '/var/run/sphinxsearch/',
			'fulltext_sphinx_rt_mem_limit'	=> '128',
		);

		foreach ($defaults as $name => $value)
		{
			$sql = 'SELECT config_value
				FROM ' . CONFIG_TABLE . "
				WHERE config_name = '" . $db->sql_escape($name) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row === false)
			{
				$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'config_name'	=> $name,
					'config_value'	=> $value,
					'is_dynamic'	=> 0,
				));
				$db->sql_query($sql);
			}
			else
			{
				$sql = 'UPDATE ' . CONFIG_TABLE . "
					SET config_value = '" . $db->sql_escape($value) . "'
					WHERE config_name = '" . $db->sql_escape($name) . "'";
				$db->sql_query($sql);
			}
		}

		$this->purge_cache();
	}
}
