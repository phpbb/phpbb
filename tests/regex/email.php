<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once '../phpBB/includes/functions.php';

class phpbb_regex_email_test extends phpbb_test_case
{
	protected $regex;

	public function setUp()
	{
		$this->regex = '#^' . get_preg_expression('email') . '$#i';
	}

	public function positive_match_data()
	{
		return array(
			array('nobody@phpbb.com'),
			array('Nobody@sub.phpbb.com'),
			array('alice.bob@foo.phpbb.com'),
			array('alice-foo@bar.phpbb.com'),
			array('alice_foo@bar.phpbb.com'),
			array('alice+tag@foo.phpbb.com'),
			array('alice&amp;tag@foo.phpbb.com'),

			//array('"John Doe"@example.com'),
			//array('Alice@[192.168.2.1]'),		// IPv4
			//array('Bob@[2001:0db8:85a3:08d3:1319:8a2e:0370:7344]'), // IPv6
		);
	}

	public function negative_match_data()
	{
		return array(
			array('foo.example.com'),			// @ is missing
			array('.foo.example.com'),			// . as first character
			array('Foo.@example.com'),			// . is last in local part
			array('foo..123@example.com'),		// . doubled
			array('a@b@c@example.com'),			// @ doubled

			array('()[]\;:,<>@example.com'),	// invalid characters
			array('abc(def@example.com'),		// invalid character (
			array('abc)def@example.com'),		// invalid character )
			array('abc[def@example.com'),		// invalid character [
			array('abc]def@example.com'),		// invalid character ]
			array('abc\def@example.com'),		// invalid character \
			array('abc;def@example.com'),		// invalid character ;
			array('abc:def@example.com'),		// invalid character :
			array('abc,def@example.com'),		// invalid character ,
			array('abc<def@example.com'),		// invalid character <
			array('abc>def@example.com'),		// invalid character >
		);
	}

	/**
	* @dataProvider positive_match_data
	*/
	public function test_positive_match($email)
	{
		$this->assertEquals(1, preg_match($this->regex, $email));
	}

	/**
	* @dataProvider negative_match_data
	*/
	public function test_negative_match($email)
	{
		$this->assertEquals(0, preg_match($this->regex, $email));
	}
}

