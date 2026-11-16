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

class phpbb_avatar_driver_local_test extends \phpbb_test_case
{
	/** @var array */
	private $template_block_data = [];

	public function template_assign_block_vars($blockname, array $vararray)
	{
		$this->template_block_data[$blockname][] = $vararray;
	}

	protected static function get_avatar($category, $filename)
	{
		return [
			'file'		=> $category . '/' . $filename,
			'filename'	=> $filename,
			'name'		=> $filename,
			'width'		=> 80,
			'height'	=> 80,
		];
	}

	public static function data_prepare_form(): array
	{
		// PHP stores numeric category names like "2012" as integer array keys
		$numeric_first = [
			'2012'		=> ['a.png' => self::get_avatar('2012', 'a.png')],
			'2013'		=> ['b.png' => self::get_avatar('2013', 'b.png')],
			'animals'	=> ['c.png' => self::get_avatar('animals', 'c.png')],
		];

		$numeric_last = [
			'animals'	=> ['c.png' => self::get_avatar('animals', 'c.png')],
			'zebras'	=> ['d.png' => self::get_avatar('zebras', 'd.png')],
			'2012'		=> ['a.png' => self::get_avatar('2012', 'a.png')],
		];

		return [
			'numeric first, nothing requested'		=> [$numeric_first, null, '2012', ['a.png']],
			'numeric first, numeric requested'		=> [$numeric_first, '2013', '2013', ['b.png']],
			'numeric first, non-numeric requested'	=> [$numeric_first, 'animals', 'animals', ['c.png']],
			'numeric last, nothing requested'		=> [$numeric_last, null, 'animals', ['c.png']],
			'numeric last, numeric requested'		=> [$numeric_last, '2012', '2012', ['a.png']],
			'numeric last, non-numeric requested'	=> [$numeric_last, 'zebras', 'zebras', ['d.png']],
		];
	}

	/**
	 * @dataProvider data_prepare_form
	 */
	public function test_prepare_form($avatar_list, $requested_category, $expected_category, $expected_avatars)
	{
		global $phpbb_root_path, $phpEx;

		$this->template_block_data = [];

		$config = new \phpbb\config\config(['avatar_gallery_path' => 'images/avatars/gallery']);

		$cache = $this->createMock('\phpbb\cache\driver\driver_interface');
		$cache->method('get')
			->willReturn($avatar_list);

		$template = $this->createMock('\phpbb\template\template');
		$template->method('assign_block_vars')
			->willReturnCallback([$this, 'template_assign_block_vars']);

		$user = $this->createMock('\phpbb\user');
		$user->data = ['user_lang' => 'en'];

		$request = new \phpbb\request\request(null, false);
		$request->overwrite('avatar_local_cat', $requested_category);

		$driver = new \phpbb\avatar\driver\local(
			$config,
			new \FastImageSize\FastImageSize(),
			$phpbb_root_path,
			$phpEx,
			$this->createMock('\phpbb\path_helper'),
			$cache
		);

		$error = [];
		$this->assertTrue($driver->prepare_form($request, $template, $user, ['avatar' => ''], $error));
		$this->assertEquals([], $error);

		$selected_categories = [];
		foreach ($this->template_block_data['avatar_local_cats'] as $category)
		{
			if ($category['SELECTED'])
			{
				$selected_categories[] = (string) $category['NAME'];
			}
		}
		$this->assertCount(count($avatar_list), $this->template_block_data['avatar_local_cats']);
		$this->assertSame([$expected_category], $selected_categories);

		$this->assertArrayHasKey('avatar_local_row.avatar_local_col', $this->template_block_data, 'No avatars listed for the selected category');
		$this->assertSame($expected_avatars, array_column($this->template_block_data['avatar_local_row.avatar_local_col'], 'AVATAR_FILE'));
	}
}
