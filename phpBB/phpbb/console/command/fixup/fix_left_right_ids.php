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
namespace phpbb\console\command\fixup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class fix_left_right_ids extends \phpbb\console\command\command
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache)
	{
		$this->user = $user;
		$this->db = $db;
		$this->cache = $cache;

		parent::__construct($user);
	}

	protected function configure()
	{
		$this
			->setName('fixup:fix-left-right-ids')
			->setDescription($this->user->lang('CLI_DESCRIPTION_FIX_LEFT_RIGHT_IDS'))
		;
	}

	/**
	* The code is mainly borrowed from Support toolkit for phpBB Olympus
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$changes_made = false;

		// Fix Left/Right IDs for the modules table
		$result = $this->db->sql_query('SELECT DISTINCT(module_class) FROM ' . MODULES_TABLE);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$i = 1;
			$where = array('module_class = \'' . $row['module_class'] .'\'');
			$changes_made = (($this->fixem($i, 'module_id', MODULES_TABLE, 0, $where)) || $changes_made) ? true : false;
		}
		$this->db->sql_freeresult($result);

		// Fix the Left/Right IDs for the forums table
		$i = 1;
		$changes_made = (($this->fixem($i, 'forum_id', FORUMS_TABLE)) || $changes_made) ? true : false;

		$this->cache->purge();

		$output->writeln('<info>' . $this->user->lang('CLI_FIXUP_FIX_LEFT_RIGHT_IDS_SUCCESS') . '</info>');
	}

	function fixem(&$i, $pkey, $table, $parent_id = 0, $where = array())
	{
		$changes_made = false;
		$sql = 'SELECT * FROM ' . $table . '
			WHERE parent_id = ' . (int) $parent_id .
			((!empty($where)) ? ' AND ' . implode(' AND ', $where) : '') . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Update the left_id for this module
			if ($row['left_id'] != $i)
			{
				$this->db->sql_query('UPDATE ' . $table . ' SET ' . $this->db->sql_build_array('UPDATE', array('left_id' => $i)) . " WHERE $pkey = {$row[$pkey]}");
				$changes_made = true;
			}
			$i++;

			// Go through children and update their left/right IDs
			$changes_made = (($this->fixem($i, $pkey, $table, $row[$pkey], $where)) || $changes_made) ? true : false;

			// Update the right_id for the module
			if ($row['right_id'] != $i)
			{
				$this->db->sql_query('UPDATE ' . $table . ' SET ' . $this->db->sql_build_array('UPDATE', array('right_id' => $i)) . " WHERE $pkey = {$row[$pkey]}");
				$changes_made = true;
			}
			$i++;
		}
		$this->db->sql_freeresult($result);

		return $changes_made;
	}
}
