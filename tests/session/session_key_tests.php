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
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_key.xml');
	}

	public function test_set_key_manually()
	{
		$this->session_factory->merge_config_data(array('allow_autologin' => true));
		$session = $this->session_factory->get_session($this->db);
		$session->cookie_data['u'] = 4;
		$session->cookie_data['k'] = 4;
		$session->session_create(4, false, 4);
		$this->assertEquals(4, $session->data['user_id']);
	}
}
