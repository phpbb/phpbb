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

class phpbb_headers_encoding_test extends phpbb_test_case
{
	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		if (!function_exists('mail_encode'))
		{
			include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		}
	}

	public function headers_encoding_data()
	{
		return [
			['test@yourdomain.com <phpBB fake test email>', 'Q', 'US-ASCII'],
			['test@yourdomain.com <Несуществующий почтовый адрес phpBB>', 'B', 'UTF-8'],
			["\xE3\x83\x86\xE3\x82\xB9\xE3\x83\x88\xE3\x83\x86\xE3\x82\xB9\xE3\x83\x88", 'B', 'UTF-8'],
		];
	}

	/**
	 * @dataProvider headers_encoding_data
	 */
	public function test_headers_encoding($header, $scheme, $encoding)
	{
		$encoded_string = mail_encode($header);
		$this->assertStringStartsWith("=?$encoding?$scheme?", $encoded_string);
		$this->assertStringEndsWith('?=', $encoded_string);

		// Result of iconv_mime_decode() on decoded header should be equal to initial header
		$decoded_string = iconv_mime_decode($encoded_string, 0, $encoding);
		$this->assertEquals(0, strcmp($header, $decoded_string));
	}
}
