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
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/**
	 * API Repository
	 * @var \phpbb\model\repository\auth
	 */
	protected $auth_repository;
	protected $auth;

	protected function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	protected function setUp()
	{
		global $phpbb_root_path, $phpEx, $config;
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->auth = $this->getMock('\phpbb\auth\auth');

		$config = new \phpbb\config\config(array(
			'allow_api' => 1,
			'rand_seed_last_update' => time(),
			'rand_seed' => 0,
		));

		$this->auth_repository = new \phpbb\model\repository\auth($config,
			$this->db, $this->auth, new phpbb\request\request(), $phpbb_root_path, $phpEx);

		if (!function_exists('unique_id'))
		{
			include($phpbb_root_path . 'includes/functions.' . $phpEx);
		}
	}

	public function test_generate_keys()
	{
		$exchange_key = $this->auth_repository->generate_keys();
		$this->assertEquals(16, strlen($exchange_key));

		$sql = 'SELECT * FROM ' . API_EXCHANGE_KEYS_TABLE ."
			WHERE exchange_key = '$exchange_key'";

		$result = $this->db->sql_query($sql);

		$this->assertEquals(1, $result->num_rows);

		$row = $this->db->sql_fetchrow($result);

		$this->assertEquals(16, strlen($row['sign_key']));
		$this->assertEquals(16, strlen($row['auth_key']));

		$this->db->sql_freeresult($result);
	}

	static public function allow_data()
	{
		return array(
			array(
				null,
				null,
				'exchange_key = "cccccccccccccccd"',
				array(
					array(
						'exchange_key' => 'cccccccccccccccd',
						'user_id' => '2',
						'name' => 'testing',
					),
				),
				array(
					array(
						'exchange_key' => 'cccccccccccccccd',
						'timestamp' => 1337,
						'auth_key' => 'aaaaaaaaaaaaaaaa',
						'sign_key' => 'bbbbbbbbbbbbbbbb',
						'user_id' => '2',
						'name' => 'testing',
					),
				),
				'Allowing existing key should pass',
			),
			array(
				400,
				'\phpbb\model\exception\invalid_key_exception',
				null,
				array(
					array(
						'exchange_key' => 'invalidkeyaaaaaa',
						'user_id' => '2',
						'name' => 'testing',
					),
				),
				null,
				'Invalid key should throw invalid_key_exception',
			),
			array(
				400,
				'\phpbb\model\exception\duplicate_name_exception',
				null,
				array(
					array(
						'exchange_key' => 'cccccccccccccccd',
						'user_id' => '2',
						'name' => 'test',
					),
				),
				null,
				'User already has a key with this name should throw duplicate_name_exception',
			),
		);
	}

	/**
	 * @dataProvider allow_data
	 */
	public function test_allow($status, $exception, $where, $inputs, $expected, $description)
	{
		if($exception !== null) {
			$this->setExpectedException($exception, null, $status);
		}

		foreach ($inputs as $input) {
			$this->auth_repository->allow($input['exchange_key'], $input['user_id'], $input['name']);
		}

		if($exception !== null) {
			$this->fail('An expected exception ' . $exception . ' has not been raised. ' . $description);
		}

		$result = $this->db->sql_query("SELECT * FROM phpbb_api_exchange_keys WHERE {$where}");

		$ary = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ary[] = $row;
		}

		$this->db->sql_freeresult($result);

		$this->assertEquals($expected, $ary);
	}

	static public function exchange_key_data()
	{
		return array(
			array(
				null,
				null,
				array(
					'exchange_key = "llllllllllllllll"',
					'auth_key = "xxxxxxxxxxxxxxxx"',
				),
				array(
					array(
						'exchange_key' => 'llllllllllllllll',
					),
				),
				array(
					array(
						'key_id' => 2,
						'auth_key' => 'xxxxxxxxxxxxxxxx',
						'sign_key' => 'zzzzzzzzzzzzzzzz',
						'user_id' => '2',
						'name' => 'tester',
						'serial' => 0,
					),
				),
				'Valid exchange should transfer auth and sign key to api_key table and delete the exchange key',
			),
			array(
				400,
				'\phpbb\model\exception\no_permission_exception',
				null,
				array(
					array(
						'exchange_key' => 'qqqqqqqqqqqqqqqq',
					),
				),
				null,
				'no_permission_exception should be thrown if user has not allowed application yet',
			),
			array(
				400,
				'\phpbb\model\exception\invalid_key_exception',
				null,
				array(
					array(
						'exchange_key' => 'invalid',
					),
				),
				null,
				'Invalid key should throw invalid_key_exception',
			),
		);
	}

	/**
	 * @dataProvider exchange_key_data
	 */
	public function test_exchange_key($status, $exception, $where, $inputs, $expected, $description)
	{
		if($exception !== null) {
			$this->setExpectedException($exception, null, $status);
		}

		foreach ($inputs as $input) {
			$this->auth_repository->exchange_key($input['exchange_key']);
		}

		if($exception !== null) {
			$this->fail('An expected exception ' . $exception . ' has not been raised. ' . $description);
		}

		$result = $this->db->sql_query("SELECT * FROM phpbb_api_exchange_keys WHERE " . $where[0]);

		$this->assertEquals(0, $result->num_rows);

		$result = $this->db->sql_query("SELECT * FROM phpbb_api_keys WHERE " . $where[1]);

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
			array(
				1,
				null,
				false,
				'guest',
				1,
				'',
				'Guest access should give the user id 1 when guests have permissions'
			),
			array(
				2,
				null,
				false,
				'aaaaaaaaaaaaaaaa',
				1,
				'457dfadb13d252aa7c35c8ee8628eac3028175b7049ade5c8e14b6ddc0d1b63a',
				'Valid key and hash should give user id 2'
			),
			array(
				400,
				'\phpbb\model\exception\invalid_request_exception',
				false,
				'aaaaaaaaaaaaaaaa',
				0,
				'c838ce3a204b683c5a552aba35c7b8f3eb865e30db760dd65677567dba8a7963',
				'Valid key and hash but invalid serial should give 400'
			),
			array(
				401,
				'\phpbb\model\exception\not_authed_exception',
				true,
				'invalid',
				'invalid',
				2,
				'Non-existant auth_key should give http status code 401'
			),
			array(
				403,
				'\phpbb\model\exception\no_permission_exception',
				true,
				'aaaaaaaaaaaaaaaa',
				3,
				'e14f00de0ad81f1db601ad65f18fb07c4755ebf39540a8e4dc7dcd47f2e6defb',
				'Valid key and hash but no permissions should give 403'
			),
			array(
				400,
				'\phpbb\model\exception\invalid_request_exception',
				true,
				'aaaaaaaaaaaaaaaa',
				4,
				'invalid',
				'Invalid hash should give 400'
			),
		);
	}

	/**
	 * @dataProvider auth_data
	 */
	public function test_auth($status, $exception, $nopermission, $auth_key, $serial, $hash, $description)
	{
		if (!$nopermission)
		{
			$this->auth->expects($this->any())
				->method('acl_get')
				->will($this->returnValue(true));
		}

		if($exception !== null) {
			$this->setExpectedException($exception, null, $status);
		}

		$response = $this->auth_repository->auth(0, 'api/auth/verify', $auth_key, $serial, $hash);
		$this->assertEquals($status, $response, $description);

	}
}
