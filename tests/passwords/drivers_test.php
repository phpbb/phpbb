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

class phpbb_passwords_helper_test extends \phpbb_test_case
{
	public function setUp()
	{
		// Prepare dependencies for drivers
		$config =  new \phpbb\config\config(array());
		$request = new phpbb_mock_request(array(), array(), array(), array(), array('password' => 'fööbar'));
		$this->driver_helper = new \phpbb\passwords\driver\helper($config);
		$phpbb_root_path = dirname(__FILE__) . '/../../phpBB/';
		$php_ext = 'php';

		$this->passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($config, $this->driver_helper),
			'passwords.driver.bcrypt'	=> new \phpbb\passwords\driver\bcrypt($config, $this->driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($config, $this->driver_helper),
			'passwords.driver.phpass'	=> new \phpbb\passwords\driver\phpass($config, $this->driver_helper),
			'passwords.driver.sha1_smf'	=> new \phpbb\passwords\driver\sha1_smf($config, $this->driver_helper),
			'passwords.driver.sha1_wcf1'	=> new \phpbb\passwords\driver\sha1_wcf1($config, $this->driver_helper),
			'passwords.driver.convert_password'=> new \phpbb\passwords\driver\convert_password($config, $this->driver_helper),
			'passwords.driver.sha1'		=> new \phpbb\passwords\driver\sha1($config, $this->driver_helper),
			'passwords.driver.md5_mybb'	=> new \phpbb\passwords\driver\md5_mybb($config, $this->driver_helper),
			'passwords.driver.md5_vb'	=> new \phpbb\passwords\driver\md5_vb($config, $this->driver_helper),
			'passwords.driver.sha_xf1'	=> new \phpbb\passwords\driver\sha_xf1($config, $this->driver_helper),
		);
		$this->passwords_drivers['passwords.driver.md5_phpbb2']	= new \phpbb\passwords\driver\md5_phpbb2($request, $this->passwords_drivers['passwords.driver.salted_md5'], $this->driver_helper, $phpbb_root_path, $php_ext);
		$this->passwords_drivers['passwords.driver.bcrypt_wcf2'] = new \phpbb\passwords\driver\bcrypt_wcf2($this->passwords_drivers['passwords.driver.bcrypt'], $this->driver_helper);
	}

	public function data_helper_encode64()
	{
		return array(
			array('foobar', 6, 'axqPW3aQ'),
			array('foobar', 7, 'axqPW3aQ..'),
			array('foobar', 5, 'axqPW34'),
		);
	}

	/**
	* @dataProvider data_helper_encode64
	*/
	public function test_helper_encode64($input, $length, $output)
	{
		$return = $this->driver_helper->hash_encode64($input, $length);
		$this->assertSame($output, $return);
	}

	public function data_get_random_salt()
	{
		return array(
			array(24, false),
			array(24, '/dev/foobar'),
		);
	}

	/**
	* @dataProvider data_get_random_salt
	*/
	public function test_get_random_salt($length, $rand_seed)
	{
		$rand_string = (empty($rand_seed)) ? $this->driver_helper->get_random_salt($length) : $this->driver_helper->get_random_salt($length, $rand_seed);
		$start = microtime(true);

		// Run each test for max. 1 second
		while ((microtime(true) - $start) < 1)
		{
			$urandom_string = (empty($rand_seed)) ? $this->driver_helper->get_random_salt($length) : $this->driver_helper->get_random_salt($length, $rand_seed);
			$this->assertSame($length, strlen($urandom_string));
			$this->assertNotSame($rand_string, $urandom_string);
		}
	}

	public function test_get_hash_settings_salted_md5()
	{
		$settings = $this->passwords_drivers['passwords.driver.salted_md5']->get_hash_settings('$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1');
		$this->assertEquals(array(
				'count'	=> pow(2, 11),
				'salt'	=> 'isfrtKXW',
				'full'	=> '$H$9isfrtKXW',
			),
			$settings
		);
		$this->assertEquals(false, $this->passwords_drivers['passwords.driver.salted_md5']->get_hash_settings(false));
	}

	public function data_hash_sha1_smf()
	{
		return array(
			array(false, 'test', array()),
			array(false, 'test', ''),
			array('6f9e2a1899e1f15708fd2e554103480eb53e8b57', 'foobar', array('login_name' => 'test')),
		);
	}

