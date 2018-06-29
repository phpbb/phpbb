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

namespace phpbb\storage\controller;

use phpbb\auth\auth;
use phpbb\cache\service;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\request\request;
use phpbb\storage\storage;
use phpbb\user;

class attachment extends controller
{
	/** @var auth */
	protected $auth;

	/** @var service */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var dispatcher */
	protected $phpbb_dispatcher;

	/** @var request */
	protected $request;

	/** @var storage */
	protected $storage;

	/** @var user */
	protected $user;

	public function __construct(auth $auth, service $cache, config $config, driver_interface $db, dispatcher $phpbb_dispatcher, request $request, storage $storage, user $user)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->request = $request;
		$this->storage = $storage;
		$this->user = $user;
	}

	public function handle($file)
	{
		global $phpbb_root_path, $phpEx, $phpbb_container;
		require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);

		$attach_id = $file;
		$mode = $this->request->variable('mode', '');
		$thumbnail = $this->request->variable('t', false);

		// Start session management, do not update session page.
		$this->user->session_begin(false);
		$this->auth->acl($this->user->data);
		$this->user->setup('viewtopic');

		$phpbb_content_visibility = $phpbb_container->get('content.visibility');

		if (!$this->config['allow_attachments'] && !$this->config['allow_pm_attach'])
		{
			send_status_line(404, 'Not Found');
			trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
		}

		if (!$attach_id)
		{
			send_status_line(404, 'Not Found');
			trigger_error('NO_ATTACHMENT_SELECTED');
		}

		$sql = 'SELECT attach_id, post_msg_id, topic_id, in_message, poster_id, is_orphan, physical_filename, real_filename, extension, mimetype, filesize, filetime
			FROM ' . ATTACHMENTS_TABLE . "
			WHERE attach_id = $attach_id";
		$result = $this->db->sql_query($sql);
		$attachment = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$attachment)
		{
			send_status_line(404, 'Not Found');
			trigger_error('ERROR_NO_ATTACHMENT');
		}
		else if (!download_allowed())
		{
			send_status_line(403, 'Forbidden');
			trigger_error($user->lang['LINKAGE_FORBIDDEN']);
		}
		else
		{
			$attachment['physical_filename'] = utf8_basename($attachment['physical_filename']);

			if (!$attachment['in_message'] && !$this->config['allow_attachments'] || $attachment['in_message'] && !$this->config['allow_pm_attach'])
			{
				send_status_line(404, 'Not Found');
				trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
			}

			if ($attachment['is_orphan'])
			{
				// We allow admins having attachment permissions to see orphan attachments...
				$own_attachment = ($this->auth->acl_get('a_attach') || $attachment['poster_id'] == $this->user->data['user_id']) ? true : false;

				if (!$own_attachment || ($attachment['in_message'] && !$this->auth->acl_get('u_pm_download')) || (!$attachment['in_message'] && !$this->auth->acl_get('u_download')))
				{
					send_status_line(404, 'Not Found');
					trigger_error('ERROR_NO_ATTACHMENT');
				}

				// Obtain all extensions...
				$extensions = $this->cache->obtain_attach_extensions(true);
			}
			else
			{
				if (!$attachment['in_message'])
				{
					phpbb_download_handle_forum_auth($this->db, $this->auth, $attachment['topic_id']);

					$sql = 'SELECT forum_id, post_visibility
						FROM ' . POSTS_TABLE . '
						WHERE post_id = ' . (int) $attachment['post_msg_id'];
					$result = $this->db->sql_query($sql);
					$post_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$post_row || !$phpbb_content_visibility->is_visible('post', $post_row['forum_id'], $post_row))
					{
						// Attachment of a soft deleted post and the user is not allowed to see the post
						send_status_line(404, 'Not Found');
						trigger_error('ERROR_NO_ATTACHMENT');
					}
				}
				else
				{
					// Attachment is in a private message.
					$post_row = array('forum_id' => false);
					phpbb_download_handle_pm_auth($this->db, $this->auth, $this->user->data['user_id'], $attachment['post_msg_id']);
				}

				$extensions = array();
				if (!extension_allowed($post_row['forum_id'], $attachment['extension'], $extensions))
				{
					send_status_line(403, 'Forbidden');
					trigger_error(sprintf($this->user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
				}
			}

			$display_cat = $extensions[$attachment['extension']]['display_cat'];

			if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$this->user->optionget('viewimg'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			if ($display_cat == ATTACHMENT_CATEGORY_FLASH && !$this->user->optionget('viewflash'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			if ($thumbnail)
			{
				$attachment['physical_filename'] = 'thumb_' . $attachment['physical_filename'];
			}
			else if ($display_cat == ATTACHMENT_CATEGORY_NONE && !$attachment['is_orphan'] && !phpbb_http_byte_range($attachment['filesize']))
			{
				// Update download count
				phpbb_increment_downloads($this->db, $attachment['attach_id']);
			}

			$redirect = '';

			/**
			* Event to modify data before sending file to browser
			*
			* @event core.download_file_send_to_browser_before
			* @var	int		attach_id			The attachment ID
			* @var	array	attachment			Array with attachment data
			* @var	int		display_cat			Attachment category
			* @var	array	extensions			Array with file extensions data
			* @var	string	mode				Download mode
			* @var	bool	thumbnail			Flag indicating if the file is a thumbnail
			* @var	string	redirect			Do a redirection instead of reading the file
			* @since 3.1.6-RC1
			* @changed 3.1.7-RC1	Fixing wrong name of a variable (replacing "extension" by "extensions")
			* @changed 3.3.0-a1		Add redirect variable
			*/
			$vars = array(
				'attach_id',
				'attachment',
				'display_cat',
				'extensions',
				'mode',
				'thumbnail',
				'redirect',
			);
			extract($this->phpbb_dispatcher->trigger_event('core.download_file_send_to_browser_before', compact($vars)));

			if (!empty($redirect))
			{
				redirect($redirect, false, true);
			}
			else
			{
				send_file_to_browser($attachment, $display_cat);
			}

			file_gc();
		}
	}
}
