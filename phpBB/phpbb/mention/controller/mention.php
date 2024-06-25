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

use phpbb\di\service_collection;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class mention
{
	/** @var service_collection */
	protected $mention_sources;

	/** @var  request_interface */
	protected $request;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param service_collection|array $mention_sources
	 * @param request_interface $request
	 * @param string $phpbb_root_path
	 * @param string $phpEx
	 */
	public function __construct($mention_sources, request_interface $request, string $phpbb_root_path, string $phpEx)
	{
		$this->mention_sources = $mention_sources;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	/**
	 * Handle requests to mention controller
	 *
	 * @return JsonResponse|RedirectResponse
	 */
	public function handle()
	{
		if (!$this->request->is_ajax())
		{
			return new RedirectResponse(append_sid($this->phpbb_root_path . 'index.' . $this->php_ext));
		}

		$keyword = $this->request->variable('keyword', '', true);
		$topic_id = $this->request->variable('topic_id', 0);
		$names = [];
		$has_names_remaining = false;

		foreach ($this->mention_sources as $source)
		{
			$has_names_remaining = !$source->get($names, $keyword, $topic_id) || $has_names_remaining;
		}

		return new JsonResponse([
			'names' => array_values($names),
			'all' => !$has_names_remaining,
		]);
	}
}
