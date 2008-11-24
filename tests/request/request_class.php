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

require_once 'test_framework/framework.php';

require_once '../phpBB/includes/functions.php';

class phpbb_request_request_class_test extends phpbb_test_case
{
	protected function setUp()
	{
		$_POST['test'] = 1;
		$_GET['test'] = 2;
		$_COOKIE['test'] = 3;
		$_REQUEST['test'] = 3;

		// reread data from super globals
		request::reset();
	}

	public function test_toggle_super_globals()
	{
		// toggle super globals
		request::disable_super_globals();
		request::enable_super_globals();

		$this->assertEquals(1, $_POST['test'], 'Checking $_POST toggling via request::dis/enable_super_globals');
		$this->assertEquals(2, $_GET['test'], 'Checking $_GET toggling via request::dis/enable_super_globals');
		$this->assertEquals(3, $_COOKIE['test'], 'Checking $_COOKIE toggling via request::dis/enable_super_globals');
		$this->assertEquals(3, $_REQUEST['test'], 'Checking $_REQUEST toggling via request::dis/enable_super_globals');

		$_POST['x'] = 2;
		$this->assertEquals($_POST, $GLOBALS['_POST'], 'Checking whether $_POST can still be accessed via $GLOBALS[\'_POST\']');
	}

	/**
	* Checks that directly accessing $_POST will trigger
	* an error.
	*/
	public function test_disable_post_super_global()
	{
		request::disable_super_globals();

		$this->setExpectedTriggerError(E_USER_ERROR);
		$_POST['test'] = 3;
	}

	public function test_is_set_post()
	{
		$_GET['unset'] = '';
		request::reset();

		$this->assertTrue(request::is_set_post('test'));
		$this->assertFalse(request::is_set_post('unset'));
	}

	/**
	* Makes sure super globals work properly after these tests
	*/
	protected function tearDown()
	{
		request::enable_super_globals();
		request::reset();
	}
}