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
		$this->driver_helper = new \phpbb\passwords\driver\helper($config);

		$this->passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($config, $this->driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($config, $this->driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($config, $this->driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($config, $this->driver_helper),
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
}
