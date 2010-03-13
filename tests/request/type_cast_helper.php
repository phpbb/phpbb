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
require_once '../phpBB/includes/request/type_cast_helper.php';

class phpbb_type_cast_helper_test extends phpbb_test_case
{
	private $type_cast_helper;

	protected function setUp()
	{
		$this->type_cast_helper = new phpbb_type_cast_helper();
	}

	public function test_addslashes_recursively()
	{
		$data = array('some"string' => array('that"' => 'really"', 'needs"' => '"escaping'));
		$expected = array('some\\"string' => array('that\\"' => 'really\\"', 'needs\\"' => '\\"escaping'));

		$this->type_cast_helper->addslashes_recursively($data);

		$this->assertEquals($expected, $data);
	}
}
