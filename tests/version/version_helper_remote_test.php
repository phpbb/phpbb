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

class version_helper_remote_test extends \phpbb_test_case
{
	protected $file_downloader;
	protected $cache;
	protected $version_helper;

	public function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		$config = new \phpbb\config\config(array(
			'version'	=> '3.1.0',
		));
		$container = new \phpbb_mock_container_builder();
		$db = new \phpbb\db\driver\factory($container);
		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
			->setMethods(array('get'))
			->setConstructorArgs(array(new \phpbb\cache\driver\dummy(), $config, $db, '../../', 'php'))
			->getMock();

		$this->cache->expects($this->any())
			->method('get')
			->with($this->anything())
			->will($this->returnValue(false));
		$this->file_downloader = new phpbb_mock_file_downloader();

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
		$this->file_downloader->set($input);

		if (!$valid_data)
		{
			try {
				$return = $this->version_helper->get_versions();
			} catch (\phpbb\exception\runtime_exception $e) {
				$this->assertEquals((string)$e->getMessage(), $expected_exception);
			}
		}
		else
		{
			$return = $this->version_helper->get_versions();
		}

		$this->assertEquals($expected_return, $return);
	}
}
