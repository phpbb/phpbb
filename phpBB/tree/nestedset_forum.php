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

namespace phpbb\tree;

class nestedset_forum extends \phpbb\tree\nestedset
{
	/**
	* Construct
	*
	* @param \phpbb\db\driver\driver_interface	$db		Database connection
	* @param \phpbb\lock\db		$lock	Lock class used to lock the table when moving forums around
	* @param string				$table_name		Table name
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\lock\db $lock, $table_name)
	{
		parent::__construct(
			$db,
			$lock,
			$table_name,
			'FORUM_NESTEDSET_',
			'',
			array(
				'forum_id',
				'forum_name',
				'forum_type',
			),
			array(
				'item_id'		=> 'forum_id',
				'item_parents'	=> 'forum_parents',
			)
		);
	}
}
