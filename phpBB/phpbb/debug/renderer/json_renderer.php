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

namespace phpbb\debug\renderer;

use phpbb\json_response;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * JSON renderer for exceptions
 */
class json_renderer extends renderer_base
{
	/**
	 * {@inheritDoc}
	 */
	public function decorate(FlattenException $exception): void
	{
		$admin_contact = '';
		if (!empty($this->config['board_contact']))
		{
			$admin_contact = $this->config['board_contact'];
		}
		$message_text = $this->language->lang('EXCEPTION_OCCURRED_AJAX', $admin_contact);

		$json_response = new json_response();
		$json_response->send([
			'title'		=> $this->get_title($exception),
			'message'	=> $message_text,
		]);
	}
}
