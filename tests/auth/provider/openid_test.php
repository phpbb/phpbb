<?php
/**
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_auth_provider_openid_test extends phpbb_database_test_case
{
	private $config;
	private $db;
	private $user;

	const ID       = "http://id.myopenid.com/";
	const REAL_ID  = "http://real_id.myopenid.com/";
	const SERVER   = "http://www.myopenid.com/";

	const HANDLE   = "d41d8cd98f00b204e9800998ecf8427e";
	const MAC_FUNC = "sha256";
	const SECRET   = "4fa03202081808bd19f92b667a291873";

	protected function setUp()
	{
		$this->markTestSkipped('zendramework/zendopenid is currently broken as as such the test is skipped.');
		global $db;
		$this->db = $db = $this->new_dbal();
		$this->config = new phpbb_config(array(
			'auth_provider_openid_enabled'	=> true,
			'auth_provider_openid_admin'	=> true,
		));
		$this->user = new phpbb_user();
		ZendOpenId\OpenId::$exitOnRedirect = false;
	}

	public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/openid_test.xml');
    }

	public function test_login()
	{
		$post = array(
			'auth_action'		=> 'login',
			'openid_identifier'	=> self::ID,
		);
		$request = new phpbb_mock_request(array(), $post);
		$provider = new phpbb_auth_provider_openid($request, $this->db, $this->config);
		$provider->set_user($this->user);
		$response = new phpbb_mock_openid_response(true);
		$provider->set_response_helper($response);

		$provider->process(false);
	}
}
