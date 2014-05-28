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
			array('alice@phpbb.australia'),
			array('alice@phpbb.topZlevelZdomainZnamesZcanZbeZupZtoZsixtyZthreeZcharactersZlong'),

			//array('"John Doe"@example.com'),
			//array('Alice@[192.168.2.1]'),		// IPv4
			//array('Bob@[2001:0db8:85a3:08d3:1319:8a2e:0370:7344]'), // IPv6
			
			// http://fightingforalostcause.net/misc/2006/compare-email-regex.php
			array('l3tt3rsAndNumb3rs@domain.com'),
			array('has-dash@domain.com'),
			array('hasApostrophe.o\'leary@domain.org'),
			array('uncommonTLD@domain.museum'),
			array('uncommonTLD@domain.travel'),
			array('uncommonTLD@domain.mobi'),
			array('countryCodeTLD@domain.uk'),
			array('countryCodeTLD@domain.rw'),
			array('numbersInDomain@911.com'),
			array('underscore_inLocal@domain.net'),
			array('IPInsteadOfDomain@127.0.0.1'),
			array('IPAndPort@127.0.0.1:25'),
			array('subdomain@sub.domain.com'),
			array('local@dash-inDomain.com'),
			array('dot.inLocal@foo.com'),
			array('a@singleLetterLocal.org'),
			array('singleLetterDomain@x.org'),
			array('&amp;*=?^+{}\'~@validCharsInLocal.net'),
			array('foor@bar.newTLD'),
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
			
			// http://fightingforalostcause.net/misc/2006/compare-email-regex.php
			array('missingDomain@.com'),
			array('@missingLocal.org'),
			array('missingatSign.net'),
			array('missingDot@com'),
			array('two@@signs.com'),
			array('colonButNoPort@127.0.0.1:'),
			array(''),
			array('someone-else@127.0.0.1.26'),
			array('.localStartsWithDot@domain.com'),
			array('localEndsWithDot.@domain.com'),
			array('two..consecutiveDots@domain.com'),
			array('domainStartsWithDash@-domain.com'),
			array('domainEndsWithDash@domain-.com'),
			array('numbersInTLD@domain.c0m'),
			array('missingTLD@domain.'),
			array('! "#$%(),/;<>[]`|@invalidCharsInLocal.org'),
			array('invalidCharsInDomain@! "#$%(),/;<>_[]`|.org'),
			array('local@SecondLevelDomainNamesAreInvalidIfTheyAreLongerThan64Charactersss.org'),
			array('alice@phpbb.topZlevelZdomainZnamesZcanZbeZupZtoZsixtyZthreeZcharactersZlongZ'),
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

