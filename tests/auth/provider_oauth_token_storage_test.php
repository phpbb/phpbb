<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use OAuth\OAuth2\Token\StdOAuth2Token;

class phpbb_auth_provider_oauth_token_storage_test extends phpbb_database_test_case
{
	protected $db;
	protected $service_name;
	protected $session_id;
	protected $token_storage;
	protected $token_storage_table;
	protected $user;

	protected function setup()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$this->user = $this->getMock('phpbb_user');
		$this->service_name = 'auth.provider.oauth.service.testing';
		$this->token_storage_table = 'phpbb_oauth_tokens';

		// Give the user a session_id that we will remember
		$this->session_id = '12345';
		$this->user->data['session_id'] = $this->session_id;

		// Set the user id to anonymous
		$this->user->data['user_id'] = ANONYMOUS;

		$this->token_storage = new phpbb_auth_provider_oauth_token_storage($this->db, $this->user, $this->service_name, $this->token_storage_table);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/oauth_tokens.xml');
	}

	public static function retrieveAccessToken_data()
	{
		return array(
			array(null, new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param')), null),
			array(new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') ), null, null),
			array(null, null, 'OAuth\Common\Storage\Exception\TokenNotFoundException'),
		);
	}

	/**
	* @dataProvider retrieveAccessToken_data
	*/
	public function test_retrieveAccessToken($cache_token, $db_token, $exception)
	{
		if ($db_token)
		{
			$temp_storage = new phpbb_auth_provider_oauth_token_storage($this->db, $this->user, $this->service_name, $this->token_storage_table);
			$temp_storage->storeAccessToken($db_token);
			unset($temp_storage);
			$token = $db_token;
		}

		if ($cache_token)
		{
			$this->token_storage->storeAccessToken($cache_token);
			$token = $cache_token;
		}

		$this->setExpectedException($exception);

		$stored_token = $this->token_storage->retrieveAccessToken();
		$this->assertEquals($token, $stored_token);
	}

	public function test_storeAccessToken()
	{
		$token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') );
		$this->token_storage->storeAccessToken($token);

		// Confirm that the token is cached
		$extraParams = $this->token_storage->retrieveAccessToken()->getExtraParams();
		$this->assertEquals( 'param', $extraParams['extra'] );
		$this->assertEquals( 'access', $this->token_storage->retrieveAccessToken()->getAccessToken() );

		$row = $this->get_token_row_by_session_id($this->session_id);

		// The token is serialized before stored in the database
		$this->assertEquals(serialize($token), $row['oauth_token']);
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
			$this->token_storage->storeAccessToken($token);
		}

		$has_access_token = $this->token_storage->hasAccessToken();
		$this->assertEquals($expected, $has_access_token);
	}

	public function test_clearToken()
	{
		$token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') );
		$this->token_storage->storeAccessToken($token);

		$this->token_storage->clearToken();

		// Check that the database has been cleared
		$row = $this->get_token_row_by_session_id($this->session_id);
		$this->assertFalse($row);

		// Check that the token is no longer in memory
		$this->assertFalse($this->token_storage->hasAccessToken());
	}

	public function test_set_user_id()
	{
		$token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') );
		$this->token_storage->storeAccessToken($token);

		$new_user_id = ANONYMOUS + 1;
		$this->token_storage->set_user_id($new_user_id);

		$row = $this->get_token_row_by_session_id($this->session_id);
		$this->assertEquals($new_user_id, $row['user_id']);
	}

	protected function get_token_row_by_session_id($session_id)
	{
		// Test that the token is stored in the database
		$sql = 'SELECT * FROM phpbb_oauth_tokens 
			WHERE session_id = \'' . $session_id . '\'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}
}
