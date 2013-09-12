<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_filesystem_web_root_path_test extends phpbb_test_case
{
	protected $filesystem;
	protected $phpbb_root_path = '';

	public function setUp()
	{
		parent::setUp();

		$this->set_phpbb_root_path();

		$this->filesystem = new phpbb_filesystem($this->phpbb_root_path);
	}

	/**
	* Set the phpbb_root_path
	*
	* This is necessary because dataProvider functions are called
	*	before setUp or setUpBeforeClass; so we must set the path
	*	any time we wish to use it in one of these functions (and
	*	also in general for everything else)
	*/
	public function set_phpbb_root_path()
	{
		$this->phpbb_root_path = dirname(__FILE__) . './../../phpBB/';
	}

	public function test_get_web_root_path()
	{
		// Symfony Request = null, so always should return phpbb_root_path
		$this->assertEquals($this->phpbb_root_path, $this->filesystem->get_web_root_path());
	}

	public function update_web_root_path_data()
	{
		$this->set_phpbb_root_path();

		return array(
			array(
				$this->phpbb_root_path . 'test.php',
			),
			array(
				'test.php',
				$this->phpbb_root_path . 'test.php',
			),
			array(
				$this->phpbb_root_path . $this->phpbb_root_path . 'test.php',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				$this->phpbb_root_path . 'test.php',
				'/',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				$this->phpbb_root_path . '../test.php',
				'//',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				$this->phpbb_root_path . '../test.php',
				'//',
				'foo/bar.php',
				'bar.php',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				$this->phpbb_root_path . '../../test.php',
				'/foo/template',
				'/phpbb3-fork/phpBB/app.php/foo/template',
				'/phpbb3-fork/phpBB/app.php',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				$this->phpbb_root_path . '../test.php',
				'/foo/template',
				'/phpbb3-fork/phpBB/foo/template',
				'/phpbb3-fork/phpBB/app.php',
			),
		);
	}

	/**
	* @dataProvider update_web_root_path_data
	*/
	public function test_update_web_root_path($input, $expected = null, $getPathInfo = null, $getRequestUri = null, $getScriptName = null)
	{
		$expected = ($expected === null) ? $input : $expected;

		$symfony_request = null;
		if ($getPathInfo !== null)
		{
			$symfony_request = $this->getMock("Symfony\Component\HttpFoundation\Request");
			$symfony_request->expects($this->any())
				->method('getPathInfo')
				->will($this->returnValue($getPathInfo));
			$symfony_request->expects($this->any())
				->method('getRequestUri')
				->will($this->returnValue($getRequestUri));
			$symfony_request->expects($this->any())
				->method('getScriptName')
				->will($this->returnValue($getScriptName));
		}

		$this->assertEquals($expected, $this->filesystem->update_web_root_path($input, $symfony_request));
	}
}
