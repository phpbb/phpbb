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

	/**
	* Constructor of crypto driver object
	*
	* @return string	Hash prefix
	*/
	public function __construct(phpbb_config $config)
	{
		$this->config = $config;
	}

	/**
	* @inheritdoc
	*/
	public function is_supported()
	{
		return true;
	}
}
