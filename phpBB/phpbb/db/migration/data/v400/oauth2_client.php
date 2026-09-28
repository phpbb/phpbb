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

use phpbb\db\migration\migration;

/**
 * Prepare OAuth state storage for the League OAuth 2.0 client.
 */
class oauth2_client extends migration
{
	public static function depends_on(): array
	{
		return ['\phpbb\db\migration\data\v400\v400a2'];
	}

	public function update_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'oauth_tokens' => [
					'oauth_resource_owner_id' => ['VCHAR:255', ''],
				],
				$this->table_prefix . 'oauth_states' => [
					'oauth_code_verifier' => ['VCHAR:128', ''],
				],
			],
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'clear_legacy_oauth_data']]],
		];
	}

	public function clear_legacy_oauth_data(): void
	{
		$this->db->sql_query('DELETE FROM ' . $this->table_prefix . 'oauth_tokens');
		$this->db->sql_query('DELETE FROM ' . $this->table_prefix . 'oauth_states');

		$this->remove_x_oauth_data();
	}

	/**
	 * Remove data for the X provider, which is no longer supported.
	 */
	protected function remove_x_oauth_data(): void
	{
		$this->db->sql_query("DELETE FROM " . $this->table_prefix . "oauth_accounts
			WHERE provider = 'twitter'");

		foreach (['auth_oauth_twitter_key', 'auth_oauth_twitter_secret'] as $config_name)
		{
			if (isset($this->config[$config_name]))
			{
				$this->config->delete($config_name);
			}
		}
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'oauth_tokens' => [
					'oauth_resource_owner_id',
				],
				$this->table_prefix . 'oauth_states' => [
					'oauth_code_verifier',
				],
			],
		];
	}
}
