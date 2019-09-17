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

namespace phpbb\db\migration\data\v32x;

class smtp_dynamic_data extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v326rc1',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'set_smtp_dynamic'))),
		);
	}

	public function set_smtp_dynamic()
	{
		$smtp_auth_entries = [
			'smtp_password',
			'smtp_username',
		];
		$this->sql_query('UPDATE ' . CONFIG_TABLE . '
			SET is_dynamic = 1
			WHERE ' . $this->db->sql_in_set('config_name', $smtp_auth_entries));
	}
}
