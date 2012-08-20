<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/session.php';

class phpbb_user_lang_test extends phpbb_test_case
{
	public function test_user_lang_sprintf()
	{
		$user = new user;
		$user->lang = array(
			'FOO'		=> 'BAR',
			'BARZ'		=> 'PENG',
			'EMPTY'		=> '',
			'ZERO'		=> '0',
			'STR'		=> '%d %s, %d topics',
			'STR2'		=> '%d foos',
			'ARRY'		=> array(
				0		=> 'No posts',		// 0
				1		=> '1 post',		// 1
				2		=> '%d posts',		// 2+
			),
		);

		// No param
		$this->assertEquals($user->lang('FOO'), 'BAR');
		$this->assertEquals($user->lang('EMPTY'), '');
		$this->assertEquals($user->lang('ZERO'), '0');

		// Invalid index
		$this->assertEquals($user->lang('VOID'), 'VOID');

		// Unnecessary param
		$this->assertEquals($user->lang('FOO', 2), 'BAR');
		$this->assertEquals($user->lang('FOO', 2, 3), 'BAR');
		$this->assertEquals($user->lang('FOO', 2, 3, 'BARZ'), 'BAR');

		// String
		$this->assertEquals($user->lang('STR', 24, 'x', 42), '24 x, 42 topics');
		$this->assertEquals($user->lang('STR2', 64), '64 foos');

		// Array
		$this->assertEquals($user->lang('ARRY', 0), 'No posts');
		$this->assertEquals($user->lang('ARRY', 1), '1 post');
		$this->assertEquals($user->lang('ARRY', 2), '2 posts');
		$this->assertEquals($user->lang('ARRY', 123), '123 posts');

		// Bug PHPBB3-9949
		$this->assertEquals($user->lang('ARRY', 1, 2), '1 post');
		$this->assertEquals($user->lang('ARRY', 1, 's', 2), '1 post');
	}
}
