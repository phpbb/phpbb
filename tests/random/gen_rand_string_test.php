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

class phpbb_random_gen_rand_string_test extends phpbb_test_case
{
	const TEST_COUNT = 100;
	const MIN_STRING_LENGTH = 1;
	const MAX_STRING_LENGTH = 15;

	protected function setUp(): void
	{
		global $config;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;
	}

	public function test_gen_rand_string()
	{
		for ($tests = 0; $tests <= self::TEST_COUNT; ++$tests)
		{
			for ($num_chars = self::MIN_STRING_LENGTH; $num_chars <= self::MAX_STRING_LENGTH; ++$num_chars)
			{
				$random_string = gen_rand_string($num_chars);
				$random_string_length = strlen($random_string);

				$this->assertTrue($random_string_length >= self::MIN_STRING_LENGTH);
				$this->assertTrue(
					$random_string_length == $num_chars,
					sprintf('Failed asserting that random string length matches expected length. Expected %1$u, Actual %2$u', $num_chars, $random_string_length)
				);
				$this->assertMatchesRegularExpression('#^[A-Z0-9]+$#', $random_string);
			}
		}
	}

	public function test_gen_rand_string_friendly()
	{
		for ($tests = 0; $tests <= self::TEST_COUNT; ++$tests)
		{
			for ($num_chars = self::MIN_STRING_LENGTH; $num_chars <= self::MAX_STRING_LENGTH; ++$num_chars)
			{
				$random_string = gen_rand_string_friendly($num_chars);
				$random_string_length = strlen($random_string);

				$this->assertTrue($random_string_length >= self::MIN_STRING_LENGTH);
				$this->assertTrue(
					$random_string_length == $num_chars,
					sprintf('Failed asserting that random string length matches expected length. Expected %1$u, Actual %2$u', $num_chars, $random_string_length)
				);
				$this->assertMatchesRegularExpression('#^[A-NP-Z1-9]+$#', $random_string);
			}
		}
	}
}
