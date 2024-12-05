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

use phpbb\filesystem\helper as filesystem_helper;

class phpbb_path_helper_test extends phpbb_test_case
{
	/** @var \phpbb\path_helper */
	protected $path_helper;
	protected $phpbb_root_path = '';

	protected function setUp(): void
	{
		parent::setUp();

		$this->set_phpbb_root_path();

		$this->path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$this->createMock('\phpbb\request\request'),
			$this->phpbb_root_path,
			'php',
			'adm/'
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
	public function set_phpbb_root_path()
	{
		$this->phpbb_root_path = filesystem_helper::clean_path(__DIR__ . '/../../phpBB/');
	}

	public function test_get_web_root_path()
	{
		$this->assertEquals($this->phpbb_root_path, $this->path_helper->get_web_root_path());

		// Second call will use class property
		$this->assertEquals($this->phpbb_root_path, $this->path_helper->get_web_root_path());
	}

	public function test_get_adm_relative_path()
	{
		$this->assertEquals( 'adm/', $this->path_helper->get_adm_relative_path());
	}

	public function test_get_php_ext()
	{
		$this->assertSame('php', $this->path_helper->get_php_ext());
	}

	public function basic_update_web_root_path_data()
	{
		$this->set_phpbb_root_path();

		return [
			[
				'http://www.test.com/test.php',
				'http://www.test.com/test.php',
				'/',
			],
			[
				$this->phpbb_root_path . 'test.php',
				$this->phpbb_root_path . 'test.php',
			],
			[
				'test.php',
				'test.php',
			],
			[
				$this->phpbb_root_path . $this->phpbb_root_path . 'test.php',
				filesystem_helper::clean_path($this->phpbb_root_path . $this->phpbb_root_path . 'test.php'),
			],
		];
	}

	/**
	* @dataProvider basic_update_web_root_path_data
	*/
	public function test_basic_update_web_root_path($input, $expected)
	{
		$this->assertEquals($expected, $this->path_helper->update_web_root_path($input));
	}

	public function test_update_web_root_path_app()
	{
		$path_helper = $this->getMockBuilder('\phpbb\path_helper')
			->setConstructorArgs([
				new \phpbb\symfony_request(
					new phpbb_mock_request()
				),
				$this->createMock('\phpbb\request\request'),
				$this->phpbb_root_path,
				'php',
				'adm/'
			])
			->setMethods(['get_web_root_path'])
			->getMock();
		$path_helper->method('get_web_root_path')
			->willReturn('/var/www/phpbb/app.php/');
		$this->assertEquals('/var/www/phpbb/app.php/foo', $path_helper->update_web_root_path($this->phpbb_root_path . 'app.php/foo'));
	}

	public function update_web_root_path_data()
	{
		$this->set_phpbb_root_path();

		return array(
			array(
				$this->phpbb_root_path . 'test.php',
				'/',
				'',
				'',
				'',
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

			// No correction if the path is already prepend by the web root path
			array(
				'./../' . $this->phpbb_root_path . 'test.php',
				'//',
				'foo/bar.php',
				'bar.php',
				'',
			),
			array(
				'./../../' . $this->phpbb_root_path . 'test.php',
				'/foo/template',
				'/phpbb3-fork/phpBB/app.php/foo/template',
				'/phpbb3-fork/phpBB/app.php',
				'',
			),
			array(
				'./../' . $this->phpbb_root_path . 'test.php',
				'/foo/template',
				'/phpbb3-fork/phpBB/foo/template',
				'/phpbb3-fork/phpBB/app.php',
				'',
			),
			array(
				'./../'.$this->phpbb_root_path . 'test.php',
				'/',
				'/phpbb3-fork/phpBB/app.php/',
				'/phpbb3-fork/phpBB/app.php',
				'',
			),
			array(
				'./../'.$this->phpbb_root_path . 'test.php',
				'',
				'/phpbb3-fork/phpBB/foo',
				'/phpbb3-fork/phpBB/app.php',
				'',
			),
		);
	}

	/**
	* @dataProvider update_web_root_path_data
	*/
	public function test_update_web_root_path($input, $getPathInfo, $getRequestUri, $getScriptName, $correction)
	{
		$symfony_request = $this->createMock('\phpbb\symfony_request');
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
			$this->createMock('\phpbb\request\request'),
			$this->phpbb_root_path,
			'php'
		);

		$this->assertEquals($correction . $input, $path_helper->update_web_root_path($input));
	}

	public function remove_web_root_path_data()
	{
		$filesystem = new \phpbb\filesystem\filesystem();
		$this->set_phpbb_root_path($filesystem);

		return [
			[
				'web/root/path/some_url',
				'web/root/path/some_url'
			],
			[
				'/var/www/phpbb/test.php',
				$this->phpbb_root_path . 'test.php'
			]
		];
	}

	/**
	 * @dataProvider remove_web_root_path_data
	 */
	public function test_remove_web_root_path($input, $expected)
	{
		$path_helper = $this->getMockBuilder('\phpbb\path_helper')
			->setConstructorArgs([
				new \phpbb\symfony_request(
					new phpbb_mock_request()
				),
				$this->createMock('\phpbb\request\request'),
				$this->phpbb_root_path,
				'php',
				'adm/'
			])
			->setMethods(['get_web_root_path'])
			->getMock();
		$path_helper->method('get_web_root_path')
			->willReturn('/var/www/phpbb/');

		$this->assertEquals($expected, $path_helper->remove_web_root_path($input));
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

	public function test_get_web_root_path_ajax()
	{
		$symfony_request = $this->getMockBuilder('\phpbb\symfony_request')
			->setConstructorArgs([new phpbb_mock_request()])
			->setMethods(['get', 'getSchemeAndHttpHost', 'getBasePath', 'getPathInfo'])
			->getMock();
		$symfony_request->method('getSchemeAndHttpHost')
			->willReturn('http://www.phpbb.com');
		$symfony_request->method('getBasePath')
			->willReturn('/community');
		$symfony_request->expects($this->any())
			->method('getPathInfo')
			->will($this->returnValue('foo/bar'));

		$request = $this->createMock('phpbb\request\request');
		$request->method('is_ajax')
			->willReturn(true);
		$request->method('escape')
			->willReturnArgument(0);
		$request->method('header')
			->with('Referer')
			->willReturn('http://www.phpbb.com/community/route1/route2/');

		$path_helper = new \phpbb\path_helper(
			$symfony_request,
			$request,
			$this->phpbb_root_path,
			'php',
			'adm/'
		);

		$this->assertEquals($this->phpbb_root_path . '../../', $path_helper->get_web_root_path());
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
		return [
			[
				'http://www.phpbb.com/community/route1/route2/',
				'http://www.phpbb.com/community',
				'../../',
			],
			[
				'http://www.phpbb.com/community/route1/route2/?f=9',
				'http://www.phpbb.com/community',
				'../../',
			],
			[
				'http://www.phpbb.com/community/route1/route2',
				'http://www.phpbb.com/community',
				'../',
			],
			[
				'http://www.phpbb.com/community/route1',
				'http://www.phpbb.com/community',
				'',
			],
			[
				'http://www.phpbb.com/community/',
				'http://www.phpbb.com/community',
				'',
			],
			[
				'http://www.phpbb.com/notcommunity/route1/route2/',
				'http://www.phpbb.com/community',
				'../../../community/',
			],
			[
				'http://www.phpbb.com/notcommunity/route1/route2/?f=9',
				'http://www.phpbb.com/community',
				'../../../community/',
			],
			[
				'http://www.phpbb.com/notcommunity/route1/route2',
				'http://www.phpbb.com/community',
				'../../community/',
			],
			[
				'http://www.phpbb.com/notcommunity/route1',
				'http://www.phpbb.com/community',
				'../community/',
			],
			[
				'http://www.phpbb.com/notcommunity/',
				'http://www.phpbb.com/community',
				'../community/',
			],
			[
				'http://www.phpbb.com/foobar',
				'http://www.phpbb.com',
				'',
			],
			[
				'http://www.foobar.com',
				'http://www.phpbb.com',
				'/www.phpbb.com/',
			],
			[
				'foobar',
				'http://www.phpbb.com/community',
				'',
			],
			[
				'https://www.phpbb.com',
				'https://www.phpbb.com',
				''
			]
		];
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

	public function is_router_used_data()
	{
		return [
			[
				'index.php',
				false,
			],
			[
				'app.php',
				true,
			],
		];
	}

	/**
	 * @dataProvider is_router_used_data
	 */
	public function test_is_router_used($script_name, $expected)
	{
		$symfony_request = $this->getMockBuilder('\phpbb\symfony_request')
			->setConstructorArgs([new phpbb_mock_request()])
			->setMethods(['getScriptName'])
			->getMock();
		$symfony_request->method('getScriptName')
			->willReturn($script_name);

		$path_helper = new \phpbb\path_helper(
			$symfony_request,
			$this->createMock('\phpbb\request\request'),
			$this->phpbb_root_path,
			'php',
			'adm/'
		);

		$this->assertSame($expected, $path_helper->is_router_used());
	}
}
