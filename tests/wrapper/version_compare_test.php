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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_wrapper_version_compare_test extends phpbb_test_case
{
	public function test_two_parameters()
	{
		$this->assertEquals(-1, phpbb_version_compare('1.0.0', '1.0.1'));
		$this->assertEquals(0, phpbb_version_compare('1.0.0', '1.0.0'));
		$this->assertEquals(1, phpbb_version_compare('1.0.1', '1.0.0'));
	}
	
	public function test_three_parameters()
	{
		$this->assertEquals(true, phpbb_version_compare('1.0.0', '1.0.1', '<'));
		$this->assertEquals(true, phpbb_version_compare('1.0.0', '1.0.0', '<='));
		$this->assertEquals(true, phpbb_version_compare('1.0.0', '1.0.0', '='));
		$this->assertEquals(true, phpbb_version_compare('1.0.0', '1.0.0', '>='));
		$this->assertEquals(true, phpbb_version_compare('1.0.1', '1.0.0', '>'));
	}

	public function test_strict_order()
	{
		$releases = array(
			'2.0.0',
			'2.0.1',
			// Those are not version_compare() compatible
			//'2.0.6a',
			//'2.0.6b',
			//'2.0.6c',
			//'2.0.6d',
			'2.0.7',
			'2.0.23',
			'3.0.A1',
			'3.0.A2',
			'3.0.A3',
			'3.0.B1',
			'3.0.B2',
			'3.0.B4',
			'3.0.B5',
			'3.0.RC1',
			'3.0.RC5',
			'3.0.0',
			'3.0.1',
			'3.0.2',
			'3.0.7',
			'3.0.7-PL1',
			'3.0.8-RC1',
			'3.0.8',
			'3.0.9-dev',
			'3.0.9-RC1',
			'3.0.9-RC2',
			'3.0.9-RC4',
			'3.0.10-RC1',
			'3.1-dev',
			'3.2-A1',
		);

		for ($i = 0, $size = sizeof($releases); $i < $size - 1; ++$i)
		{
			$version1 = $releases[$i];
			$version2 = $releases[$i + 1];

			$this->assertEquals(
				true,
				phpbb_version_compare($version1, $version2, '<'),
				"Result of version comparison $version1 < $version2 is incorrect."
			);
		}
	}

	/**
	* @dataProvider equality_test_data
	*/
	public function test_equality($version1, $version2)
	{
		$this->assertEquals(
			0,
			phpbb_version_compare($version1, $version2),
			"Result of version comparison $version1 = $version2 is incorrect."
		);

		$this->assertEquals(
			true,
			phpbb_version_compare($version1, $version2, '='),
			"Result of version comparison $version1 = $version2 is incorrect."
		);
	}

	public function equality_test_data()
	{
		return array(
			array('1.1.0-A2', '1.1.0-a2'),
			array('1.1.0-B1', '1.1.0-b1'),
		);
	}

	/**
	* @dataProvider alpha_beta_test_data
	*/
	public function test_alpha_beta($expected, $version1, $version2)
	{
		$this->assertEquals(
			$expected,
			phpbb_version_compare($version1, $version2),
			"Result of version comparison ($version1, $version2) = $expected is incorrect."
		);
		
	}

	public function alpha_beta_test_data()
	{
		return array(
			array(-1, '1.1.0-A2', '1.1.0-B1'),
			array(-1, '1.1.0-a2', '1.1.0-b1'),

			array(-1, '1.1.0-a2', '1.1.0-B1'),
			array(-1, '1.1.0-A2', '1.1.0-b1'),
		);
	}

}
