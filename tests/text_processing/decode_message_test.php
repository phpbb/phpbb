<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';

class phpbb_text_processing_decode_message_test extends phpbb_test_case
{
	/**
	* @dataProvider get_legacy_tests
	*/
	public function test_legacy($original, $expected, $bbcode_uid = '')
	{
		$actual = $original;
		decode_message($actual, $bbcode_uid);

		$this->assertSame($expected, $actual);
	}

	public function get_legacy_tests()
	{
		return array(
			array(
				"&amp;&lt;&gt;&quot;'",
				"&amp;&lt;&gt;&quot;'"
			),
			array(
				'<!-- s:) --><img src="{SMILIES_PATH}/icon_e_smile.gif" alt=":)" title="Smile" /><!-- s:) -->',
				':)'
			),
			/**
			* Fails as per PHPBB3-8420
			* @link http://tracker.phpbb.com/browse/PHPBB3-8420
			*
			array(
				'[url=http://example.com:2cpxwbdy]<!-- s:arrow: --><img src="{SMILIES_PATH}/icon_arrow.gif" alt=":arrow:" title="Arrow" /><!-- s:arrow: --> here[/url:2cpxwbdy]',
				'[url=http://example.com] :arrow: here[/url]',
				'2cpxwbdy'
			),
			*/
		);
	}

	/**
	* @dataProvider get_text_formatter_tests
	*/
	public function test_text_formatter($original, $expected)
	{
		$this->get_test_case_helpers()->set_s9e_services();

		$actual = $original;
		decode_message($actual);

		$this->assertSame($expected, $actual);
	}

	public function get_text_formatter_tests()
	{
		return array(
			array(
				"<t>&amp;&lt;&gt;\"'",
				"&amp;&lt;&gt;&quot;'"
			),
			array(
				'<r><E>:)</E></r>',
				':)'
			),
			array(
				"<t>a<br/>\nb</t>",
				"a\nb"
			),
			/**
			* @link http://tracker.phpbb.com/browse/PHPBB3-8420
			*/
			array(
				'<r><URL url="http://example.com"><s>[url=http://example.com]</s> <E>:arrow:</E> here<e>[/url]</e></URL></r>',
				'[url=http://example.com] :arrow: here[/url]'
			),
		);
	}
}
