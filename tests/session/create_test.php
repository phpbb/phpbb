<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_create_test extends phpbb_session_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_full.xml');
	}

	static function bot($bot_agent, $user_id, $bot_ip)
	{
		return array(array(
			'bot_agent' => $bot_agent,
			'user_id' => $user_id,
			'bot_ip' => $bot_ip,
		));
	}

	function test_bot_session()
	{
		$output = $this->session_facade->session_create(
			false,
			false,
			false,
			false,
			array(),
			'user agent',
			'127.0.0.1',
			self::bot('user agent', 13, '127.0.0.1'),
			''
		);
		$this->assertEquals($output->data['is_bot'], true, 'should be a bot');
	}
}
