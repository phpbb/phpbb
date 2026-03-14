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

namespace phpbb\db\migration\data\v33x;

class profilefields_x_update extends \phpbb\db\migration\migration
{

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\v3310',
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'update_other_profile_fields']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'revert_other_profile_fields']]],
		];
	}

	public function update_other_profile_fields(): void
	{
		$profile_fields = $this->table_prefix . 'profile_fields';

		$this->db->sql_query(
			"UPDATE $profile_fields
				SET field_contact_url = 'https://x.com/%s'
				WHERE field_name = 'phpbb_twitter'"
		);
	}

	public function revert_other_profile_fields(): void
	{
		$profile_fields = $this->table_prefix . 'profile_fields';

		$this->db->sql_query(
			"UPDATE $profile_fields
				SET field_contact_url = 'https://twitter.com/%s'
				WHERE field_name = 'phpbb_twitter'"
		);
	}

}
