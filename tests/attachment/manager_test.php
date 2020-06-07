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

class phpbb_attachment_manager_test extends \phpbb_test_case
{
	protected $delete;
	protected $resync;
	protected $upload;

	protected function setUp(): void
	{
		$this->delete = $this->getMockBuilder('\phpbb\attachment\delete')
			->disableOriginalConstructor()
			->setMethods(['delete', 'unlink_attachment'])
			->getMock();
		$this->resync = $this->getMockBuilder('\phpbb\attachment\resync')
			->disableOriginalConstructor()
			->setMethods(['resync'])
			->getMock();
		$this->upload = $this->getMockBuilder('\phpbb\attachment\upload')
			->disableOriginalConstructor()
			->setMethods(['upload'])
			->getMock();
	}

	protected function get_manager()
	{
		return new \phpbb\attachment\manager($this->delete, $this->resync, $this->upload);
	}

	public function data_manager()
	{
		return array(
			array(
				'delete',
				'unlink_attachment',
				'unlink',
				['foo'],
				['foo', 'file', false],
				true,
				true,
			),
			array(
				'delete',
				'unlink_attachment',
				'unlink',
				['foo', 'bar'],
				['foo', 'bar', false],
				true,
				true,
			),
			array(
				'delete',
				'unlink_attachment',
				'unlink',
				['foo', 'bar', true],
				['foo', 'bar', true],
				true,
				true,
			),
			array(
				'delete',
				'delete',
				'delete',
				['foo', [1, 2, 3]],
				['foo', [1, 2, 3], true],
				5,
				5,
			),
			array(
				'delete',
				'delete',
				'delete',
				['foo', [1, 2, 3], false],
				['foo', [1, 2, 3], false],
				2,
				2,
			),
			array(
				'resync',
				'resync',
				'resync',
				['foo', [1, 2, 3]],
				['foo', [1, 2, 3]],
				true,
				null,
			),
			array(
				'upload',
				'upload',
				'upload',
				['foo', 1],
				['foo', 1, false, '', false, []],
				true,
				true,
			),
			array(
				'upload',
				'upload',
				'upload',
				['foo', 1, true, 'bar', true, ['filename' => 'foobar']],
				['foo', 1, true, 'bar', true, ['filename' => 'foobar']],
				true,
				true,
			),
		);
	}

	/**
	 * @dataProvider data_manager
	 */
	public function test_manager($class, $method_class, $method_manager, $input_manager, $input_method, $return, $output)
	{
		$mock = call_user_func_array([$this->{$class}, 'expects'], [$this->atLeastOnce()]);
		$mock = $mock->method($method_class);
		$mock = call_user_func_array([$mock, 'with'], $input_method);
		$mock->willReturn($return);
		$manager = $this->get_manager();
		$this->assertSame($output, call_user_func_array([$manager, $method_manager], $input_manager));
	}
}
