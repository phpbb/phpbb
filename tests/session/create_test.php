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
		$this->assertEquals(true, $output->data['is_bot'], 'should be a bot');
	}
}
