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
use Symfony\Component\HttpFoundation\RedirectResponse;

class mention
{
	/** @var \phpbb\di\service_collection */
	protected $mention_sources;

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
	public function __construct($mention_sources, \phpbb\request\request_interface $request, $phpbb_root_path, $phpEx)
	{
		$this->mention_sources = $mention_sources;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	public function handle()
	{
		if (!$this->request->is_ajax())
		{
			new RedirectResponse(append_sid($this->phpbb_root_path . 'index.' . $this->php_ext));
		}

		$keyword = $this->request->variable('keyword', '', true);
		$topic_id = $this->request->variable('topic_id', 0);
		$names = [];

		foreach ($this->mention_sources as $source)
		{
			$source->get($names, $keyword, $topic_id);
		}

		return new JsonResponse(array_values($names));
	}
}
