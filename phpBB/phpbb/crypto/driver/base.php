<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package crypto
*/
abstract class phpbb_crypto_driver_base implements phpbb_crypto_driver_interface
{
	/** @var phpbb_config */
	protected $config;

	/** @var phpbb_crypto_driver_helper */
	protected $helper;

	/** @var driver name */
	protected $name;

	/**
	* Constructor of crypto driver object
	*
	* @return string	Hash prefix
	*/
	public function __construct(phpbb_config $config)
	{
		$this->config = $config;
		$this->helper = new phpbb_crypto_driver_helper($this);
	}

	/**
	* @inheritdoc
	*/
	public function is_supported()
	{
		return true;
	}

	/**
	* @inheritdoc
	*/
	public function get_name()
	{
		return $this->name;
	}

	/**
	* Set driver name
	*
	* @param string $name Driver name
	*/
	public function set_name($name)
	{
		$this->name = $name;
	}
}
