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
	/** @var phpbb_db_driver */
	protected $db;

	/** @var phpbb_lock_db */
	protected $lock;

	/** @var String */
	protected $table_name;

	/** @var String */
	protected $item_class = 'phpbb_nestedset_item_forum';

	/**
	* Column names in the table
	* @var String
	*/
	protected $column_item_id = 'forum_id';
	protected $column_item_parents = 'forum_parents';

	/**
	* Additional SQL restrictions
	* Allows to have multiple nestedsets in one table
	* Columns must be prefixed with %1$s
	* @var String
	*/
	protected $sql_where = '';

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

	/**
	* @inheritdoc
	*/
	public function move_children(array $current_parent, array $new_parent)
	{
		while (!$this->lock->acquire())
		{
			// Retry after 0.2 seconds
			usleep(200 * 1000);
		}

		try
		{
			$return = parent::move_children($current_parent, $new_parent);
		}
		catch (phpbb_nestedset_exception $e)
		{
			$this->lock->release();
			throw new phpbb_nestedset_exception('FORUM_NESTEDSET_' . $e->getMessage());
		}
		$this->lock->release();

		return $return;
	}

	/**
	* @inheritdoc
	*/
	public function set_parent(array $item, array $new_parent)
	{
		while (!$this->lock->acquire())
		{
			// Retry after 0.2 seconds
			usleep(200 * 1000);
		}

		try
		{
			$return = parent::set_parent($item, $new_parent);
		}
		catch (phpbb_nestedset_exception $e)
		{
			$this->lock->release();
			throw new phpbb_nestedset_exception('FORUM_NESTEDSET_' . $e->getMessage());
		}
		$this->lock->release();

		return $return;
	}
}
