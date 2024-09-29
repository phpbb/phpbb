<?php

use GuzzleHttp\Exception\RequestException;

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

class version_helper_remote_test extends \phpbb_test_case
{
	protected $file_downloader;
	protected $cache;
	protected $version_helper;
	protected $user;

	// Guzzle mock data
	protected $guzzle_status = 200; // Default to 200 status
	protected $guzzle_data;
	protected $guzzle_mock;

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		$config = new \phpbb\config\config(array(
			'version'	=> '3.1.0',
		));
		$container = new \phpbb_mock_container_builder();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$db = new \phpbb\db\driver\factory($container);
		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
			->setMethods(array('get'))
			->setConstructorArgs(array(new \phpbb\cache\driver\dummy(), $config, $db, $phpbb_dispatcher, '../../', 'php'))
			->getMock();

		$this->cache->expects($this->any())
			->method('get')
			->withAnyParameters()
			->will($this->returnValue(false));

		$this->guzzle_mock = $this->getMockBuilder('\GuzzleHttp\Client')
			->addMethods(['set_data'])
			->onlyMethods(['request'])
			->getMock();
		$this->guzzle_mock->method('set_data')
			->will($this->returnCallback(function($data)
				{
					$this->guzzle_data = $data;
				}
			));
		$this->guzzle_mock->method('request')
			->will($this->returnCallback(function()
				{
					return new \GuzzleHttp\Psr7\Response($this->guzzle_status, [], $this->guzzle_data);
				}
			));

