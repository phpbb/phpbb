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

namespace phpbb\viewtopic\exception;

use phpbb\exception\runtime_exception;

/**
 * Exception for when the post is not visible but the topic is.
 */
class post_invisible_exception extends runtime_exception
{
	/**
	 * @var int
	 */
	private $topic_id;

	/**
	 * Constructor
	 *
	 * @param string		$message	The Exception message to throw (must be a language variable).
	 * @param array			$parameters	The parameters to use with the language var.
	 * @param \Exception	$previous	The previous runtime_exception used for the runtime_exception chaining.
	 * @param integer		$code		The Exception code.
	 */
	public function __construct(int $topic_id, string $message = '', array $parameters = array(), \Exception $previous = null, int $code = 0)
	{
		$this->topic_id = $topic_id;

		parent::__construct($message, $parameters, $previous, $code);
	}

	/**
	 * Returns the topic ID of the post.
	 *
	 * @return int The ID of the topic where the post is.
	 */
	public function get_topic_id()
	{
		return $this->topic_id;
	}
}
