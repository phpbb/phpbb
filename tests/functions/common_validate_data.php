<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_functions_common_validate_data extends phpbb_test_case
{
	/**
	* Test provided input data with supplied checks and compare to expected
	* results
	*
	* @param array $input Input data with specific array keys that need to
	*		be matched by the ones in the other 2 params
	* @param array $validate_check Array containing validate_data check
	*		settings, i.e. array('foobar' => array('string'))
	* @param array $expected Array containing the expected results. Either
	*		an array containing the error message or the an empty
	*		array if input is correct
	*/
	public function validate_data_check($input, $validate_check, $expected)
	{
		foreach ($input as $key => $data)
		{
			$this->assertEquals($expected[$key], validate_data(array($data), array($validate_check[$key])));
		}
	}
}
