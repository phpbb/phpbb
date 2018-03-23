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

namespace phpbb\template\twig\extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class routing extends RoutingExtension
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	* Constructor
	*
	* @param \phpbb\routing\helper $helper
	*/
	public function __construct(\phpbb\routing\helper $helper)
	{
		$this->helper = $helper;
	}

	public function getPath($name, $parameters = array(), $relative = false)
	{
		return $this->helper->route($name, $parameters, true, false, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
	}

	public function getUrl($name, $parameters = array(), $schemeRelative = false)
	{
		return $this->helper->route($name, $parameters, true, false, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
	}
}
