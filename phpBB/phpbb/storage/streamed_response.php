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

namespace phpbb\storage;

use Symfony\Component\HttpFoundation\StreamedResponse;

// Temporal fix for: https://github.com/symfony/symfony/issues/27924
class streamed_response extends StreamedResponse
{
	/**
	 * {@inheritdoc}
	 */
	public function setNotModified()
	{
		$this->setCallback(function () {});

		return parent::setNotModified();
	}
}
