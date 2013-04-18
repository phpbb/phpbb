<?php
/**
*
* @package Nested Set
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

class phpbb_nestedset_forum extends phpbb_nestedset_base
{
	/**
	* Column names in the table
	* @var string
	*/
	protected $column_item_id = 'forum_id';
	protected $column_item_parents = 'forum_parents';

	/**
	* Prefix for the language keys returned by exceptions
	* @var string
	*/
	protected $message_prefix = 'FORUM_NESTEDSET_';

	/**
	* List of item properties to be cached in $item_parents
	* @var array
	*/
	protected $item_basic_data = array('forum_id', 'forum_name', 'forum_type');

	/**
	* Construct
	*
	* @param phpbb_db_driver	$db		Database connection
	* @param phpbb_lock_db		$lock	Lock class used to lock the table when moving forums around
	* @param string				$table_name		Table name
	*/
	public function __construct(phpbb_db_driver $db, phpbb_lock_db $lock, $table_name)
	{
		$this->db = $db;
		$this->lock = $lock;
		$this->table_name = $table_name;
	}
}
