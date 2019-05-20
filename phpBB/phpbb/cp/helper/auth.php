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

namespace phpbb\cp\helper;

class auth
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config  */
	protected $config;

	/** @var \phpbb\event\dispatcher  */
	protected $dispatcher;

	/** @var \phpbb\request\request  */
	protected $request;

	/** @var array Array with enabled extensions */
	protected $extensions;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\extension\manager $ext_manager,
		\phpbb\request\request $request
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->dispatcher	= $dispatcher;
		$this->request		= $request;

		$this->extensions	= array_keys($ext_manager->all_enabled());
	}

	/**
	 * Check the item's authorisation.
	 *
	 * @param string	$auth			The item's authorisation
	 * @param int		$forum_id		The forum identifier
	 * @return bool						Whether the current user is allowed to access this item
	 */
	public function check_auth($auth, $forum_id = 0)
	{
		$auth = trim($auth);

		if (empty($auth))
		{
			return true;
		}

		preg_match_all(
			'/(?:
				"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
				\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
				[(),]                                  |
				[^\s(),]+
			)/x',
			$auth,
			$matches
		);

		$tokens = $matches[0];
		for ($i = 0, $size = count($tokens); $i < $size; $i++)
		{
			// Make sure we are not transferring preg_match data
			unset($match);

			// Get the current value by reference
			$token = &$tokens[$i];

			switch ($token)
			{
				case ')':
				case '(':
				case '&&':
				case '||':
					// Preserve operators
				break;

				// Unset "," as that is used to join "$id" with "acl_*"
				case ',':
					unset($tokens[$i]);
				break;

				// Auth: $auth->acl_get() with possible $forum_id
				case (preg_match('#acl_([a-z0-9_]+)#', $token, $match) ? true : false):
					if (
						!empty($tokens[$i + 1])
						&& $tokens[$i + 1] === ','
						&& !empty($tokens[$i + 2])
						&& $tokens[$i + 2] === '$id'
					)
					{
						$token = (bool) $this->auth->acl_get($match[1], (int) $forum_id);
					}
					else
					{
						$token = (bool) $this->auth->acl_get($match[1]);
					}
				break;

				// Auth global: $auth->acl_getf_global()
				case (preg_match('#aclf_([a-z0-9_]+)#', $token, $match) ? true : false):
					$token = (bool) $this->auth->acl_getf_global($match[1]);
				break;

				// Forum identifier: $id or !$id
				case (preg_match('#(!)*\$id#', $token, $match) ? true : false):
					$token = (bool) ($match[1] === '!' ? empty($forum_id) : !empty($forum_id));
				break;

				// Config setting: $config['']
				case (preg_match('#cfg_([a-z0-9_]+)#', $token, $match) ? true : false):
					$token = (bool) $this->config[$match[1]];
				break;

				// Request variable: $request->variable('', false)
				case (preg_match('#request_([a-zA-Z0-9_]+)#', $token, $match) ? true : false):
					$token = (bool) $this->request->variable($match[1], false);
				break;

				// Extension is enabled
				case (preg_match('#ext_([a-zA-Z0-9_/]+)#', $token, $match) ? true : false):
					$token = (bool) in_array($match[1], $this->extensions);
				break;

				// Config auth_method comparison
				case (preg_match('#authmethod_([a-z0-9_\\\\]+)#', $token, $match) ? true : false):
					$token = (bool) ($this->config['auth_method'] === $match[1]);
				break;

				default:
					$auth_token = '';

					/**
					 * Check custom tokens for control panel's item authorisation.
					 *
					 * Previously core.module_auth
					 *
					 * @event core.cp_item_auth
					 * @var string	auth_token		Set to a boolean
					 * @var string	item_auth		The item's auth string
					 * @var int		forum_id		The current forum identifier
					 * @since 4.0.0
					 */
					$vars = ['auth_token', 'item_auth', 'forum_id'];
					extract($this->dispatcher->trigger_event('core.cp_item_auth', compact($vars)));

					switch (true)
					{
						case $auth_token === true:
							$token = true;
						break;

						case $auth_token === false:
							$token = false;
						break;

						default:
							unset($tokens[$i]);
						break;
					}
				break;
			}
		}

		return $this->array_reduce_auth($tokens);
	}

	/**
	 * Reduce an array with operators and booleans to a single boolean.
	 *
	 * @param array		$array		Array produced by check_auth()
	 * @return bool					The reduced boolean value
	 */
	protected function array_reduce_auth(array $array)
	{
		$i = 0;
		$auth = [];
		$operator = [];

		foreach ($array as $value)
		{
			switch (true)
			{
				/** @noinspection PhpMissingBreakStatementInspection */
				case ($value === false):
					// If at base level
					// and the value is "false"
					// and the operator is "&&"
					// the authorisation is "false"
					if ($i === 0 && isset($operator[$i]) && $operator[$i] === '&&')
					{
						return false;
					}
				# no break;
				case ($value === true):
					// Is there an operator
					$switch = !empty($operator[$i]) ? $operator[$i] : '';

					switch ($switch)
					{
						case '||':
							// Preserve a "true" value
							$auth[$i] = $auth[$i] ? $auth[$i] : $value;
						break;

						case '&&';
							// Preserve a "false" value
							$auth[$i] = !$auth[$i] ? $auth[$i] : $value;
						break;

						default:
							$auth[$i] = $value;
						break;
					}
				break;

				// Set the operator
				case ($value === '&&'):
				case ($value === '||'):
					$operator[$i] = $value;
				break;

				// Open new depth level
				case ($value === '('):
					$i++;
				break;

				// Close depth level
				case ($value === ')'):
					$j = $i--;

					// Is there an operator, otherwise default to "and"
					$switch = !empty($operator[$i]) ? $operator[$i] : '&&';

					switch ($switch)
					{
						case '||':
							// Preserve a "true" value
							$auth[$i] = $auth[$i] ? $auth[$i] : $auth[$j];
						break;

						case '&&':
							// If back to base level
							// and the depth level auth is "false"
							// and the operator is "&&"
							// the authorisation is "false"
							if ($i === 0 && $auth[$j] === false)
							{
								return false;
							}

							// Preserve a "false" value
							$auth[$i] = !$auth[$i] ? $auth[$i] : $auth[$j];
						break;
					}

					// Unset this depth level
					unset ($auth[$j], $operator[$j], $j);
				break;
			}
		}

		return isset($auth[0]) ? (bool) $auth[0] : false;
	}
}
