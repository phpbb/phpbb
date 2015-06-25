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
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_session_garbage_collection_test extends phpbb_session_test_case
{
	public $session;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_garbage.xml');
	}

	public function setUp()
	{
		parent::setUp();
		$this->session = $this->session_factory->get_session($this->db);

		global $phpbb_container;

		$plugins = new \phpbb\di\service_collection($phpbb_container);
		$plugins->add('core.captcha.plugins.nogd');
		$phpbb_container->set(
			'captcha.factory',
			new \phpbb\captcha\factory($phpbb_container, $plugins)
		);
		$phpbb_container->set(
			'core.captcha.plugins.nogd',
			new \phpbb\captcha\plugins\nogd()
		);
	}

	public function test_cleanup_all()
	{
		$this->check_sessions_equals(
			array(
				array(
					'session_id' => 'anon_session00000000000000000000',
					'session_user_id' => 1,
				),
				array(
					'session_id' => 'bar_session000000000000000000000',
					'session_user_id' => 4,
				),
			),
			'Before test, should have some sessions.'
		);
		// Set session length so it clears all
		global $config;
		$config['session_length'] = 0;
		// There is an error unless the captcha plugin is set
		$config['captcha_plugin'] = 'core.captcha.plugins.nogd';
		$this->session->session_gc();
		$this->check_sessions_equals(
			array(),
			'After setting session time to 0, should remove all.'
		);
	}
}
