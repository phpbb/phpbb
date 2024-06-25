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

require_once __DIR__ . '/common_avatar_test_case.php';

/**
 * @group functional
 */
class phpbb_functional_avatar_acp_groups_test extends phpbb_functional_common_avatar_test_case
{
	public function get_url()
	{
		return 'adm/index.php?i=acp_groups&mode=manage&action=edit&g=5';
	}

	public function avatar_acp_groups_data()
	{
		return array(
			// Correct Gravatar
			array(
				'GROUP_UPDATED',
				'avatar_driver_gravatar',
				array(
					'avatar_gravatar_email'		=> 'test@example.com',
					'avatar_gravatar_width'		=> 80,
					'avatar_gravatar_height'	=> 80,
				),
			),
			// Gravatar with incorrect size
			array(
				'The submitted avatar is 140 wide and 140 high. Avatars must be at least 40 wide and 40 high, but no larger than 120 wide and 120 high.',
				'avatar_driver_gravatar',
				array(
					'avatar_gravatar_email'		=> 'test@example.com',
					'avatar_gravatar_width'		=> 140,
					'avatar_gravatar_height'	=> 140,
				),
			),
			// Delete avatar image to reset group settings
			array(
				array('CONFIRM_AVATAR_DELETE', 'GROUP_UPDATED'),
				'avatar_driver_gravatar',
				array(
					'avatar_delete'	=> array('tick', ''),
				),
			),
			array(
				'EMAIL_INVALID_EMAIL',
				'avatar_driver_gravatar',
				array(
					'avatar_gravatar_email'		=> 'foobar123',
					'avatar_gravatar_width'		=> 120,
					'avatar_gravatar_height'	=> 120,
				),
			),
		);
	}

	/**
	* @dataProvider avatar_acp_groups_data
	*/
	public function test_avatar_acp_groups($expected, $avatar_type, $data)
	{
		$this->assert_avatar_submit($expected, $avatar_type, $data);
	}

	// Test if avatar was really deleted
	public function test_no_avatar_acp_groups()
	{
		$crawler = self::request('GET', $this->get_url() . '&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form_data = $form->getValues();
		$this->assertFalse(isset($form_data['avatar_type']));
	}
}
