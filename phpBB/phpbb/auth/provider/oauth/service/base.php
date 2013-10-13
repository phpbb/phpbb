<?php
/**
*
* @package auth
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\auth\provider\oauth\service;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Base OAuth abstract class that all OAuth services should implement
*
* @package auth
*/
abstract class base implements \phpbb\auth\provider\oauth\service\service_interface
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
