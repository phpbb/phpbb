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

namespace phpbb\passwords\driver;

abstract class base_native extends base
{
	/**
	* Return the constant name for this driver's algorithm
	*
	* @link https://www.php.net/manual/en/password.constants.php
	*
	* @return string
	*/
	abstract public function get_algo_name();

	/**
	* Return the options set for this driver instance
	*
	* @return array
	*/
	abstract public function get_options();

	/**
	* {@inheritdoc}
	*/
	public function check($password, $hash, $user_row = [])
	{
		return password_verify($password, $hash);
	}

	/**
	* Return the value for this driver's algorithm
	*
	* @return integer
	*/
	public function get_algo_value()
	{
		return constant($this->get_algo_name());
	}

	/**
	* {@inheritdoc}
	*/
	public function hash($password)
	{
		return password_hash($password, $this->get_algo_value(), $this->get_options());
	}

	/**
	* {@inheritdoc}
	*/
	public function is_supported()
	{
		return defined($this->get_algo_name()) && function_exists('password_hash') && function_exists('password_needs_rehash') && function_exists('password_verify');
	}

	/**
	* {@inheritdoc}
	*/
	public function needs_rehash($hash)
	{
		return password_needs_rehash($hash, $this->get_algo_value(), $this->get_options());
	}
}
