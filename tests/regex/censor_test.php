<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
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
		$regex = get_censor_preg_expression($pattern);

		$this->assertRegExp($regex, $subject);
	}
}