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

namespace phpbb\messenger\method;

/**
 * Messenger method interface class
 */
interface messenger_interface
{
	/** @var int Email notify method used */
	public const NOTIFY_EMAIL = 0;

	/** @var int Instant messaging (Jabber) notify method used */
	public const NOTIFY_IM = 1;

	/** @var int Both notify methods used */
	public const NOTIFY_BOTH = 2;

	/**
	 * Get messenger method id
	 *
	 * @return int
	 */
	public function get_id(): int;

	/**
	 * Check if the messenger method is enabled
	 *
	 * @return bool
	 */
	public function is_enabled(): bool;

	/**
	 * Set up subject for the message
	 *
	 * @param string	$subject	Email subject
	 *
	 * @return void
	 */
	public function subject(string $subject = ''): void;

	/**
	 * Send out messages
	 *
	 * @return bool
	 */
	public function send(): bool;

	/**
	 * Add error message to log
	 *
	 * @param string	$msg	Error message text
	 *
	 * @return void
	 */

	public function error(string $msg): void;

	/**
	 * Add message header
	 *
	 * @param string	$header_name	Message header name
	 * @param mixed		$header_value	Message header value
	 *
	 * @return void
	 */
	public function header(string $header_name, mixed $header_value): void;
}
