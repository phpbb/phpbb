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
/*
		$config['allow_avatar_' . get_class($this->avatar_foobar)] = true;
		$config['allow_avatar_' . get_class($this->avatar_barfoo)] = false;
*/
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
		$hash = $this->manager->hash_password('foobar', $type);
		preg_match('#^\$([a-zA-Z0-9\\\]*?)\$#', $hash, $match);
		$this->assertEquals($prefix, $match[1]);
		$this->assertEquals($length, strlen($hash));
	}

	public function check_password_data()
	{
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			return array(
				array('foobar', 'crypto.driver.bcrypt'),
				array('foobar', 'crypto.driver.salted_md5'),
				array('barfoo', 'crypto.driver.phpass'),
			);
		}
		else
		{
			return array(
				array('foobar', 'crypto.driver.bcrypt_2y'),
				array('barfoo', 'crypto.driver.bcrypt'),
				array('foobar', 'crypto.driver.salted_md5'),
				array('barfoo', 'crypto.driver.phpass'),
			);
		}
	}

	/**
	* @dataProvider check_password_data
	*/
	public function test_check_password($password, $hash_type)
	{
		$hash = $this->manager->hash_password($password, $hash_type);
		$test_word = $password;
		$time = microtime(true);

		// Limit each test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$this->assertEquals($test_word === $password, $this->manager->check_hash($test_word, $hash));
			$test_word = str_shuffle($test_word);
		}
	}

	public function test_hash_password_length()
	{
		foreach ($this->crypto_drivers as $driver)
		{
			$this->assertEquals(false, $driver->hash('foobar', 'foobar'));
		}
	}

	public function test_combined_hash_data()
	{
		return array(
			array(
				'crypto.driver.salted_md5',
				array('crypto.driver.bcrypt_2y', 'crypto.driver.bcrypt'),
			),
			array(
				'crypto.driver.salted_md5',
				array('crypto.driver.bcrypt'),
			),
			array(
				'crypto.driver.phpass',
				array('crypto.driver.salted_md5'),
			),
		);
	}

	/**
	* @dataProvider test_combined_hash_data
	*/
	public function test_combined_hash_password($first_type, $second_type)
	{
		$password = 'foobar';
		$test_word = $password;
		$hash = $this->manager->hash_password($password, $first_type);
		$combined_hash = $this->manager->hash_password($hash, $second_type);

		$time = microtime(true);
		// Limit each test to 1 second
		while ((microtime(true) - $time) < 1)
		{
			$this->assertEquals(($test_word === $password), $this->manager->check_hash($test_word, $combined_hash));
			$test_word = str_shuffle($test_word);
		}
	}
}
