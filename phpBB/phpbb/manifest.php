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
use Symfony\Component\HttpFoundation\JsonResponse;

class manifest
{
	/** @var config */
	protected $config;

	/** @var dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var user */
	protected $user;

	/**
	 * Constructor for manifest controller
	 *
	 * @param config $config
	 * @param dispatcher_interface $phpbb_dispatcher
	 * @param user $user
	 */
	public function __construct(config $config, dispatcher_interface $phpbb_dispatcher, user $user)
	{
		$this->config = $config;
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
		// Get the board URL and extract the path component
		$board_path = $this->config['force_server_vars'] ? $this->config['script_path'] : (parse_url(generate_board_url())['path'] ?? '');

		// Ensure path ends with '/' for PWA scope
		$scope = rtrim($board_path, '/\\') . '/';
		$start_url = $scope;

		$sitename = html_entity_decode($this->config['sitename'], ENT_QUOTES, 'UTF-8');
		$sitename_short = html_entity_decode($this->config['sitename_short'], ENT_QUOTES, 'UTF-8');

		$manifest = [
			'name'			=> $sitename,
			'short_name'	=> $sitename_short ?: utf8_substr($sitename, 0, 12),
			'display'		=> 'standalone',
			'orientation'	=> 'portrait',
			'start_url'		=> $start_url,
			'scope'			=> $scope,
		];

		/**
		 * Event to modify manifest data before it is outputted
		 *
		 * @event core.modify_manifest
		 * @var	array	manifest	    Array of manifest members
		 * @var string  scope           PWA scope path
		 * @var string  start_url       PWA start URL
		 * @var	string	sitename	    Full name of the board
		 * @var	string	sitename_short	Shortened name of the board
		 * @since 4.0.0-a1
		 */
		$vars = ['manifest', 'scope', 'start_url', 'sitename', 'sitename_short'];
		extract($this->phpbb_dispatcher->trigger_event('core.modify_manifest', compact($vars)));

		$response = new JsonResponse($manifest);
		$response->setPublic();
		$response->setMaxAge(3600);
		$response->headers->addCacheControlDirective('must-revalidate', true);

		if (!empty($this->user->data['is_bot']))
		{
			// Let reverse proxies know we detected a bot.
			$response->headers->set('X-PHPBB-IS-BOT', 'yes');
		}

		return $response;
	}
}
