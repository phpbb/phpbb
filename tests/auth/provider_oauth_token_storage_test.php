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

use League\OAuth2\Client\Token\AccessToken;
use phpbb\auth\provider\oauth\token_storage;

require_once __DIR__ . '/phpbb_not_a_token.php';

class phpbb_auth_provider_oauth_token_storage_test extends phpbb_database_test_case
{
	protected $db;
	protected $service_name;
	protected $session_id;
	protected $token_storage;
	protected $token_storage_table;
	protected $state_table;

	/** @var \phpbb\user */
	protected $user;

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->service_name = 'auth.provider.oauth.service.testing';
		$this->token_storage_table = 'phpbb_oauth_tokens';
		$this->state_table = 'phpbb_oauth_states';

		// The base test schema predates the OAuth 2 client migration.
		$db_tools_factory = new \phpbb\db\tools\factory();
		$db_tools = $db_tools_factory->get($this->new_doctrine_dbal());
		if (!$db_tools->sql_column_exists($this->token_storage_table, 'oauth_resource_owner_id'))
		{
			$db_tools->sql_column_add($this->token_storage_table, 'oauth_resource_owner_id', ['VCHAR:255', '']);
		}

		// Give the user a session_id that we will remember
		$this->session_id = '12345';
		$this->user->data['session_id'] = $this->session_id;

		// Set the user id to anonymous
		$this->user->data['user_id'] = ANONYMOUS;

