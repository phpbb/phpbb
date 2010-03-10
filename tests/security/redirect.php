<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';

require_once '../phpBB/includes/functions.php';
require_once '../phpBB/includes/session.php';

class phpbb_security_redirect_test extends phpbb_test_case
{
	public static function provider()
	{
		// array(Input -> redirect(), expected triggered error (else false), expected returned result url (else false))
		return array(
			array('data://x', false, 'http://localhost/phpBB'),
			array('bad://localhost/phpBB/index.php', 'Tried to redirect to potentially insecure url.', false),
			array('http://www.otherdomain.com/somescript.php', false, 'http://localhost/phpBB'),
			array("http://localhost/phpBB/memberlist.php\n\rConnection: close", 'Tried to redirect to potentially insecure url.', false),
			array('javascript:test', false, 'http://localhost/phpBB/../tests/javascript:test'),
			array('http://localhost/phpBB/index.php;url=', 'Tried to redirect to potentially insecure url.', false),
		);
	}

	protected function setUp()
	{
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

