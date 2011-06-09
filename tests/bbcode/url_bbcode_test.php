<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/bbcode.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/message_parser.php';
require_once dirname(__FILE__) . '/../mock_user.php';

class phpbb_url_bbcode_test extends phpbb_test_case
{
	public function url_bbcode_test_data()
	{
		return array(
			array(
				'url only',
				'[url]http://www.phpbb.com/community/[/url]',
				'[url:]http&#58;//www&#46;phpbb&#46;com/community/[/url:]'
			),
			array(
				'url with title',
				'[url=http://www.phpbb.com/community/]One line URL text[/url]',
				'[url=http&#58;//www&#46;phpbb&#46;com/community/:]One line URL text[/url:]'
			),
			array(
				'url with multiline title',
				"[url=http://www.phpbb.com/community/]Multiline\x0AURL\x0Atext[/url]",
				"[url=http&#58;//www&#46;phpbb&#46;com/community/:]Multiline\x0AURL\x0Atext[/url:]"
			),
			array(
				'unclosed url with multiline',
				"test [url] test \x0A test [url=http://www.phpbb.com/]test[/url] test",
				"test [url] test \x0A test [url=http&#58;//www&#46;phpbb&#46;com/:]test[/url:] test"
			),
			array(
				'unclosed url with multiline and title',
				"test [url=http://www.phpbb.com/]test \x0A [url]http://phpbb.com[/url] test",
				"test [url=http&#58;//www&#46;phpbb&#46;com/:]test \x0A [url]http://phpbb.com[/url:] test"
			),
		);
	}

	/**
	* @dataProvider url_bbcode_test_data
	*/
	public function test_url($description, $message, $expected)
	{
		global $user;
		$user = new phpbb_mock_user;

		$bbcode = new bbcode_firstpass();
		$bbcode->message = $message;
		$bbcode->bbcode_init(false);
		$bbcode->parse_bbcode();
		$this->assertEquals($expected, $bbcode->message);
	}
}
