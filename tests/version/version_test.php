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
	protected $cache;
	protected $version_helper;

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
			->disableOriginalConstructor()
			->getMock();

		$this->version_helper = new \phpbb\version_helper(
			$this->cache,
			new \phpbb\config\config(array(
				'version'	=> '3.1.0',
			)),
			new \phpbb\file_downloader()
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

	public function get_update_on_branch_data()
	{
		return array(
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.0.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'1.0.1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			array(
				'1.0.1-a1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1-a2',
						'eol'		=> false,
						'security'	=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.0',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.0.1-a2',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'1.1.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'1.1.1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			array(
				'1.1.0-a1',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.0-a2',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.0-a2',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'1.1.0',
				array(),
				array(),
			),
			// Latest safe release is 1.0.1
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'			=> false,
						'security'		=> '1.0.1',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.0.1',
					'eol'			=> false,
					'security'		=> '1.0.1',
				),
			),
			// Latest safe release is 1.0.0
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'			=> false,
						'security'		=> '1.0.0',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.0.1',
					'eol'			=> false,
					'security'		=> '1.0.0',
				),
			),
			// Latest safe release is 1.1.0
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'			=> false,
						'security'		=> '1.1.0',
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			// Latest 1.0 release is EOL
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'			=> true,
						'security'	=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			// All are EOL -- somewhat undefined behavior
			array(
				'1.0.0',
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'eol'			=> true,
						'security'		=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'eol'			=> true,
						'security'		=> false,
					),
				),
				array(),
			),
		);
	}

	/**
	 * @dataProvider get_update_on_branch_data
	 */
	public function test_get_update_on_branch($current_version, $versions, $expected)
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

		$this->assertSame($expected, $version_helper->get_update_on_branch());
	}

	public function get_ext_update_on_branch_data()
	{
		return array(
			// Single branch, check version for current branch
			array(
				'3.1.0',
				'1.0.0',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.0.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.1.0',
				'1.0.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			array(
				'3.2.0',
				'1.0.0',
				array(
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.2.0',
				'1.1.1',
				array(
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			// Single branch, check for newest version when branches don't match up
			array(
				'3.1.0',
				'1.0.0',
				array(
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.1.0',
				'1.1.1',
				array(
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			array(
				'3.2.0',
				'1.0.0',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.0.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.2.0',
				'1.0.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			array(
				'3.3.0',
				'1.0.0',
				array(
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.3.0',
				'1.1.1',
				array(
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			// Multiple branches, check version for current branch
			array(
				'3.1.0',
				'1.0.0',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.0.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.1.0',
				'1.0.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			array(
				'3.1.0',
				'1.1.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			array(
				'3.2.0',
				'1.0.0',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.2.0',
				'1.0.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.2.0',
				'1.1.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
			// Multiple branches, check for newest version when branches don't match up
			array(
				'3.3.0',
				'1.0.0',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.3.0',
				'1.0.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.3.0',
				'1.1.0',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(
					'current'		=> '1.1.1',
					'eol'		=> false,
					'security'	=> false,
				),
			),
			array(
				'3.3.0',
				'1.1.1',
				array(
					'3.1'	=> array(
						'current'		=> '1.0.1',
						'eol'		=> false,
						'security'	=> false,
					),
					'3.2'	=> array(
						'current'		=> '1.1.1',
						'eol'		=> false,
						'security'	=> false,
					),
				),
				array(),
			),
		);
	}

	/**
	 * @dataProvider get_ext_update_on_branch_data
	 */
	public function test_get_ext_update_on_branch($phpbb_version, $ext_version, $versions, $expected)
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
					'version'	=> $phpbb_version,
				)),
				new \phpbb\file_downloader(),
				new \phpbb\user($lang, '\phpbb\datetime'),
			))
			->getMock()
		;

		$version_helper->expects($this->any())
			->method('get_versions_matching_stability')
			->will($this->returnValue($versions));

		$version_helper->set_current_version($ext_version);

		$this->assertSame($expected, $version_helper->get_ext_update_on_branch());
	}
}
