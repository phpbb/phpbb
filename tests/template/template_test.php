<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_test extends phpbb_template_template_test_case
{
	/**
	 * @todo put test data into templates/xyz.test
	 */
	public static function template_data()
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
				"pass\npass\n<!-- DUMMY var -->",
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
				'0',
			),
			array(
				'if.html',
				array('S_VALUE' => true),
				array(),
				array(),
				"1\n0",
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
				"first\n0\nx\nset\nlast",
			),/* no nested top level loops
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y'))),
				array(),
				"first\n0\n0\n2\nx\nset\n1\n1\n2\ny\nset\nlast",
			),
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'loop.inner' => array(array(), array())),
				array(),
				"first\n0\n0\n2\nx\nset\n1\n1\n2\ny\nset\nlast\n0\n\n1\nlast inner\ninner loop",
			),*/
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
				"xyz\nabc",
			),
			array(
				'expressions.html',
				array(),
				array(),
				array(),
				trim(str_repeat("pass", 39)),
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
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'loop.inner' => array(array(), array())),
				array('loop'),
				'',
			),/* no top level nested loops
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'loop.inner' => array(array(), array())),
				array('loop.inner'),
				"first\n0\n0\n2\nx\nset\n1\n1\n2\ny\nset\nlast",
			),*/
			array(
				'lang.html',
				array(),
				array(),
				array(),
				"{ VARIABLE }\n{ VARIABLE }",
			),
			array(
				'lang.html',
				array('L_VARIABLE' => "Value'"),
				array(),
				array(),
				"Value'\nValue\'",
			),
			array(
				'lang.html',
				array('LA_VARIABLE' => "Value'"),
				array(),
				array(),
				"{ VARIABLE }\nValue'",
			),
		);
	}

	public function test_missing_file()
	{
		$filename = 'file_not_found.html';

		$this->template->set_filenames(array('test' => $filename));
		$this->assertFileNotExists($this->template_path . '/' . $filename, 'Testing missing file, file cannot exist');

		$expecting = sprintf('template->_tpl_load_file(): File %s does not exist or is empty', realpath($this->template_path . '/../') . '/templates/' . $filename);
		$this->setExpectedTriggerError(E_USER_ERROR, $expecting);

		$this->display('test');
	}

	public function test_empty_file()
	{
		$expecting = 'template->set_filenames: Empty filename specified for test';

		$this->setExpectedTriggerError(E_USER_ERROR, $expecting);
		$this->template->set_filenames(array('test' => ''));
	}

	public function test_invalid_handle()
	{
		$expecting = 'template->_tpl_load(): No file specified for handle test';
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

	public function test_php()
	{
		$GLOBALS['config']['tpl_allow_php'] = true;

		$cache_file = $this->template->cachepath . 'php.html.php';

		$this->assertFileNotExists($cache_file);

		$this->run_template('php.html', array(), array(), array(), 'test', $cache_file);

		$GLOBALS['config']['tpl_allow_php'] = false;
	}

	public function test_includephp()
	{
		$GLOBALS['config']['tpl_allow_php'] = true;

		$cache_file = $this->template->cachepath . 'includephp.html.php';

		$this->run_template('includephp.html', array(), array(), array(), 'testing included php', $cache_file);

		$this->template->set_filenames(array('test' => 'includephp.html'));
		$this->assertEquals('testing included php', $this->display('test'), "Testing INCLUDEPHP");

		$GLOBALS['config']['tpl_allow_php'] = false;
	}

	public static function alter_block_array_data()
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

