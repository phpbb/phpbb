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

namespace phpbb\ban\type;

class email extends base
{
	/**
	 * {@inheritDoc}
	 */
	public function get_type()
	{
		return 'email';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_user_column()
	{
		return 'user_email';
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_for_storage(array $items)
	{
		if (!$this->get_excluded())
		{
			// TODO throw exception
		}
		$regex = get_preg_expression('email');

		$ban_items = [];
		foreach ($items as $item)
		{
			$item = trim($item);
			if (strlen($item) > 100 || preg_match($regex, $item) || in_array($item, $this->excluded))
			{
				continue;
			}
			$ban_items[] = $item;
		}

		if (empty($ban_items))
		{
			// TODO throw exception - no valid emails defined
		}

		return $ban_items;
	}
}
