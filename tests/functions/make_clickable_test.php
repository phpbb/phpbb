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

class phpbb_functions_make_clickable_test extends phpbb_test_case
{
	/**
	* Tags:
	* 'm' - full URL like xxxx://aaaaa.bbb.cccc.
	* 'l' - local relative board URL like http://domain.tld/path/to/board/index.php
	* 'w' - URL without http/https protocol like www.xxxx.yyyy[/zzzz] aka 'lazy' URLs
	* 'e' - email@domain type address
	*
	* Classes:
	* "postlink-local" for 'l' URLs
	* "postlink" for the rest of URLs
	* empty for email addresses
	**/
	public function data_test_make_clickable_url_positive()
	{
		return array(
			array(
				'http://www.phpbb.com/community/',
				'<!-- m --><a class="postlink" href="http://www.phpbb.com/community/">http://www.phpbb.com/community/</a><!-- m -->'
			),
			array(
				'http://www.phpbb.com/path/file.ext#section',
				'<!-- m --><a class="postlink" href="http://www.phpbb.com/path/file.ext#section">http://www.phpbb.com/path/file.ext#section</a><!-- m -->'
			),
			array(
				'ftp://ftp.phpbb.com/',
				'<!-- m --><a class="postlink" href="ftp://ftp.phpbb.com/">ftp://ftp.phpbb.com/</a><!-- m -->'
			),
			array(
				'sip://bantu@phpbb.com',
				'<!-- m --><a class="postlink" href="sip://bantu@phpbb.com">sip://bantu@phpbb.com</a><!-- m -->'
			),
			array(
				'www.phpbb.com/community/',
				'<!-- w --><a class="postlink" href="http://www.phpbb.com/community/">www.phpbb.com/community/</a><!-- w -->'
			),
			array(
				'http://testhost/viewtopic.php?t=1',
				'<!-- l --><a class="postlink-local" href="http://testhost/viewtopic.php?t=1">viewtopic.php?t=1</a><!-- l -->'
			),
			array(
				'email@domain.com',
				'<!-- e --><a href="mailto:email@domain.com">email@domain.com</a><!-- e -->'
			),
			// Test appending punctuation mark to the URL
			array(
				'http://testhost/viewtopic.php?t=1!',
				'<!-- l --><a class="postlink-local" href="http://testhost/viewtopic.php?t=1">viewtopic.php?t=1</a><!-- l -->!'
			),
			array(
				'www.phpbb.com/community/?',
				'<!-- w --><a class="postlink" href="http://www.phpbb.com/community/">www.phpbb.com/community/</a><!-- w -->?'
			),
			// Test shortened text for URL > 55 characters long
			// URL text should be turned into: first 39 chars + ' ... ' + last 10 chars
			array(
				'http://www.phpbb.com/community/path/to/long/url/file.ext#section',
				'<!-- m --><a class="postlink" href="http://www.phpbb.com/community/path/to/long/url/file.ext#section">http://www.phpbb.com/community/path/to/ ... xt#section</a><!-- m -->'
			),
		);
	}

	public function data_test_make_clickable_url_idn()
	{
		return array(
			array(
				'http://www.täst.de/community/',
				'<!-- m --><a class="postlink" href="http://www.täst.de/community/">http://www.täst.de/community/</a><!-- m -->'
			),
			array(
				'http://www.täst.de/path/file.ext#section',
				'<!-- m --><a class="postlink" href="http://www.täst.de/path/file.ext#section">http://www.täst.de/path/file.ext#section</a><!-- m -->'
			),
			array(
				'ftp://ftp.täst.de/',
				'<!-- m --><a class="postlink" href="ftp://ftp.täst.de/">ftp://ftp.täst.de/</a><!-- m -->'
			),
			array(
				'sip://bantu@täst.de',
				'<!-- m --><a class="postlink" href="sip://bantu@täst.de">sip://bantu@täst.de</a><!-- m -->'
			),
			array(
				'www.täst.de/community/',
				'<!-- w --><a class="postlink" href="http://www.täst.de/community/">www.täst.de/community/</a><!-- w -->'
			),
			// Test appending punctuation mark to the URL
			array(
				'http://домен.рф/viewtopic.php?t=1!',
				'<!-- m --><a class="postlink" href="http://домен.рф/viewtopic.php?t=1">http://домен.рф/viewtopic.php?t=1</a><!-- m -->!'
			),
			array(
				'www.домен.рф/сообщество/?',
				'<!-- w --><a class="postlink" href="http://www.домен.рф/сообщество/">www.домен.рф/сообщество/</a><!-- w -->?'
			),
			// Test shortened text for URL > 55 characters long
			// URL text should be turned into: first 39 chars + ' ... ' + last 10 chars
			array(
				'http://www.домен.рф/сообщество/путь/по/длинной/ссылке/file.ext#section',
				'<!-- m --><a class="postlink" href="http://www.домен.рф/сообщество/путь/по/длинной/ссылке/file.ext#section">http://www.домен.рф/сообщество/путь/по/ ... xt#section</a><!-- m -->'
			),

			// IDN with invalid characters shouldn't be parsed correctly (only 'valid' part)
			array(
				'http://www.täst╫.de',
				'<!-- m --><a class="postlink" href="http://www.täst">http://www.täst</a><!-- m -->╫.de'
			),
			// IDN in emails is unsupported yet
			array('почта@домен.рф', 'почта@домен.рф'),
		);
	}

	public function data_test_make_clickable_local_url_idn()
	{
		return array(
			array(
				'http://www.домен.рф/viewtopic.php?t=1',
				'<!-- l --><a class="postlink-local" href="http://www.домен.рф/viewtopic.php?t=1">viewtopic.php?t=1</a><!-- l -->'
			),
			// Test appending punctuation mark to the URL
			array(
				'http://www.домен.рф/viewtopic.php?t=1!',
				'<!-- l --><a class="postlink-local" href="http://www.домен.рф/viewtopic.php?t=1">viewtopic.php?t=1</a><!-- l -->!'
			),
			array(
				'http://www.домен.рф/сообщество/?',
				'<!-- l --><a class="postlink-local" href="http://www.домен.рф/сообщество/">сообщество/</a><!-- l -->?'
			),
		);
	}

	protected function setUp()
	{
		parent::setUp();

		global $config, $user, $request;
		$user = new phpbb_mock_user();
		$request = new phpbb_mock_request();
	}

	/**
	 * @dataProvider data_test_make_clickable_url_positive
	 */
	public function test_urls_matching_positive($url, $expected)
	{
		$this->assertSame($expected, make_clickable($url));
	}

	/**
	 * @dataProvider data_test_make_clickable_url_idn
	 */
	public function test_urls_matching_idn($url, $expected)
	{
		$this->assertSame($expected, make_clickable($url));
	}

	/**
	 * @dataProvider data_test_make_clickable_local_url_idn
	 */
	public function test_local_urls_matching_idn($url, $expected)
	{
		$this->assertSame($expected, make_clickable($url, "http://www.домен.рф"));
	}
}
