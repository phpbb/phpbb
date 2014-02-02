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
* @package passwords
*/
abstract class base implements driver_interface
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
	* @param \phpbb\config\config $config phpBB config
	* @param \phpbb\passwords\driver\helper $helper Password driver helper
	*/
	public function __construct(\phpbb\config\config $config, helper $helper)
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
}
