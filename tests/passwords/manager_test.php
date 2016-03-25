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
		$request = new phpbb_mock_request(array(), array(), array(), array(), array('password' => 'töst'));
		$phpbb_root_path = dirname(__FILE__) . '/../../phpBB/';
		$php_ext = 'php';

		$this->passwords_drivers = array(
			'passwords.driver.bcrypt_2y'		=> new \phpbb\passwords\driver\bcrypt_2y($config, $this->driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($config, $this->driver_helper),
			'passwords.driver.salted_md5'		=> new \phpbb\passwords\driver\salted_md5($config, $this->driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($config, $this->driver_helper),
			'passwords.driver.convert_password'	=> new \phpbb\passwords\driver\convert_password($config, $this->driver_helper),
			'passwords.driver.sha1_smf'		=> new \phpbb\passwords\driver\sha1_smf($config, $this->driver_helper),
			'passwords.driver.sha1'			=> new \phpbb\passwords\driver\sha1($config, $this->driver_helper),
			'passwords.driver.sha1_wcf1'		=> new \phpbb\passwords\driver\sha1_wcf1($config, $this->driver_helper),
			'passwords.driver.md5_mybb'		=> new \phpbb\passwords\driver\md5_mybb($config, $this->driver_helper),
			'passwords.driver.md5_vb'		=> new \phpbb\passwords\driver\md5_vb($config, $this->driver_helper),
			'passwords.driver.sha_xf1'	=> new \phpbb\passwords\driver\sha_xf1($config, $this->driver_helper),
		);
		$this->passwords_drivers['passwords.driver.md5_phpbb2']	= new \phpbb\passwords\driver\md5_phpbb2($request, $this->passwords_drivers['passwords.driver.salted_md5'], $this->driver_helper, $phpbb_root_path, $php_ext);
		$this->passwords_drivers['passwords.driver.bcrypt_wcf2'] = new \phpbb\passwords\driver\bcrypt_wcf2($this->passwords_drivers['passwords.driver.bcrypt'], $this->driver_helper);

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
			array('3858f62230ac3c915f300c664312c63f', true),
			array('$CP$3858f62230ac3c915f300c664312c63f', true), // md5_phpbb2
			array('$CP$3858f62230ac3c915f300c', false),
			array('$S$b57a939fa4f2c04413a4eea9734a0903647b7adb93181295', false),
			array('$2a\S$kkkkaakdkdiej39023903204j2k3490234jk234j02349', false),
			array('$H$kklk938d023k//k3023', false),
			array('$H$3PtYMgXb39lrIWkgoxYLWtRkZtY3AY/', false),
			array('$2a$kwiweorurlaeirw', false),
			array('6f9e2a1899e1f15708fd2e554103480eb53e8b57', false),
			array('6f9e2a1899e1f15708fd2e554103480eb53e8b57', false, 'foobar', array('login_name' => 'test')),
			array('$CP$6f9e2a1899e1f15708fd2e554103480eb53e8b57', true, 'foobar', array('login_name' => 'test')), // sha1_smf
			array('6f9e2a1899', false, 'foobar', array('login_name' => 'test')),
			array('ae2fc75e20ee25d4520766788fbc96ae', false, 'fööbar'),
			array('$CP$ae2fc75e20ee25d4520766788fbc96ae', false, 'fööbar'),
			array('$CP$ae2fc75e20ee25d4520766788fbc96ae', true, utf8_decode('fööbar')), // md5_phpbb2
			array('b86ee7e24008bfd2890dcfab1ed31333', false, 'foobar', array('user_passwd_salt' => 'yeOtfFO6')),
			array('$CP$b86ee7e24008bfd2890dcfab1ed31333', true, 'foobar', array('user_passwd_salt' => 'yeOtfFO6')), // md5_mybb
			array('$CP$b452c54c44c588fc095d2d000935c470', true, 'foobar', array('user_passwd_salt' => '9^F')), // md5_vb
			array('$CP$f23a8241bd115d270c703213e3ef7f52', true, 'foobar', array('user_passwd_salt' => 'iaU*U%`CBl;/e~>D%do2m@Xf/,KZB0')), // md5_vb
			array('$CP$fc46b9d9386167ce365ea3b891bf5dc31ddcd3ff', true, 'foobar', array('user_passwd_salt' => '1a783e478d63f6422783a868db667aed3a857840')), // sha_wcf1
			array('$2a$08$p8h14U0jsEiVb1Luy.s8oOTXSQ0hVWUXpcNGBoCezeYNXrQyCKHfi', false),
			array('$CP$$2a$08$p8h14U0jsEiVb1Luy.s8oOTXSQ0hVWUXpcNGBoCezeYNXrQyCKHfi', true), // bcrypt_wcf2
			array('$CP$7f65d2fa8a826d232f8134772252f8b1aaef8594b1edcabd9ab65e5b0f236ff0', true, 'foobar', array('user_passwd_salt' => '15b6c02cedbd727f563dcca607a89b085287b448966f19c0cc78cae263b1e38c')), // sha_xf1
			array('$CP$69962ae2079420573a3948cc4dedbabd35680051', true, 'foobar', array('user_passwd_salt' => '15b6c02cedbd727f563dcca607a89b085287b448966f19c0cc78cae263b1e38c')), // sha_xf1
		);
	}

	/**
	* @dataProvider check_hash_exceptions_data
	*/
	public function test_check_hash_exceptions($hash, $expected, $password = 'foobar', $user_row = array())
	{
		$this->assertEquals($expected, $this->manager->check($password, $hash, $user_row));
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

	public function data_test_string_compare()
	{
		return array(
			array('foo', 'bar', false),
			array(1, '1', false),
			array('one', 'one', true),
			array('foobar', 'foobaf', false),
		);
	}

	/**
	 * @dataProvider data_test_string_compare
	 */
	public function test_string_compare($a, $b, $expected)
	{
		$this->assertSame($expected, $this->driver_helper->string_compare($a, $b));
	}
}
