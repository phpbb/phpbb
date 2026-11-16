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

use OAuth\OAuth2\Token\StdOAuth2Token;
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
			array(new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param')), null),
			array(null, 'OAuth\Common\Storage\Exception\TokenNotFoundException'),
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
		catch (\OAuth\Common\Storage\Exception\TokenNotFoundException $e)
		{
		}

		$row = $this->get_token_row_by_session_id('abcd');
		$this->assertFalse($row);
	}

	public function test_retrieveAccessToken_from_db()
	{
		$expected_token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES);

		// Store a token in the database
		$temp_storage = new token_storage($this->db, $this->user, $this->token_storage_table, $this->state_table);
		$temp_storage->storeAccessToken($this->service_name, $expected_token);
		unset($temp_storage);

		// Test to see if the token can be retrieved
		$stored_token = $this->token_storage->retrieveAccessToken($this->service_name);
		$this->assertEquals($expected_token, $stored_token);
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
		$expected_token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES);

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
		$token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') );
		$this->token_storage->storeAccessToken($this->service_name, $token);

		// Confirm that the token is cached
		$extraParams = $this->token_storage->retrieveAccessToken($this->service_name)->getExtraParams();
		$this->assertEquals( 'param', $extraParams['extra'] );
		$this->assertEquals( 'access', $this->token_storage->retrieveAccessToken($this->service_name)->getAccessToken() );

		$row = $this->get_token_row_by_session_id($this->session_id);

		// The token is serialized before stored in the database
		$this->assertEquals($this->token_storage->json_encode_token($token), $row['oauth_token']);
	}

	public static function hasAccessToken_data()
	{
		return array(
			array(null, false),
			array(new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') ), true),
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
		$token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') );
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
		$token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') );
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
		$this->assertNull($this->token_storage->retrieveAuthorizationState($this->service_name));
	}

	public function test_retrieve_not_stored_state()
	{
		$result = $this->token_storage->retrieveAuthorizationState($this->service_name);
		$this->assertNull($result);
	}

	public function test_validate_authorization_state_invalid()
	{
		$google_service = $this->getMockBuilder(\OAuth\OAuth2\Service\Google::class)
			->setMethodsExcept(['requestAccessToken'])
			->disableOriginalConstructor()
			->getMock();
		$google_reflection = new \ReflectionClass($google_service);
		$storage = $google_reflection->getProperty('storage');
		$storage->setAccessible(true);
		$storage->setValue($google_service, $this->token_storage);

		$expected_state = 'abc123_securestate';
		$this->token_storage->storeAuthorizationState(\OAuth\OAuth2\Service\Google::class, $expected_state);

		$this->expectException(\OAuth\OAuth2\Service\Exception\InvalidAuthorizationStateException::class);

		$google_service->requestAccessToken('does_not_matter', 'foobar');
	}
}
