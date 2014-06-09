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
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';

class phpbb_text_processing_censor_text_test extends phpbb_test_case
{
	public function censor_text_data()
	{
		global $cache, $user;
		$cache = new phpbb_mock_cache;
		$user = new phpbb_mock_user;

		$user->optionset('viewcensors', false);

		return array(
			array('', ''),

			array('badword1', 'replacement1'),
			array(' badword1', ' replacement1'),
			array('badword1 ', 'replacement1 '),
			array(' badword1 ', ' replacement1 '),
			array('abadword1', 'replacement1'),
			array('badword1w', 'replacement1'),
			array('abadword1w', 'replacement1'),
			array('anotherbadword1test', 'replacement1'),
			array('this badword1', 'this replacement1'),
			array('this badword1 word', 'this replacement1 word'),

			array('badword2', 'replacement2'),
			array('bbadword2', 'replacement2'),
			array('bbbadword2', 'replacement2'),
			array('badword2d', 'badword2d'),
			array('bbadword2d', 'bbadword2d'),
			array('test badword2', 'test replacement2'),
			array('test badword2 word', 'test replacement2 word'),

			array('badword3', 'replacement3'),
			array('bbadword3', 'bbadword3'),
			array('badword3d', 'replacement3'),
			array('badword3ddd', 'replacement3'),
			array('bbadword3d', 'bbadword3d'),
			array(' badword3 ', ' replacement3 '),
			array(' badword3', ' replacement3'),

			array('badword4', 'replacement4'),
			array('this badword4 word', 'this replacement4 word'),
			array('abadword4', 'abadword4'),
			array('badword4d', 'badword4d'),
			array('abadword4d', 'abadword4d'),

			array('badword1 badword2 badword3 badword4', 'replacement1 replacement2 replacement3 replacement4'),
			array('badword1 badword2 badword3 badword4d', 'replacement1 replacement2 replacement3 badword4d'),
			array('abadword1 badword2 badword3 badword4', 'replacement1 replacement2 replacement3 replacement4'),

			array("new\nline\ntest", "new\nline\ntest"),
			array("tab\ttest\t", "tab\ttest\t"),
			array('öäü', 'öäü'),
			array('badw' . chr(1) . 'ord1', 'badw' . chr(1) . 'ord1'),
			array('badw' . chr(2) . 'ord1', 'badw' . chr(2) . 'ord1'),
			array('badw' . chr(3) . 'ord1', 'badw' . chr(3) . 'ord1'),
			array('badw' . chr(4) . 'ord1', 'badw' . chr(4) . 'ord1'),
			array('badw' . chr(5) . 'ord1', 'badw' . chr(5) . 'ord1'),
			array('badw' . chr(6) . 'ord1', 'badw' . chr(6) . 'ord1'),
		);
	}

	/**
	* @dataProvider censor_text_data
	*/
	public function test_censor_text($input, $expected)
	{
		$label = 'Testing word censor: ' . $input;
		$this->assertEquals($expected, censor_text($input), $label);
	}
}
