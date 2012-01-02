<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_regex_censor_test extends phpbb_test_case
{
	public function censor_test_data()
	{
		return array(
			array('bad*word', 'bad word'),
			array('bad***word', 'bad word'),
			array('bad**word', 'bad word'),
			array('*bad*word*', 'bad word'),
			array('b*d', 'bad'),
			array('*bad*', 'bad'),
			array('*b*d*', 'bad'),
			array('*b*d*', 'b d'),
			array('b*d*word', 'bad word'),
			array('**b**d**word**', 'bad word'),
			array('**b**d**word**', 'the bad word catched'),
		);
	}

	/**
	* @dataProvider censor_test_data
	*/
	public function test_censor_unicode($pattern, $subject)
	{
		$regex = get_censor_preg_expression($pattern, true);

		$this->assertRegExp($regex, $subject);
	}

	/**
	* @dataProvider censor_test_data
	*/
	public function test_censor_no_unicode($pattern, $subject)
	{
		$regex = get_censor_preg_expression($pattern, false);

		$this->assertRegExp($regex, $subject);
	}
}