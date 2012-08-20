<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_normalizer.php';

/**
* @group slow
*/
class phpbb_utf_normalizer_test extends phpbb_test_case
{
	static private $data_dir;

	static public function setUpBeforeClass()
	{
		self::$data_dir = dirname(__file__) . '/../tmp';
		self::download('http://www.unicode.org/Public/UNIDATA/NormalizationTest.txt', self::$data_dir);
		self::download('http://www.unicode.org/Public/UNIDATA/UnicodeData.txt', self::$data_dir);
	}

	public function test_normalizer()
	{
		$test_suite = array(
			/**
			* NFC
			*   c2 ==  NFC(c1) ==  NFC(c2) ==  NFC(c3)
			*   c4 ==  NFC(c4) ==  NFC(c5)
			*/
			'NFC'	=>	array(
				'c2'	=>	array('c1', 'c2', 'c3'),
				'c4'	=>	array('c4', 'c5')
			),

			/**
			* NFD
			*   c3 ==  NFD(c1) ==  NFD(c2) ==  NFD(c3)
			*   c5 ==  NFD(c4) ==  NFD(c5)
			*/
			'NFD'	=>	array(
				'c3'	=>	array('c1', 'c2', 'c3'),
				'c5'	=>	array('c4', 'c5')
			),

			/**
			* NFKC
			*   c4 == NFKC(c1) == NFKC(c2) == NFKC(c3) == NFKC(c4) == NFKC(c5)
			*/
			'NFKC'	=>	array(
				'c4'	=>	array('c1', 'c2', 'c3', 'c4', 'c5')
			),

			/**
			* NFKD
			*   c5 == NFKD(c1) == NFKD(c2) == NFKD(c3) == NFKD(c4) == NFKD(c5)
			*/
			'NFKD'	=>	array(
				'c5'	=>	array('c1', 'c2', 'c3', 'c4', 'c5')
			)
		);

		$tested_chars = array();

		$fp = fopen(self::$data_dir . '/NormalizationTest.txt', 'rb');
		while (!feof($fp))
		{
			$line = fgets($fp);

			if ($line[0] == '@')
			{
				continue;
			}

			if (!strpos(' 0123456789ABCDEF', $line[0]))
			{
				continue;
			}

			list($c1, $c2, $c3, $c4, $c5) = explode(';', $line);

			if (!strpos($c1, ' '))
			{
				/**
				* We are currently testing a single character, we add it to the list of
				* characters we have processed so that we can exclude it when testing
				* for invariants
				*/
				$tested_chars[$c1] = 1;
			}

			foreach ($test_suite as $form => $serie)
			{
				foreach ($serie as $expected => $tests)
				{
					$hex_expected = ${$expected};
					$utf_expected = $this->hexseq_to_utf($hex_expected);

					foreach ($tests as $test)
					{
						$utf_result = $utf_expected;
						call_user_func_array(array('utf_normalizer', $form), array(&$utf_result));

						$hex_result = $this->utf_to_hexseq($utf_result);
						$this->assertEquals($utf_expected, $utf_result, "$expected == $form($test) ($hex_expected != $hex_result)");
					}
				}
			}
		}
		fclose($fp);

		return $tested_chars;
	}

	/**
	* @depends test_normalizer
	*/
	public function test_invariants(array $tested_chars)
	{
		$fp = fopen(self::$data_dir . '/UnicodeData.txt', 'rb');

		while (!feof($fp))
		{
			$line = fgets($fp, 1024);

			if (!$pos = strpos($line, ';'))
			{
				continue;
			}

			$hex_tested = $hex_expected = substr($line, 0, $pos);

			if (isset($tested_chars[$hex_tested]))
			{
				continue;
			}

			$utf_expected = $this->hex_to_utf($hex_expected);

			if ($utf_expected >= UTF8_SURROGATE_FIRST
			 && $utf_expected <= UTF8_SURROGATE_LAST)
			{
				/**
				* Surrogates are illegal on their own, we expect the normalizer
				* to return a replacement char
				*/
				$utf_expected = UTF8_REPLACEMENT;
				$hex_expected = $this->utf_to_hexseq($utf_expected);
			}

			foreach (array('nfc', 'nfkc', 'nfd', 'nfkd') as $form)
			{
				$utf_result = $utf_expected;
				call_user_func_array(array('utf_normalizer', $form), array(&$utf_result));
				$hex_result = $this->utf_to_hexseq($utf_result);

				$this->assertEquals($utf_expected, $utf_result, "$hex_expected == $form($hex_tested) ($hex_expected != $hex_result)");
			}
		}
		fclose($fp);
	}

