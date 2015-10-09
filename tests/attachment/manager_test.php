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

	public function setUp()
	{
		$this->delete = $this->getMockBuilder('\phpbb\attachment\delete')
			->disableOriginalConstructor()
			->setMethods(['delete', 'unlink'])
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

	public function data_delete()
	{
		return array(
			[
				['foo', [1, 2, 3], false],
				['foo', [1, 2, 3], false],
				true,
			],
			[
				['foo', [1, 2, 3], true],
				['foo', [1, 2, 3]],
				true,
			],
		);
	}

	/**
	 * @dataProvider data_delete
	 */
	public function test_delete($input, $input_manager, $output)
	{
		$mock = $this->delete->expects($this->atLeastOnce())
			->method('delete');
		$mock = call_user_func_array([$mock, 'with'], $input);
		$mock->willReturn($output);
		$manager = $this->get_manager();
		$this->assertSame($output, call_user_func_array([$manager, 'delete'], $input_manager));
	}
}
