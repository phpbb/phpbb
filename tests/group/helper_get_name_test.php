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

require_once __DIR__ . '/helper_test_case.php';

class phpbb_group_helper_get_name_test extends phpbb_group_helper_test_case
{
	public function test_get_name()
	{
		// They should be totally fine
		$this->assertEquals('Bots', $this->group_helper->get_name('Bots'));
		$this->assertEquals('Some new group', $this->group_helper->get_name('new_group'));
		$this->assertEquals('Should work', $this->group_helper->get_name('group_with_ümlauts'));

		// This should fail (obviously)
		$this->assertNotEquals('The key does not contain uppercase letters', $this->group_helper->get_name('not_uppercase'));

		// The key doesn't exist so just return group name...
		$this->assertEquals('Awesome group', $this->group_helper->get_name('Awesome group'));
	}
}
