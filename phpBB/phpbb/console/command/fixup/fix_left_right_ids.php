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
use Symfony\Component\Console\Style\SymfonyStyle;

class fix_left_right_ids extends \phpbb\console\command\command
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/**
	* Constructor
	*
	* @param \phpbb\user							$user	User instance
	* @param \phpbb\db\driver\driver_interface		$db		Database connection
	* @param \phpbb\cache\driver\driver_interface	$cache	Cache instance
	*/
	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache)
	{
		$this->user = $user;
		$this->db = $db;
		$this->cache = $cache;

		parent::__construct($user);
	}

	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('fixup:fix-left-right-ids')
			->setDescription($this->user->lang('CLI_DESCRIPTION_FIX_LEFT_RIGHT_IDS'))
		;
	}

	/**
	* Executes the command fixup:fix-left-right-ids.
	*
	* Repairs the tree structure of the forums and modules.
	* The code is mainly borrowed from Support toolkit for phpBB Olympus
	*
	* @param InputInterface  $input  An InputInterface instance
	* @param OutputInterface $output An OutputInterface instance
	*
	* @return void
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		// Fix Left/Right IDs for the modules table
		$result = $this->db->sql_query('SELECT DISTINCT(module_class) FROM ' . MODULES_TABLE);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$i = 1;
			$where = array("module_class = '" . $this->db->sql_escape($row['module_class']) . "'");
			$this->fix_ids_tree($i, 'module_id', MODULES_TABLE, 0, $where);
		}
		$this->db->sql_freeresult($result);

		// Fix the Left/Right IDs for the forums table
		$i = 1;
		$this->fix_ids_tree($i, 'forum_id', FORUMS_TABLE);

		$this->cache->purge();

		$io->success($this->user->lang('CLI_FIXUP_FIX_LEFT_RIGHT_IDS_SUCCESS'));
	}

	/**
	 * Item's tree structure rebuild helper
	 * The item is either forum or ACP/MCP/UCP module
	 *
	 * @param int		$i			Item id offset index
	 * @param string	$field		The key field to fix, forum_id|module_id
	 * @param string	$table		The table name to perform, FORUMS_TABLE|MODULES_TABLE
	 * @param int		$parent_id	Parent item id
	 * @param array		$where		Additional WHERE clause condition
	 *
	 * @return bool	True on rebuild success, false otherwise
	 */
	protected function fix_ids_tree(&$i, $field, $table, $parent_id = 0, $where = array())
	{
		$changes_made = false;
		$sql = 'SELECT * FROM ' . $table . '
			WHERE parent_id = ' . (int) $parent_id .
			((!empty($where)) ? ' AND ' . implode(' AND ', $where) : '') . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Update the left_id for the item
			if ($row['left_id'] != $i)
			{
				$this->db->sql_query('UPDATE ' . $table . ' SET ' . $this->db->sql_build_array('UPDATE', array('left_id' => $i)) . " WHERE $field = " . (int) $row[$field]);
				$changes_made = true;
			}
			$i++;

			// Go through children and update their left/right IDs
			$changes_made = (($this->fix_ids_tree($i, $field, $table, $row[$field], $where)) || $changes_made) ? true : false;

			// Update the right_id for the item
			if ($row['right_id'] != $i)
			{
				$this->db->sql_query('UPDATE ' . $table . ' SET ' . $this->db->sql_build_array('UPDATE', array('right_id' => $i)) . " WHERE $field = " . (int) $row[$field]);
				$changes_made = true;
			}
			$i++;
		}
		$this->db->sql_freeresult($result);

		return $changes_made;
	}
}
