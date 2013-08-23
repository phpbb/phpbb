<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class phpbb_storage_db_session extends phpbb_database_test_case
{
	var $session;
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../fixtures/sessions_full.xml');
	}

	public function setUp()
	{
		parent::setUp();
		$this->session = new phpbb_session();
		$this->session->db_session->set_db($this->new_dbal());
	}

	function test_create_session()
	{
		$test_session_data = array(
			'session_id' => 'anon_session00000000000000000001',
			'session_user_id' => '1',
			'session_ip' => '127.0.0.1',
			'session_browser' => 'anonymous user agent',
			'session_admin' => '0',
		);
		$this->session->db_session->create($test_session_data);
		$result = $this->session->db_session->get($test_session_data['session_id']);
		foreach(array_keys($test_session_data) as $key)
		{
			$this->assertEquals($test_session_data[$key], $result[$key]);
		}
	}
}
