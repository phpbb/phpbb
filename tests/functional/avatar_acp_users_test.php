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
class phpbb_functional_avatar_acp_users_test extends phpbb_functional_common_avatar_test
{
	public function get_url()
	{
		return 'adm/index.php?i=acp_users&u=2&mode=avatar';
	}

	public function avatar_acp_users_data()
	{
		return array(
			// Correct gravatar
			array(
				'USER_AVATAR_UPDATED',
				'avatar_driver_gravatar',
				array(
					'avatar_gravatar_email'		=> 'test@example.com',
					'avatar_gravatar_width'		=> 80,
					'avatar_gravatar_height'	=> 80,
				),
			),
			// Gravatar with incorrect sizes
			array(
				'The submitted avatar is 120 wide and 120 high. Avatars must be at least 20 wide and 20 high, but no larger than 90 wide and 90 high.',
				'avatar_driver_gravatar',
				array(
					'avatar_gravatar_email'		=> 'test@example.com',
					'avatar_gravatar_width'		=> 120,
					'avatar_gravatar_height'	=> 120,
				),
			),
			// Gravatar with incorrect email
			array(
				'EMAIL_INVALID_EMAIL',
				'avatar_driver_gravatar',
				array(
					'avatar_gravatar_email'		=> 'test.example.com',
					'avatar_gravatar_width'		=> 80,
					'avatar_gravatar_height'	=> 80,
				),
			),
			// Remote avatar with correct link
			array(
				'USER_AVATAR_UPDATED',
				'avatar_driver_upload',
				array(
					'avatar_upload_url'	=> 'https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
				),
			),
			// Incorrect URL
			array(
				'AVATAR_URL_INVALID',
				'avatar_driver_upload',
				array(
					'avatar_upload_url'	=> 'https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0?s=80',
				),
			),
			/*
			// Does not work due to DomCrawler issue
			// Valid file upload
			array(
				'PROFILE_UPDATED',
				'avatar_driver_upload',
				array(
					'avatar_upload_file'	=> array('upload', $this->path . 'valid.jpg'),
				),
			),
			*/
			// Correct remote avatar
			array(
				'USER_AVATAR_UPDATED',
				'avatar_driver_remote',
				array(
					'avatar_remote_url'	=> 'https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
					'avatar_remote_width'	=> 80,
					'avatar_remote_height'	=> 80,
				),
			),
			// Remote avatar with incorrect size
			array(
				'The submitted avatar is 120 wide and 120 high. Avatars must be at least 20 wide and 20 high, but no larger than 90 wide and 90 high.',
				'avatar_driver_remote',
				array(
					'avatar_remote_url'	=> 'https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
					'avatar_remote_width'	=> 120,
					'avatar_remote_height'	=> 120,
				),
			),
			// Wrong driver selected
			array(
				'NO_AVATAR_SELECTED',
				'avatar_driver_upload',
				array(
					'avatar_remote_url'	=> 'https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
					'avatar_remote_width'	=> 80,
					'avatar_remote_height'	=> 80,
				),
			),
			// File does not exist, remote avatar currently does
			// not check if file exists if size is specified
			array(
				'USER_AVATAR_UPDATED',
				'avatar_driver_remote',
				array(
					'avatar_remote_url'	=> 'https://www.phpbb.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
					'avatar_remote_width'	=> 80,
					'avatar_remote_height'	=> 80,
				),
			),
			// File does not exist and remote avatar errors when
			// trying to get the image size
			array(
				'UNABLE_GET_IMAGE_SIZE',
				'avatar_driver_remote',
				array(
					'avatar_remote_url'	=> 'https://www.phpbb.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
					'avatar_remote_width'	=> '',
					'avatar_remote_height'	=> '',
				),
			),
			// Reset avatar settings
			array(
				'USER_AVATAR_UPDATED',
				'avatar_driver_gravatar',
				array(
					'avatar_delete'	=> array('tick', ''),
				),
			),
		);
	}

	/**
	* @dataProvider avatar_acp_users_data
	*/
	public function test_avatar_acp_users($expected, $avatar_type, $data)
	{
		$this->assert_avatar_submit($expected, $avatar_type, $data);
	}
}
