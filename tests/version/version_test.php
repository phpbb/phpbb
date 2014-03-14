<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

		$this->version_helper = new \phpbb\version_helper(
			$this->cache,
			new \phpbb\config\config(array(
				'version'	=> '3.1.0',
			)),
			new \phpbb\user()
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
		);
	}

	/**
	* @dataProvider get_suggested_updates_data
	*/
	public function test_get_suggested_updates($current_version, $versions, $expected)
	{
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
				new \phpbb\user(),
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
		);
	}

	/**
	* @dataProvider get_latest_on_current_branch_data
	*/
	public function test_get_latest_on_current_branch($current_version, $versions, $expected)
	{
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
				new \phpbb\user(),
			))
			->getMock()
		;

		$version_helper->expects($this->any())
			->method('get_versions_matching_stability')
			->will($this->returnValue($versions));

		$this->assertSame($expected, $version_helper->get_latest_on_current_branch());
	}
}
