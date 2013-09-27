<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\passwords\driver;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package passwords
*/
abstract class base implements \phpbb\passwords\driver\driver_interface
{
	/** @var phpbb\config\config */
	protected $config;

	/** @var phpbb\passwords\driver\helper */
	protected $helper;

	/** @var driver name */
	protected $name;

	/**
	* Constructor of passwords driver object
	*
	* @return string	Hash prefix
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\passwords\driver\helper $helper)
	{
		$this->config = $config;
		$this->helper = $helper;
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