	/**
	* @dataProvider data_hash_sha1_smf
	*/
	public function test_hash_sha1_smf($expected, $password, $user_row)
	{
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.sha1_smf']->hash($password, $user_row));
	}

	public function data_get_settings()
	{
		return array(
			array(false, '6f9e2a1899e1f15708fd2e554103480eb53e8b57', 'passwords.driver.sha1_smf'),
		);
	}

	/**
	* @dataProvider data_get_settings
	*/
	public function test_get_settings_only($expected, $hash, $driver)
	{
		$this->assertSame($expected, $this->passwords_drivers[$driver]->get_settings_only($hash));
	}

	public function data_md5_phpbb2_check()
	{
		return array(
			array(false, 'foobar', 'ae2fc75e20ee25d4520766788fbc96ae'),
			array(false, 'foobar', 'ae2fc75e20ee25d4520766788fbc96aeddsf'),
			array(false, 'fööbar', 'ae2fc75e20ee25d4520766788fbc96ae'),
			array(true, 'fööbar', 'ae2fc75e20ee25d4520766788fbc96ae', utf8_decode('fööbar')),
			array(true, 'fööbar', '$H$966CepJh9RC3hFIm7aKywR6jEn0kpA0', utf8_decode('fööbar')),
			array(true, 'fööbar', '$H$9rNjgwETtmc8befO8JL1xFMrrMw8MC.', $this->utf8_to_cp1252(utf8_decode('fööbar'))),
			array(true, 'fööbar', '$H$9rNjgwETtmc8befO8JL1xFMrrMw8MC.', $this->utf8_to_cp1252('fööbar')),
		);
	}

	/**
	* @dataProvider data_md5_phpbb2_check
	*/
	public function test_md5_phpbb2_check($expected, $password, $hash, $request_password = false)
	{
		if (!$request_password)
		{
			unset($_REQUEST['password']);
		}
		else
		{
			$_REQUEST['password'] = $request_password;
		}
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.md5_phpbb2']->check($password, $hash));
	}

