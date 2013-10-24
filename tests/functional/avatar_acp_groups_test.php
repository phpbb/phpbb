<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/common_avatar_test.php';

/**
 * @group functional
 */
class phpbb_functional_avatar_acp_groups_test extends phpbb_functional_common_avatar_test
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
				'The submitted avatar is 120 wide and 120 high. Avatars must be at least 20 wide and 20 high, but no larger than 90 wide and 90 high.',
				'avatar_driver_gravatar',
				array(
					'avatar_gravatar_email'		=> 'test@example.com',
					'avatar_gravatar_width'		=> 120,
					'avatar_gravatar_height'	=> 120,
				),
			),
			// Delete avatar image to reset group settings
			array(
				'GROUP_UPDATED',
				'avatar_driver_gravatar',
				array(
					'avatar_delete'	=> array('tick', ''),
				),
			),
			array(
				'The URL you specified is invalid.',
				'avatar_driver_remote',
				array(
					'avatar_remote_url'	=> 'https://www.phpbb.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
					'avatar_remote_width'	=> 80,
					'avatar_remote_height'	=> 80,
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
}
