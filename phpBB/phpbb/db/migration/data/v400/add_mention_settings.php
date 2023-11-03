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

namespace phpbb\db\migration\data\v330;

class add_mention_settings extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('allow_mentions', true)),
			array('config.add', array('mention_batch_size', 50)),
			array('config.add', array('mention_names_limit', 10)),

			// Set up user permissions
			array('permission.add', array('u_mention', true)),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_mention')),
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_mention')),
			array('permission.permission_set', array('ROLE_USER_LIMITED', 'u_mention')),
			array('permission.permission_set', array('ROLE_USER_NOPM', 'u_mention')),
			array('permission.permission_set', array('ROLE_USER_NOAVATAR', 'u_mention')),

			// Set up forum permissions
			array('permission.add', array('f_mention', false)),
			array('permission.permission_set', array('ROLE_FORUM_FULL', 'f_mention')),
			array('permission.permission_set', array('ROLE_FORUM_STANDARD', 'f_mention')),
			array('permission.permission_set', array('ROLE_FORUM_LIMITED', 'f_mention')),
			array('permission.permission_set', array('ROLE_FORUM_ONQUEUE', 'f_mention')),
			array('permission.permission_set', array('ROLE_FORUM_POLLS', 'f_mention')),
			array('permission.permission_set', array('ROLE_FORUM_LIMITED_POLLS', 'f_mention')),
		);
	}
}
