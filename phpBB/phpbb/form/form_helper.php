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

namespace phpbb\form;

use phpbb\config\config;
use phpbb\request\request_interface;
use phpbb\user;

class form_helper
{
	/** @var config */
	protected $config;

	/** @var request_interface  */
	protected $request;

	/** @var user */
	protected $user;

	/**
	 * Constructor for form_helper
	 *
	 * @param config $config
	 * @param request_interface $request
	 * @param user $user
	 */
	public function __construct(config $config, request_interface $request, user $user)
	{
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
	}

	/**
	 * Get form tokens for form
	 *
	 * @param string $form_name Name of form
	 * @param int|null $now Token generation time
	 * @param string|null $token_sid SID used for form token
	 * @param string|null $token Generated token
	 *
	 * @return array Array containing form_token and creation_time of form token
	 */
	public function get_form_tokens(string $form_name, ?int &$now = 0, ?string &$token_sid = '', ?string &$token = ''): array
	{
		$now = time();
		$token_sid = ($this->user->data['user_id'] == ANONYMOUS && !empty($this->config['form_token_sid_guests'])) ? $this->user->session_id : '';
		$token = sha1($now . $this->user->data['user_form_salt'] . $form_name . $token_sid);

		return [
			'creation_time' => $now,
			'form_token'	=> $token,
		];
	}

	/**
	 * Check form token for form
	 *
	 * @param string $form_name Name of form
	 * @param int|null $timespan Lifetime of token or null if default value should be used
	 * @return bool True if form token is valid, false if not
	 */
	public function check_form_tokens(string $form_name, ?int $timespan = null): bool
	{
		if ($timespan === null)
		{
			// we enforce a minimum value of half a minute here.
			$timespan = ($this->config['form_token_lifetime'] == -1) ? -1 : max(30, $this->config['form_token_lifetime']);
		}

		if ($this->request->is_set_post('creation_time') && $this->request->is_set_post('form_token'))
		{
			$creation_time	= abs($this->request->variable('creation_time', 0));
			$token = $this->request->variable('form_token', '');

			$diff = time() - $creation_time;

			// If creation_time and the time() now is zero we can assume it was not a human doing this (the check for if ($diff)...
			if (defined('DEBUG_TEST') || $diff && ($diff <= $timespan || $timespan === -1))
			{
				$token_sid = ($this->user->data['user_id'] == ANONYMOUS && !empty($this->config['form_token_sid_guests'])) ? $this->user->session_id : '';
				$key = sha1($creation_time . $this->user->data['user_form_salt'] . $form_name . $token_sid);

				if (hash_equals($key, $token))
				{
					return true;
				}
			}
		}

		return false;
	}
}
