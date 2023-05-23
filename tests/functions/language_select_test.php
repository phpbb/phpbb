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
						'selected'			=> false,
						'value'			=> 'cs',
						'label'	=> 'Čeština',
					],
					[
						'selected'			=> false,
						'value'			=> 'en',
						'label'	=> 'English',
					],
				]
			],
			[
				'en',
				[
					[
						'selected'			=> false,
						'value'			=> 'cs',
						'label'	=> 'Čeština',
					],
					[
						'selected'			=> true,
						'value'			=> 'en',
						'label'	=> 'English',
					],
				]
			],
			[
				'cs',
				[
					[
						'selected'			=> true,
						'value'			=> 'cs',
						'label'	=> 'Čeština',
					],
					[
						'selected'			=> false,
						'value'			=> 'en',
						'label'	=> 'English',
					],
				]
			],
			[
				'de',
				[
					[
						'selected'			=> false,
						'value'			=> 'cs',
						'label'	=> 'Čeština',
					],
					[
						'selected'			=> false,
						'value'			=> 'en',
						'label'	=> 'English',
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

		$lang_options = phpbb_language_select($db, $default);

		$this->assertEquals($expected, $lang_options);
	}
}
