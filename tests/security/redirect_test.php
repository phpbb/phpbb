<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/base.php';

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_security_redirect_test extends phpbb_security_test_base
{
	public function provider()
	{
		// array(Input -> redirect(), expected triggered error (else false), expected returned result url (else false))
		return array(
			array('data://x', false, false, 'http://localhost/phpBB'),
			array('bad://localhost/phpBB/index.php', false, 'INSECURE_REDIRECT', false),
			array('http://www.otherdomain.com/somescript.php', false, false, 'http://localhost/phpBB'),
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
		);
	}

	protected function setUp()
	{
		parent::setUp();

		$GLOBALS['config'] = array(
			'force_server_vars'	=> '0',
		);
	}

	/**
	* @dataProvider provider
	*/
	public function test_redirect($test, $disable_cd_check, $expected_error, $expected_result)
	{
		global $user, $phpbb_root_path;

		$temp_phpbb_root_path = $phpbb_root_path;
		// We need to hack phpbb_root_path here, so it matches the actual fileinfo of the testing script.
		// Otherwise the paths are returned incorrectly.
		$phpbb_root_path = '';

		if ($expected_error !== false)
		{
			$this->setExpectedTriggerError(E_USER_ERROR, $expected_error);
		}

		$result = redirect($test, true, $disable_cd_check);

		// only verify result if we did not expect an error
		if ($expected_error === false)
		{
			$this->assertEquals($expected_result, $result);
		}
		$phpbb_root_path = $temp_phpbb_root_path;
	}
}
