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

class user_signature extends \phpbb\textreparser\row_based_plugin
{
	/**
	* {@inheritdoc}
	*/
	protected function get_columns()
	{
		return array(
			'id'         => 'user_id',
			'text'       => 'user_sig',
			'bbcode_uid' => 'user_sig_bbcode_uid',
		);
	}

	/**
	* {@inheritdoc}
	*/
	protected function get_table_name()
	{
		return USERS_TABLE;
	}
}
