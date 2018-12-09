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

namespace phpbb\template\twig\extension;

class auth extends \Twig_Extension
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth	$auth	Authentication object
	 */
	public function __construct(\phpbb\auth\auth $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Get the name of this extension
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'auth';
	}

	/**
	 * Returns a list of global functions to add to the existing list.
	 *
	 * @return array An array of global functions
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('auth', array($this, 'get_auth')),
			new \Twig_SimpleFunction('auth_global', array($this, 'get_auth_global')),
		);
	}

	/**
	 * Look up permission option(s).
	 *
	 * How to use in a template:
	 * - {{ auth(options, forum_id) }}
	 *
	 * The options are required, either as a single string 'a_' or as a twig array ['a_', 'm_'].
	 * The forum identifier is optional.
	 *
	 * @return bool
	 */
	public function get_auth()
	{
		$args = func_get_args();

		$options = $args[0];
		$forum_id = isset($args[1]) ? (int) $args[1] : 0;

		return is_array($options) ? $this->auth->acl_gets($options, $forum_id) : $this->auth->acl_get($options, $forum_id);
	}

	/**
	 * Look up permission option(s) for any forum
	 *
	 * How to use in a template:
	 * - {{ auth_global(options) }}
	 *
	 * The options are required, either as a single string 'a_' or as a twig array ['a_', 'm_'].
	 *
	 * @return bool
	 */
	public function get_auth_global()
	{
		$args = func_get_args();

		return $this->auth->acl_getf_global($args);
	}
}
