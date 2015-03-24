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

class phpbb_functions_make_clickable_email_test extends phpbb_test_case
{
	protected function setUp()
	{
		parent::setUp();

		global $config, $user, $request;
		$user = new phpbb_mock_user();
		$request = new phpbb_mock_request();
	}

	/**
	* 'e' tag for email addresses html
	**/
	public function data_test_make_clickable_email_positive()
	{
		return array(
			array(
				'nobody@phpbb.com',
				'<!-- e --><a href="mailto:nobody@phpbb.com">nobody@phpbb.com</a><!-- e -->'
			),
			array(
				'Nobody@sub.phpbb.com',
				'<!-- e --><a href="mailto:Nobody@sub.phpbb.com">Nobody@sub.phpbb.com</a><!-- e -->'
			),
			array(
				'alice.bob@foo.phpbb.com',
				'<!-- e --><a href="mailto:alice.bob@foo.phpbb.com">alice.bob@foo.phpbb.com</a><!-- e -->'
			),
			array(
				'alice-foo@bar.phpbb.com',
				'<!-- e --><a href="mailto:alice-foo@bar.phpbb.com">alice-foo@bar.phpbb.com</a><!-- e -->'
			),
			array(
				'alice_foo@bar.phpbb.com',
				'<!-- e --><a href="mailto:alice_foo@bar.phpbb.com">alice_foo@bar.phpbb.com</a><!-- e -->'
			),
			array(
				'alice+tag@foo.phpbb.com',
				'<!-- e --><a href="mailto:alice+tag@foo.phpbb.com">alice+tag@foo.phpbb.com</a><!-- e -->'
			),
			array(
				'alice&amp;tag@foo.phpbb.com',
				'<!-- e --><a href="mailto:alice&amp;tag@foo.phpbb.com">alice&amp;tag@foo.phpbb.com</a><!-- e -->'
			),
			array(
				'alice@phpbb.australia',
				'<!-- e --><a href="mailto:alice@phpbb.australia">alice@phpbb.australia</a><!-- e -->'
			),

			// Test shortened text for email > 55 characters long
			// Email text should be turned into: first 39 chars + ' ... ' + last 10 chars
			array(
				'alice@phpbb.topZlevelZdomainZnamesZcanZbeZupZtoZsixtyZthreeZcharactersZlong',
				'<!-- e --><a href="mailto:alice@phpbb.topZlevelZdomainZnamesZcanZbeZupZtoZsixtyZthreeZcharactersZlong">alice@phpbb.topZlevelZdomainZnamesZcanZ ... ctersZlong</a><!-- e -->'
			),
			array(
				'l3tt3rsAndNumb3rs@domain.com',
				'<!-- e --><a href="mailto:l3tt3rsAndNumb3rs@domain.com">l3tt3rsAndNumb3rs@domain.com</a><!-- e -->'
			),
			array(
				'has-dash@domain.com',
				'<!-- e --><a href="mailto:has-dash@domain.com">has-dash@domain.com</a><!-- e -->'
			),
			array(
				'hasApostrophe.o\'leary@domain.org',
				'<!-- e --><a href="mailto:hasApostrophe.o\'leary@domain.org">hasApostrophe.o\'leary@domain.org</a><!-- e -->'
			),
			array(
				'uncommonTLD@domain.museum',
				'<!-- e --><a href="mailto:uncommonTLD@domain.museum">uncommonTLD@domain.museum</a><!-- e -->'
			),
			array(
				'uncommonTLD@domain.travel',
				'<!-- e --><a href="mailto:uncommonTLD@domain.travel">uncommonTLD@domain.travel</a><!-- e -->'
			),
			array(
				'uncommonTLD@domain.mobi',
				'<!-- e --><a href="mailto:uncommonTLD@domain.mobi">uncommonTLD@domain.mobi</a><!-- e -->'
			),
			array(
				'countryCodeTLD@domain.uk',
				'<!-- e --><a href="mailto:countryCodeTLD@domain.uk">countryCodeTLD@domain.uk</a><!-- e -->'
			),
			array(
				'countryCodeTLD@domain.rw',
				'<!-- e --><a href="mailto:countryCodeTLD@domain.rw">countryCodeTLD@domain.rw</a><!-- e -->'
			),
			array(
				'numbersInDomain@911.com',
				'<!-- e --><a href="mailto:numbersInDomain@911.com">numbersInDomain@911.com</a><!-- e -->'
			),
			array(
				'underscore_inLocal@domain.net',
				'<!-- e --><a href="mailto:underscore_inLocal@domain.net">underscore_inLocal@domain.net</a><!-- e -->'
			),
			array(
				'IPInsteadOfDomain@127.0.0.1',
				'<!-- e --><a href="mailto:IPInsteadOfDomain@127.0.0.1">IPInsteadOfDomain@127.0.0.1</a><!-- e -->'
			),
			array(
				'IPAndPort@127.0.0.1:25',
				'<!-- e --><a href="mailto:IPAndPort@127.0.0.1:25">IPAndPort@127.0.0.1:25</a><!-- e -->'
			),
			array(
				'subdomain@sub.domain.com',
				'<!-- e --><a href="mailto:subdomain@sub.domain.com">subdomain@sub.domain.com</a><!-- e -->'
			),
			array(
				'local@dash-inDomain.com',
				'<!-- e --><a href="mailto:local@dash-inDomain.com">local@dash-inDomain.com</a><!-- e -->'
			),
			array(
				'dot.inLocal@foo.com',
				'<!-- e --><a href="mailto:dot.inLocal@foo.com">dot.inLocal@foo.com</a><!-- e -->'
			),
			array(
				'a@singleLetterLocal.org',
				'<!-- e --><a href="mailto:a@singleLetterLocal.org">a@singleLetterLocal.org</a><!-- e -->'
			),
			array(
				'singleLetterDomain@x.org',
				'<!-- e --><a href="mailto:singleLetterDomain@x.org">singleLetterDomain@x.org</a><!-- e -->'
			),
			array(
				'&amp;*=?^+{}\'~@validCharsInLocal.net',
				'<!-- e --><a href="mailto:&amp;*=?^+{}\'~@validCharsInLocal.net">&amp;*=?^+{}\'~@validCharsInLocal.net</a><!-- e -->'
			),
			array(
				'foor@bar.newTLD',
				'<!-- e --><a href="mailto:foor@bar.newTLD">foor@bar.newTLD</a><!-- e -->'
			),
		);
	}

