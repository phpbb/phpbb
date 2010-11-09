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
require_once '../phpBB/includes/bbcode/bbcode_parser_base.php';
require_once '../phpBB/includes/bbcode/bbcode_parser.php';

class phpbb_bbcode_parser_test extends PHPUnit_Framework_TestCase
{
	public function test_both_passes()
	{
		$parser = new phpbb_bbcode_parser();

		$result = $parser->first_pass('[i]Italic [u]underlined text[/u][/i]');
		$result = $parser->second_pass($result);

		$expected = '<span style="font-style: italic">Italic <span style="text-decoration: underline">underlined text</span></span>';

		$this->assertEquals($expected, $result, 'Simple nested BBCode first+second pass');
	}
}
