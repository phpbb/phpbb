<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		$this->helper->assert_validate_data(array(
			'empty'			=> array(),
			'no_seperator'		=> array('WRONG_DATA'),
			'no_user'		=> array('WRONG_DATA'),
			'no_realm'		=> array('WRONG_DATA'),
			'dot_realm'		=> array('WRONG_DATA'),
			'-realm'		=> array('WRONG_DATA'),
			'realm-'		=> array('WRONG_DATA'),
			'correct'		=> array(),
			'prohibited'		=> array('WRONG_DATA'),
			'prohibited_char'	=> array('WRONG_DATA'),
		),
		array(
			'empty'			=> '',
			'no_seperator'		=> 'testjabber.ccc',
			'no_user'		=> '@jabber.ccc',
			'no_realm'		=> 'user@',
			'dot_realm'		=> 'user@.....',
			'-realm'		=> 'user@-jabber.ccc',
			'realm-'		=> 'user@jabber.ccc-',
			'correct'		=> 'user@jabber.09A-z.org',
			'prohibited'		=> 'u@ser@jabber.ccc.org',
			'prohibited_char'	=> 'u<s>er@jabber.ccc.org',
		),
		array(
			'empty'			=> array('jabber'),
			'no_seperator'		=> array('jabber'),
			'no_user'		=> array('jabber'),
			'no_realm'		=> array('jabber'),
			'dot_realm'		=> array('jabber'),
			'-realm'		=> array('jabber'),
			'realm-'		=> array('jabber'),
			'correct'		=> array('jabber'),
			'prohibited'		=> array('jabber'),
			'prohibited_char'	=> array('jabber'),
		));
	}
}
