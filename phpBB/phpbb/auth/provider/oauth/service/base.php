<?php
/**
*
* @package auth
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
* Bitly OAuth service
*
* @package auth
*/
abstract class phpbb_auth_provider_oauth_service_base implements phpbb_auth_provider_oauth_service_interface
{
	/**
	* External OAuth service provider
	*
	* @var \OAuth\Common\Service\ServiceInterface
	*/
	protected $service_provider;

	/**
	* {@inheritdoc}
	*/
	public function get_external_service_provider()
	{
		return $this->service_provider;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_auth_scope()
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function set_external_service_provider(\OAuth\Common\Service\ServiceInterface $service_provider)
	{
		$this->service_provider = $service_provider;
	}
}
