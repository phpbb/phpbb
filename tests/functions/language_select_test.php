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

class phpbb_functions_language_select_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/language_select.xml');
	}

	public static function language_select_data()
	{
		return [
			[
				'',
				[
					[
						'SELECTED'			=> false,
						'LANG_ISO'			=> 'cs',
						'LANG_LOCAL_NAME'	=> 'Čeština',
					],
					[
						'SELECTED'			=> false,
						'LANG_ISO'			=> 'en',
						'LANG_LOCAL_NAME'	=> 'English',
					],
				]
			],
			[
				'en',
				[
					[
						'SELECTED'			=> false,
						'LANG_ISO'			=> 'cs',
						'LANG_LOCAL_NAME'	=> 'Čeština',
					],
					[
						'SELECTED'			=> true,
						'LANG_ISO'			=> 'en',
						'LANG_LOCAL_NAME'	=> 'English',
					],
				]
			],
			[
				'cs',
				[
					[
						'SELECTED'			=> true,
						'LANG_ISO'			=> 'cs',
						'LANG_LOCAL_NAME'	=> 'Čeština',
					],
					[
						'SELECTED'			=> false,
						'LANG_ISO'			=> 'en',
						'LANG_LOCAL_NAME'	=> 'English',
					],
				]
			],
			[
				'de',
				[
					[
						'SELECTED'			=> false,
						'LANG_ISO'			=> 'cs',
						'LANG_LOCAL_NAME'	=> 'Čeština',
					],
					[
						'SELECTED'			=> false,
						'LANG_ISO'			=> 'en',
						'LANG_LOCAL_NAME'	=> 'English',
					],
				]
			],
		];
	}

	/**
	* @dataProvider language_select_data
	*/
	public function test_language_select($default, $expected)
	{
		global $db;
		$db = $this->new_dbal();
		$template	= $this->getMockBuilder('\phpbb\template\base')
			->disableOriginalConstructor()
			->getMock();
		$template_data = [];
		$template->expects($this->any())
			->method('assign_block_vars')
			->willReturnCallback(function ($blockname, array $vararray) use (&$template_data) {
				$template_data[$blockname][] = $vararray;
				return null;
			});

		phpbb_language_select($db, $template, $default);

		$this->assertEquals($expected, $template_data['lang_options']);
	}
}