	/**
	* Convert a UTF string to a sequence of codepoints in hexadecimal
	*
	* @param	string	$utf	UTF string
	* @return	integer			Unicode codepoints in hex
	*/
	protected function utf_to_hexseq($str)
	{
		$pos = 0;
		$len = strlen($str);
		$ret = array();

		while ($pos < $len)
		{
			$c = $str[$pos];
			switch ($c & "\xF0")
			{
				case "\xC0":
				case "\xD0":
					$utf_char = substr($str, $pos, 2);
					$pos += 2;
					break;

				case "\xE0":
					$utf_char = substr($str, $pos, 3);
					$pos += 3;
					break;

				case "\xF0":
					$utf_char = substr($str, $pos, 4);
					$pos += 4;
					break;

				default:
					$utf_char = $c;
					++$pos;
			}

			$hex = dechex($this->utf_to_cp($utf_char));

			if (!isset($hex[3]))
			{
				$hex = substr('000' . $hex, -4);
			}

			$ret[] = $hex;
		}

		return strtr(implode(' ', $ret), 'abcdef', 'ABCDEF');
	}

	/**
	* Convert a UTF-8 char to its codepoint
	*
	* @param	string	$utf_char	UTF-8 char
	* @return	integer				Unicode codepoint
	*/
	protected function utf_to_cp($utf_char)
	{
		switch (strlen($utf_char))
		{
			case 1:
				return ord($utf_char);

			case 2:
				return ((ord($utf_char[0]) & 0x1F) << 6) | (ord($utf_char[1]) & 0x3F);

			case 3:
				return ((ord($utf_char[0]) & 0x0F) << 12) | ((ord($utf_char[1]) & 0x3F) << 6) | (ord($utf_char[2]) & 0x3F);

			case 4:
				return ((ord($utf_char[0]) & 0x07) << 18) | ((ord($utf_char[1]) & 0x3F) << 12) | ((ord($utf_char[2]) & 0x3F) << 6) | (ord($utf_char[3]) & 0x3F);

			default:
				throw new RuntimeException('UTF-8 chars can only be 1-4 bytes long');
		}
	}

	/**
	* Return a UTF string formed from a sequence of codepoints in hexadecimal
	*
	* @param	string	$seq		Sequence of codepoints, separated with a space
	* @return	string				UTF-8 string
	*/
	protected function hexseq_to_utf($seq)
	{
		return implode('', array_map(array($this, 'hex_to_utf'), explode(' ', $seq)));
	}

	/**
	* Convert a codepoint in hexadecimal to a UTF-8 char
	*
	* @param	string	$hex		Codepoint, in hexadecimal
	* @return	string				UTF-8 char
	*/
	protected function hex_to_utf($hex)
	{
		return $this->cp_to_utf(hexdec($hex));
	}

	/**
	* Convert a codepoint to a UTF-8 char
	*
	* @param	integer	$cp			Unicode codepoint
	* @return	string				UTF-8 string
	*/
	protected function cp_to_utf($cp)
	{
		if ($cp > 0xFFFF)
		{
			return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
		}
		else if ($cp > 0x7FF)
		{
			return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
		}
		else if ($cp > 0x7F)
		{
			return chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
		}
		else
		{
			return chr($cp);
		}
	}

	// chunked download helper
	static protected function download($url, $to)
	{
		$target = $to . '/' . basename($url);

		if (file_exists($target))
		{
			return;
		}

		if (!$fpr = fopen($url, 'rb'))
		{
			echo "Failed to download $url\n";
			return;
		}

		if (!$fpw = fopen($target, 'wb'))
		{
			echo "Failed to open $target for writing\n";
			return;
		}

		$chunk = 32768;

		while (!feof($fpr))
		{
			fwrite($fpw, fread($fpr, $chunk));
		}
		fclose($fpr);
		fclose($fpw);
	}
}
