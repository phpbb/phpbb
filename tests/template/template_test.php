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
require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_test extends phpbb_template_template_test_case
{
	/**
	 * @todo put test data into templates/xyz.test
	 */
	public function template_data()
	{
		return array(
			/*
			array(
				'', // File
				array(), // vars
				array(), // block vars
				array(), // destroy
				'', // Expected result
			),
			*/
			array(
				'basic.html',
				array(),
				array(),
				array(),
				"pass\npass\npass\n<!-- DUMMY var -->",
			),
			array(
				'variable.html',
				array('VARIABLE' => 'value'),
				array(),
				array(),
				'value',
			),
			array(
				'if.html',
				array(),
				array(),
				array(),
				'03!false',
			),
			array(
				'if.html',
				array('S_VALUE' => true),
				array(),
				array(),
				'1!false',
			),
			array(
				'if.html',
				array('S_VALUE' => true, 'S_OTHER_VALUE' => true),
				array(),
				array(),
				'1!false',
			),
			array(
				'if.html',
				array('S_OTHER_OTHER_VALUE' => true),
				array(),
				array(),
				'|S_OTHER_OTHER_VALUE|!false',
			),
			array(
				'if.html',
				array('S_VALUE' => false, 'S_OTHER_VALUE' => true),
				array(),
				array(),
				'2!false',
			),
			array(
				'if.html',
				array('S_TEST' => false),
				array(),
				array(),
				'03false',
			),
			array(
				'if.html',
				array('S_TEST' => 0),
				array(),
				array(),
				'03!false',
			),
			array(
				'if.html',
				array('VALUE_TEST' => 'value'),
				array(),
				array(),
				'03!falsevalue',
			),
			array(
				'loop.html',
				array(),
				array(),
				array(),
				"noloop\nnoloop",
			),
			array(
				'loop.html',
				array(),
				array('test_loop' => array(array())),
				array(),
				"loop\nloop",
			),
			array(
				'loop.html',
				array(),
				array('test_loop' => array(array(), array()), 'test_loop.block' => array(array())),
				array(),
				"loop\nloop\nloop\nloop",
			),
			array(
				'loop.html',
				array(),
				array('test_loop' => array(array(), array()), 'test_loop.block' => array(array()), 'block' => array(array(), array())),
				array(),
				"loop\nloop\nloop\nloop\nloop#0-block#0\nloop#0-block#1\nloop#1-block#0\nloop#1-block#1",
			),
			array(
				'loop_twig.html',
				array(),
				array(),
				array(),
				"noloop\nnoloop",
			),
			array(
				'loop_twig.html',
				array(),
				array('test_loop' => array(array())),
				array(),
				"loop\nloop",
			),
			array(
				'loop_twig.html',
				array(),
				array('test_loop' => array(array(), array()), 'test_loop.block' => array(array())),
				array(),
				"loop\nloop\nloop\nloop",
			),
			array(
				'loop_twig.html',
				array(),
				array('test_loop' => array(array(), array()), 'test_loop.block' => array(array()), 'block' => array(array(), array())),
				array(),
				"loop\nloop\nloop\nloop\nloop#0-block#0\nloop#0-block#1\nloop#1-block#0\nloop#1-block#1",
			),
			array(
				'loop_vars.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'))),
				array(),
				"first\n0 - a\nx - b\nset\nlast",
			),
			array(
				'loop_vars.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y'))),
				array(),
				"first\n0 - a\nx - b\nset\n1 - a\ny - b\nset\nlast",
			),
			array(
				'loop_vars.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'test_loop.inner' => array(array(), array())),
				array(),
				"first\n0 - a\nx - b\nset\n1 - a\ny - b\nset\nlast\n0 - c\n1 - c\nlast inner",
			),
			array(
				'loop_vars_twig.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'))),
				array(),
				"first\n0 - a\nx - b\nset\nlast",
			),
			array(
				'loop_vars_twig.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y'))),
				array(),
				"first\n0 - a\nx - b\nset\n1 - a\ny - b\nset\nlast",
			),
			array(
				'loop_vars_twig.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'test_loop.inner' => array(array(), array())),
				array(),
				"first\n0 - a\nx - b\nset\n1 - a\ny - b\nset\nlast\n0 - c\n1 - c\nlast inner",
			),
			array(
				'loop_advanced.html',
				array(),
				array('test_loop' => array(array(), array(), array(), array(), array(), array(), array())),
				array(),
				"101234561\nx\n101234561\nx\n101234561\nx\n1234561\nx\n1\nx\n101\nx\n234\nx\n10\nx\n561\nx\n561",
			),
			array(
				'loop_advanced_twig.html',
				array(),
				array('test_loop' => array(array(), array(), array(), array(), array(), array(), array())),
				array(),
				"101234561\nx\n101234561\nx\n101234561\nx\n1234561\nx\n1\nx\n101\nx\n234\nx\n10\nx\n561\nx\n561",
			),
			array(
				'loop_nested2.html',
				array(),
				array('outer' => array(array(), array()), 'outer.middle' => array(array(), array())),
				array(),
				"o0o1m01m11",
			),
			array(
				'loop_nested2_twig.html',
				array(),
				array('outer' => array(array(), array()), 'outer.middle' => array(array(), array())),
				array(),
				"o0o1m01m11",
			),
			array(
				'define.html',
				array(),
				array('test_loop' => array(array(), array(), array(), array(), array(), array(), array()), 'test' => array(array()), 'test.deep' => array(array()), 'test.deep.defines' => array(array())),
				array(),
				"xyz\nabc\n\$VALUE == 'abc'\n(\$VALUE == 'abc')\n((\$VALUE == 'abc'))\n!\$DOESNT_EXIST\n(!\$DOESNT_EXIST)\nabc\nbar\nbar\nabc\ntest!@#$%^&*()_-=+{}[]:;\",<.>/?[]|foobar|false",
			),
			array(
				'define_advanced.html',
				array(),
				array('test_loop' => array(array(), array(), array(), array(), array(), array(), array()), 'test' => array(array()), 'test.deep' => array(array()), 'test.deep.defines' => array(array())),
				array(),
				"abc\nzxc\ncde\nbcd",
			),
			array(
				'expressions.html',
				array(),
				array(),
				array(),
				trim(str_repeat("pass\n", 10) . "\n"
					. str_repeat("pass\n", 4) . "\n"
					. str_repeat("pass\n", 2) . "\n"
					. str_repeat("pass\n", 6) . "\n"
					. str_repeat("pass\n", 2) . "\n"
					. str_repeat("pass\n", 6) . "\n"
					. str_repeat("pass\n", 2) . "\n"
					. str_repeat("pass\n", 2) . "\n"
					. str_repeat("pass\n", 3) . "\n"
					. str_repeat("pass\n", 2) . "\n"),
			),
			array(
				'php.html',
				array(),
				array(),
				array(),
				'',
			),
			array(
				'include.html',
				array('VARIABLE' => 'value'),
				array(),
				array(),
				'value',
			),
			array(
				'include_define.html',
				array('VARIABLE' => 'value'),
				array(),
				array(),
				'value',
			),
			array(
				'include_loop.html',
				array(),
				array('test_loop' => array(array('NESTED_FILE' => 'include_loop1.html')), 'test_loop.inner' => array(array('NESTED_FILE' => 'include_loop1.html'), array('NESTED_FILE' => 'include_loop2.html'), array('NESTED_FILE' => 'include_loop3.html'))),
				array(),
				"1\n_1\n_02\n_3",
			),
			array(
				'include_variable.html',
				array('FILE' => 'variable.html', 'VARIABLE' => 'value'),
				array(),
				array(),
				'value',
			),
			array(
				'include_variables.html',
				array('SUBDIR' => 'subdir', 'VARIABLE' => 'value'),
				array(),
				array(),
				'value',
			),
			array(
				'loop_vars.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'test_loop.inner' => array(array(), array())),
				array('test_loop'),
				'',
			),
			array(
				'loop_vars_twig.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'test_loop.inner' => array(array(), array())),
				array('test_loop'),
				'',
			),
			array(
				'include_define_variable.html',
				array('VARIABLE' => 'variable.html'),
				array(),
				array(),
				"variable.html\nvariable.html\nvariable.html",
			),
			array(
				'include_loop_define.html',
				array('VARIABLE' => 'value'),
				array('test_loop' => array(array('NESTED_FILE' => 'variable.html'))),
				array(),
				'value',
			),
			/* no top level nested loops
			array(
				'loop_vars.html',
				array(),
				array('test_loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'test_loop.inner' => array(array(), array())),
				array('test_loop.inner'),
				"first\n0\n0\n2\nx\nset\n1\n1\n2\ny\nset\nlast",
			),*/
			array(
				// Just like a regular loop but the name begins
				// with an underscore
				'loop_underscore.html',
				array(),
				array(),
				array(),
				"noloop\nnoloop",
			),
			array(
				// Just like a regular loop but the name begins
				// with an underscore
				'loop_underscore_twig.html',
				array(),
				array(),
				array(),
				"noloop\nnoloop",
			),
			array(
				'lang.html',
				array(),
				array(),
				array(),
				"VARIABLE\n1_VARIABLE\nVARIABLE\n1_VARIABLE",
			),
			array(
				'lang.html',
				array(),
				array(),
				array(),
				"Value'\n1 O'Clock\nValue\\x27\n1\\x20O\\x27Clock",
				array('VARIABLE' => "Value'", '1_VARIABLE' => "1 O'Clock"),
			),
			array(
				'loop_nested_multilevel_ref.html',
				array(),
				array(),
				array(),
				"top-level content",
			),
			array(
				'loop_nested_multilevel_ref_twig.html',
				array(),
				array(),
				array(),
				"top-level content",
			),
			array(
				'loop_nested_multilevel_ref.html',
				array(),
				array('outer' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'outer.inner' => array(array('VARIABLE' => 'z'), array('VARIABLE' => 'zz'))),
				array(),
				"top-level content\nouter x\nouter y\ninner z\nfirst row\n\ninner zz",
			),
			array(
				'loop_nested_multilevel_ref_twig.html',
				array(),
				array('outer' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'outer.inner' => array(array('VARIABLE' => 'z'), array('VARIABLE' => 'zz'))),
				array(),
				"top-level content\nouter x\nouter y\ninner z\nfirst row\n\ninner zz",
			),
			array(
				'loop_nested_deep_multilevel_ref.html',
				array(),
				array('outer' => array(array()), 'outer.middle' => array(array()), 'outer.middle.inner' => array(array('VARIABLE' => 'z'), array('VARIABLE' => 'zz'))),
				array(),
				"top-level content\nouter\nmiddle\ninner z\nfirst row of 2 in inner\n\ninner zz",
			),
			array(
				'loop_nested_deep_multilevel_ref_twig.html',
				array(),
				array('outer' => array(array()), 'outer.middle' => array(array()), 'outer.middle.inner' => array(array('VARIABLE' => 'z'), array('VARIABLE' => 'zz'))),
				array(),
				"top-level content\nouter\nmiddle\ninner z\nfirst row of 2 in inner\n\ninner zz",
			),
			array(
				'loop_size.html',
				array(),
				array('test_loop' => array(array()), 'empty_loop' => array()),
				array(),
				"nonexistent = 0\n! nonexistent\n\nempty = 0\n! empty\nloop\n\nin loop",
			),
			array(
				'loop_size_twig.html',
				array(),
				array('test_loop' => array(array()), 'empty_loop' => array()),
				array(),
				"nonexistent = 0\n! nonexistent\n\nempty = 0\n! empty\nloop\n\nin loop",
			),
			array(
				'loop_include.html',
				array(),
				array('test_loop' => array(array('foo' => 'bar'), array('foo' => 'bar1'))),
				array(),
				"barbarbar1bar1",
			),
			array(
				'loop_include_twig.html',
				array(),
				array('test_loop' => array(array('foo' => 'bar'), array('foo' => 'bar1'))),
				array(),
				"barbarbar1bar1",
			),
			array(
				'loop_nested_include.html',
				array(),
				array(
					'test_loop' => array(array('foo' => 'bar'), array('foo' => 'bar1')),
					'test_loop.inner' => array(array('myinner' => 'works')),
				),
				array(),
				"[bar|[bar|]][bar1|[bar1|[bar1|works]]]",
				array(),
			),
			array(
				'loop_nested_include_twig.html',
				array(),
				array(
					'test_loop' => array(array('foo' => 'bar'), array('foo' => 'bar1')),
					'test_loop.inner' => array(array('myinner' => 'works')),
				),
				array(),
				"[bar|[bar|]][bar1|[bar1|[bar1|works]]]",
				array(),
			),
			/* Does not pass with the current implementation.
			array(
				'loop_reuse.html',
				array(),
				array('one' => array(array('VAR' => 'a'), array('VAR' => 'b')), 'one.one' => array(array('VAR' => 'c'), array('VAR' => 'd'))),
				array(),
				// Not entirely sure what should be outputted but the current output of "a" is most certainly wrong
				"a\nb\nc\nd",
			),*/
			array(
				'loop_reuse_twig.html',
				array(),
				array('one' => array(array('VAR' => 'a'), array('VAR' => 'b')), 'one.one' => array(array('VAR' => 'c'), array('VAR' => 'd'))),
				array(),
				// Not entirely sure what should be outputted but the current output of "a" is most certainly wrong
				"a\nb\nc\nd",
			),
			array(
				'twig.html',
				array('VARIABLE' => 'FOObar',),
				array(),
				array(),
				"13FOOBAR|foobar",
			),
			array(
				'if_nested_tags.html',
				array('S_VALUE' => true,),
				array(),
				array(),
				'inner_value',
			),
			array(
				'loop_expressions.html',
				array(),
				array('loop' => array(array(),array(),array(),array(),array(),array()),),
				array(),
				'yesnononoyesnoyesnonoyesnono',
			),
			array(
				'loop_expressions_twig.html',
				array(),
				array('loop' => array(array(),array(),array(),array(),array(),array()),),
				array(),
				'yesnononoyesnoyesnonoyesnono',
			),
			array(
				'loop_expressions_twig2.html',
				array('loop' => array(array(),array(),array(),array(),array(),array()),),
				array(),
				array(),
				'yesnononoyesnoyesnonoyesnono',
			),
		);
	}

	public function test_missing_file()
	{
		$filename = 'file_not_found.html';

		$this->template->set_filenames(array('test' => $filename));
		$this->assertFileNotExists($this->template_path . '/' . $filename, 'Testing missing file, file cannot exist');

		$this->setExpectedException('Twig_Error_Loader');

		$this->display('test');
	}


	public function test_invalid_handle()
	{
		$this->setExpectedException('Twig_Error_Loader');

		$this->display('test');
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($file, array $vars, array $block_vars, array $destroy, $expected, $lang_vars = array(), $incomplete_message = '')
	{
		if ($incomplete_message)
		{
			$this->markTestIncomplete($incomplete_message);
		}

		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $lang_vars);
	}

	public function test_assign_display()
	{
		$this->run_template('basic.html', array(), array(), array(), "pass\npass\npass\n<!-- DUMMY var -->");

		$this->template->set_filenames(array(
			'test'		=> 'basic.html',
			'container'	=> 'variable.html',
		));

		$this->template->assign_display('test', 'VARIABLE', false);

		$this->assertEquals("pass\npass\npass\n<!-- DUMMY var -->", $this->display('container'), "Testing assign_display($file)");
	}

	public function test_append_var_without_assign_var()
	{
		$this->template->set_filenames(array(
			'append_var'	=> 'variable.html'
		));

		$items = array('This ', 'is ', 'a ', 'test');
		$expecting = implode('', $items);

		foreach ($items as $word)
		{
			$this->template->append_var('VARIABLE', $word);
		}

		$this->assertEquals($expecting, $this->display('append_var'));
	}

	public function test_append_var_with_assign_var()
	{
		$this->template->set_filenames(array(
			'append_var'	=> 'variable.html'
		));

		$start = 'This ';
		$items = array('is ', 'a ', 'test');
		$expecting = $start . implode('', $items);

		$this->template->assign_var('VARIABLE', $start);
		foreach ($items as $word)
		{
			$this->template->append_var('VARIABLE', $word);
		}

		$this->assertEquals($expecting, $this->display('append_var'));
	}

	public function test_php()
	{
		global $phpbb_root_path;

		$template_text = '<!-- PHP -->echo "test";<!-- ENDPHP -->';

		$cache_dir = $phpbb_root_path . 'cache/';
		$fp = fopen($cache_dir . 'php.html', 'w');
		fputs($fp, $template_text);
		fclose($fp);

		$this->setup_engine(array('tpl_allow_php' => true));

		$this->template->set_custom_style('tests', $cache_dir);

		$this->run_template('php.html', array(), array(), array(), 'test');
	}

	public function alter_block_array_data()
	{
		return array(
			array(
				'outer',
				array('VARIABLE' => 'before'),
				false,
				'insert',
				<<<EOT
outer - 0 - before
outer - 1
middle - 0
middle - 1
outer - 2
middle - 0
middle - 1
outer - 3
middle - 0
middle - 1
EOT
,
				'Test inserting before on top level block',
			),
			array(
				'outer',
				array('VARIABLE' => 'after'),
				true,
				'insert',
				<<<EOT
outer - 0
middle - 0
middle - 1
outer - 1
middle - 0
middle - 1
outer - 2
middle - 0
middle - 1
outer - 3 - after
EOT
,
				'Test inserting after on top level block',
			),
			array(
				'outer',
				array('VARIABLE' => 'pos #1'),
				1,
				'insert',
				<<<EOT
outer - 0
middle - 0
middle - 1
outer - 1 - pos #1
outer - 2
middle - 0
middle - 1
outer - 3
middle - 0
middle - 1
EOT
,
				'Test inserting at 1 on top level block',
			),
			array(
				'outer',
				array('VARIABLE' => 'pos #1'),
				0,
				'change',
				<<<EOT
outer - 0 - pos #1
middle - 0
middle - 1
outer - 1
middle - 0
middle - 1
outer - 2
middle - 0
middle - 1
EOT
,
				'Test inserting at 1 on top level block',
			),
		);
	}

	/**
	* @dataProvider alter_block_array_data
	*/
	public function test_alter_block_array($alter_block, array $vararray, $key, $mode, $expect, $description)
	{
		$this->template->set_filenames(array('test' => 'loop_nested.html'));

		// @todo Change this
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());

		$this->assertEquals("outer - 0middle - 0middle - 1outer - 1middle - 0middle - 1outer - 2middle - 0middle - 1", $this->display('test'), 'Ensuring template is built correctly before modification');

		$this->template->alter_block_array($alter_block, $vararray, $key, $mode);
		$this->assertEquals(str_replace(array("\n", "\r", "\t"), '', $expect), str_replace(array("\n", "\r", "\t"), '', $this->display('test')), $description);
	}

	public function test_more_alter_block_array()
	{
		$this->template->set_filenames(array('test' => 'loop_nested.html'));

		$this->template->assign_var('TEST_MORE', true);

		// @todo Change this
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());

		$expect = 'outer - 0[outer|3]middle - 0[middle|1]outer - 1[outer|3]middle - 0[middle|2]middle - 1[middle|2]outer - 2[outer|3]middle - 0[middle|3]middle - 1[middle|3]middle - 2[middle|3]';
		$this->assertEquals($expect, str_replace(array("\n", "\r", "\t"), '', $this->display('test')), 'Ensuring template is built correctly before modification');

		$this->template->alter_block_array('outer', array());

		$expect = 'outer - 0[outer|4]outer - 1[outer|4]middle - 0[middle|1]outer - 2[outer|4]middle - 0[middle|2]middle - 1[middle|2]outer - 3[outer|4]middle - 0[middle|3]middle - 1[middle|3]middle - 2[middle|3]';
		$this->assertEquals($expect, str_replace(array("\n", "\r", "\t"), '', $this->display('test')), 'Ensuring S_NUM_ROWS is correct after insertion');

		$this->template->alter_block_array('outer', array('VARIABLE' => 'test'), 2, 'change');

		$expect = 'outer - 0[outer|4]outer - 1[outer|4]middle - 0[middle|1]outer - 2 - test[outer|4]middle - 0[middle|2]middle - 1[middle|2]outer - 3[outer|4]middle - 0[middle|3]middle - 1[middle|3]middle - 2[middle|3]';
		$this->assertEquals($expect, str_replace(array("\n", "\r", "\t"), '', $this->display('test')), 'Ensuring S_NUM_ROWS is correct after modification');
	}

	public function assign_block_vars_array_data()
	{
		return array(
			array(
				array(
					'outer' => array(
						array('VARIABLE' => 'Test assigning block vars array loop 0:'),
						array('VARIABLE' => 'Test assigning block vars array loop 1:'),
					),
					'outer.middle' => array(
						array('VARIABLE' => '1st iteration',),
						array('VARIABLE' => '2nd iteration',),
						array('VARIABLE' => '3rd iteration',),
					),
				)
			)
		);
	}

	/**
	* @dataProvider assign_block_vars_array_data
	*/
	public function test_assign_block_vars_array($block_data)
	{
		$this->template->set_filenames(array('test' => 'loop_nested.html'));

		foreach ($block_data as $blockname => $block_vars_array)
		{
			$this->template->assign_block_vars_array($blockname, $block_vars_array);
		}

		$this->assertEquals("outer - 0 - Test assigning block vars array loop 0:outer - 1 - Test assigning block vars array loop 1:middle - 0 - 1st iterationmiddle - 1 - 2nd iterationmiddle - 2 - 3rd iteration", $this->display('test'), 'Ensuring assigning block vars array to template is working correctly');
	}

	/**
	* @expectedException Twig_Error_Syntax
	*/
	public function test_define_error()
	{
		$this->run_template('define_error.html', array(), array(), array(), '');
	}
}
