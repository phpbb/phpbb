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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/validate_data_helper.php';

class phpbb_functions_validate_jabber_test extends phpbb_test_case
{
	protected $helper;

	protected function setUp()
	{
		parent::setUp();

		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	public function test_validate_jabber()
	{
		$this->helper->assert_valid_data(array(
			'empty' => array(
				array(),
				'',
				array('jabber'),
			),	
			'no_seperator' => array(
				array('WRONG_DATA'),
				'testjabber.ccc',
				array('jabber'),
			),
			'no_user' => array(
				array('WRONG_DATA'),
				'@jabber.ccc',
				array('jabber'),
			),
			'no_realm' => array(
				array('WRONG_DATA'),
				'user@',
				array('jabber'),
			),
			'dot_realm' => array(
				array('WRONG_DATA'),
				'user@.....',
				array('jabber'),
			),
			'-realm' => array(
				array('WRONG_DATA'),
				'user@-jabber.ccc',
				array('jabber'),
			),
			'realm-' => array(
				array('WRONG_DATA'),
				'user@jabber.ccc-',
				array('jabber'),
			),
			'correct' => array(
				array(),
				'user@jabber.09A-z.org',
				array('jabber'),
			),
			'prohibited' => array(
				array('WRONG_DATA'),
				'u@ser@jabber.ccc.org',
				array('jabber'),
			),
			'prohibited_char' => array(
				array('WRONG_DATA'),
				'u<s>er@jabber.ccc.org',
				array('jabber'),
			),
		));
	}
}
