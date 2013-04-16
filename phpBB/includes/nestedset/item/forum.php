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

class phpbb_nestedset_item_forum extends phpbb_nestedset_item_base
{
	public function __construct(array $forum_row)
	{
		$this->item_id = (int) $forum_row['forum_id'];
		$this->parent_id = (int) $forum_row['parent_id'];
		$this->left_id = (int) $forum_row['left_id'];
		$this->right_id = (int) $forum_row['right_id'];
		$this->item_parents_data = (string) $forum_row['forum_parents'];
	}
}
