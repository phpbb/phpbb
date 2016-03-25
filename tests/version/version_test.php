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

class phpbb_version_helper_test extends phpbb_test_case
{
	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
			->disableOriginalConstructor()
			->getMock();

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);

		$this->version_helper = new \phpbb\version_helper(
			$this->cache,
			new \phpbb\config\config(array(
				'version'	=> '3.1.0',
			)),
			new \phpbb\file_downloader(),
			new \phpbb\user(new \phpbb\language\language($lang_loader), '\phpbb\datetime')
		);
	}

	public function is_stable_data()
	{
		return array(
			array(
				'3.0.0-a1',
				false,
			),
			array(
				'3.0.0-b1',
				false,
			),
			array(
				'3.0.0-rc1',
				false,
			),
			array(
				'3.0.0-RC1',
				false,
			),
			array(
				'3.0.0',
				true,
			),
			array(
				'3.0.0-pl1',
				true,
			),
			array(
				'3.0.0.1-pl1',
				true,
			),
			array(
				'3.1-dev',
				false,
			),
			array(
				'foobar',
				false,
			),
		);
	}

	/**
	* @dataProvider is_stable_data
	*/
	public function test_is_stable($version, $expected)
	{
		$this->assertSame($expected, $this->version_helper->is_stable($version));
	}

	public function get_suggested_updates_data()
	{
		return array(
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
			),
			array(
				'1.0.1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				array(
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
			),
			array(
				'1.0.1-a1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1-a2',
					),
					'1.1'	=> array(
						'current'		=> '1.1.0',
					),
				),
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1-a2',
					),
					'1.1'	=> array(
						'current'		=> '1.1.0',
					),
				),
			),
			array(
				'1.1.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				array(
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
			),
			array(
				'1.1.1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				array(),
			),
			array(
				'1.1.0-a1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.0-a2',
					),
				),
				array(
					'1.1'	=> array(
						'current'		=> '1.1.0-a2',
					),
				),
			),
			array(
				'1.1.0',
				array(),
				array(),
			),
		);
	}

	/**
	* @dataProvider get_suggested_updates_data
	*/
	public function test_get_suggested_updates($current_version, $versions, $expected)
	{
		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);

		$version_helper = $this
			->getMockBuilder('\phpbb\version_helper')
			->setMethods(array(
				'get_versions_matching_stability',
			))
			->setConstructorArgs(array(
				$this->cache,
				new \phpbb\config\config(array(
					'version'	=> $current_version,
				)),
				new \phpbb\file_downloader(),
				new \phpbb\user($lang, '\phpbb\datetime'),
			))
			->getMock()
		;

		$version_helper->expects($this->any())
			->method('get_versions_matching_stability')
			->will($this->returnValue($versions));

		$this->assertSame($expected, $version_helper->get_suggested_updates());
	}

	public function get_latest_on_current_branch_data()
	{
		return array(
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				'1.0.1',
			),
			array(
				'1.0.1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				'1.0.1',
			),
			array(
				'1.0.1-a1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1-a2',
					),
					'1.1'	=> array(
						'current'		=> '1.1.0',
					),
				),
				'1.0.1-a2',
			),
			array(
				'1.1.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				'1.1.1',
			),
			array(
				'1.1.1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
					),
				),
				'1.1.1',
			),
			array(
				'1.1.0-a1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.0-a2',
					),
				),
				'1.1.0-a2',
			),
			array(
				'1.1.0',
				array(),
				null,
			),
		);
	}

	/**
	* @dataProvider get_latest_on_current_branch_data
	*/
	public function test_get_latest_on_current_branch($current_version, $versions, $expected)
	{
		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);

		$version_helper = $this
			->getMockBuilder('\phpbb\version_helper')
			->setMethods(array(
				'get_versions_matching_stability',
			))
			->setConstructorArgs(array(
				$this->cache,
				new \phpbb\config\config(array(
					'version'	=> $current_version,
				)),
				new \phpbb\file_downloader(),
				new \phpbb\user($lang, '\phpbb\datetime'),
			))
			->getMock()
		;

		$version_helper->expects($this->any())
			->method('get_versions_matching_stability')
			->will($this->returnValue($versions));

		$this->assertSame($expected, $version_helper->get_latest_on_current_branch());
	}
}
