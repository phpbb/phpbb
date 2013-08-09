<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @group functional
 */
class phpbb_functional_api_auth_test extends phpbb_functional_test_case
{

	public function setUp()
	{
		parent::setUp();

		$db = $this->get_db();

		$db->sql_query("UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'allow_api'");

		$db->sql_query("INSERT INTO phpbb_acl_users
			VALUES (2, 0, 89, 5, 1)");

	}

	public function test_generate_keys_json()
	{
		$crawler = $this->request('GET', 'app.php?controller=api/auth/generatekeys', array(), false);

		$decoded = json_decode($crawler->text());

		$this->assertEquals(200, $decoded->status);
		$this->assertEquals(16, strlen($decoded->data->auth_key));
		$this->assertEquals(16, strlen($decoded->data->sign_key));

	}

}
