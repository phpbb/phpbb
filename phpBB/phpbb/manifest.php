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

namespace phpbb;

use phpbb\config\config;
use phpbb\event\dispatcher_interface;
use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class manifest
{
	/** @var config */
	protected $config;

	/** @var path_helper */
	protected $path_helper;

	/** @var dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var user */
	protected $user;

	/**
	 * Constructor for manifest controller
	 *
	 * @param config $config
	 * @param path_helper $path_helper
	 * @param dispatcher_interface $phpbb_dispatcher
	 * @param user $user
	 */
	public function __construct(config $config, path_helper $path_helper, dispatcher_interface $phpbb_dispatcher, user $user)
	{
		$this->config = $config;
		$this->path_helper = $path_helper;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->user = $user;
	}

	/**
	 * Handle creation of a manifest json file for progressive web-app support
	 *
	 * @return JsonResponse
	 */
	public function handle(): JsonResponse
	{
		if ($this->user->data['is_bot'])
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'NO_AUTH_OPERATION');
		}

		$board_path = $this->config['force_server_vars'] ? $this->config['script_path'] : $this->path_helper->get_web_root_path();

		$sitename = html_entity_decode($this->config['sitename'], ENT_QUOTES, 'UTF-8');
		$sitename_short = html_entity_decode($this->config['sitename_short'], ENT_QUOTES, 'UTF-8');

		$manifest = [
			'name'			=> $sitename,
			'short_name'	=> $sitename_short ?: utf8_substr($sitename, 0, 12),
			'display'		=> 'standalone',
			'orientation'	=> 'portrait',
			'start_url'		=> $board_path,
			'scope'			=> $board_path,
		];

		/**
		 * Event to modify manifest data before it is outputted
		 *
		 * @event core.modify_manifest
		 * @var	array	manifest	Array of manifest members
		 * @var	string	board_path	Path to the board root
		 * @since 4.0.0-a1
		 */
		$vars = array('manifest', 'board_path');
		extract($this->phpbb_dispatcher->trigger_event('core.modify_manifest', compact($vars)));

		$response = new JsonResponse($manifest);
		$response->setPublic();
		$response->setMaxAge(3600);
		$response->headers->addCacheControlDirective('must-revalidate', true);
		return $response;
	}
}
