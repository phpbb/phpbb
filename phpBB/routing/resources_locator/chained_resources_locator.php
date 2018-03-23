<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license       GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\routing\resources_locator;

class chained_resources_locator implements resources_locator_interface
{
	/**
	 * @var resources_locator_interface[]
	 */
	protected $locators;

	/**
	 * Construct method
	 *
	 * @param resources_locator_interface[]	$locators	Locators
	 */
	public function __construct($locators)
	{
		$this->locators		= $locators;
	}

	/**
	 * {@inheritdoc}
	 */
	public function locate_resources()
	{
		$resources = [];

		foreach ($this->locators as $locator)
		{
			$resources = array_merge($resources, $locator->locate_resources());
		}

		return $resources;
	}
}
