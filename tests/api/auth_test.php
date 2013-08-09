<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */


class phpbb_api_auth_test extends phpbb_database_test_case
{

	protected $db;
	protected $auth_repository;
	protected $auth;

	protected function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->auth = $this->getMock('phpbb_auth');

		$this->auth_repository = new phpbb_model_repository_auth(new phpbb_config(array('allow_api' => 1)), $this->db, $this->auth);
	}

	static public function fetchrow_data_allow()
	{
		return array(
			array('key_id = 2', array(array('key_id' => '2', 'auth_key' => 'cccccccccccccccc', 'sign_key' => 'dddddddddddddddd', 'user_id' => '2', 'name' => 'test'))),
		);
	}

	/**
	 * @dataProvider fetchrow_data_allow
	 */
	public function test_allow($where, $expected)
	{
		$this->auth_repository->allow('cccccccccccccccc', 'dddddddddddddddd', 2, 'test');

		$result = $this->db->sql_query("SELECT * FROM phpbb_api_keys WHERE {$where}");

		$ary = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ary[] = $row;
		}
		$this->db->sql_freeresult($result);

		$this->assertEquals($expected, $ary);
	}

	static public function auth_data()
	{
		return array(
			array(1, false, 1, 'guest', '', 'Guest access should give the user id 1 when guests have permissions'),
			array(2, false, 2, 'aaaaaaaaaaaaaaaa', 'c838ce3a204b683c5a552aba35c7b8f3eb865e30db760dd65677567dba8a7963','Valid key and hash should give user id 2'),
			array(401, true, 2, 'invalid', 'invalid', 'Non-existant auth_key should give http status code 401'),
			array(403, true, 2, 'aaaaaaaaaaaaaaaa', 'c838ce3a204b683c5a552aba35c7b8f3eb865e30db760dd65677567dba8a7963','Valid key and hash but no permissions should give 403'),
			array(400, true, 2, 'aaaaaaaaaaaaaaaa', 'invalid','Invalid hash should give 400'),
		);
	}

	/**
	 * @dataProvider auth_data
	 */
	public function test_auth($status, $nopermission, $userid, $auth_key, $hash, $description)
	{
		if (!$nopermission)
		{
			$this->auth->expects($this->once())
			->method('acl_get')
			->will($this->returnValue(true));

			$response = $this->auth_repository->auth('api/auth/verify', $auth_key, 0, $hash);

			$this->assertEquals($status, $response, $description);
		}
		else
		{
			$response = $this->auth_repository->auth('api/auth/verify', $auth_key, 0, $hash);

			$this->assertEquals($status, $response['status'], $description);
		}

	}

}
