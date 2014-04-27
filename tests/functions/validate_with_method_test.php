<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/validate_data_helper.php';

class phpbb_functions_validate_with_method_test extends phpbb_test_case
{
	protected $helper;

	protected function setUp()
	{
		parent::setUp();

		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	public function test_validate_date()
	{
		$this->helper->assert_valid_data(array(
			'method_call' => array(
				array(),
				true,
				array(array(array($this, 'with_method'), false)),
			),
		));
	}

	public function validate_with_method($bool, $optional = false)
	{
		return ! $bool;
	}
}
