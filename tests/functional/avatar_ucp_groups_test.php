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
require_once dirname(__FILE__) . '/common_avatar_test_case.php';

/**
 * @group functional
 */
class phpbb_functional_avatar_ucp_groups_test extends phpbb_functional_common_avatar_test_case
{
	public function get_url()
	{
		return 'ucp.php?i=ucp_groups&mode=manage&action=edit&g=5';
	}

	public function avatar_ucp_groups_data()
	{
		return array(
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
				'GROUP_UPDATED',
				'avatar_driver_upload',
				array(
					'avatar_upload_file'	=> array('upload', $this->path . 'valid.jpg'),
				),
			),
			*/
			// Correct remote avatar
			array(
				'GROUP_UPDATED',
				'avatar_driver_remote',
				array(
					'avatar_remote_url'	=> 'https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg',
					'avatar_remote_width'	=> 80,
					'avatar_remote_height'	=> 80,
				),
			),
			array(
				array('CONFIRM_AVATAR_DELETE', 'GROUP_UPDATED'),
				'avatar_driver_gravatar',
				array(
					'avatar_delete'	=> array('tick', ''),
				),
			),
		);
	}

	/**
	* @dataProvider avatar_ucp_groups_data
	*/
	public function test_avatar_ucp_groups($expected, $avatar_type, $data)
	{
		$this->assert_avatar_submit($expected, $avatar_type, $data);
	}
}
