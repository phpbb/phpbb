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

abstract class phpbb_nestedset_item_base implements phpbb_nestedset_item_interface
{
	/** @var int */
	protected $item_id;

	/** @var int */
	protected $parent_id;

	/** @var string */
	protected $item_parents_data;

	/** @var int */
	protected $left_id;

	/** @var int */
	protected $right_id;

	/**
	* @inheritdoc
	*/
	public function get_item_id()
	{
		return (int) $this->item_id;
	}

	/**
	* @inheritdoc
	*/
	public function get_parent_id()
	{
		return (int) $this->parent_id;
	}

	/**
	* @inheritdoc
	*/
	public function get_item_parents_data()
	{
		return (string) $this->item_parents_data;
	}

	/**
	* @inheritdoc
	*/
	public function get_left_id()
	{
		return (int) $this->left_id;
	}

	/**
	* @inheritdoc
	*/
	public function get_right_id()
	{
		return (int) $this->right_id;
	}

	/**
	* @inheritdoc
	*/
	public function has_children()
	{
		return $this->right_id - $this->left_id > 1;
	}
}
