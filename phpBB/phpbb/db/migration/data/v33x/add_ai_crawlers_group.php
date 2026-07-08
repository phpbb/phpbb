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

use phpbb\db\migration\exception;
use phpbb\db\migration\migration;

class add_ai_crawlers_group extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\bot_update_v2',
			'\phpbb\db\migration\data\v33x\guest_session_config',
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'add_crawlers_group']]],
		];
	}

	/**
	 * @throws exception
	 */
	public function add_crawlers_group(): void
	{
		$sql = 'SELECT group_id
			FROM ' . $this->table_prefix . 'groups
			WHERE ' . $this->db->sql_build_array('SELECT', ['group_name' => 'AI_CRAWLERS']);
		$result = $this->db->sql_query($sql);
		$group_exists = (bool) $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if ($group_exists)
		{
			return;
		}

		if (!function_exists('group_create'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$group_id = 0;
		$group_attributes = [
			'colour'			=> '7D9EAE',
			'legend'			=> 0,
			'max_recipients'	=> 5,
			'founder_manage'	=> 0,
		];

		$error = group_create($group_id, GROUP_SPECIAL, 'AI_CRAWLERS', '', $group_attributes);

		if ($error)
		{
			throw new exception('Failed to create AI_CRAWLERS group.');
		}
	}
}
