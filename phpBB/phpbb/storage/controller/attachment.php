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
use phpbb\content_visibility;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\request\request;
use phpbb\storage\storage;
use phpbb\user;

class attachment extends controller
{
	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var content_visibility */
	protected $content_visibility;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var request */
	protected $request;

	/** @var storage */
	protected $storage;

	/** @var user */
	protected $user;

	public function __construct(auth $auth, service $cache, config $config, $content_visibility, driver_interface $db, dispatcher $dispatcher, request $request, storage $storage, user $user)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->request = $request;
		$this->storage = $storage;
		$this->user = $user;
	}

	public function handle($file)
	{
		$attach_id = $file;
		$mode = $this->request->variable('mode', '');
		$thumbnail = $this->request->variable('t', false);
		global $phpbb_container;

		// Start session management, do not update session page.
		$this->user->session_begin(false);
		$this->auth->acl($this->user->data);
		$this->user->setup('viewtopic');

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
		else if (!$this->download_allowed())
		{
			send_status_line(403, 'Forbidden');
			trigger_error($this->user->lang['LINKAGE_FORBIDDEN']);
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
					$this->phpbb_download_handle_forum_auth($attachment['topic_id']);

					$sql = 'SELECT forum_id, post_visibility
						FROM ' . POSTS_TABLE . '
						WHERE post_id = ' . (int) $attachment['post_msg_id'];
					$result = $this->db->sql_query($sql);
					$post_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$post_row || !$this->content_visibility->is_visible('post', $post_row['forum_id'], $post_row))
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
					$this->phpbb_download_handle_pm_auth( $attachment['post_msg_id']);
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
			else if ($display_cat == ATTACHMENT_CATEGORY_NONE && !$attachment['is_orphan'])
			{
				// Update download count
				$this->phpbb_increment_downloads($attachment['attach_id']);
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
			extract($this->dispatcher->trigger_event('core.download_file_send_to_browser_before', compact($vars)));

			if (!empty($redirect))
			{
				redirect($redirect, false, true);
			}
			else
			{
				$this->send_file_to_browser($attachment, $display_cat);
			}

			$this->file_gc();
		}
	}

	/**
	* Send file to browser
	*/
	protected function send_file_to_browser($attachment, $category)
	{
		$filename = $attachment['physical_filename'];

		if (!$this->storage->exists($filename))
		{
			send_status_line(404, 'Not Found');
			trigger_error('ERROR_NO_ATTACHMENT');
		}

		// Correct the mime type - we force application/octetstream for all files, except images
		// Please do not change this, it is a security precaution
		if ($category != ATTACHMENT_CATEGORY_IMAGE || strpos($attachment['mimetype'], 'image') !== 0)
		{
			$attachment['mimetype'] = (strpos(strtolower($this->user->browser), 'msie') !== false || strpos(strtolower($this->user->browser), 'opera') !== false) ? 'application/octetstream' : 'application/octet-stream';
		}

		if (@ob_get_length())
		{
			@ob_end_clean();
		}

		// Now send the File Contents to the Browser
		try
		{
			$file_info = $this->storage->file_info($filename);
			$size = $file_info->size;
		}
		catch (\Exception $e)
		{
			$size = 0;
		}

		/**
		* Event to alter attachment before it is sent to browser.
		*
		* @event core.send_file_to_browser_before
		* @var	array	attachment	Attachment data
		* @var	int		category	Attachment category
		* @var	string	filename	Path to file, including filename
		* @var	int		size		File size
		* @since 3.1.11-RC1
		*/
		$vars = array(
			'attachment',
			'category',
			'filename',
			'size',
		);
		extract($this->dispatcher->trigger_event('core.send_file_to_browser_before', compact($vars)));

		// To correctly display further errors we need to make sure we are using the correct headers for both (unsetting content-length may not work)

		// Check if headers already sent or not able to get the file contents.
		if (headers_sent())
		{
			send_status_line(500, 'Internal Server Error');
			trigger_error('UNABLE_TO_DELIVER_FILE');
		}

		// Make sure the database record for the filesize is correct
		if ($size > 0 && $size != $attachment['filesize'] && strpos($attachment['physical_filename'], 'thumb_') === false)
		{
			// Update database record
			$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET filesize = ' . (int) $size . '
				WHERE attach_id = ' . (int) $attachment['attach_id'];
			$this->db->sql_query($sql);
		}

		// Now the tricky part... let's dance
		header('Cache-Control: public');

		// Send out the Headers. Do not set Content-Disposition to inline please, it is a security measure for users using the Internet Explorer.
		header('Content-Type: ' . $attachment['mimetype']);

		header('X-Content-Type-Options: nosniff');

		if ($category == ATTACHMENT_CATEGORY_FLASH && $this->request->variable('view', 0) === 1)
		{
			// We use content-disposition: inline for flash files and view=1 to let it correctly play with flash player 10 - any other disposition will fail to play inline
			header('Content-Disposition: inline');
		}
		else
		{
			header('Content-Disposition: ' . ((strpos($attachment['mimetype'], 'image') === 0) ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode(htmlspecialchars_decode($attachment['real_filename'])));

			if (strpos($attachment['mimetype'], 'image') !== 0)
			{
				header('X-Download-Options: noopen');
			}
		}

		if (!$this->set_modified_headers($attachment['filetime'], $this->user->browser))
		{
			if ($size)
			{
				header("Content-Length: $size");
			}

			// Try to deliver in chunks
			@set_time_limit(0);

			$fp = $this->storage->read_stream($filename);

			// Close the db connection before sending the file etc.
			$this->file_gc(false);

			if ($fp !== false)
			{
				$output = fopen('php://output', 'w+b');
				stream_copy_to_stream($fp, $output);
				fclose($fp);
			}

			flush();
		}

		exit;
	}

	/**
	* Handles authentication when downloading attachments from a post or topic
	*
	* @param int $topic_id The id of the topic that we are downloading from
	*
	* @return null
	*/
	protected function phpbb_download_handle_forum_auth($topic_id)
	{
		$sql_array = array(
			'SELECT'	=> 't.topic_visibility, t.forum_id, f.forum_name, f.forum_password, f.parent_id',
			'FROM'		=> array(
				TOPICS_TABLE => 't',
				FORUMS_TABLE => 'f',
			),
			'WHERE'	=> 't.topic_id = ' . (int) $topic_id . '
				AND t.forum_id = f.forum_id',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row && !$this->content_visibility->is_visible('topic', $row['forum_id'], $row))
		{
			send_status_line(404, 'Not Found');
			trigger_error('ERROR_NO_ATTACHMENT');
		}
		else if ($row && $this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']))
		{
			if ($row['forum_password'])
			{
				// Do something else ... ?
				login_forum_box($row);
			}
		}
		else
		{
			send_status_line(403, 'Forbidden');
			trigger_error('SORRY_AUTH_VIEW_ATTACH');
		}
	}

	/**
	* Handles authentication when downloading attachments from PMs
	*
	* @param int $msg_id The id of the PM that we are downloading from
	*
	* @return null
	*/
	protected function phpbb_download_handle_pm_auth($msg_id)
	{
		if (!$this->auth->acl_get('u_pm_download'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('SORRY_AUTH_VIEW_ATTACH');
		}

		$allowed = $this->phpbb_download_check_pm_auth($msg_id);

		/**
		* Event to modify PM attachments download auth
		*
		* @event core.modify_pm_attach_download_auth
		* @var	bool	allowed		Whether the user is allowed to download from that PM or not
		* @var	int		msg_id		The id of the PM to download from
		* @var	int		user_id		The user id for auth check
		* @since 3.1.11-RC1
		*/
		$vars = array('allowed', 'msg_id', 'user_id');
		extract($this->dispatcher->trigger_event('core.modify_pm_attach_download_auth', compact($vars)));

		if (!$allowed)
		{
			send_status_line(403, 'Forbidden');
			trigger_error('ERROR_NO_ATTACHMENT');
		}
	}

	/**
	* Checks whether a user can download from a particular PM
	*
	* @param int $msg_id The id of the PM that we are downloading from
	*
	* @return bool Whether the user is allowed to download from that PM or not
	*/
	protected function phpbb_download_check_pm_auth($msg_id)
	{
		$user_id = $this->user->data['user_id'];

		// Check if the attachment is within the users scope...
		$sql = 'SELECT msg_id
			FROM ' . PRIVMSGS_TO_TABLE . '
			WHERE msg_id = ' . (int) $msg_id . '
				AND (
					user_id = ' . (int) $user_id . '
					OR author_id = ' . (int) $user_id . '
				)';
		$result = $this->db->sql_query_limit($sql, 1);
		$allowed = (bool) $this->db->sql_fetchfield('msg_id');
		$this->db->sql_freeresult($result);

		return $allowed;
	}

	/**
	* Increments the download count of all provided attachments
	*
	* @param array|int $ids The attach_id of each attachment
	*
	* @return null
	*/
	protected function phpbb_increment_downloads($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET download_count = download_count + 1
			WHERE ' . $this->db->sql_in_set('attach_id', $ids);
		$this->db->sql_query($sql);
	}

	/**
	* Check if downloading item is allowed
	*/
	protected function download_allowed()
	{
		if (!$this->config['secure_downloads'])
		{
			return true;
		}

		$url = htmlspecialchars_decode($this->request->header('Referer'));

		if (!$url)
		{
			return ($this->config['secure_allow_empty_referer']) ? true : false;
		}

		// Split URL into domain and script part
		$url = @parse_url($url);

		if ($url === false)
		{
			return ($this->config['secure_allow_empty_referer']) ? true : false;
		}

		$hostname = $url['host'];
		unset($url);

		$allowed = ($this->config['secure_allow_deny']) ? false : true;
		$iplist = array();

		if (($ip_ary = @gethostbynamel($hostname)) !== false)
		{
			foreach ($ip_ary as $ip)
			{
				if ($ip)
				{
					$iplist[] = $ip;
				}
			}
		}

		// Check for own server...
		$server_name = $this->user->host;

		// Forcing server vars is the only way to specify/override the protocol
		if ($this->config['force_server_vars'] || !$server_name)
		{
			$server_name = $this->config['server_name'];
		}

		if (preg_match('#^.*?' . preg_quote($server_name, '#') . '.*?$#i', $hostname))
		{
			$allowed = true;
		}

		// Get IP's and Hostnames
		if (!$allowed)
		{
			$sql = 'SELECT site_ip, site_hostname, ip_exclude
				FROM ' . SITELIST_TABLE;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$site_ip = trim($row['site_ip']);
				$site_hostname = trim($row['site_hostname']);

				if ($site_ip)
				{
					foreach ($iplist as $ip)
					{
						if (preg_match('#^' . str_replace('\*', '.*?', preg_quote($site_ip, '#')) . '$#i', $ip))
						{
							if ($row['ip_exclude'])
							{
								$allowed = ($this->config['secure_allow_deny']) ? false : true;
								break 2;
							}
							else
							{
								$allowed = ($this->config['secure_allow_deny']) ? true : false;
							}
						}
					}
				}

				if ($site_hostname)
				{
					if (preg_match('#^' . str_replace('\*', '.*?', preg_quote($site_hostname, '#')) . '$#i', $hostname))
					{
						if ($row['ip_exclude'])
						{
							$allowed = ($this->config['secure_allow_deny']) ? false : true;
							break;
						}
						else
						{
							$allowed = ($this->config['secure_allow_deny']) ? true : false;
						}
					}
				}
			}
			$this->db->sql_freeresult($result);
		}

		return $allowed;
	}

	/**
	* Check if the browser has the file already and set the appropriate headers-
	* @returns false if a resend is in order.
	*/
	protected function set_modified_headers($stamp, $browser)
	{
		// let's see if we have to send the file at all
		$last_load 	=  $this->request->header('If-Modified-Since') ? strtotime(trim($this->request->header('If-Modified-Since'))) : false;

		if ($last_load !== false && $last_load >= $stamp)
		{
			send_status_line(304, 'Not Modified');
			// seems that we need those too ... browsers
			header('Cache-Control: public');
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
			return true;
		}
		else
		{
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $stamp) . ' GMT');
		}
		return false;
	}
}
