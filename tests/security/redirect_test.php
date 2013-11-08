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
			array('data://x', false, 'http://localhost/phpBB'),
			array('bad://localhost/phpBB/index.php', 'INSECURE_REDIRECT', false),
			array('http://www.otherdomain.com/somescript.php', false, 'http://localhost/phpBB'),
			array("http://localhost/phpBB/memberlist.php\n\rConnection: close", 'INSECURE_REDIRECT', false),
			array('javascript:test', false, 'http://localhost/phpBB/javascript:test'),
			array('http://localhost/phpBB/index.php;url=', 'INSECURE_REDIRECT', false),
			array('http://localhost/phpBB/app.php/foobar', false, 'http://localhost/phpBB/app.php/foobar'),
			array('./app.php/foobar', false, 'http://localhost/phpBB/app.php/foobar'),
			array('app.php/foobar', false, 'http://localhost/phpBB/app.php/foobar'),
			array('./../app.php/foobar', false, 'http://localhost/phpBB/app.php/foobar'),
			array('./../app.php/foo/bar', false, 'http://localhost/phpBB/app.php/foo/bar'),
			array('./../foo/bar', false, 'http://localhost/phpBB/foo/bar'),
			array('app.php/', false, 'http://localhost/phpBB/app.php/'),
			array('./app.php/', false, 'http://localhost/phpBB/app.php/'),
			array('foobar', false, 'http://localhost/phpBB/foobar'),
			array('./foobar', false, 'http://localhost/phpBB/foobar'),
			array('foo/bar', false, 'http://localhost/phpBB/foo/bar'),
			array('./foo/bar', false, 'http://localhost/phpBB/foo/bar'),
			array('./../index.php', false, 'http://localhost/phpBB/index.php'),
			array('../index.php', false, 'http://localhost/phpBB/index.php'),
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
	public function test_redirect($test, $expected_error, $expected_result)
	{
		global $user;

		if ($expected_error !== false)
		{
			$this->setExpectedTriggerError(E_USER_ERROR, $expected_error);
		}

		$result = redirect($test, true);

		// only verify result if we did not expect an error
		if ($expected_error === false)
		{
			$this->assertEquals($expected_result, $result);
		}
	}
}
