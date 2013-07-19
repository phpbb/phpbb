<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../mock/container_builder.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/crypto/driver/bcrypt.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/crypto/driver/bcrypt_2y.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/crypto/driver/salted_md5.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/crypto/driver/phpass.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/crypto/driver/helper.php';

class phpbb_crypto_manager_test extends PHPUnit_Framework_TestCase
{
	protected $crypto_drivers;

	protected $pw_characters = '0123456789abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVXYZ.,_!?/\\';

	protected $default_pw = 'foobar';

	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		// Mock phpbb_container
		$this->phpbb_container = new phpbb_mock_container_builder;

		// Prepare dependencies for manager and driver
		$config = new phpbb_config(array());

		$this->crypto_drivers = array(
			'crypto.driver.bcrypt'		=> new phpbb_crypto_driver_bcrypt($config),
			'crypto.driver.bcrypt_2y'	=> new phpbb_crypto_driver_bcrypt_2y($config),
			'crypto.driver.salted_md5'	=> new phpbb_crypto_driver_salted_md5($config),
			'crypto.driver.phpass'		=> new phpbb_crypto_driver_phpass($config),
		);

		foreach ($this->crypto_drivers as $key => $driver)
		{
			$this->phpbb_container->set($key, $driver);
		}

		// Set up avatar manager
		$this->manager = new phpbb_crypto_manager($config, $this->phpbb_container, $this->crypto_drivers);
	}

	public function hash_password_data()
	{
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			return array(
				array('', '2a', 60),
				array('crypto.driver.bcrypt_2y', '2a', 60),
				array('crypto.driver.bcrypt', '2a', 60),
				array('crypto.driver.salted_md5', 'H', 34),
			);
		}
		else
		{
			return array(
				array('', '2y', 60),
				array('crypto.driver.bcrypt_2y', '2y', 60),
				array('crypto.driver.bcrypt', '2a', 60),
				array('crypto.driver.salted_md5', 'H', 34),
			);
		}
	}

	/**
	* @dataProvider hash_password_data
	*/
	public function test_hash_password($type, $prefix, $length)
	{
		$password = $this->default_pw;
		$time = microtime(true);

		// Limit each test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$hash = $this->manager->hash_password($password, $type);
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
				array('crypto.driver.bcrypt'),
				array('crypto.driver.salted_md5'),
				array('crypto.driver.phpass'),
			);
		}
		else
		{
			return array(
				array('crypto.driver.bcrypt_2y'),
				array('crypto.driver.bcrypt'),
				array('crypto.driver.salted_md5'),
				array('crypto.driver.phpass'),
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
			$hash = $this->manager->hash_password($password, $hash_type);
			$this->assertEquals(true, $this->manager->check_hash($password, $hash));
			$password .= $this->pw_characters[mt_rand(0, 66)];
			$this->assertEquals(false, $this->manager->check_hash($password, $hash));
		}
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
		$this->assertEquals($expected, $this->manager->check_hash($password, $hash));
	}

	public function test_hash_password_length()
	{
		foreach ($this->crypto_drivers as $driver)
		{
			$this->assertEquals(false, $driver->hash('foobar', 'foobar'));
		}
	}

	public function test_hash_password_8bit_bcrypt()
	{
		$this->assertEquals(false, $this->manager->hash_password('foobar𝄞', 'crypto.driver.bcrypt'));
	}

	public function test_combined_hash_data()
	{
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			return array(
				array(
					'crypto.driver.salted_md5',
					array('crypto.driver.bcrypt'),
				),
				array(
					'crypto.driver.phpass',
					array('crypto.driver.salted_md5'),
				),
				array(
					'crypto.driver.salted_md5',
					array('crypto.driver.phpass', 'crypto.driver.bcrypt'),
				),
				array(
					'crypto.driver.salted_md5',
					array('crypto.driver.salted_md5'),
					false,
				),
			);
		}
		else
		{
			return array(
				array(
					'crypto.driver.salted_md5',
					array('crypto.driver.bcrypt_2y'),
				),
				array(
					'crypto.driver.salted_md5',
					array('crypto.driver.bcrypt'),
				),
				array(
					'crypto.driver.phpass',
					array('crypto.driver.salted_md5'),
				),
				array(
					'crypto.driver.salted_md5',
					array('crypto.driver.bcrypt_2y', 'crypto.driver.bcrypt'),
				),
				array(
					'crypto.driver.salted_md5',
					array('crypto.driver.salted_md5'),
					false,
				),
			);
		}
	}

	/**
	* @dataProvider test_combined_hash_data
	*/
	public function test_combined_hash_password($first_type, $second_type, $expected = true)
	{
		$password = $this->default_pw;
		$time = microtime(true);
		// Limit each test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$hash = $this->manager->hash_password($password, $first_type);
			$combined_hash = $this->manager->hash_password($hash, $second_type);
			$this->assertEquals($expected, $this->manager->check_hash($password, $combined_hash));
			$password .= $this->pw_characters[mt_rand(0, 66)];
			$this->assertEquals(false, $this->manager->check_hash($password, $combined_hash));

			// If we are expecting the check to fail then there is
			// no need to run this more than once
			if (!$expected)
			{
				break;
			}
		}
	}
}
