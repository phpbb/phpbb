<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
define('PHP_EXT', 'php');
define('PHPBB_ROOT_PATH', '../phpBB/');

require_once 'test_framework/framework.php';

require_once '../phpBB/includes/constants.php';
require_once '../phpBB/includes/functions.php';
require_once '../phpBB/includes/template.php';

class phpbb_template_template_test extends phpbb_test_case
{
	private $template;
	private function display($handle)
	{
		ob_start();
		$this->assertTrue($this->template->display($handle, false));
		$contents = self::trim_template_result(ob_get_contents());
		ob_end_clean();

		return $contents;
	}

	private static function trim_template_result($result)
	{
		return str_replace("\n\n", "\n", implode("\n", array_map('trim', explode("\n", trim($result)))));
	}

	private function setup_engine()
	{
		$this->template = new template;
		$this->template->set_custom_template(dirname(__FILE__) . '/templates/', 'tests');
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
			'load_tplcompile' => true
		);
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
				array('loop' => array(array(), array())),
				array(),
				"loop\nloop\nloop\nloop",
			),
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'))),
				array(),
				"first\n0\n0\n1\nx\nset\nlast",
			),
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
			),
			array(
				'loop_advanced.html',
				array(),
				array('loop' => array(array(), array(), array(), array(), array(), array(), array())),
				array(),
				"101234561\n101234561\n101234561\n1234561\n1\n101\n234\n10\n561\n561",
			),
			array(
				'define.html',
				array(),
				array(),
				array(),
				"xyz\nabc",
			),
			array(
				'expressions.html',
				array(),
				array(),
				array(),
				trim(str_repeat("pass\n", 40)),
			),
			array(
				'php.html',
				array(),
				array(),
				array(),
				'<!-- echo "test"; -->',
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
			),
			array(
				'loop_vars.html',
				array(),
				array('loop' => array(array('VARIABLE' => 'x'), array('VARIABLE' => 'y')), 'loop.inner' => array(array(), array())),
				array('loop.inner'),
				"first\n0\n0\n2\nx\nset\n1\n1\n2\ny\nset\nlast",
			),
			array(
				'loop_expressions.html',
				array(),
				array('loop' => array(array(), array(), array(), array(), array(), array(), array(), array(), array(), array(), array(), array())),
				array(),
				"on\non\non\non\noff\noff\noff\noff\non\non\non\non\n\noff\noff\noff\non\non\non\noff\noff\noff\non\non\non",
			),
		);
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

		$this->assertEquals($expected, $this->display('test'), "Testing $file");
		$this->assertFileExists($cache_file);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($file, array $vars, array $block_vars, array $destroy, $expected)
	{
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.' . PHP_EXT;

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
		global $config;

		$config['tpl_allow_php'] = 1;

		$cache_file = $this->template->cachepath . 'php.html.' . PHP_EXT;

		$this->assertFileNotExists($cache_file);

		$this->run_template('php.html', array(), array(), array(), 'test', $cache_file);

		unset($config['tpl_allow_php']);
	}

	public function test_includephp()
	{
		global $config;

		$config['tpl_allow_php'] = 1;

		$cwd = getcwd();
		chdir(dirname(__FILE__) . '/templates');

		//$this->run_template('includephp.html', array(), array(), array(), 'testing included php', $cache_file);

		$this->template->set_filenames(array('test' => 'includephp.html'));
		$this->assertEquals('testing included php', $this->display('test'), "Testing $file");

		chdir($cwd);

		unset($config['tpl_allow_php']);
	}
}
?>