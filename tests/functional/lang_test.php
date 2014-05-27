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

/**
* @group functional
*/
class phpbb_functional_lang_test extends phpbb_functional_test_case
{
	public function test_lang()
	{
		// Test a language string present in the common language file
		$this->assertEquals('Board index', $this->lang('FORUM_INDEX'));
	}

	/**
	* @expectedException RuntimeException
	*/
	public function test_lang_missing()
	{
		$this->assertEquals('Your account has now been activated. Thank you for registering.', $this->lang('ACCOUNT_ACTIVE'));
	}

	public function test_add_lang()
	{
		$this->add_lang('ucp');

		// Test a language string present only in the UCP language file
		$this->assertEquals('Your account has now been activated. Thank you for registering.', $this->lang('ACCOUNT_ACTIVE'));
	}

	public function test_add_langs()
	{
		$this->add_lang(array('groups', 'memberlist'));

		// Test a language string from each UCP and memberlist
		$this->assertEquals('The selected group is already your default group.', $this->lang('ALREADY_DEFAULT_GROUP'));
		$this->assertEquals('Profile', $this->lang('ABOUT_USER'));
	}
}
