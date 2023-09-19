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

class phpbb_functions_style_select_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/style_select.xml');
	}

	static public function style_select_data()
	{
		return [
			[
				'',
				false,
				[
					[
						'value'	=>	'1',
						'selected'	=> false,
						'label'		=> 'prosilver',
					],
					[
						'value'	=>	'2',
						'selected'	=> false,
						'label'		=> 'subsilver2',
					],
				]
			],
			[
				'',
				true,
				[
					[
						'value'	=>	'1',
						'selected'	=> false,
						'label'		=> 'prosilver',
					],
					[
						'value'	=>	'2',
						'selected'	=> false,
						'label'		=> 'subsilver2',
					],
					[
						'value'	=>	'3',
						'selected'	=> false,
						'label'		=> 'zoo',
					],
				]
			],
			[
				'1',
				false,
				[
					[
						'value'	=>	'1',
						'selected'	=> true,
						'label'		=> 'prosilver',
					],
					[
						'value'	=>	'2',
						'selected'	=> false,
						'label'		=> 'subsilver2',
					],
				]
			],
			[
				'1',
				true,
				[
					[
						'value'	=>	'1',
						'selected'	=> true,
						'label'		=> 'prosilver',
					],
					[
						'value'	=>	'2',
						'selected'	=> false,
						'label'		=> 'subsilver2',
					],
					[
						'value'	=>	'3',
						'selected'	=> false,
						'label'		=> 'zoo',
					],
				]
			],
			[
				'3',
				false,
				[
					[
						'value'	=>	'1',
						'selected'	=> false,
						'label'		=> 'prosilver',
					],
					[
						'value'	=>	'2',
						'selected'	=> false,
						'label'		=> 'subsilver2',
					],
				]
			],
			[
				'3',
				true,
				[
					[
						'value'	=>	'1',
						'selected'	=> false,
						'label'		=> 'prosilver',
					],
					[
						'value'	=>	'2',
						'selected'	=> false,
						'label'		=> 'subsilver2',
					],
					[
						'value'	=>	'3',
						'selected'	=> true,
						'label'		=> 'zoo',
					],
				]
			],
		];
	}

	/**
	* @dataProvider style_select_data
	*/
	public function test_style_select($default, $all, $expected)
	{
		global $db;
		$db = $this->new_dbal();

		$this->assertEquals($expected, style_select($default, $all));
	}
}
