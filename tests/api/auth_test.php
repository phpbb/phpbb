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
			array('key_id = 2', array(array('key_id' => '2', 'auth_key' => 'cccccccccccccccc', 'sign_key' => 'dddddddddddddddd', 'user_id' => '2', 'name' => 'test', 'serial' => 0))),
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
			array(1, false, 'guest', 1, '', 'Guest access should give the user id 1 when guests have permissions'),
			array(2, false, 'aaaaaaaaaaaaaaaa', 1, '457dfadb13d252aa7c35c8ee8628eac3028175b7049ade5c8e14b6ddc0d1b63a','Valid key and hash should give user id 2'),
			array(400, false, 'aaaaaaaaaaaaaaaa', 0, 'c838ce3a204b683c5a552aba35c7b8f3eb865e30db760dd65677567dba8a7963','Valid key and hash but invalid serial should give 400'),
			array(401, true, 'invalid', 'invalid', 2, 'Non-existant auth_key should give http status code 401'),
			array(403, true, 'aaaaaaaaaaaaaaaa', 3, 'e14f00de0ad81f1db601ad65f18fb07c4755ebf39540a8e4dc7dcd47f2e6defb','Valid key and hash but no permissions should give 403'),
			array(400, true, 'aaaaaaaaaaaaaaaa', 4, 'invalid','Invalid hash should give 400'),

		);
	}

	/**
	 * @dataProvider auth_data
	 */
	public function test_auth($status, $nopermission, $auth_key, $serial, $hash, $description)
	{
		if (!$nopermission)
		{
			$this->auth->expects($this->any())
			->method('acl_get')
			->will($this->returnValue(true));
		}

		$response = $this->auth_repository->auth('api/auth/verify', $auth_key, $serial, $hash);
		$this->assertEquals($status, (is_int($response)) ? $response : $response['status'], $description);
	}
}
