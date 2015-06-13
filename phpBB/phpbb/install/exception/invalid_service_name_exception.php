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

namespace phpbb\install\exception;

class invalid_service_name_exception extends installer_exception
{
	/**
	 * @var string
	 */
	private $params;

	/**
	 * @var string
	 */
	private $error;

	/**
	 * Constructor
	 *
	 * @param string	$error	The name of the missing installer module
	 * @param array		$params	Additional values for message translation
	 */
	public function __construct($error, $params = array())
	{
		$this->error = $error;
		$this->params = $params;
	}

	/**
	 * Returns the language entry's name for the error
	 *
	 * @return string
	 */
	public function get_error()
	{
		return $this->error;
	}

	/**
	 * Returns parameters for the language entry, if there is any
	 *
	 * @return array
	 */
	public function get_params()
	{
		return $this->params;
	}

	/**
	 * Returns true, if there are any parameters set
	 *
	 * @return bool
	 */
	public function has_params()
	{
		return (sizeof($this->params) !== 0);
	}
}
