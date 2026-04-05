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

namespace phpbb\bookmark\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\exception\http_exception;
use phpbb\request\request_interface;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class add
{
	/** @var config */
	protected $config;

	/** @var auth */
	protected $auth;

	/** @var driver_interface */
	protected $db;

	/** @var request_interface */
	protected $request;

	/** @var user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	public function __construct(config $config, auth $auth, driver_interface $db, request_interface $request, user $user, string $phpbb_root_path, string $php_ext)
	{
		$this->config = $config;
		$this->auth = $auth;
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function handle(int $topic_id): Response
	{
		if (!$this->config['allow_bookmarks'])
		{
			throw new http_exception(404, 'PAGE_NOT_FOUND');
		}

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			if ($this->request->is_ajax())
			{
				throw new http_exception(403, 'LOGIN_REQUIRED');
			}
			login_box('', $this->user->lang('LOGIN_REQUIRED'));
		}

		if (!check_link_hash($this->request->variable('hash', ''), "topic_$topic_id"))
		{
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		$forum_id = $this->get_forum_id($topic_id);
		if (!$forum_id || !$this->auth->acl_get('f_read', $forum_id))
		{
			throw new http_exception(404, 'NO_TOPIC');
		}

		$sql = 'SELECT topic_id
			FROM ' . BOOKMARKS_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . '
				AND topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$bookmarked = (bool) $this->db->sql_fetchfield('topic_id');
		$this->db->sql_freeresult($result);

		if (!$bookmarked)
		{
			$sql = 'INSERT INTO ' . BOOKMARKS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
				'user_id' => (int) $this->user->data['user_id'],
				'topic_id' => (int) $topic_id,
			));
			$this->db->sql_query($sql);
		}

		if ($this->request->is_ajax())
		{
			return new JsonResponse(array(
				'success' => true,
			));
		}

		// TODO: Maybe use meta_refresh and trigger_error again for no-ajax
		return new RedirectResponse(append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", 't=' . $topic_id));
	}

	// TODO: phpbb/forum/helper
	protected function get_forum_id(int $topic_id): int
	{
		$sql = 'SELECT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$forum_id = (int) $this->db->sql_fetchfield('forum_id');
		$this->db->sql_freeresult($result);

		return $forum_id;
	}
}
