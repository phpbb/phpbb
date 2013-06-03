<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_functions_validate_data_helper extends PHPUnit_Framework_TestCase
{
	protected $test_case;

	public function __construct($test_case)
	{
		$this->test_case = $test_case;
	}

	/**
	* Test provided input data with supplied checks and compare to expected
	* results
	*
	* @param array $expected Array containing the expected results. Either
	*		an array containing the error message or the an empty
	*		array if input is correct
	* @param array $input Input data with specific array keys that need to
	*		be matched by the ones in the other 2 params
	* @param array $validate_check Array containing validate_data check
	*		settings, i.e. array('foobar' => array('string'))
	*/
	public function assert_validate_data($expected, $input, $validate_check)
	{
		foreach ($input as $key => $data)
		{
			$this->test_case->assertEquals($expected[$key], validate_data(array($data), array($validate_check[$key])));
		}
	}
}
