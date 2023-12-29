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

namespace phpbb\tests\unit\assets;

class iconify_bundler_test extends \phpbb_test_case
{
	/** @var array Log content */
	protected $log_content = [];

	/** @var \phpbb\assets\iconify_bundler */
	protected $bundler;

	public function setUp(): void
	{
		global $phpbb_root_path;

		$log = $this->getMockBuilder('\phpbb\log\dummy')
			->onlyMethods(['add'])
			->getMock();
		$log->method('add')
			->willReturnCallback(function ($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = array()) {
				$this->log_content[] = $log_operation;
			});

		$this->bundler = new \phpbb\assets\iconify_bundler($log, $phpbb_root_path);
	}

	public function data_test_generate_bundle()
	{
		return [
			[
				['fa:address-card-o'],
				['"prefix":"fa"', '"address-card-o"'],
			],
			[
				['fa:address-card-o', 'ic:baseline-credit-card'],
				['"prefix":"fa"', '"address-card-o"', '"prefix":"ic"', '"baseline-credit-card"'],
			],
			[
				['fa:address-card-o', 'fa:foo-bar'],
				['"prefix":"fa"', '"address-card-o"'],
				['LOG_ICON_INVALID'],
			],
			[
				['fa:address-card-o', 'ic:baseline-credit-card', 'ic:baseline-credit-card'],
				['"prefix":"fa"', '"address-card-o"', '"prefix":"ic"', '"baseline-credit-card"'],
			],
			[
				['fa:address-card-o', 'ic:baseline-credit-card', 'ic:baseline-add-ic-call'],
				['"prefix":"fa"', '"address-card-o"', '"prefix":"ic"', '"baseline-credit-card"', '"baseline-add-ic-call"'],
			],
			[
				['fa:address-card-o', 'fa:bell', 'ic:baseline-credit-card', 'ic:baseline-add-ic-call'],
				['"prefix":"fa"', '"address-card-o"', '"bell"', '"prefix":"ic"', '"baseline-credit-card"', '"baseline-add-ic-call"'],
			],
			[
				['@test'],
				[],
				['LOG_ICON_INVALID'],
			],
			[
				['fa:address-foo-o'],
				['"prefix":"fa"', '"icons":[]'],
				['LOG_ICON_INVALID'],
			],
			[
				['foo:bar'],
				[],
				['LOG_ICON_COLLECTION_INVALID']
			],
			[
				['@iconify:fa:address-card-o'],
				['"prefix":"fa"', '"address-card-o"'],
			],
			[
				['@iconify:someother:fa:address-card-o'],
				[],
				['LOG_ICON_INVALID'],
			],
			[
				['iconify:fa:address-card-o'],
				['"prefix":"fa"', '"address-card-o"'],
			],
			[
				['iconify:fa:fa:address-card-o'],
				[],
				['LOG_ICON_INVALID'],
			],
			[
				['test'],
				[],
				['LOG_ICON_INVALID'],
			],
			[
				[''],
				[],
				['LOG_ICON_INVALID'],
			],
			[
				['fa-address-card-o'],
				['"prefix":"fa"', '"address-card-o"'],
			],
		];
	}

	/**
	 * @dataProvider data_test_generate_bundle
	 */
	public function test_generate_bundle($icons, $expected, $log_content = [])
	{
		$this->bundler->add_icons($icons);
		$bundle = $this->bundler->run();
		foreach ($expected as $expected_part)
		{
			$this->assertStringContainsString($expected_part, $bundle, 'Failed asserting that generated bundle contains ' . $expected_part);
		}

		if (!count($expected))
		{
			$this->assertEquals($bundle, '', 'Failed asserting that generated bundle is empty');
		}

		if (count($log_content))
		{
			$this->assertEquals($this->log_content, $log_content, 'Failed asserting that log content is correct');
		}
		else
		{
			$this->assertEmpty($this->log_content, 'Failed asserting that log content is empty');
		}
	}
}
