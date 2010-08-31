<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once '../phpBB/includes/request/type_cast_helper_interface.php';
require_once '../phpBB/includes/request/interface.php';
require_once '../phpBB/includes/request/deactivated_super_global.php';
require_once '../phpBB/includes/request/request.php';

class phpbb_request_test extends phpbb_test_case
{
	private $type_cast_helper;
	private $request;

	protected function setUp()
	{
		// populate super globals
		$_POST['test'] = 1;
		$_GET['test'] = 2;
		$_COOKIE['test'] = 3;
		$_REQUEST['test'] = 3;
		$_GET['unset'] = '';

		$this->type_cast_helper = $this->getMock('phpbb_request_type_cast_helper_interface');

		$this->request = new phpbb_request($this->type_cast_helper);
	}

	public function test_toggle_super_globals()
	{
		$this->assertTrue($this->request->super_globals_disabled(), 'Superglobals were not disabled');

		$this->request->enable_super_globals();

		$this->assertFalse($this->request->super_globals_disabled(), 'Superglobals were not enabled');

		$this->assertEquals(1, $_POST['test'], 'Checking $_POST after enable_super_globals');
		$this->assertEquals(2, $_GET['test'], 'Checking $_GET after enable_super_globals');
		$this->assertEquals(3, $_COOKIE['test'], 'Checking $_COOKIE after enable_super_globals');
		$this->assertEquals(3, $_REQUEST['test'], 'Checking $_REQUEST after enable_super_globals');

		$_POST['x'] = 2;
		$this->assertEquals($_POST, $GLOBALS['_POST'], 'Checking whether $_POST can still be accessed via $GLOBALS[\'_POST\']');
	}

	/**
	* Checks that directly accessing $_POST will trigger
	* an error.
	*/
	public function test_disable_post_super_global()
	{
		$this->setExpectedTriggerError(E_USER_ERROR);
		$_POST['test'] = 3;
	}

	public function test_is_set_post()
	{
		$this->assertTrue($this->request->is_set_post('test'));
		$this->assertFalse($this->request->is_set_post('unset'));
	}

	public function test_variable_names()
	{
		$expected = array('test', 'unset');
		$result = $this->request->variable_names();
		$this->assertEquals($expected, $result);
	}

	/**
	* Makes sure super globals work properly after these tests
	*/
	protected function tearDown()
	{
		$this->request->enable_super_globals();
	}
}
