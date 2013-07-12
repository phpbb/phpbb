<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_login_keys_test extends phpbb_session_test_case
{
	protected $user_id = 4;
	protected $key_id = 4;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_key.xml');
	}

	public function test_set_key_manually()
	{
		// With AutoLogin setup
		$this->session_factory->merge_config_data(array('allow_autologin' => true));
		$session = $this->session_factory->get_session($this->db);
		// Using a user_id and key that is already in the database
		$session->cookie_data['u'] = $this->user_id;
		$session->cookie_data['k'] = $this->key_id;
		// Try to access session
		$session->session_create($this->user_id, false, $this->user_id);

		$this->assertEquals($this->user_id, $session->data['user_id'], "session should automatically login");
	}

	public function test_reset_keys()
	{
		// With AutoLogin setup
		$this->session_factory->merge_config_data(array('allow_autologin' => true));
		$session = $this->session_factory->get_session($this->db);
		// Reset of the keys for this user
		$session->reset_login_keys($this->user_id);
		// Using a user_id and key that was in the database (before reset)
		$session->cookie_data['u'] = $this->user_id;
		$session->cookie_data['k'] = $this->key_id;
		// Try to access session
		$session->session_create($this->user_id, false, $this->user_id);

		$this->assertNotEquals($this->user_id, $session->data['user_id'], "session should be cleared");
	}
}
