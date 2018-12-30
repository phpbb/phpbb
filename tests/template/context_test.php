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

class context_test extends phpbb_test_case
{
	protected $context;
	protected function setUp(): void
	{
		$this->context = new \phpbb\template\context();

		for ($i = 0; $i < 10; $i++)
		{
			$this->context->assign_block_vars('block' . $i, array(
				'FOO' . $i	=> 'foo' . $i,
				'BAR' . $i	=> 'bar' . $i,
			));

			for ($j = 0; $j < 10; $j++)
			{
				$this->context->assign_block_vars('block' . $i . '.subblock', array(
					'SUBFOO'	=> 'subfoo' . $j,
					'SUBBAR'	=> 'subbar' . $j,
				));

				for ($k = 0; $k < 10; $k++)
				{
					$this->context->assign_block_vars('block' . $i . '.subblock.subsubblock', array(
						'SUBSUBFOO'	=> 'subsubfoo' . $k,
						'SUBSUBBAR'	=> 'subsubbar' . $k,
					));
				}
			}
		}
	}

	public function retrieve_block_vars_data()
	{
		return array(
			array('foo', array(), array()), // non-existent top-level block
			array('block1.foo', array(), array()), // non-existent sub-level block
			array('block1', array(), array( // top-level block, all vars
				'FOO1'	=> 'foo1',
				'BAR1'	=> 'bar1',
			)),
			array('block1', array('FOO1'), array( // top-level block, one var
				'FOO1'	=> 'foo1',
			)),
			array('block2.subblock', array(), array( // sub-level block, all vars
				'SUBFOO'	=> 'subfoo9',
				'SUBBAR'	=> 'subbar9',
			)),
			array('block2.subblock', array('SUBBAR'), array( // sub-level block, one var
				'SUBBAR'	=> 'subbar9',
			)),
			array('block2.subblock.subsubblock', array(), array( // sub-sub-level block, all vars
				'SUBSUBFOO'	=> 'subsubfoo9',
				'SUBSUBBAR'	=> 'subsubbar9',
			)),
			array('block2.subblock.subsubblock', array('SUBSUBBAR'), array( // sub-sub-level block, one var
				'SUBSUBBAR'	=> 'subsubbar9',
			)),
			array('block3.subblock[2]', array(), array( // sub-level, exact index, all vars
				'SUBFOO'	=> 'subfoo2',
				'SUBBAR'	=> 'subbar2',
			)),
			array('block3.subblock[2]', array('SUBBAR'), array( // sub-level, exact index, one var
				'SUBBAR'	=> 'subbar2',
			)),
			array('block3.subblock[3].subsubblock[5]', array(), array( // sub-sub-level, exact index, all vars
				'SUBSUBFOO'	=> 'subsubfoo5',
				'SUBSUBBAR'	=> 'subsubbar5',
			)),
			array('block3.subblock[4].subsubblock[6]', array('SUBSUBFOO'), array( // sub-sub-level, exact index, one var
				'SUBSUBFOO'	=> 'subsubfoo6',
			)),
		);
	}

	/**
	* @dataProvider retrieve_block_vars_data
	*/
	public function test_retrieve_block_vars($blockname, $vararray, $result)
	{
		$this->assertEquals($result, $this->context->retrieve_block_vars($blockname, $vararray));
	}
}
