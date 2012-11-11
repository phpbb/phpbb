<?php
/**
 *
 * @package phpbb_plupload
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * This class handles all server-side plupload functions
 *
 * @package phpbb_plupload
 */
class phpbb_plupload
{
	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var phpbb_request
	 */
	protected $request;

	/**
	 * @var phpbb_user
	 */
	protected $user;

	/**
	 * @var phpbb_php_ini
	 */
	protected $ini;

	/**
	 * @var phpbb_mimetype_extension_map
	 */
	protected $extension_map;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor function, just used to store the class variables and check
	 * whether plupload is sending the page request or not
	 *
	 * @param array $config
	 * @param object $request
	 * @param object $user
	 * @param string $phpbb_root_path
	 *
	 * @return null
	 */
	public function __construct($config, $request, $user, $phpbb_root_path, $phpbb_php_ini, $phpbb_mimetype_extension_map)
	{
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->ini = $phpbb_php_ini;
		$this->extension_map = $phpbb_mimetype_extension_map;
	}

	/**
	 * Checks whether the chunk we are about to deal with was actually uploaded
	 * by PHP and actually exists, if not, it generates an error
	 *
	 * @param boolean $is_multipart Are we dealing with POSTed multipart data
	 * @param string $form_name The name of the file in the form data
	 *
	 * @return null
	 */
	protected function check_file_valid($is_multipart, $form_name)
	{
		$upload = $this->request->file($form_name);

		if (
			$is_multipart
			&& (
				!isset($upload['tmp_name'])
				|| !is_uploaded_file($upload['tmp_name'])
			)
		)
		{
			$this->emit_error(103, 'PLUPLOAD_ERR_MOVE_UPLOADED');
		}
	}

	/**
	 * If the plupload subdirectory does not exist in the tmp upload
	 * directory then create it
	 *
	 * @param string $tmp_dir The full path of the plupload subdirectory
	 *
	 * @return null
	 */
	protected function check_tmp_dir($tmp_dir)
	{
		if (!file_exists($tmp_dir))
		{
			mkdir($tmp_dir);
			copy(
				$this->phpbb_root_path . $this->config['upload_path'] . '/index.htm',
				$tmp_dir . '/index.htm'
			);
		}
	}

	/**
	 * Fill in the plupload configuration options in the template
	 *
	 * @param object $cache
	 * @param object $template
	 * @param string $s_action The URL to submit the POST data to
	 * @param string $forum_id The ID of the forum
	 *
	 * @return null
	 */
	public function configure($cache, $template, $s_action, $forum_id)
	{
		$filters = $this->generate_filter_string($cache, $forum_id);
		$chunk_size = $this->get_chunk_size();
		$resize = $this->generate_resize_string();

		$template->assign_vars(array(
			'S_RESIZE'			=> $resize,
			'S_PLUPLOAD'		=> true,
			'FILTERS'			=> $filters,
			'CHUNK_SIZE'		=> $chunk_size,
			'S_PLUPLOAD_URL'	=> htmlspecialchars_decode($s_action),
		));

		$this->user->add_lang('plupload');
	}

	/**
	 * Sends an error message back to the client via JSON response
	 *
	 * @param int $code		The error code
	 * @param string $msg	The translation string of the message to be sent
	 *
	 * @return null
	 */
	public function emit_error($code, $msg)
	{
		$json_response = new phpbb_json_response();
		$json_response->send(array(
			'jsonrpc' => '2.0',
			'id' => 'id',
			'error' => array(
				'code' => $code,
				'message' => $this->user->lang($msg),
			),
		));
	}

	/**
	 * Looks at the list of allowed extensions and generates a string
	 * appropriate for use in configuring plupload with
	 *
	 * @param object $cache
	 * @param string $forum_id The ID of the forum
	 *
	 * @return string
	 */
	protected function generate_filter_string($cache, $forum_id)
	{
		$attach_extensions = $cache->obtain_attach_extensions($forum_id);
		unset($attach_extensions['_allowed_']);
		$groups = array();

		// Re-arrange the extension array to $groups[$group_name][]
		foreach ($attach_extensions as $extension => $extension_info)
		{
			if (!isset($groups[$extension_info['group_name']]))
			{
				$groups[$extension_info['group_name']] = array();
			}

			$groups[$extension_info['group_name']][] = $extension;
		}

		$filters = array();
		foreach ($groups as $group => $extensions)
		{
			$filters[] = sprintf(
				"{title: '%s', extensions: '%s'}",
				addslashes(ucfirst(strtolower($group))),
				addslashes(implode(',', $extensions))
			);
		}

		return implode(',', $filters);
	}

