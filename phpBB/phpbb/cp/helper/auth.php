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

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\request\request  */
	protected $request;

	/** @var array Array with enabled extensions */
	protected $extensions;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\extension\manager $ext_manager,
		\phpbb\request\request $request
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->ext_manager	= $ext_manager;
		$this->request		= $request;
	}

	/**
	 * Check the item's authorisation.
	 *
	 * @param string	$auth			The item's authorisation
	 * @param int		$forum_id		The forum identifier
	 * @param int		$topic_id		The topic identifier
	 * @param int		$post_id		The post identifier
	 * @return bool						Whether the current user is allowed to access this item
	 */
	public function check_auth($auth, $forum_id = 0, $topic_id = 0, $post_id = 0)
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

				case ',':
					// Unset "," as that is used to join "$forum_id" with "acl_*"
					unset($tokens[$i]);
				break;

				// Auth: $auth->acl_get() with possible $forum_id
				case (preg_match('#acl_([a-z0-9_]+)#', $token, $match) ? true : false):
					if (
						!empty($tokens[$i + 1])
						&& $tokens[$i + 1] === ','
						&& !empty($tokens[$i + 2])
						&& $tokens[$i + 2] === '$forum_id'
					)
					{
						// We have to make sure a forum id is set,
						// as acl_get() always returns true if the forum id is set to 0.
						$token = $forum_id ? (bool) $this->auth->acl_get($match[1], (int) $forum_id) : false;
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

				// Identifier: $forum_id or !$forum_id (forum_id|topic_id|post_id)
				case (preg_match('#(!)*\$(forum|topic|post)_id#', $token, $match) ? true : false):
					switch ($match[2])
					{
						case 'forum':
							if ($i > 0 && !isset($tokens[$i - 1]))
							{
								// This forum identifier is part of the $this->auth check
								unset($tokens[$i]);

								break;
							}

							$token = (bool) (!empty($match[1]) ? empty($forum_id) : !empty($forum_id));
						break;
						case 'topic':
							$token = (bool) (!empty($match[1]) ? empty($topic_id) : !empty($topic_id));
						break;
						case 'post':
							$token = (bool) (!empty($match[1]) ? empty($post_id) : !empty($post_id));
						break;
						default:
							unset($tokens[$i]);
						break;
					}
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
					if ($this->extensions === null)
					{
						$this->extensions = array_keys($this->ext_manager->all_enabled());
					}

					$token = (bool) in_array($match[1], $this->extensions);
				break;

				// Config auth_method comparison
				case (preg_match('#authmethod_([a-z0-9_\\\\]+)#', $token, $match) ? true : false):
					$token = (bool) ($this->config['auth_method'] === $match[1]);
				break;

				default:
					unset($tokens[$i]);
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

					// Is there an operator
					$switch = !empty($operator[$i]) ? $operator[$i] : '';

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

						default:
							$auth[$i] = $auth[$j];
						break;
					}

					// Unset this depth level
					unset($auth[$j], $operator[$j], $j);
				break;
			}
		}

		return isset($auth[0]) ? (bool) $auth[0] : false;
	}
}
