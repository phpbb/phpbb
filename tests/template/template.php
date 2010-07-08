<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';

require_once '../phpBB/includes/template.php';

class phpbb_template_template_test extends phpbb_test_case
{
	private $template;
	private $template_path;

	// Keep the contents of the cache for debugging?
	const PRESERVE_CACHE = true;

	private function display($handle)
	{
		// allow the templates to throw notices
		$error_level = error_reporting();
		error_reporting($error_level & ~E_NOTICE);

		ob_start();

		try
		{
			$this->assertTrue($this->template->display($handle, false));
		}
		catch (Exception $exception)
		{
			// reset the error level even when an error occured
			// PHPUnit turns trigger_error into exceptions as well
			error_reporting($error_level);
			throw $exception;
		}

		$result = self::trim_template_result(ob_get_clean());

		// reset error level
		error_reporting($error_level);
		return $result;
	}

	private static function trim_template_result($result)
	{
		return str_replace("\n\n", "\n", implode("\n", array_map('trim', explode("\n", trim($result)))));
	}

	private function setup_engine()
	{
		$this->template_path = dirname(__FILE__) . '/templates';
		$this->template = new template();
		$this->template->set_custom_template($this->template_path, 'tests');
	}

	protected function setUp()
	{
		// Test the engine can be used
		$this->setup_engine();

		if (!is_writable(dirname($this->template->cachepath)))
		{
			$this->markTestSkipped("Template cache directory is not writable.");
		}

		foreach (glob($this->template->cachepath . '*') as $file)
		{
			unlink($file);
		}

		$GLOBALS['config'] = array(
			'load_tplcompile'	=> true,
			'tpl_allow_php'		=> false,
		);
	}

	protected function tearDown()
	{
		if (is_object($this->template))
		{
			foreach (glob($this->template->cachepath . '*') as $file)
			{
				unlink($file);
			}
		}
	}

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

