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

namespace phpbb\attachment;

use phpbb\auth\auth;
use phpbb\cache\service;
use phpbb\config\config;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\mimetype\guesser;
use phpbb\plupload\plupload;
use phpbb\user;

/**
 * Attachment upload class
 */
class upload
{
	/** @var auth */
	protected $auth;

	/** @var service */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var \phpbb\files\upload Upload class */
	protected $files_upload;

	/** @var language */
	protected $language;

	/** @var guesser Mimetype guesser */
	protected $mimetype_guesser;

	/** @var dispatcher */
	protected $phpbb_dispatcher;

	/** @var plupload Plupload */
	protected $plupload;

	/** @var user */
	protected $user;

	/** @var \phpbb\files\filespec Current filespec instance */
	private $file;

	/** @var array File data */
	private $file_data = array(
		'error'	=> array()
	);

	/** @var array Extensions array */
	private $extensions;

	/**
	 * Constructor for attachments upload class
	 *
	 * @param auth $auth
	 * @param service $cache
	 * @param config $config
	 * @param \phpbb\files\upload $files_upload
	 * @param language $language
	 * @param guesser $mimetype_guesser
	 * @param dispatcher $phpbb_dispatcher
	 * @param plupload $plupload
	 * @param user $user
	 * @param $phpbb_root_path
	 */
	public function __construct(auth $auth, service $cache, config $config, \phpbb\files\upload $files_upload, language $language, guesser $mimetype_guesser, dispatcher $phpbb_dispatcher, plupload $plupload, user $user, $phpbb_root_path)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->files_upload = $files_upload;
		$this->language = $language;
		$this->mimetype_guesser = $mimetype_guesser;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->plupload = $plupload;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Upload Attachment - filedata is generated here
	 * Uses upload class
	 *
	 * @param string			$form_name		The form name of the file upload input
	 * @param int			$forum_id		The id of the forum
	 * @param bool			$local			Whether the file is local or not
	 * @param string			$local_storage	The path to the local file
	 * @param bool			$is_message		Whether it is a PM or not
	 * @param array		$local_filedata	An file data object created for the local file
	 *
	 * @return array File data array
	 */
	public function upload($form_name, $forum_id, $local = false, $local_storage = '', $is_message = false, $local_filedata = array())
	{
		$this->init_files_upload($forum_id, $is_message);

		$this->file_data['post_attach'] = $local || $this->files_upload->is_valid($form_name);

		if (!$this->file_data['post_attach'])
		{
			$this->file_data['error'][] = $this->language->lang('NO_UPLOAD_FORM_FOUND');
			return $this->file_data;
		}

		$this->file = ($local) ? $this->files_upload->handle_upload('files.types.local', $local_storage, $local_filedata) : $this->files_upload->handle_upload('files.types.form', $form_name);

		if ($this->file->init_error())
		{
			$this->file_data['post_attach'] = false;
			return $this->file_data;
		}

		// Whether the uploaded file is in the image category
		$is_image = (isset($this->extensions[$this->file->get('extension')]['display_cat'])) ? $this->extensions[$this->file->get('extension')]['display_cat'] == ATTACHMENT_CATEGORY_IMAGE : false;

		if (!$this->auth->acl_get('a_') && !$this->auth->acl_get('m_', $forum_id))
		{
			// Check Image Size, if it is an image
			if ($is_image)
			{
				$this->file->upload->set_allowed_dimensions(0, 0, $this->config['img_max_width'], $this->config['img_max_height']);
			}

			// Admins and mods are allowed to exceed the allowed filesize
			if (!empty($this->extensions[$this->file->get('extension')]['max_filesize']))
			{
				$allowed_filesize = $this->extensions[$this->file->get('extension')]['max_filesize'];
			}
			else
			{
				$allowed_filesize = ($is_message) ? $this->config['max_filesize_pm'] : $this->config['max_filesize'];
			}

			$this->file->upload->set_max_filesize($allowed_filesize);
		}

		$this->file->clean_filename('unique', $this->user->data['user_id'] . '_');

		// Are we uploading an image *and* this image being within the image category?
		// Only then perform additional image checks.
		$this->file->move_file($this->config['upload_path'], false, !$is_image);

		// Do we have to create a thumbnail?
		$this->file_data['thumbnail'] = ($is_image && $this->config['img_create_thumbnail']) ? 1 : 0;

		// Make sure the image category only holds valid images...
		$this->check_image($is_image);

		if (count($this->file->error))
		{
			$this->file->remove();
			$this->file_data['error'] = array_merge($this->file_data['error'], $this->file->error);
			$this->file_data['post_attach'] = false;

			return $this->file_data;
		}

		$this->fill_file_data();

		$filedata = $this->file_data;

		/**
		 * Event to modify uploaded file before submit to the post
		 *
		 * @event core.modify_uploaded_file
		 * @var	array	filedata	Array containing uploaded file data
		 * @var	bool	is_image	Flag indicating if the file is an image
		 * @since 3.1.0-RC3
		 */
		$vars = array(
			'filedata',
			'is_image',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.modify_uploaded_file', compact($vars)));
		$this->file_data = $filedata;
		unset($filedata);

		// Check for attachment quota and free space
		if (!$this->check_attach_quota() || !$this->check_disk_space())
		{
			return $this->file_data;
		}

		// Create Thumbnail
		$this->create_thumbnail();

		return $this->file_data;
	}

	/**
	 * Create thumbnail for file if necessary
	 *
	 * @return array Updated $filedata
	 */
	protected function create_thumbnail()
	{
		if ($this->file_data['thumbnail'])
		{
			$source = $this->file->get('destination_file');
			$destination = $this->file->get('destination_path') . '/thumb_' . $this->file->get('realname');

			if (!create_thumbnail($source, $destination, $this->file->get('mimetype')))
			{
				$this->file_data['thumbnail'] = 0;
			}
		}
	}

	/**
	 * Init files upload class
	 *
	 * @param int $forum_id Forum ID
	 * @param bool $is_message Whether attachment is inside PM or not
	 */
	protected function init_files_upload($forum_id, $is_message)
	{
		if ($this->config['check_attachment_content'] && isset($this->config['mime_triggers']))
		{
			$this->files_upload->set_disallowed_content(explode('|', $this->config['mime_triggers']));
		}
		else if (!$this->config['check_attachment_content'])
		{
			$this->files_upload->set_disallowed_content(array());
		}

		$this->extensions = $this->cache->obtain_attach_extensions((($is_message) ? false : (int) $forum_id));
		$this->files_upload->set_allowed_extensions(array_keys($this->extensions['_allowed_']));
	}

	/**
	 * Check if uploaded file is really an image
	 *
	 * @param bool $is_image Whether file is image
	 */
	protected function check_image($is_image)
	{
		// Make sure the image category only holds valid images...
		if ($is_image && !$this->file->is_image())
		{
			$this->file->remove();

			if ($this->plupload && $this->plupload->is_active())
			{
				$this->plupload->emit_error(104, 'ATTACHED_IMAGE_NOT_IMAGE');
			}

			// If this error occurs a user tried to exploit an IE Bug by renaming extensions
			// Since the image category is displaying content inline we need to catch this.
			$this->file->set_error($this->language->lang('ATTACHED_IMAGE_NOT_IMAGE'));
		}
	}

	/**
	 * Check if attachment quota was reached
	 *
	 * @return bool False if attachment quota was reached, true if not
	 */
	protected function check_attach_quota()
	{
		if ($this->config['attachment_quota'])
		{
			if (intval($this->config['upload_dir_size']) + $this->file->get('filesize') > $this->config['attachment_quota'])
			{
				$this->file_data['error'][] = $this->language->lang('ATTACH_QUOTA_REACHED');
				$this->file_data['post_attach'] = false;

				$this->file->remove();

				return false;
			}
		}

		return true;
	}

	/**
	 * Check if there is enough free space available on disk
	 *
	 * @return bool True if disk space is available, false if not
	 */
	protected function check_disk_space()
	{
		if (function_exists('disk_free_space'))
		{
			$free_space = @disk_free_space($this->phpbb_root_path);

			if ($free_space <= $this->file->get('filesize'))
			{
				if ($this->auth->acl_get('a_'))
				{
					$this->file_data['error'][] = $this->language->lang('ATTACH_DISK_FULL');
				}
				else
				{
					$this->file_data['error'][] = $this->language->lang('ATTACH_QUOTA_REACHED');
				}
				$this->file_data['post_attach'] = false;

				$this->file->remove();

				return false;
			}
		}

		return true;
	}

	/**
	 * Fills file data with file information and current time as filetime
	 */
	protected function fill_file_data()
	{
		$this->file_data['filesize'] = $this->file->get('filesize');
		$this->file_data['mimetype'] = $this->file->get('mimetype');
		$this->file_data['extension'] = $this->file->get('extension');
		$this->file_data['physical_filename'] = $this->file->get('realname');
		$this->file_data['real_filename'] = $this->file->get('uploadname');
		$this->file_data['filetime'] = time();
	}
}
