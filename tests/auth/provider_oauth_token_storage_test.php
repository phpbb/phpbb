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
	protected $session_id;
	protected $token_storage;
	protected $user;

	protected function setup()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$this->user = $this->getMock('phpbb_user');
		$service_name = 'auth.provider.oauth.service.testing';
		$token_storage_table = 'phpbb_oauth_tokens';

		// Give the user a session_id that we will remember
		$this->session_id = '12345';
		$this->user->data['session_id'] = $this->session_id;

		// Set the user id to anonymous
		$this->user->data['user_id'] = ANONYMOUS;

		$this->token_storage = new phpbb_auth_provider_oauth_token_storage($this->db, $this->user, $service_name, $token_storage_table);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/oauth_tokens.xml');
	}

	public function test_retrieveAccessToken()
	{

	}

	public function test_storeAccessToken()
	{
		$token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param') );
		$this->token_storage->storeAccessToken($token);

		// Confirm that the token is cached
		$extraParams = $this->token_storage->retrieveAccessToken()->getExtraParams();
        $this->assertEquals( 'param', $extraParams['extra'] );
        $this->assertEquals( 'access', $this->token_storage->retrieveAccessToken()->getAccessToken() );

        // Test that the token is stored in the database
        $sql = 'SELECT oauth_token FROM phpbb_oauth_tokens 
        	WHERE session_id = \'' . $this->session_id . '\'';
        $result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// The token is serialized before stored in the database
		$this->assertEquals(serialize($token), $row['oauth_token']);
	}

	public function test_hasAccessToken()
	{

	}

	public function test_clearToken()
	{

	}

	public function test_set_user_id()
	{

	}
}