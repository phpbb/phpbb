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

class phpbb_path_helper_test extends phpbb_test_case
{
	/** @var \phpbb\path_helper */
	protected $path_helper;
	protected $phpbb_root_path = '';

	public function setUp()
	{
		parent::setUp();

		$filesystem = new \phpbb\filesystem\filesystem();
		$this->set_phpbb_root_path($filesystem);

		$this->path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			new \phpbb\filesystem\filesystem(),
			$this->getMock('\phpbb\request\request'),
			$this->phpbb_root_path,
			'php'
		);
	}

	/**
	* Set the phpbb_root_path
	*
	* This is necessary because dataProvider functions are called
	*	before setUp or setUpBeforeClass; so we must set the path
	*	any time we wish to use it in one of these functions (and
	*	also in general for everything else)
	*/
	public function set_phpbb_root_path($filesystem)
	{
		$this->phpbb_root_path = $filesystem->clean_path(dirname(__FILE__) . '/../../phpBB/');
	}

	public function test_get_web_root_path()
	{
		// Symfony Request = null, so always should return phpbb_root_path
		$this->assertEquals($this->phpbb_root_path, $this->path_helper->get_web_root_path());
	}

	public function basic_update_web_root_path_data()
	{
		$filesystem = new \phpbb\filesystem\filesystem();
		$this->set_phpbb_root_path($filesystem);

		return array(
			array(
				'http://www.test.com/test.php',
				'http://www.test.com/test.php',
				'/',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				$this->phpbb_root_path . 'test.php',
			),
			array(
				'test.php',
				'test.php',
			),
			array(
				$this->phpbb_root_path . $this->phpbb_root_path . 'test.php',
				$filesystem->clean_path($this->phpbb_root_path . $this->phpbb_root_path . 'test.php'),
			),
		);
	}

	/**
	* @dataProvider basic_update_web_root_path_data
	*/
	public function test_basic_update_web_root_path($input, $expected)
	{
		$this->assertEquals($expected, $this->path_helper->update_web_root_path($input));
	}

	public function update_web_root_path_data()
	{
		$this->set_phpbb_root_path(new \phpbb\filesystem\filesystem());

		return array(
			array(
				$this->phpbb_root_path . 'test.php',
				'/',
				null,
				null,
				'',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				'//',
				null,
				null,
				'./../',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				'//',
				'foo/bar.php',
				'bar.php',
				'./../',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				'/foo/template',
				'/phpbb3-fork/phpBB/app.php/foo/template',
				'/phpbb3-fork/phpBB/app.php',
				'./../../',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				'/foo/template',
				'/phpbb3-fork/phpBB/foo/template',
				'/phpbb3-fork/phpBB/app.php',
				'./../',
			),
			array(
				$this->phpbb_root_path . 'test.php',
				'/',
				'/phpbb3-fork/phpBB/app.php/',
				'/phpbb3-fork/phpBB/app.php',
				'./../',
			),
		);
	}

	/**
	* @dataProvider update_web_root_path_data
	*/
	public function test_update_web_root_path($input, $getPathInfo, $getRequestUri, $getScriptName, $correction)
	{
		$symfony_request = $this->getMock('\phpbb\symfony_request', array(), array(
			new phpbb_mock_request(),
		));
		$symfony_request->expects($this->any())
			->method('getPathInfo')
			->will($this->returnValue($getPathInfo));
		$symfony_request->expects($this->any())
			->method('getRequestUri')
			->will($this->returnValue($getRequestUri));
		$symfony_request->expects($this->any())
			->method('getScriptName')
			->will($this->returnValue($getScriptName));

		$path_helper = new \phpbb\path_helper(
			$symfony_request,
			new \phpbb\filesystem\filesystem(),
			$this->getMock('\phpbb\request\request'),
			$this->phpbb_root_path,
			'php'
		);

		$this->assertEquals($correction . $input, $path_helper->update_web_root_path($input, $symfony_request));
	}

	public function clean_url_data()
	{
		return array(
			array('', ''),
			array('://', '://'),
			array('http://', 'http://'),
			array('http://one/two/three', 'http://one/two/three'),
			array('http://../one/two', 'http://../one/two'),
			array('http://one/../two/three', 'http://two/three'),
			array('http://one/two/../three', 'http://one/three'),
			array('http://one/two/../../three', 'http://three'),
			array('http://one/two/../../../three', 'http://../three'),
		);
	}

	/**
	* @dataProvider clean_url_data
	*/
	public function test_clean_url($input, $expected)
	{
		$this->assertEquals($expected, $this->path_helper->clean_url($input));
	}

	public function glue_url_params_data()
	{
		return array(
			array(
				array(),
				'',
			),
			array(
				array('test' => 'xyz'),
				'test=xyz',
			),
			array(
				array('test' => 'xyz', 'var' => 'value'),
				'test=xyz&amp;var=value',
			),
			array(
				array('test' => null),
				'test',
			),
			array(
				array('test' => null, 'var' => null),
				'test&amp;var',
			),
			array(
				array('test' => 'xyz', 'var' => null, 'bar' => 'value'),
				'test=xyz&amp;var&amp;bar=value',
			),
		);
	}

	/**
	* @dataProvider glue_url_params_data
	*/
	public function test_glue_url_params($params, $expected)
	{
		$this->assertEquals($expected, $this->path_helper->glue_url_params($params));
	}

	public function get_url_parts_data()
	{
		return array(
			array(
				'viewtopic.php',
				true,
				array('base' => 'viewtopic.php', 'params' => array()),
			),
			array(
				'./viewtopic.php?t=5&amp;f=6',
				true,
				array('base' => './viewtopic.php', 'params' => array('t' => '5', 'f' => '6')),
			),
			array(
				'viewtopic.php?t=5&f=6',
				false,
				array('base' => 'viewtopic.php', 'params' => array('t' => '5', 'f' => '6')),
			),
			array(
				'https://phpbb.com/community/viewtopic.php?t=5&amp;f=6',
				true,
				array('base' => 'https://phpbb.com/community/viewtopic.php', 'params' => array('t' => '5', 'f' => '6')),
			),
			array(
				'test.php?topic=post=5&amp;f=3',
				true,
				array('base' => 'test.php', 'params' => array('topic' => 'post=5', 'f' => '3')),
			),
			array(
				'mcp.php?&amp;t=4&amp;f=3',
				true,
				array('base' => 'mcp.php', 'params' => array('t' => '4', 'f' => '3')),
			),
			array(
				'mcp.php?=4&amp;f=3',
				true,
				array('base' => 'mcp.php', 'params' => array('f' => '3')),
			),
			array(
				'index.php?ready',
				false,
				array('base' => 'index.php', 'params' => array('ready' => null)),
			),
			array(
				'index.php?i=1&amp;ready',
				true,
				array('base' => 'index.php', 'params' => array('i' => '1', 'ready' => null)),
			),
			array(
				'index.php?ready&i=1',
				false,
				array('base' => 'index.php', 'params' => array('ready' => null, 'i' => '1')),
			),
		);
	}

	/**
	* @dataProvider get_url_parts_data
	*/
	public function test_get_url_parts($url, $is_amp, $expected)
	{
		$this->assertEquals($expected, $this->path_helper->get_url_parts($url, $is_amp));
	}

	public function strip_url_params_data()
	{
		return array(
			array(
				'viewtopic.php',
				'sid',
				false,
				'viewtopic.php',
			),
			array(
				'./viewtopic.php?t=5&amp;f=6',
				'f',
				true,
				'./viewtopic.php?t=5',
			),
			array(
				'viewtopic.php?t=5&f=6&sid=19adc288814103cbb4625e74e77455aa',
				array('t'),
				false,
				'viewtopic.php?f=6&amp;sid=19adc288814103cbb4625e74e77455aa',
			),
			array(
				'https://phpbb.com/community/viewtopic.php?t=5&amp;f=6',
				array('t', 'f'),
				true,
				'https://phpbb.com/community/viewtopic.php',
			),
		);
	}

	/**
	* @dataProvider strip_url_params_data
	*/
	public function test_strip_url_params($url, $strip, $is_amp, $expected)
	{
		$this->assertEquals($expected, $this->path_helper->strip_url_params($url, $strip, $is_amp));
	}

	public function append_url_params_data()
	{
		return array(
			array(
				'viewtopic.php',
				array(),
				false,
				'viewtopic.php',
			),
			array(
				'./viewtopic.php?t=5&amp;f=6',
				array('t' => '7'),
				true,
				'./viewtopic.php?t=7&amp;f=6',
			),
			array(
				'viewtopic.php?t=5&f=6&sid=19adc288814103cbb4625e74e77455aa',
				array('p' => '5'),
				false,
				'viewtopic.php?t=5&amp;f=6&amp;p=5&amp;sid=19adc288814103cbb4625e74e77455aa',
			),
			array(
				'https://phpbb.com/community/viewtopic.php',
				array('t' => '7', 'f' => '8'),
				true,
				'https://phpbb.com/community/viewtopic.php?t=7&amp;f=8',
			),
		);
	}

	/**
	* @dataProvider append_url_params_data
	*/
	public function test_append_url_params($url, $params, $is_amp, $expected)
	{
		$this->assertEquals($expected, $this->path_helper->append_url_params($url, $params, $is_amp));
	}

	public function get_web_root_path_from_ajax_referer_data()
	{
		return array(
			array(
				'http://www.phpbb.com/community/route1/route2/',
				'http://www.phpbb.com/community',
				'../../',
			),
			array(
				'http://www.phpbb.com/community/route1/route2',
				'http://www.phpbb.com/community',
				'../',
			),
			array(
				'http://www.phpbb.com/community/route1',
				'http://www.phpbb.com/community',
				'',
			),
			array(
				'http://www.phpbb.com/community/',
				'http://www.phpbb.com/community',
				'',
			),
			array(
				'http://www.phpbb.com/notcommunity/route1/route2/',
				'http://www.phpbb.com/community',
				'../../../community/',
			),
			array(
				'http://www.phpbb.com/notcommunity/route1/route2',
				'http://www.phpbb.com/community',
				'../../community/',
			),
			array(
				'http://www.phpbb.com/notcommunity/route1',
				'http://www.phpbb.com/community',
				'../community/',
			),
			array(
				'http://www.phpbb.com/notcommunity/',
				'http://www.phpbb.com/community',
				'../community/',
			),
			array(
				'http://www.phpbb.com/foobar',
				'http://www.phpbb.com',
				'',
			),
			array(
				'http://www.foobar.com',
				'http://www.phpbb.com',
				'/www.phpbb.com/',
			),
			array(
				'foobar',
				'http://www.phpbb.com/community',
				'',
			)
		);
	}

	/**
	* @dataProvider get_web_root_path_from_ajax_referer_data
	*/
	public function test_get_web_root_path_from_ajax_referer($referer_url, $board_url, $expected)
	{
		$this->assertEquals($this->phpbb_root_path . $expected, $this->path_helper->get_web_root_path_from_ajax_referer($referer_url, $board_url));
	}

	public function data_get_valid_page()
	{
		return array(
			// array( current page , mod_rewrite setting , expected output )
			array('index', true, 'index'),
			array('index', false, 'index'),
			array('foo/index', true, 'foo/index'),
			array('foo/index', false, 'foo/index'),
			array('app.php/foo', true, 'foo'),
			array('app.php/foo', false, 'app.php/foo'),
			array('/../app.php/foo', true, '../foo'),
			array('/../app.php/foo', false, '../app.php/foo'),
			array('/../example/app.php/foo/bar', true, '../example/foo/bar'),
			array('/../example/app.php/foo/bar', false, '../example/app.php/foo/bar'),
		);
	}

	/**
	 * @dataProvider data_get_valid_page
	 */
	public function test_get_valid_page($page, $mod_rewrite, $expected)
	{
		$this->assertEquals($this->phpbb_root_path . $expected, $this->path_helper->get_valid_page($page, $mod_rewrite));
	}
}
