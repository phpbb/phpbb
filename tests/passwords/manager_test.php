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

class phpbb_passwords_manager_test extends \phpbb_test_case
{
	protected $passwords_drivers;

	protected $pw_characters = '0123456789abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVXYZ.,_!?/\\';

	protected $default_pw = 'foobar';

	public function setUp()
	{
		// Prepare dependencies for manager and driver
		$config =  new \phpbb\config\config(array());
		$this->driver_helper = new \phpbb\passwords\driver\helper($config);

		$this->passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($config, $this->driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($config, $this->driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($config, $this->driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($config, $this->driver_helper),
		);

		$this->helper = new \phpbb\passwords\helper;
		// Set up passwords manager
		$this->manager = new \phpbb\passwords\manager($config, $this->passwords_drivers, $this->helper, array_keys($this->passwords_drivers));
	}

	public function hash_password_data()
	{
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			return array(
				array('', '2a', 60),
				array('passwords.driver.bcrypt_2y', '2a', 60),
				array('passwords.driver.bcrypt', '2a', 60),
				array('passwords.driver.salted_md5', 'H', 34),
				array('passwords.driver.foobar', '', false),
			);
		}
		else
		{
			return array(
				array('', '2y', 60),
				array('passwords.driver.bcrypt_2y', '2y', 60),
				array('passwords.driver.bcrypt', '2a', 60),
				array('passwords.driver.salted_md5', 'H', 34),
				array('passwords.driver.foobar', '', false),
			);
		}
	}

	/**
	* @dataProvider hash_password_data
	*/
	public function test_hash_password($type, $prefix, $length)
	{
		$password = $this->default_pw;

		if (!$length)
		{
			$this->assertEquals(false, $hash = $this->manager->hash($password, $type));
			return;
		}
		$time = microtime(true);

		// Limit each test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$hash = $this->manager->hash($password, $type);
			preg_match('#^\$([a-zA-Z0-9\\\]*?)\$#', $hash, $match);
			$this->assertEquals($prefix, $match[1]);
			$this->assertEquals($length, strlen($hash));
			$password .= $this->pw_characters[mt_rand(0, 66)];
		}
	}

