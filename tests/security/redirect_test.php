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

require_once dirname(__FILE__) . '/base.php';

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_security_redirect_test extends phpbb_security_test_base
{
	protected $path_helper;

	public function provider()
	{
		// array(Input -> redirect(), expected triggered error (else false), expected returned result url (else false))
		return array(
			array('data://x', false, 'INSECURE_REDIRECT', false),
			array('bad://localhost/phpBB/index.php', false, 'INSECURE_REDIRECT', false),
			array('http://www.otherdomain.com/somescript.php', false, 'INSECURE_REDIRECT', false),
			array("http://localhost/phpBB/memberlist.php\n\rConnection: close", false, 'INSECURE_REDIRECT', false),
			array('javascript:test', false, false, 'http://localhost/phpBB/javascript:test'),
			array('http://localhost/phpBB/index.php;url=', false, 'INSECURE_REDIRECT', false),
			array('http://localhost/phpBB/app.php/foobar', false, false, 'http://localhost/phpBB/app.php/foobar'),
			array('./app.php/foobar', false, false, 'http://localhost/phpBB/app.php/foobar'),
			array('app.php/foobar', false, false, 'http://localhost/phpBB/app.php/foobar'),
			array('./../app.php/foobar', false, false, 'http://localhost/app.php/foobar'),
			array('./../app.php/foobar', true, false, 'http://localhost/app.php/foobar'),
			array('./../app.php/foo/bar', false, false, 'http://localhost/app.php/foo/bar'),
			array('./../app.php/foo/bar', true, false, 'http://localhost/app.php/foo/bar'),
			array('./../foo/bar', false, false, 'http://localhost/foo/bar'),
			array('./../foo/bar', true, false, 'http://localhost/foo/bar'),
			array('app.php/', false, false, 'http://localhost/phpBB/app.php/'),
			array('app.php/a', false, false, 'http://localhost/phpBB/app.php/a'),
			array('app.php/a/b', false, false, 'http://localhost/phpBB/app.php/a/b'),
			array('./app.php/', false, false, 'http://localhost/phpBB/app.php/'),
			array('foobar', false, false, 'http://localhost/phpBB/foobar'),
			array('./foobar', false, false, 'http://localhost/phpBB/foobar'),
			array('foo/bar', false, false, 'http://localhost/phpBB/foo/bar'),
			array('./foo/bar', false, false, 'http://localhost/phpBB/foo/bar'),
			array('./../index.php', false, false, 'http://localhost/index.php'),
			array('./../index.php', true, false, 'http://localhost/index.php'),
			array('../index.php', false, false, 'http://localhost/index.php'),
			array('../index.php', true, false, 'http://localhost/index.php'),
			array('./index.php', false, false, 'http://localhost/phpBB/index.php'),
			array('https://foobar.com\@http://localhost/phpBB', false, 'INSECURE_REDIRECT', false),
			array('https://foobar.com\@localhost/troll/http://localhost/', false, 'INSECURE_REDIRECT', false),
			array('http://localhost.foobar.com\@localhost/troll/http://localhost/', false, 'INSECURE_REDIRECT', false),
			array('http://localhost/phpBB', false, false, 'http://localhost/phpBB'),
			array('http://localhost/phpBB/', false, false, 'http://localhost/phpBB/'),
		);
	}

	protected function get_path_helper()
	{
		if (!($this->path_helper instanceof \phpbb\path_helper))
		{
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
		return $this->path_helper;
	}

	protected function setUp()
	{
		global $phpbb_dispatcher;

		parent::setUp();

		$GLOBALS['config'] = array(
			'force_server_vars'	=> '0',
		);

		$this->path_helper = $this->get_path_helper();

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
	}

	/**
	* @dataProvider provider
	*/
	public function test_redirect($test, $disable_cd_check, $expected_error, $expected_result)
	{
		global $user, $phpbb_root_path, $phpbb_path_helper;

		$phpbb_path_helper = $this->path_helper;

		$temp_phpbb_root_path = $phpbb_root_path;
		$temp_page_dir = $user->page['page_dir'];
		// We need to hack phpbb_root_path and the user's page_dir here
		// so it matches the actual fileinfo of the testing script.
		// Otherwise the paths are returned incorrectly.
		$phpbb_root_path = '';
		$user->page['page_dir'] = '';

		if ($expected_error !== false)
		{
			$this->setExpectedTriggerError(E_USER_ERROR, $user->lang[$expected_error]);
		}

		$result = redirect($test, true, $disable_cd_check);

		// only verify result if we did not expect an error
		if ($expected_error === false)
		{
			$this->assertEquals($expected_result, $result);
		}
		$phpbb_root_path = $temp_phpbb_root_path;
		$user->page['page_dir'] = $temp_page_dir;
	}
}
