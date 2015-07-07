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

namespace phpbb\textreparser\plugins;

class forum_description extends \phpbb\textreparser\row_based_plugin
{
	/**
	* {@inheritdoc}
	*/
	public function get_columns()
	{
		return array(
			'id'         => 'forum_id',
			'text'       => 'forum_desc',
			'bbcode_uid' => 'forum_desc_uid',
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_table_name()
	{
		return FORUMS_TABLE;
	}
}
