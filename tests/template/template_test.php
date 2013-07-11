<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
				'03',
			),
			array(
				'if.html',
				array('S_VALUE' => true),
				array(),
				array(),
				'1',
			),
			array(
				'if.html',
				array('S_VALUE' => true, 'S_OTHER_VALUE' => true),
				array(),
				array(),
				'1',
			),
			array(
				'if.html',
				array('S_VALUE' => false, 'S_OTHER_VALUE' => true),
				array(),
				array(),
				'2',
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
				array('loop' => array(array())),
				array(),
				"loop\nloop",
			),
			array(
				'loop.html',
				array(),
				array('loop' => array(array(), array()), 'loop.block' => array(array())),
				array(),
				"loop\nloop\nloop\nloop",
			),
			array(
				'loop.html',
				array(),
				array('loop' => array(array(), array()), 'loop.block' => array(array()), 'block' => array(array(), array())),
				array(),
				"loop\nloop\nloop\nloop\nloop#0-block#0\nloop#0-block#1\nloop#1-block#0\nloop#1-block#1",
			),
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'))),
				array(),
				"first\n0 - a\nx - b\nset\nlast",
			),
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y'))),
				array(),
				"first\n0 - a\nx - b\nset\n1 - a\ny - b\nset\nlast",
			),
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'loop.inner' => array(array(), array())),
				array(),
				"first\n0 - a\nx - b\nset\n1 - a\ny - b\nset\nlast\n0 - c\n1 - c\nlast inner\ninner loop",
			),
			array(
				'loop_advanced.html',
				array(),
				array('loop' => array(array(), array(), array(), array(), array(), array(), array())),
				array(),
				"101234561\nx\n101234561\nx\n101234561\nx\n1234561\nx\n1\nx\n101\nx\n234\nx\n10\nx\n561\nx\n561",
			),
			array(
				'define.html',
				array(),
				array('loop' => array(array(), array(), array(), array(), array(), array(), array()), 'test' => array(array()), 'test.deep' => array(array()), 'test.deep.defines' => array(array())),
				array(),
				"xyz\nabc\nabc\nbar\nbar\nabc",
			),
			array(
				'define_advanced.html',
				array(),
				array('loop' => array(array(), array(), array(), array(), array(), array(), array()), 'test' => array(array()), 'test.deep' => array(array()), 'test.deep.defines' => array(array())),
				array(),
				"abc\nzxc\ncde\nbcd",
			),
			array(
				'define_unclosed.html',
				array(),
				array(),
				array(),
				"test",
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
				array('loop' => array(array('NESTED_FILE' => 'include_loop1.html')), 'loop.inner' => array(array('NESTED_FILE' => 'include_loop1.html'), array('NESTED_FILE' => 'include_loop2.html'), array('NESTED_FILE' => 'include_loop3.html'))),
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
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'loop.inner' => array(array(), array())),
				array('loop'),
				'',
			),
			array(
				'include_define_variable.html',
				array('VARIABLE' => 'variable.html'),
				array(),
				array(),
				'variable.html',
			),
			array(
				'include_loop_define.html',
				array('VARIABLE' => 'value'),
				array('loop' => array(array('NESTED_FILE' => 'variable.html'))),
				array(),
				'value',
			),
			/* no top level nested loops
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'loop.inner' => array(array(), array())),
				array('loop.inner'),
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
				'lang.html',
				array(),
				array(),
				array(),
				"{ VARIABLE }\n{ 1_VARIABLE }\n{ VARIABLE }\n{ 1_VARIABLE }",
			),
			array(
				'lang.html',
				array('L_VARIABLE' => "Value'", 'L_1_VARIABLE' => "1 O'Clock"),
				array(),
				array(),
				"Value'\n1 O'Clock\nValue\'\n1 O\'Clock",
			),
			array(
				'lang.html',
				array('LA_VARIABLE' => "Value'", 'LA_1_VARIABLE' => "1 O'Clock"),
				array(),
				array(),
				"{ VARIABLE }\n{ 1_VARIABLE }\nValue'\n1 O'Clock",
			),
			array(
				'loop_nested_multilevel_ref.html',
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
				// I don't completely understand this output, hopefully it's correct
				"top-level content\nouter x\nouter y\ninner z\nfirst row\n\ninner zz",
			),
			array(
				'loop_nested_deep_multilevel_ref.html',
				array(),
				array('outer' => array(array()), 'outer.middle' => array(array()), 'outer.middle.inner' => array(array('VARIABLE' => 'z'), array('VARIABLE' => 'zz'))),
				array(),
				// I don't completely understand this output, hopefully it's correct
				"top-level content\nouter\nmiddle\ninner z\nfirst row of 2 in inner\n\ninner zz",
			),
			array(
				'loop_size.html',
				array(),
				array('loop' => array(array()), 'empty_loop' => array()),
				array(),
				"nonexistent = 0\n! nonexistent\n\nempty = 0\n! empty\nloop\n\nin loop",
			),
			/* Does not pass with the current implementation.
			array(
				'loop_reuse.html',
				array(),
				array('one' => array(array('VAR' => 'a'), array('VAR' => 'b')), 'one.one' => array(array('VAR' => 'c'), array('VAR' => 'd'))),
				array(),
				// Not entirely sure what should be outputted but the current output of "a" is most certainly wrong
				"a\nb\nc\nd",
			),
			*/
		);
	}

	public function test_missing_file()
	{
		$filename = 'file_not_found.html';

		$this->template->set_filenames(array('test' => $filename));
		$this->assertFileNotExists($this->template_path . '/' . $filename, 'Testing missing file, file cannot exist');

		$expecting = sprintf('style resource locator: File for handle test does not exist. Could not find: %s', $this->test_path . '/templates/' . $filename);
		$this->setExpectedTriggerError(E_USER_ERROR, $expecting);

		$this->display('test');
	}

	public function test_empty_file()
	{
		$expecting = 'style resource locator: set_filenames: Empty filename specified for test';

		$this->setExpectedTriggerError(E_USER_ERROR, $expecting);
		$this->template->set_filenames(array('test' => ''));
	}

	public function test_invalid_handle()
	{
		$expecting = 'No file specified for handle test';
		$this->setExpectedTriggerError(E_USER_ERROR, $expecting);

		$this->display('test');
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($file, array $vars, array $block_vars, array $destroy, $expected)
	{
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.php';

		$this->assertFileNotExists($cache_file);

		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);

		// Reset the engine state
		$this->setup_engine();

		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_assign_display($file, array $vars, array $block_vars, array $destroy, $expected)
	{
		$this->template->set_filenames(array(
			'test' => $file,
			'container' => 'variable.html',
		));
		$this->template->assign_vars($vars);

		foreach ($block_vars as $block => $loops)
		{
			foreach ($loops as $_vars)
			{
				$this->template->assign_block_vars($block, $_vars);
			}
		}

		foreach ($destroy as $block)
		{
			$this->template->destroy_block_vars($block);
		}

		$this->assertEquals($expected, self::trim_template_result($this->template->assign_display('test')), "Testing assign_display($file)");

		$this->template->assign_display('test', 'VARIABLE', false);

		$this->assertEquals($expected, $this->display('container'), "Testing assign_display($file)");
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
		$this->setup_engine(array('tpl_allow_php' => true));

		$cache_file = $this->template->cachepath . 'php.html.php';

		$this->assertFileNotExists($cache_file);

		$this->run_template('php.html', array(), array(), array(), 'test', $cache_file);
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

		$this->assertEquals("outer - 0\nmiddle - 0\nmiddle - 1\nouter - 1\nmiddle - 0\nmiddle - 1\nouter - 2\nmiddle - 0\nmiddle - 1", $this->display('test'), 'Ensuring template is built correctly before modification');

		$this->template->alter_block_array($alter_block, $vararray, $key, $mode);
		$this->assertEquals($expect, $this->display('test'), $description);
	}

}