	/**
	 * Generates a string that is used to tell plupload to automatically resize
	 * files before uploading them.
	 *
	 * @return string
	 */
	protected function generate_resize_string()
	{
		$resize = '';
		if ($this->config['img_max_height'] > 0 && $this->config['img_max_width'] > 0)
		{
			$resize = sprintf(
				'resize: {width: %d, height: %d, quality: 100},',
				(int) $this->config['img_max_height'],
				(int) $this->config['img_max_width']
			);
		}

		return $resize;
	}

	/**
	 * Checks various php.ini values and the maximum file size to determine
	 * the maximum size chunks a file can be split up into for upload
	 *
	 * @return int
	 */
	protected function get_chunk_size()
	{
		return min(
			$this->ini->get_bytes('upload_max_filesize'),
			$this->ini->get_bytes('post_max_size'),
			max(1, $this->ini->get_bytes('memory_limit')),
			$this->config['max_filesize']
		);
	}

	/**
	 * Plupload allows for chunking so we must check for that and assemble
	 * the whole file first before performing any checks on it.
	 *
	 * @param string $form_name The name of the file element in the upload form
	 * @return array|null Null if there are no chunks to piece together or an
	 *	array containing the path to the pieced-together file and its size
	 */
	public function handle_upload($form_name)
	{
		// Most of this code is adapted from the sample upload script provided
		// with plupload
		$tmp_dir = $this->phpbb_root_path . $this->config['upload_path'] . '/plupload';

		$chunk = $this->request->variable('chunk', 0);
		$chunks_expected = $this->request->variable('chunks', 0);
		$file_name = $this->request->variable('name', '');
		$realname = $this->request->variable('real_filename', '');

		// If chunking is disabled or we are not using plupload, just return
		// and handle the file as usual
		if ($chunks_expected < 2)
		{
			return;
		}

		$this->user->add_lang('plupload');

		// Must preserve the extension
		$ext = filespec::get_extension($file_name);
		$file_name = md5($this->config['plupload_salt'] . $file_name) . ".$ext";
		$file_path = $tmp_dir . '/' . $file_name;
		$this->check_tmp_dir($tmp_dir);

		$content_type = $this->request->server('CONTENT_TYPE');
		$is_multipart = (strpos($content_type, 'multipart') !== false);
		$this->check_file_valid($is_multipart, $form_name);
		$tmp_file = $this->move_chunk_tmp_dir($tmp_dir, $form_name);

		$out = fopen("{$file_path}.part", $chunk == 0 ? 'wb' : 'ab');
		if (!$out)
		{
			$this->emit_error(102, 'PLUPLOAD_ERR_OUTPUT');
		}

		$in = fopen(($is_multipart) ? $tmp_file : 'php://input', 'rb');
		if(!$in)
		{
			$this->emit_error(101, 'PLUPLOAD_ERR_INPUT');
		}

		$this->write_chunk($in, $out);

		fclose($in);
		fclose($out);

		if ($is_multipart)
		{
			unlink($tmp_file);
		}

		// If we are done with all the chunks, strip the .part suffix and then
		// handle the resulting file as normal, otherwise die and await the
		// next chunk
		if ($chunk == $chunks_expected - 1)
		{
			rename("{$file_path}.part", $file_path);

			// Need to modify some of the $_FILES values to reflect the new
			// file
			return array(
				'tmp_name' => $file_path,
				'name' => $realname,
				'size' => filesize($file_path),
				'type' => $this->extension_map->get_mimetype($ext),
			);
		}
		else
		{
			$json_response = new phpbb_json_response();
			$json_response->send(array(
				'jsonrpc' => '2.0',
				'id' => 'id',
				'result' => null,
			));
		}
	}

	/**
	 * Checks whether the page request was sent by plupload or not
	 *
	 * @return boolean
	 */
	public function is_active()
	{
		return $this->request->header('X-PHPBB-USING-PLUPLOAD', false);
	}

	/**
	 * Move the file safely to our working tmp dir to read from it
	 *
	 * @param string $tmp_dir The temporary working directory for our plupload
	 *	chunks
	 * @param string $form_name The name of the file in the form data
	 *
	 * @return string The full path of the newly-moved chunk
	 */
	protected function move_chunk_tmp_dir($tmp_dir, $form_name)
	{
		$upload = $this->request->file($form_name);
		$tmp_file =
			$tmp_dir
			. '/'
			. basename($upload['tmp_name']);

		if (!move_uploaded_file($upload['tmp_name'], $tmp_file))
		{
			$this->emit_error(103, 'PLUPLOAD_ERR_MOVE_UPLOADED');
		}

		return $tmp_file;
	}

	/**
	 * Write the data in the chunk onto the end of the file
	 *
	 * @param int $in File handle to the open chunk file for reading
	 * @param int $out File handle to the open uploading file for writing
	 *
	 * @return null
	 */
	protected function write_chunk($in, $out)
	{
		while ($buf = fread($in, 4096))
		{
			fwrite($out, $buf);
		}
	}
}