		$this->token_storage = new token_storage($this->db, $this->user, $this->token_storage_table, $this->state_table);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/oauth_tokens.xml');
	}

	public static function retrieveAccessToken_data()
	{
		return array(
			array(new AccessToken(['access_token' => 'access', 'refresh_token' => 'refresh', 'extra' => 'param']), null),
			array(null, 'RuntimeException'),
		);
	}

	/**
	* @dataProvider retrieveAccessToken_data
	*/
	public function test_retrieveAccessToken($cache_token, $exception)
	{
		if ($cache_token)
		{
			$this->token_storage->storeAccessToken($this->service_name, $cache_token);
			$token = $cache_token;
		}

		if (!empty($exception))
		{
			$this->expectException($exception);
		}

		$stored_token = $this->token_storage->retrieveAccessToken($this->service_name);
		$this->assertEquals($token, $stored_token);
	}

	public function test_retrieveAccessToken_wrong_token()
	{
		$this->user->data['session_id'] = 'abcd';
		try
		{
			$this->token_storage->retrieveAccessToken($this->service_name);
			$this->fail('The token can not be deserialized and an exception should be thrown.');
		}
		catch (\RuntimeException $e)
		{
		}

		$row = $this->get_token_row_by_session_id('abcd');
		$this->assertFalse($row);
	}

	public function test_retrieveAccessToken_from_db()
	{
		$expected_token = new AccessToken([
			'access_token' => 'access',
			'refresh_token' => 'refresh',
			'resource_owner_id' => 'resource-owner',
		]);

		// Store a token in the database
		$temp_storage = new token_storage($this->db, $this->user, $this->token_storage_table, $this->state_table);
		$temp_storage->storeAccessToken($this->service_name, $expected_token);
		unset($temp_storage);

		// Test to see if the token can be retrieved
		$stored_token = $this->token_storage->retrieveAccessToken($this->service_name);
		$this->assertEquals($expected_token, $stored_token);
		$this->assertSame('resource-owner', $stored_token->getResourceOwnerId());

		$row = $this->get_token_row_by_session_id($this->session_id);
		$this->assertSame('resource-owner', $row['oauth_resource_owner_id']);
	}

	/**
	* @dataProvider retrieveAccessToken_data
	*/
	public function test_retrieve_access_token_by_session($cache_token, $exception)
	{
		if ($cache_token)
		{
			$this->token_storage->storeAccessToken($this->service_name, $cache_token);
			$token = $cache_token;
		}

		if (!empty($exception))
		{
			$this->expectException($exception);
		}

		$stored_token = $this->token_storage->retrieve_access_token_by_session($this->service_name);
		$this->assertEquals($token, $stored_token);
	}

	public function test_retrieve_access_token_by_session_from_db()
	{
		$expected_token = new AccessToken(['access_token' => 'access', 'refresh_token' => 'refresh']);

		// Store a token in the database
		$temp_storage = new token_storage($this->db, $this->user,  $this->token_storage_table, $this->state_table);
		$temp_storage->storeAccessToken($this->service_name, $expected_token);
		unset($temp_storage);

		// Test to see if the token can be retrieved
		$stored_token = $this->token_storage->retrieve_access_token_by_session($this->service_name);
		$this->assertEquals($expected_token, $stored_token);
	}

	public function test_storeAccessToken()
	{
		$token = new AccessToken(['access_token' => 'access', 'refresh_token' => 'refresh', 'extra' => 'param']);
		$this->token_storage->storeAccessToken($this->service_name, $token);

		// Confirm that the token is cached
		$extraParams = $this->token_storage->retrieveAccessToken($this->service_name)->getValues();
		$this->assertEquals('param', $extraParams['extra']);
		$this->assertEquals('access', $this->token_storage->retrieveAccessToken($this->service_name)->getToken());

		$row = $this->get_token_row_by_session_id($this->session_id);

		// The token is serialized before stored in the database
		$this->assertEquals($this->token_storage->json_encode_token($token), $row['oauth_token']);
	}

	public static function hasAccessToken_data()
	{
		return array(
			array(null, false),
			array(new AccessToken(['access_token' => 'access', 'refresh_token' => 'refresh', 'extra' => 'param']), true),
		);
	}

	/**
	* @dataProvider hasAccessToken_data
	*/
	public function test_hasAccessToken($token, $expected)
	{
		if ($token)
		{
			$this->token_storage->storeAccessToken($this->service_name, $token);
		}

		$has_access_token = $this->token_storage->hasAccessToken($this->service_name);
		$this->assertEquals($expected, $has_access_token);
	}

	/**
	* @dataProvider hasAccessToken_data
	*/
	public function test_has_access_token_by_session($token, $expected)
	{
		if ($token)
		{
			$this->token_storage->storeAccessToken($this->service_name, $token);
		}

		$has_access_token = $this->token_storage->has_access_token_by_session($this->service_name);
		$this->assertEquals($expected, $has_access_token);
	}

	public function test_clearToken()
	{
		$token = new AccessToken(['access_token' => 'access', 'refresh_token' => 'refresh', 'extra' => 'param']);
		$this->token_storage->storeAccessToken($this->service_name, $token);

		$this->token_storage->clearToken($this->service_name);

		// Check that the database has been cleared
		$row = $this->get_token_row_by_session_id($this->session_id);
		$this->assertFalse($row);

		// Check that the token is no longer in memory
		$this->assertFalse($this->token_storage->hasAccessToken($this->service_name));
	}

	public function test_set_user_id()
	{
		$token = new AccessToken(['access_token' => 'access', 'refresh_token' => 'refresh', 'extra' => 'param']);
		$this->token_storage->storeAccessToken($this->service_name, $token);

		$new_user_id = ANONYMOUS + 1;
		$this->token_storage->set_user_id($new_user_id);

		$row = $this->get_token_row_by_session_id($this->session_id);
		$this->assertEquals($new_user_id, $row['user_id']);
	}

	protected function get_token_row_by_session_id($session_id)
	{
		// Test that the token is stored in the database
		$sql = 'SELECT * FROM phpbb_oauth_tokens
			WHERE session_id = \'' . $this->db->sql_escape($session_id) . '\'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	public function test_store_and_retrieve_cached_state()
	{
		$expected_state = 'abc123_securestate';

		$this->token_storage->storeAuthorizationState($this->service_name, $expected_state);
		$this->assertTrue($this->token_storage->hasAuthorizationState($this->service_name));
		$retrieved_state = $this->token_storage->retrieveAuthorizationState($this->service_name);

		$this->assertIsString($retrieved_state);
		$this->assertSame($expected_state, $retrieved_state);
	}

	public function test_store_and_retrieve_db_state()
	{
		$expected_state = 'abc123_securestate';

		$this->token_storage->storeAuthorizationState($this->service_name, $expected_state);

		$fresh_storage = new token_storage(
			$this->db,
			$this->user,
			$this->token_storage_table,
			$this->state_table
		);

		$retrieved_state = $fresh_storage->retrieveAuthorizationState($this->service_name);

		$this->assertIsString($retrieved_state);
		$this->assertSame($expected_state, $retrieved_state);
	}

	public function test_clear_db_state()
	{
		$expected_state = 'abc123_securestate';

		$this->token_storage->storeAuthorizationState($this->service_name, $expected_state);

		$fresh_storage = new token_storage(
			$this->db,
			$this->user,
			$this->token_storage_table,
			$this->state_table
		);

		$retrieved_state = $fresh_storage->retrieveAuthorizationState($this->service_name);

		$this->assertIsString($retrieved_state);
		$this->assertSame($expected_state, $retrieved_state);

		$this->token_storage->clearAuthorizationState($this->service_name);
		$this->assertFalse($this->token_storage->hasAuthorizationState($this->service_name));
		$this->expectException(\RuntimeException::class);
		$this->token_storage->retrieveAuthorizationState($this->service_name);
	}

	public function test_retrieve_not_stored_state()
	{
		$this->expectException(\RuntimeException::class);
		$result = $this->token_storage->retrieveAuthorizationState($this->service_name);
	}

	public function test_consume_authorization_state_is_exact_and_one_time()
	{
		$expected_state = 'abc123_securestate';
		$this->token_storage->storeAuthorizationState($this->service_name, $expected_state, 'verifier');

		$fresh_storage = new token_storage(
			$this->db,
			$this->user,
			$this->token_storage_table,
			$this->state_table
		);

		$this->assertSame([
			'state' => $expected_state,
			'code_verifier' => 'verifier',
		], $fresh_storage->consumeAuthorizationState($this->service_name, $expected_state));

		$this->expectException(\RuntimeException::class);
		$fresh_storage->consumeAuthorizationState($this->service_name, $expected_state);
	}

	public function test_consume_authorization_state_rejects_a_mismatched_state_without_consuming_it()
	{
		$expected_state = 'abc123_securestate';
		$this->token_storage->storeAuthorizationState($this->service_name, $expected_state);

		try
		{
			$this->token_storage->consumeAuthorizationState($this->service_name, 'unexpected_state');
			$this->fail('A mismatched authorization state must be rejected.');
		}
		catch (\RuntimeException $e)
		{
		}

		$this->assertSame([
			'state' => $expected_state,
			'code_verifier' => '',
		], $this->token_storage->consumeAuthorizationState($this->service_name, $expected_state));
	}

	public function test_consume_authorization_state_rejects_an_expired_state()
	{
		$expected_state = 'abc123_securestate';
		$this->token_storage->storeAuthorizationState($this->service_name, $expected_state);

		$sql = 'UPDATE ' . $this->state_table . '
			SET state_time = ' . (time() - 601) . "
			WHERE user_id = '" . (int) ANONYMOUS . "'
				AND session_id = '" . $this->db->sql_escape($this->session_id) . "'
				AND provider = '" . $this->db->sql_escape($this->service_name) . "'";
		$this->db->sql_query($sql);

		$this->expectException(\RuntimeException::class);
		$this->token_storage->consumeAuthorizationState($this->service_name, $expected_state);
	}
}