	private function run_template($file, array $vars, array $block_vars, array $destroy, $expected, $cache_file)
	{
		$this->template->set_filenames(array('test' => $file));
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

		try
		{
			$this->assertEquals($expected, $this->display('test'), "Testing $file");
			$this->assertFileExists($cache_file);
		}
		catch (ErrorException $e)
		{
			if (file_exists($cache_file))
			{
				copy($cache_file, str_replace('ctpl_', 'tests_ctpl_', $cache_file));
			}

			throw $e;
		}

		// For debugging
		if (self::PRESERVE_CACHE)
		{
			copy($cache_file, str_replace('ctpl_', 'tests_ctpl_', $cache_file));
		}
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($file, array $vars, array $block_vars, array $destroy, $expected)
	{
		global $phpEx;
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.' . $phpEx;

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

		$error_level = error_reporting();
		error_reporting($error_level & ~E_NOTICE);

		$this->assertEquals($expected, self::trim_template_result($this->template->assign_display('test')), "Testing assign_display($file)");

		$this->template->assign_display('test', 'VARIABLE', false);

		error_reporting($error_level);

		$this->assertEquals($expected, $this->display('container'), "Testing assign_display($file)");
	}

	public function test_php()
	{
		global $phpEx;

		$GLOBALS['config']['tpl_allow_php'] = true;

		$cache_file = $this->template->cachepath . 'php.html.' . $phpEx;

		$this->assertFileNotExists($cache_file);

		$this->run_template('php.html', array(), array(), array(), 'test', $cache_file);

		$GLOBALS['config']['tpl_allow_php'] = false;
	}

	public function test_includephp()
	{
		$this->markTestIncomplete('Include PHP test file paths are broken');

		$GLOBALS['config']['tpl_allow_php'] = true;

		$cache_file = $this->template->cachepath . 'includephp.html.' . PHP_EXT;

		$cwd = getcwd();
		chdir(dirname(__FILE__) . '/templates');

		$this->run_template('includephp.html', array(), array(), array(), 'testing included php', $cache_file);

		$this->template->set_filenames(array('test' => 'includephp.html'));
		$this->assertEquals('testing included php', $this->display('test'), "Testing $file");

		chdir($cwd);

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
outer - 0/4 - before
outer - 1/4
middle - 0/2
middle - 1/2
outer - 2/4
middle - 0/3
middle - 1/3
middle - 2/3
outer - 3/4
middle - 0/2
middle - 1/2
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
outer - 0/4
middle - 0/2
middle - 1/2
outer - 1/4
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/4
middle - 0/2
middle - 1/2
outer - 3/4 - after
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
outer - 0/4
middle - 0/2
middle - 1/2
outer - 1/4 - pos #1
outer - 2/4
middle - 0/3
middle - 1/3
middle - 2/3
outer - 3/4
middle - 0/2
middle - 1/2
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
outer - 0/3 - pos #1
middle - 0/2
middle - 1/2
outer - 1/3
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/3
middle - 0/2
middle - 1/2
EOT
,
				'Test inserting at 1 on top level block',
			),
			array(
				'outer[0].middle',
				array('VARIABLE' => 'before'),
				false,
				'insert',
				<<<EOT
outer - 0/3
middle - 0/3 - before
middle - 1/3
middle - 2/3
outer - 1/3
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/3
middle - 0/2
middle - 1/2
EOT
,
				'Test inserting before on nested block',
			),
			array(
				'outer[0].middle',
				array('VARIABLE' => 'after'),
				true,
				'insert',
				<<<EOT
outer - 0/3
middle - 0/3
middle - 1/3
middle - 2/3 - after
outer - 1/3
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/3
middle - 0/2
middle - 1/2
EOT
,
				'Test inserting after on nested block',
			),
			array(
				'outer[0].middle',
				array('VARIABLE' => 'pos #1'),
				1,
				'insert',
				<<<EOT
outer - 0/3
middle - 0/3
middle - 1/3 - pos #1
middle - 2/3
outer - 1/3
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/3
middle - 0/2
middle - 1/2
EOT
,
				'Test inserting at pos 1 on nested block',
			),
			array(
				'outer[1].middle',
				array('VARIABLE' => 'before'),
				false,
				'insert',
				<<<EOT
outer - 0/3
middle - 0/2
middle - 1/2
outer - 1/3
middle - 0/4 - before
middle - 1/4
middle - 2/4
middle - 3/4
outer - 2/3
middle - 0/2
middle - 1/2
EOT
,
				'Test inserting before on nested block (pos 1)',
			),
			array(
				'outer[].middle',
				array('VARIABLE' => 'before'),
				false,
				'insert',
				<<<EOT
outer - 0/3
middle - 0/2
middle - 1/2
outer - 1/3
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/3
middle - 0/3 - before
middle - 1/3
middle - 2/3
EOT
,
				'Test inserting before on nested block (end)',
			),
			array(
				'outer.middle',
				array('VARIABLE' => 'before'),
				false,
				'insert',
				<<<EOT
outer - 0/3
middle - 0/2
middle - 1/2
outer - 1/3
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/3
middle - 0/3 - before
middle - 1/3
middle - 2/3
EOT
,
				'Test inserting before on nested block (end)',
			),
		);
	}

/*
				<<<EOT
outer - 0/3
middle - 0/2
middle - 1/2
outer - 1/3
middle - 0/3
middle - 1/3
middle - 2/3
outer - 2/3
middle - 0/2
middle - 1/2
EOT
,
*/

	/**
	* @dataProvider alter_block_array_data
	*/
	public function test_alter_block_array($alter_block, array $vararray, $key, $mode, $expect, $description)
	{
		$this->markTestIncomplete('Alter Block Test is broken');

		$this->template->set_filenames(array('test' => 'loop_nested.html'));

		// @todo Change this
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer', array());
		$this->template->assign_block_vars('outer.middle', array());
		$this->template->assign_block_vars('outer.middle', array());

		$this->assertEquals("outer - 0/3\nmiddle - 0/2\nmiddle - 1/2\nouter - 1/3\nmiddle - 0/3\nmiddle - 1/3\nmiddle - 2/3\nouter - 2/3\nmiddle - 0/2\nmiddle - 1/2", $this->display('test'), 'Ensuring template is built correctly before modification');

		$this->template->alter_block_array($alter_block, $vararray, $key, $mode);
		$this->assertEquals($expect, $this->display('test'), $description);
	}
}

