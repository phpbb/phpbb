<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_quoteattr_test extends phpbb_test_case
{
	public function quoteattr_test_data()
	{
		return array(
			array('foo', null, '"foo"'),
			array('', null, '""'),
			array(' ', null, '" "'),
			array('<a>', null, '"&lt;a&gt;"'),
			array('&amp;', null, '"&amp;amp;"'),
			array('"hello"', null, "'\"hello\"'"),
			array("'hello'", null, "\"'hello'\""),
			array("\"'", null, "\"&quot;'\""),
			array("a\nb", null, '"a&#10;b"'),
			array("a\r\nb", null, '"a&#13;&#10;b"'),
			array("a\tb", null, '"a&#9;b"'),
			array('a b', null, '"a b"'),
			array('"a<b"', null, "'\"a&lt;b\"'"),
			array('foo', array('f' => 'z'), '"zoo"'),
			array('<a>', array('a' => '&amp;'), '"&lt;&amp;&gt;"'),
		);
	}

	/**
	* @dataProvider quoteattr_test_data
	*/
	public function test_quoteattr($input, $entities, $expected)
	{
		$output = phpbb_quoteattr($input, $entities);

		$this->assertEquals($expected, $output);
	}
}