	public function data_test_make_clickable_email_negative()
	{
		return array(
			array('foo.example.com'),			// @ is missing
			array('.foo.example.com'),			// . as first character
			array('Foo.@example.com'),			// . is last in local part
			array('foo..123@example.com'),		// . doubled
			array('a@b@c@example.com'),			// @ doubled

			// Emails with invalid characters
			// (only 'valid' pieces having localparts prepended with one of the \n \t ( > chars should parsed if any)
			array('()[]\;:,<>@example.com'),	// invalid characters
			array('abc(def@example.com', 'abc(<!-- e --><a href="mailto:def@example.com">def@example.com</a><!-- e -->'),		// invalid character (
			array('abc)def@example.com'),		// invalid character )
			array('abc[def@example.com'),		// invalid character [
			array('abc]def@example.com'),		// invalid character ]
			array('abc\def@example.com'),		// invalid character \
			array('abc;def@example.com'),		// invalid character ;
			array('abc:def@example.com'),		// invalid character :
			array('abc,def@example.com'),		// invalid character ,
			array('abc<def@example.com'),		// invalid character <
			array('abc>def@example.com', 'abc><!-- e --><a href="mailto:def@example.com">def@example.com</a><!-- e -->'),		// invalid character >
			
			// http://fightingforalostcause.net/misc/2006/compare-email-regex.php
			array('missingDomain@.com'),
			array('@missingLocal.org'),
			array('missingatSign.net'),
			array('missingDot@com'),
			array('two@@signs.com'),
			// Trailing colon is ignored
			array('colonButNoPort@127.0.0.1:', '<!-- e --><a href="mailto:colonButNoPort@127.0.0.1">colonButNoPort@127.0.0.1</a><!-- e -->:'),

			array(''),
			// Trailing part after the 3rd dot is ignored
			array('someone-else@127.0.0.1.26', '<!-- e --><a href="mailto:someone-else@127.0.0.1">someone-else@127.0.0.1</a><!-- e -->.26'),

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
			// The domain zone name part after the 63rd char is ignored
			array(
				'alice@phpbb.topZlevelZdomainZnamesZcanZbeZupZtoZsixtyZthreeZcharactersZlongZ',
				'<!-- e --><a href="mailto:alice@phpbb.topZlevelZdomainZnamesZcanZbeZupZtoZsixtyZthreeZcharactersZlong">alice@phpbb.topZlevelZdomainZnamesZcanZ ... ctersZlong</a><!-- e -->Z'
			),
		);
	}

	/**
	 * @dataProvider data_test_make_clickable_email_positive
	 */
	public function test_email_matching_positive($email, $expected)
	{
		$this->assertSame($expected, make_clickable($email));
	}

	/**
	 * @dataProvider data_test_make_clickable_email_negative
	 */
	public function test_email_matching_negative($email, $expected = null)
	{
		$expected = ($expected) ?: $email;
		$this->assertSame($expected, make_clickable($email));
	}
}
