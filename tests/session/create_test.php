<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/testable_facade.php';

class phpbb_session_create_test extends phpbb_database_test_case
{
	public $session_factory;
	public $db;
	public $session_facade;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_full.xml');
	}

	public function setUp()
	{
		$this->session_factory = new phpbb_session_testable_factory;
		$this->db = $this->new_dbal();
		$this->session_facade =
			new phpbb_session_testable_facade($this->db, $this->session_factory);
	}

	static function bot($bot_agent, $user_id, $bot_ip)
	{
		return array(array(
			'bot_agent' => $bot_agent,
			'user_id' => $user_id,
			'bot_ip' => $bot_ip
		));
	}

	static function create_inputs() {
		return array(
			array(
				false,
				false,
				false,
				false,
				array(),
				'user agent',
				'127.0.0.1',
				self::bot('user agent', 13, '127.0.0.1'),
				'',
				function ($test, $output) {
					$test->assertEquals($output->data['is_bot'], true, "should be a bot");
				}
			)
		);
	}

	/** @dataProvider  create_inputs */
	function test_session_create (
		$user_id = false,
		$set_admin = false,
		$persist_login = false,
		$viewonline = true,
		array $config_overrides = array(),
		$user_agent = "",
		$ip_address = "",
		array $bot_overrides = array(),
		$uri_sid = "",
		$test_function
	)
	{
		$output = $this->session_facade->session_create(
			$user_id,
			$set_admin,
			$persist_login,
			$viewonline,
			$config_overrides,
			$user_agent,
			$ip_address,
			$bot_overrides,
			$uri_sid
		);
		$test_function($this, $output);
	}
}