	public function check_password_data()
	{
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			return array(
				array('passwords.driver.bcrypt'),
				array('passwords.driver.salted_md5'),
				array('passwords.driver.phpass'),
			);
		}
		else
		{
			return array(
				array('passwords.driver.bcrypt_2y'),
				array('passwords.driver.bcrypt'),
				array('passwords.driver.salted_md5'),
				array('passwords.driver.phpass'),
			);
		}
	}

	/**
	* @dataProvider check_password_data
	*/
	public function test_check_password($hash_type)
	{
		$password = $this->default_pw;
		$time = microtime(true);
		// Limit each test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$hash = $this->manager->hash($password, $hash_type);
			$this->assertEquals(true, $this->manager->check($password, $hash));
			$password .= $this->pw_characters[mt_rand(0, 66)];
			$this->assertEquals(false, $this->manager->check($password, $hash));
		}

		// Check if convert_flag is correctly set
		$default_type = (version_compare(PHP_VERSION, '5.3.7', '<')) ? 'passwords.driver.bcrypt' : 'passwords.driver.bcrypt_2y';
		$this->assertEquals(($hash_type !== $default_type), $this->manager->convert_flag);
	}


	public function check_hash_exceptions_data()
	{
		return array(
			array('foobar', '3858f62230ac3c915f300c664312c63f', true),
			array('foobar', '$S$b57a939fa4f2c04413a4eea9734a0903647b7adb93181295', false),
			array('foobar', '$2a\S$kkkkaakdkdiej39023903204j2k3490234jk234j02349', false),
			array('foobar', '$H$kklk938d023k//k3023', false),
			array('foobar', '$H$3PtYMgXb39lrIWkgoxYLWtRkZtY3AY/', false),
			array('foobar', '$2a$kwiweorurlaeirw', false),
		);
	}

	/**
	* @dataProvider check_hash_exceptions_data
	*/
	public function test_check_hash_exceptions($password, $hash, $expected)
	{
		$this->assertEquals($expected, $this->manager->check($password, $hash));
	}

	public function data_hash_password_length()
	{
		return array(
			array('passwords.driver.bcrypt', false),
			array('passwords.driver.bcrypt_2y', false),
			array('passwords.driver.salted_md5', '3858f62230ac3c915f300c664312c63f'),
			array('passwords.driver.phpass', '3858f62230ac3c915f300c664312c63f'),
		);
	}

	/**
	* @dataProvider data_hash_password_length
	*/
	public function test_hash_password_length($driver, $expected)
	{
		$this->assertEquals($expected, $this->passwords_drivers[$driver]->hash('foobar', 'foobar'));
	}

	public function test_hash_password_8bit_bcrypt()
	{
		$this->assertEquals(false, $this->manager->hash('foobar𝄞', 'passwords.driver.bcrypt'));
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			$this->assertEquals(false, $this->manager->hash('foobar𝄞', 'passwords.driver.bcrypt_2y'));
		}
		else
		{
			$this->assertNotEquals(false, $this->manager->hash('foobar𝄞', 'passwords.driver.bcrypt_2y'));
		}
	}

	public function combined_hash_data()
	{
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			return array(
				array(
					'passwords.driver.salted_md5',
					array('passwords.driver.bcrypt'),
				),
				array(
					'passwords.driver.phpass',
					array('passwords.driver.salted_md5'),
				),
				array(
					'passwords.driver.salted_md5',
					array('passwords.driver.phpass', 'passwords.driver.bcrypt'),
				),
				array(
					'passwords.driver.salted_md5',
					array('passwords.driver.salted_md5'),
					false,
				),
				array(
					'$H$',
					array('$2a$'),
				),
			);
		}
		else
		{
			return array(
				array(
					'passwords.driver.salted_md5',
					array('passwords.driver.bcrypt_2y'),
				),
				array(
					'passwords.driver.salted_md5',
					array('passwords.driver.bcrypt'),
				),
				array(
					'passwords.driver.phpass',
					array('passwords.driver.salted_md5'),
				),
				array(
					'passwords.driver.salted_md5',
					array('passwords.driver.bcrypt_2y', 'passwords.driver.bcrypt'),
				),
				array(
					'passwords.driver.salted_md5',
					array('passwords.driver.salted_md5'),
					false,
				),
				array(
					'passwords.driver.bcrypt_2y',
					array('passwords.driver.salted_md4'),
					false,
				),
				array(
					'$H$',
					array('$2y$'),
				),
			);
		}
	}

	/**
	* @dataProvider combined_hash_data
	*/
	public function test_combined_hash_password($first_type, $second_type, $expected = true)
	{
		$password = $this->default_pw;
		$time = microtime(true);
		// Limit each test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$hash = $this->manager->hash($password, $first_type);
			$combined_hash = $this->manager->hash($hash, $second_type);
			$this->assertEquals($expected, $this->manager->check($password, $combined_hash));
			$password .= $this->pw_characters[mt_rand(0, 66)];
			$this->assertEquals(false, $this->manager->check($password, $combined_hash));

			// If we are expecting the check to fail then there is
			// no need to run this more than once
			if (!$expected)
			{
				break;
			}
		}
	}

	public function test_unique_id()
	{
		$time = microtime(true);
		$first_id = $this->driver_helper->unique_id();
		// Limit test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$this->assertNotSame($first_id, $this->driver_helper->unique_id());
		}
	}

	public function test_check_hash_with_large_input()
	{
		// 16 MB password, should be rejected quite fast
		$start_time = time();
		$this->assertFalse($this->manager->check(str_repeat('a', 1024 * 1024 * 16), '$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
		$this->assertLessThanOrEqual(5, time() - $start_time);
	}

	public function test_hash_password_with_large_input()
	{
		// 16 MB password, should be rejected quite fast
		$start_time = time();
		$this->assertFalse($this->manager->hash(str_repeat('a', 1024 * 1024 * 16)));
		$this->assertLessThanOrEqual(5, time() - $start_time);
	}
}
