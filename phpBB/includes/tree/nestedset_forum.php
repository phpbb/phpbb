<?php
/**
*
* @package tree
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_tree_nestedset_forum extends phpbb_tree_nestedset
{
	/**
	* Construct
	*
	* @param phpbb_db_driver	$db		Database connection
	* @param phpbb_lock_db		$lock	Lock class used to lock the table when moving forums around
	* @param string				$table_name		Table name
	*/
	public function __construct(phpbb_db_driver $db, phpbb_lock_db $lock, $table_name)
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
