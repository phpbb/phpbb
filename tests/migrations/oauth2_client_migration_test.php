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

require_once __DIR__ . '/migration_test_base.php';

class phpbb_migrations_oauth2_client_migration_test extends phpbb_migration_test_base
{
	protected $migration_class = '\\phpbb\\db\\migration\\data\\v400\\oauth2_client';
	protected $fixture = '/fixtures/migration_oauth2_client.xml';

	public function test_oauth2_client_migration_clears_legacy_tokens_and_states()
	{
		$this->assertSame(1, $this->count_rows('phpbb_oauth_tokens'));
		$this->assertSame(1, $this->count_rows('phpbb_oauth_states'));
		$this->assertSame(2, $this->count_rows('phpbb_oauth_accounts'));
		$this->assertSame('oauth1-consumer-key', $this->get_config_value('auth_oauth_twitter_key'));
		$this->assertSame('oauth1-consumer-secret', $this->get_config_value('auth_oauth_twitter_secret'));

		$this->apply_migration();

		$this->assertTrue($this->db_tools->sql_column_exists('phpbb_oauth_tokens', 'oauth_resource_owner_id'));
		$this->assertTrue($this->db_tools->sql_column_exists('phpbb_oauth_states', 'oauth_code_verifier'));
		$this->assertSame(0, $this->count_rows('phpbb_oauth_tokens'));
		$this->assertSame(0, $this->count_rows('phpbb_oauth_states'));
		$this->assertSame(1, $this->count_rows('phpbb_oauth_accounts'));
		$this->assertSame(0, $this->count_oauth_account_rows('twitter'));
		$this->assertSame(1, $this->count_oauth_account_rows('google'));
		$this->assertSame(0, $this->count_config_rows('auth_oauth_twitter_key'));
		$this->assertSame(0, $this->count_config_rows('auth_oauth_twitter_secret'));

		$this->revert_migration();
		$this->assertFalse($this->db_tools->sql_column_exists('phpbb_oauth_tokens', 'oauth_resource_owner_id'));
		$this->assertFalse($this->db_tools->sql_column_exists('phpbb_oauth_states', 'oauth_code_verifier'));
		$this->apply_migration();
	}

	protected function count_rows(string $table): int
	{
		$result = $this->db->sql_query('SELECT COUNT(*) AS row_count FROM ' . $table);
		$count = (int) $this->db->sql_fetchfield('row_count');
		$this->db->sql_freeresult($result);

		return $count;
	}

	protected function count_oauth_account_rows(string $provider): int
	{
		$sql = "SELECT COUNT(*) AS row_count FROM phpbb_oauth_accounts
			WHERE provider = '" . $this->db->sql_escape($provider) . "'";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('row_count');
		$this->db->sql_freeresult($result);

		return $count;
	}

	protected function get_config_value(string $name): string
	{
		$sql = "SELECT config_value FROM phpbb_config
			WHERE config_name = '" . $this->db->sql_escape($name) . "'";
		$result = $this->db->sql_query($sql);
		$value = (string) $this->db->sql_fetchfield('config_value');
		$this->db->sql_freeresult($result);

		return $value;
	}

	protected function count_config_rows(string $name): int
	{
		$sql = "SELECT COUNT(*) AS row_count FROM phpbb_config
			WHERE config_name = '" . $this->db->sql_escape($name) . "'";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('row_count');
		$this->db->sql_freeresult($result);

		return $count;
	}
}
