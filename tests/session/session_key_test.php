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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_login_keys_test extends phpbb_session_test_case
{
	protected $user_id = 4;
	protected $key_id = 4;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_key.xml');
	}

	public function test_set_key_manually()
	{
		// With AutoLogin setup
		$this->session_factory->merge_config_data(array('allow_autologin' => true));
		$session = $this->session_factory->get_session($this->db);

		// Using a user_id and key that is already in the database
		$session->cookie_data['u'] = $this->user_id;
		$session->cookie_data['k'] = $this->key_id;

		// Try to access session with the session key
		$session->session_create(false, false, false);
		$this->assertEquals($this->user_id, $session->data['user_id'], 'User should be logged in by the session key');
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

		// Try to access session with the session key
		$session->session_create(false, false, $this->user_id);
		$this->assertNotEquals($this->user_id, $session->data['user_id'], 'User is not logged in because the session key is invalid');

		$session->session_create($this->user_id, false, false);
		$this->assertEquals($this->user_id, $session->data['user_id'], 'User should be logged in because we create a new session');
	}
}
