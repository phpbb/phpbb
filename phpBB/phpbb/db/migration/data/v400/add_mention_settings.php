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

namespace phpbb\db\migration\data\v400;

class add_mention_settings extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v400\dev'];
	}

	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('allow_mentions')
			&& $this->config->offsetExists('mention_batch_size')
			&& $this->config->offsetExists('mention_names_limit');
	}

	public function update_data()
	{
		return [
			['config.add', ['allow_mentions', true]],
			['config.add', ['mention_batch_size', 50]],
			['config.add', ['mention_names_limit', 10]],

			// Set up user permissions
			['permission.add', ['u_mention', true]],
			['if', [
				['permission.role_exists', ['ROLE_USER_FULL']],
				['permission.permission_set', ['ROLE_USER_FULL', 'u_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_USER_STANDARD']],
				['permission.permission_set', ['ROLE_USER_STANDARD', 'u_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_USER_LIMITED']],
				['permission.permission_set', ['ROLE_USER_LIMITED', 'u_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_USER_NOPM']],
				['permission.permission_set', ['ROLE_USER_NOPM', 'u_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_USER_NOAVATAR']],
				['permission.permission_set', ['ROLE_USER_NOAVATAR', 'u_mention']],
			]],

			// Set up forum permissions
			['permission.add', ['f_mention', false]],
			['if', [
				['permission.role_exists', ['ROLE_FORUM_FULL']],
				['permission.permission_set', ['ROLE_FORUM_FULL', 'f_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_FORUM_STANDARD']],
				['permission.permission_set', ['ROLE_FORUM_STANDARD', 'f_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_FORUM_LIMITED']],
				['permission.permission_set', ['ROLE_FORUM_LIMITED', 'f_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_FORUM_ONQUEUE']],
				['permission.permission_set', ['ROLE_FORUM_ONQUEUE', 'f_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_FORUM_POLLS']],
				['permission.permission_set', ['ROLE_FORUM_POLLS', 'f_mention']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_FORUM_LIMITED_POLLS']],
				['permission.permission_set', ['ROLE_FORUM_LIMITED_POLLS', 'f_mention']],
			]],
		];
	}
}