	public function test_md5_phpbb2_hash()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.md5_phpbb2']->hash('foobar'));
	}

	public function test_convert_password_driver()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.convert_password']->hash('foobar'));
	}

	public function test_sha1_driver()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.sha1']->hash('foobar'));
	}

	public function data_md5_mybb_check()
	{
		return array(
			array(false, 'foobar', '083d11daea8675b1b4b502c7e55f8dbd'),
			array(false, 'foobar', '083d11daea8675b1b4b502c7e55f8dbd', array('user_passwd_salt' => 'ae2fc75e')),
			array(true, 'foobar', 'b86ee7e24008bfd2890dcfab1ed31333', array('user_passwd_salt' => 'yeOtfFO6')),
		);
	}

	/**
	* @dataProvider data_md5_mybb_check
	*/
	public function test_md5_mybb_check($expected, $password, $hash, $user_row = array())
	{
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.md5_mybb']->check($password, $hash, $user_row));
	}

	public function test_md5_mybb_driver()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.md5_mybb']->hash('foobar'));
	}

	public function data_md5_vb_check()
	{
		return array(
			array(false, 'foobar', '083d11daea8675b1b4b502c7e55f8dbd'),
			array(false, 'foobar', 'b86ee7e24008bfd2890dcfab1ed31333', array('user_passwd_salt' => 'yeOtfFO6')),
			array(true, 'foobar', 'b452c54c44c588fc095d2d000935c470', array('user_passwd_salt' => '9^F')),
			array(true, 'foobar', 'f23a8241bd115d270c703213e3ef7f52', array('user_passwd_salt' => 'iaU*U%`CBl;/e~>D%do2m@Xf/,KZB0')),
			array(false, 'nope', 'f23a8241bd115d270c703213e3ef7f52', array('user_passwd_salt' => 'iaU*U%`CBl;/e~>D%do2m@Xf/,KZB0')),
		);
	}

	/**
	* @dataProvider data_md5_vb_check
	*/
	public function test_md5_vb_check($expected, $password, $hash, $user_row = array())
	{
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.md5_vb']->check($password, $hash, $user_row));
	}

	public function test_md5_vb_driver()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.md5_vb']->hash('foobar'));
	}

	public function data_sha1_wcf1_check()
	{
		return array(
			array(false, 'foobar', 'fc46b9d9386167ce365ea3b891bf5dc31ddcd3ff'),
			array(false, 'foobar', 'fc46b9d9386167ce365ea3b891bf5dc31ddcd3ff', array('user_passwd_salt' => 'yeOtfFO6')),
			array(true, 'foobar', 'fc46b9d9386167ce365ea3b891bf5dc31ddcd3ff', array('user_passwd_salt' => '1a783e478d63f6422783a868db667aed3a857840')),
		);
	}

	/**
	* @dataProvider data_sha1_wcf1_check
	*/
	public function test_sha1_wcf1_check($expected, $password, $hash, $user_row = array())
	{
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.sha1_wcf1']->check($password, $hash, $user_row));
	}

	public function test_sha1_wcf1_driver()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.sha1_wcf1']->hash('foobar'));
	}

	public function data_bcrypt_wcf2_check()
	{
		return array(
			array(false, 'foobar', 'fc46b9d9386167ce365ea3b891bf5dc31ddcd3ff'),
			array(true, 'foobar', '$2a$08$p8h14U0jsEiVb1Luy.s8oOTXSQ0hVWUXpcNGBoCezeYNXrQyCKHfi'),
			array(false, 'foobar', ''),
		);
	}

	/**
	* @dataProvider data_bcrypt_wcf2_check
	*/
	public function test_bcrypt_wcf2_check($expected, $password, $hash)
	{
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.bcrypt_wcf2']->check($password, $hash));
	}

	public function test_bcrypt_wcf2_driver()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.bcrypt_wcf2']->hash('foobar'));
	}

	public function data_sha_xf1_check()
	{
		return array(
			array(false, 'foobar', 'fc46b9d9386167ce365ea3b891bf5dc31ddcd3ff'),
			array(false, 'foobar', 'fc46b9d9386167ce365ea3b891bf5dc31ddcd3ff', array('user_passwd_salt' => 'yeOtfFO6')),
			array(true, 'foobar', '7f65d2fa8a826d232f8134772252f8b1aaef8594b1edcabd9ab65e5b0f236ff0', array('user_passwd_salt' => '15b6c02cedbd727f563dcca607a89b085287b448966f19c0cc78cae263b1e38c')),
			array(true, 'foobar', '69962ae2079420573a3948cc4dedbabd35680051', array('user_passwd_salt' => '15b6c02cedbd727f563dcca607a89b085287b448966f19c0cc78cae263b1e38c')),
		);
	}

	/**
	* @dataProvider data_sha_xf1_check
	*/
	public function test_sha_xf1_check($expected, $password, $hash, $user_row = array())
	{
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.sha_xf1']->check($password, $hash, $user_row));
	}

	public function test_sha_xf1_driver()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.sha_xf1']->hash('foobar'));
	}

	protected function utf8_to_cp1252($string)
	{
		static $transform = array(
			"\xE2\x82\xAC" => "\x80",
			"\xE2\x80\x9A" => "\x82",
			"\xC6\x92" => "\x83",
			"\xE2\x80\x9E" => "\x84",
			"\xE2\x80\xA6" => "\x85",
			"\xE2\x80\xA0" => "\x86",
			"\xE2\x80\xA1" => "\x87",
			"\xCB\x86" => "\x88",
			"\xE2\x80\xB0" => "\x89",
			"\xC5\xA0" => "\x8A",
			"\xE2\x80\xB9" => "\x8B",
			"\xC5\x92" => "\x8C",
			"\xC5\xBD" => "\x8E",
			"\xE2\x80\x98" => "\x91",
			"\xE2\x80\x99" => "\x92",
			"\xE2\x80\x9C" => "\x93",
			"\xE2\x80\x9D" => "\x94",
			"\xE2\x80\xA2" => "\x95",
			"\xE2\x80\x93" => "\x96",
			"\xE2\x80\x94" => "\x97",
			"\xCB\x9C" => "\x98",
			"\xE2\x84\xA2" => "\x99",
			"\xC5\xA1" => "\x9A",
			"\xE2\x80\xBA" => "\x9B",
			"\xC5\x93" => "\x9C",
			"\xC5\xBE" => "\x9E",
			"\xC5\xB8" => "\x9F",
			"\xC2\xA0" => "\xA0",
			"\xC2\xA1" => "\xA1",
			"\xC2\xA2" => "\xA2",
			"\xC2\xA3" => "\xA3",
			"\xC2\xA4" => "\xA4",
			"\xC2\xA5" => "\xA5",
			"\xC2\xA6" => "\xA6",
			"\xC2\xA7" => "\xA7",
			"\xC2\xA8" => "\xA8",
			"\xC2\xA9" => "\xA9",
			"\xC2\xAA" => "\xAA",
			"\xC2\xAB" => "\xAB",
			"\xC2\xAC" => "\xAC",
			"\xC2\xAD" => "\xAD",
			"\xC2\xAE" => "\xAE",
			"\xC2\xAF" => "\xAF",
			"\xC2\xB0" => "\xB0",
			"\xC2\xB1" => "\xB1",
			"\xC2\xB2" => "\xB2",
			"\xC2\xB3" => "\xB3",
			"\xC2\xB4" => "\xB4",
			"\xC2\xB5" => "\xB5",
			"\xC2\xB6" => "\xB6",
			"\xC2\xB7" => "\xB7",
			"\xC2\xB8" => "\xB8",
			"\xC2\xB9" => "\xB9",
			"\xC2\xBA" => "\xBA",
			"\xC2\xBB" => "\xBB",
			"\xC2\xBC" => "\xBC",
			"\xC2\xBD" => "\xBD",
			"\xC2\xBE" => "\xBE",
			"\xC2\xBF" => "\xBF",
			"\xC3\x80" => "\xC0",
			"\xC3\x81" => "\xC1",
			"\xC3\x82" => "\xC2",
			"\xC3\x83" => "\xC3",
			"\xC3\x84" => "\xC4",
			"\xC3\x85" => "\xC5",
			"\xC3\x86" => "\xC6",
			"\xC3\x87" => "\xC7",
			"\xC3\x88" => "\xC8",
			"\xC3\x89" => "\xC9",
			"\xC3\x8A" => "\xCA",
			"\xC3\x8B" => "\xCB",
			"\xC3\x8C" => "\xCC",
			"\xC3\x8D" => "\xCD",
			"\xC3\x8E" => "\xCE",
			"\xC3\x8F" => "\xCF",
			"\xC3\x90" => "\xD0",
			"\xC3\x91" => "\xD1",
			"\xC3\x92" => "\xD2",
			"\xC3\x93" => "\xD3",
			"\xC3\x94" => "\xD4",
			"\xC3\x95" => "\xD5",
			"\xC3\x96" => "\xD6",
			"\xC3\x97" => "\xD7",
			"\xC3\x98" => "\xD8",
			"\xC3\x99" => "\xD9",
			"\xC3\x9A" => "\xDA",
			"\xC3\x9B" => "\xDB",
			"\xC3\x9C" => "\xDC",
			"\xC3\x9D" => "\xDD",
			"\xC3\x9E" => "\xDE",
			"\xC3\x9F" => "\xDF",
			"\xC3\xA0" => "\xE0",
			"\xC3\xA1" => "\xE1",
			"\xC3\xA2" => "\xE2",
			"\xC3\xA3" => "\xE3",
			"\xC3\xA4" => "\xE4",
			"\xC3\xA5" => "\xE5",
			"\xC3\xA6" => "\xE6",
			"\xC3\xA7" => "\xE7",
			"\xC3\xA8" => "\xE8",
			"\xC3\xA9" => "\xE9",
			"\xC3\xAA" => "\xEA",
			"\xC3\xAB" => "\xEB",
			"\xC3\xAC" => "\xEC",
			"\xC3\xAD" => "\xED",
			"\xC3\xAE" => "\xEE",
			"\xC3\xAF" => "\xEF",
			"\xC3\xB0" => "\xF0",
			"\xC3\xB1" => "\xF1",
			"\xC3\xB2" => "\xF2",
			"\xC3\xB3" => "\xF3",
			"\xC3\xB4" => "\xF4",
			"\xC3\xB5" => "\xF5",
			"\xC3\xB6" => "\xF6",
			"\xC3\xB7" => "\xF7",
			"\xC3\xB8" => "\xF8",
			"\xC3\xB9" => "\xF9",
			"\xC3\xBA" => "\xFA",
			"\xC3\xBB" => "\xFB",
			"\xC3\xBC" => "\xFC",
			"\xC3\xBD" => "\xFD",
			"\xC3\xBE" => "\xFE",
			"\xC3\xBF" => "\xFF"
		);
		return strtr($string, $transform);
	}
}