		$this->file_downloader = $this->getMockBuilder('\phpbb\file_downloader')
			->onlyMethods(['create_client'])
			->getMock();
		$this->file_downloader->method('create_client')
			->will($this->returnValue($this->guzzle_mock));

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);

		$this->version_helper = new \phpbb\version_helper(
			$this->cache,
			$config,
			$this->file_downloader
		);
		$this->user = new \phpbb\user(new \phpbb\language\language($lang_loader), '\phpbb\datetime');
		$this->user->add_lang('acp/common');
	}

	public function provider_get_versions()
	{
		return array(
			array('', false, '', 'VERSIONCHECK_FAIL'),
			array('foobar', false, '', 'VERSIONCHECK_FAIL'),
			array('{
    "stable": {
        "1.0": {
            "current": "1.0.1",
            "download": "https://www.phpbb.com/customise/db/download/104136",
            "announcement": "https://www.phpbb.com/customise/db/extension/boardrules/",
            "eol": null,
            "security": false
        }
    }
}', true, array (
				'stable' => array (
					'1.0' => array (
						'current' => '1.0.1',
						'download' => 'https://www.phpbb.com/customise/db/download/104136',
						'announcement' => 'https://www.phpbb.com/customise/db/extension/boardrules/',
						'eol' => NULL,
						'security' => false,
					),
				),
				'unstable' => array (
					'1.0' => array (
						'current' => '1.0.1',
						'download' => 'https://www.phpbb.com/customise/db/download/104136',
						'announcement' => 'https://www.phpbb.com/customise/db/extension/boardrules/',
						'eol' => NULL,
						'security' => false,
					),
				),
			)),
			array('{
    "foobar": {
        "1.0": {
            "current": "1.0.1",
            "download": "https://www.phpbb.com/customise/db/download/104136",
            "announcement": "https://www.phpbb.com/customise/db/extension/boardrules/",
            "eol": null,
            "security": false
        }
    }
}', false, '', 'VERSIONCHECK_FAIL'),
			array('{
    "stable": {
        "1.0": {
            "current": "1.0.1<script>alert(\'foo\');</script>",
            "download": "https://www.phpbb.com/customise/db/download/104136<script>alert(\'foo\');</script>",
            "announcement": "https://www.phpbb.com/customise/db/extension/boardrules/<script>alert(\'foo\');</script>",
            "eol": "<script>alert(\'foo\');</script>",
            "security": "<script>alert(\'foo\');</script>"
        }
    }
}', false, null, 'VERSIONCHECK_INVALID_VERSION'),
			array('{
    "unstable": {
        "1.0": {
            "current": "1.0.1<script>alert(\'foo\');</script>",
            "download": "https://www.phpbb.com/customise/db/download/104136<script>alert(\'foo\');</script>",
            "announcement": "https://www.phpbb.com/customise/db/extension/boardrules/<script>alert(\'foo\');</script>",
            "eol": "<script>alert(\'foo\');</script>",
            "security": "<script>alert(\'foo\');</script>"
        }
    }
}', false, null, 'VERSIONCHECK_INVALID_VERSION'),
			array('{
    "unstable": {
        "1.0<script>alert(\'foo\');</script>": {
            "current": "1.0.1",
            "download": "https://www.phpbb.com/customise/db/download/104136",
            "announcement": "https://www.phpbb.com/customise/db/extension/boardrules/",
            "eol": "",
            "security": ""
        }
    }
}', false, array('stable' => array(), 'unstable' => array()), 'VERSIONCHECK_INVALID_VERSION'),
			array('{
	"\"\n<script>alert(\'foo\');</script>\n": "test",
    "stable": {
        "1.0": {
            "current": "1.0.1",
            "download": "https://www.phpbb.com/customise/db/download/104136",
            "announcement": "https://www.phpbb.com/customise/db/extension/boardrules/",
            "eol": null,
            "security": false
        }
    }
}', true, array (
				'stable' => array (
					'1.0' => array (
						'current' => '1.0.1',
						'download' => 'https://www.phpbb.com/customise/db/download/104136',
						'announcement' => 'https://www.phpbb.com/customise/db/extension/boardrules/',
						'eol' => NULL,
						'security' => false,
					),
				),
				'unstable' => array (
					'1.0' => array (
						'current' => '1.0.1',
						'download' => 'https://www.phpbb.com/customise/db/download/104136',
						'announcement' => 'https://www.phpbb.com/customise/db/extension/boardrules/',
						'eol' => NULL,
						'security' => false,
					),
				),
			)),
			array('{
    "unstable": {
        "1.0": {
            "current": "1.0.1",
            "download": "https://www.phpbb.com/customise/db/download/104136",
            "announcement": "https://www.phpbb.com/customise/db/extension/boardrules/",
            "eol": null,
            "security": false,
            "foobar": "<script>alert(\'test\');<script>"
        }
    }
}', true, array('stable' => array(), 'unstable' => array('1.0' => array(
				'current' => '1.0.1',
				'download'	=> 'https://www.phpbb.com/customise/db/download/104136',
				'announcement'	=> 'https://www.phpbb.com/customise/db/extension/boardrules/',
				'security'	=> false,
			))), 'VERSIONCHECK_INVALID_ENTRY'),
			array('{
    "unstable": {
        "1.0": {
            "current<script>alert(\'foo\');</script>": "1.0.1",
            "download2": "https://www.phpbb.com/customise/db/download/104136",
            "bannouncement": "https://www.phpbb.com/customise/db/extension/boardrules/",
            "eol": null,
            "security": false,
            "foobar": "<script>alert(\'test\');<script>"
        }
    }
}', true, array('stable' => array(), 'unstable' => array('1.0' => array(
				'security'	=> false,
			))), 'VERSIONCHECK_INVALID_ENTRY'),
		);
	}

	/**
	 * @dataProvider provider_get_versions
	 */
	public function test_get_versions($input, $valid_data, $expected_return = '', $expected_exception = '')
	{
		$this->guzzle_mock->set_data($input);

		// version_helper->get_versions() doesn't return a value on VERSIONCHECK_FAIL but only throws exception
		// so the $return is undefined. Define it here
		$return = false;

		if (!$valid_data)
		{
			try {
				$return = $this->version_helper->get_versions();
			} catch (\phpbb\exception\runtime_exception $e) {
				$this->assertEquals($expected_exception, $e->getMessage());
			}
		}
		else
		{
			$return = $this->version_helper->get_versions();
		}

		$this->assertEquals($expected_return, $return);
	}

	public function test_version_phpbb_com()
	{
		$guzzle_mock = $this->getMockBuilder('\GuzzleHttp\Client')
			->onlyMethods(['request'])
			->getMock();

		$guzzle_mock->method('request')
			->will($this->returnCallback(function()
				{
					return new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(__DIR__ . '/fixture/30x.txt'));
				}
			));

		$file_downloader = $this->getMockBuilder(\phpbb\file_downloader::class)
			->onlyMethods(['create_client'])
			->getMock();

		$file_downloader->method('create_client')
			->willReturn($guzzle_mock);

		$hostname = 'version.phpbb.com';

		$file = $file_downloader->get($hostname, '/phpbb', '30x.txt');
		$errstr = $file_downloader->get_error_string();
		$errno = $file_downloader->get_error_number();

		$this->assertNotEquals(
			0,
			strlen($file),
			'Failed asserting that the response is not empty.'
		);

		$this->assertSame(
			'',
			$errstr,
			'Failed asserting that the error string is empty.'
		);

		$this->assertSame(
			0,
			$errno,
			'Failed asserting that the error number is 0 (i.e. no error occurred).'
		);

		$lines = explode("\n", $file);

		$this->assertGreaterThanOrEqual(
			2,
			count($lines),
			'Failed asserting that the version file has at least two lines.'
		);

		$this->assertStringStartsWith(
			'3.',
			$lines[0],
			"Failed asserting that the first line of the version file starts with '3.'"
		);

		$this->assertNotSame(
			false,
			filter_var($lines[1], FILTER_VALIDATE_URL),
			'Failed asserting that the second line of the version file is a valid URL.'
		);

		$this->assertStringContainsString('http', $lines[1]);
		$this->assertStringContainsString('phpbb.com', $lines[1], '', true);
	}

	public function test_file_downloader_file_not_found()
	{
		$this->guzzle_mock = $this->getMockBuilder('\GuzzleHttp\Client')
			->onlyMethods(['request'])
			->getMock();

		$this->guzzle_mock->method('request')
			->will($this->returnCallback(function()
				{
					return new \GuzzleHttp\Psr7\Response(404, [], '');
				}
			));

		$file_downloader = $this->getMockBuilder(\phpbb\file_downloader::class)
			->onlyMethods(['create_client'])
			->getMock();

		$file_downloader->method('create_client')
			->willReturn($this->guzzle_mock);

		$this->expectException(\phpbb\exception\runtime_exception::class);
		$this->expectExceptionMessage('FILE_NOT_FOUND');

		$file_downloader->get('foo.com', 'bar', 'foo.txt');
	}

	public function test_file_downloader_exception_not_found()
	{
		$this->guzzle_mock = $this->getMockBuilder('\GuzzleHttp\Client')
			->onlyMethods(['request'])
			->getMock();

		$this->guzzle_mock->method('request')
			->will($this->returnCallback(function($method, $uri)
				{
					$request = new \GuzzleHttp\Psr7\Request('GET', $uri);
					$response = new \GuzzleHttp\Psr7\Response(404, [], '');
					throw new RequestException('FILE_NOT_FOUND', $request, $response);
				}
			));

		$file_downloader = $this->getMockBuilder(\phpbb\file_downloader::class)
			->onlyMethods(['create_client'])
			->getMock();

		$file_downloader->method('create_client')
			->willReturn($this->guzzle_mock);

		$this->expectException(\phpbb\exception\runtime_exception::class);
		$this->expectExceptionMessage('FILE_NOT_FOUND');

		$file_downloader->get('foo.com', 'bar', 'foo.txt');
	}

	public function test_file_downloader_exception_moved()
	{
		$this->guzzle_mock = $this->getMockBuilder('\GuzzleHttp\Client')
			->onlyMethods(['request'])
			->getMock();

		$this->guzzle_mock->method('request')
			->will($this->returnCallback(function($method, $uri)
			{
				$request = new \GuzzleHttp\Psr7\Request('GET', $uri);
				$response = new \GuzzleHttp\Psr7\Response(302, [], '');
				throw new RequestException('FILE_MOVED', $request, $response);
			}
			));

		$file_downloader = $this->getMockBuilder(\phpbb\file_downloader::class)
			->onlyMethods(['create_client'])
			->getMock();

		$file_downloader->method('create_client')
			->willReturn($this->guzzle_mock);

		$this->assertFalse($file_downloader->get('foo.com', 'bar', 'foo.txt'));
		$this->assertEquals(302, $file_downloader->get_error_number());
		$this->assertEquals('FILE_MOVED', $file_downloader->get_error_string());
	}

	public function test_file_downloader_exception_timeout()
	{
		$this->guzzle_mock = $this->getMockBuilder('\GuzzleHttp\Client')
			->onlyMethods(['request'])
			->getMock();

		$this->guzzle_mock->method('request')
			->will($this->returnCallback(function($method, $uri)
				{
					$request = new \GuzzleHttp\Psr7\Request('GET', $uri);
					throw new RequestException('FILE_NOT_FOUND', $request);
				}
			));

		$file_downloader = $this->getMockBuilder(\phpbb\file_downloader::class)
			->onlyMethods(['create_client'])
			->getMock();

		$file_downloader->method('create_client')
			->willReturn($this->guzzle_mock);

		$this->expectException(\phpbb\exception\runtime_exception::class);
		$this->expectExceptionMessage('FSOCK_TIMEOUT');

		$file_downloader->get('foo.com', 'bar', 'foo.txt');
	}

	public function test_file_downloader_exception_other()
	{
		$this->guzzle_mock = $this->getMockBuilder('\GuzzleHttp\Client')
			->onlyMethods(['request'])
			->getMock();

		$this->guzzle_mock->method('request')
			->will($this->returnCallback(function($method, $uri)
				{
					throw new \RuntimeException('FSOCK_NOT_SUPPORTED');
				}
			));

		$file_downloader = $this->getMockBuilder(\phpbb\file_downloader::class)
			->onlyMethods(['create_client'])
			->getMock();

		$file_downloader->method('create_client')
			->willReturn($this->guzzle_mock);

		$this->expectException(\phpbb\exception\runtime_exception::class);
		$this->expectExceptionMessage('FSOCK_DISABLED');

		$file_downloader->get('foo.com', 'bar', 'foo.txt');
	}
}
