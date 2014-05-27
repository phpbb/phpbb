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
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($config, $this->driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($config, $this->driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($config, $this->driver_helper),
			'passwords.driver.sha1_smf'	=> new \phpbb\passwords\driver\sha1_smf($config, $this->driver_helper),
			'passwords.driver.phpbb2_md5'	=> new \phpbb\passwords\driver\phpbb2_md5($request, $phpbb_root_path, $php_ext),
		);
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

	public function data_phpbb2_md5_check()
	{
		return array(
			array(false, 'foobar', 'ae2fc75e20ee25d4520766788fbc96ae'),
			array(false, 'foobar', 'ae2fc75e20ee25d4520766788fbc96aeddsf'),
			array(false, 'fööbar', 'ae2fc75e20ee25d4520766788fbc96ae'),
			array(true, 'fööbar', 'ae2fc75e20ee25d4520766788fbc96ae', utf8_decode('fööbar')),
		);
	}

	/**
	* @dataProvider data_phpbb2_md5_check
	*/
	public function test_phpbb2_md5_check($expected, $password, $hash, $request_password = false)
	{
		if (!$request_password)
		{
			unset($_REQUEST['password']);
		}
		else
		{
			$_REQUEST['password'] = $request_password;
		}
		$this->assertSame($expected, $this->passwords_drivers['passwords.driver.phpbb2_md5']->check($password, $hash));
	}

	public function test_phpbb2_md5_unneeded_functions()
	{
		$this->assertSame(false, $this->passwords_drivers['passwords.driver.phpbb2_md5']->hash('foobar'));

		$this->assertSame(false, $this->passwords_drivers['passwords.driver.phpbb2_md5']->get_settings_only('ae2fc75e20ee25d4520766788fbc96ae'));
	}
}
