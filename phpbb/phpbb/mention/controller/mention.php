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

namespace phpbb\mention\controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class mention
{
	/** @var  \phpbb\request\request_interface */
	protected $request;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 */
	public function __construct(\phpbb\request\request_interface $request, $phpbb_root_path, $phpEx)
	{
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	public function handle()
	{
		if (!$this->request->is_ajax())
		{
			redirect(append_sid($this->phpbb_root_path . 'index.' . $this->php_ext));
		}

		$topic_id = $this->request->variable('topic_id', 0);
		// TODO

		return new JsonResponse();
	}
}
