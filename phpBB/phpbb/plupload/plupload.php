<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\plupload;

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
* @package \phpbb\plupload\plupload
*/
class plupload
{
	/**
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* @var \phpbb\request\request_interface
	*/
	protected $request;

	/**
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* @var \phpbb\php\ini
	*/
	protected $php_ini;

	/**
	* Final destination for uploaded files, i.e. the "files" directory.
	* @var string
	*/
	protected $upload_directory;

	/**
	* Temporary upload directory for plupload uploads.
	* @var string
	*/
	protected $temporary_directory;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path
	* @param \phpbb\config\config $config
	* @param \phpbb\request\request_interface $request
	* @param \phpbb\user $user
	* @param \phpbb\php\ini $php_ini
	*
	* @return null
	*/
	public function __construct($phpbb_root_path, \phpbb\config\config $config, \phpbb\request\request_interface $request, \phpbb\user $user, \phpbb\php\ini $php_ini)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->php_ini = $php_ini;

		$this->upload_directory = $this->phpbb_root_path . $this->config['upload_path'];
		$this->temporary_directory = $this->upload_directory . '/plupload';
	}

	/**
	* Plupload allows for chunking so we must check for that and assemble
	* the whole file first before performing any checks on it.
	*
	* @param string $form_name The name of the file element in the upload form
	*
	* @return array|null	null if there are no chunks to piece together
	*						otherwise array containing the path to the
	*						pieced-together file and its size
	*/
	public function handle_upload($form_name)
	{
		$chunks_expected = $this->request->variable('chunks', 0);

		// If chunking is disabled or we are not using plupload, just return
		// and handle the file as usual
		if ($chunks_expected < 2)
		{
			return;
		}

		$file_name = $this->request->variable('name', '');
		$chunk = $this->request->variable('chunk', 0);

		$this->user->add_lang('plupload');
		$this->prepare_temporary_directory();

		$file_path = $this->temporary_filepath($file_name);
		$this->integrate_uploaded_file($form_name, $chunk, $file_path);

		// If we are done with all the chunks, strip the .part suffix and then
		// handle the resulting file as normal, otherwise die and await the
		// next chunk.
		if ($chunk == $chunks_expected - 1)
		{
			rename("{$file_path}.part", $file_path);

			$file_info = new \Symfony\Component\HttpFoundation\File\File($file_path);

			// Need to modify some of the $_FILES values to reflect the new file
			return array(
				'tmp_name' => $file_path,
				'name' => $this->request->variable('real_filename', ''),
				'size' => filesize($file_path),
				'type' => $file_info->getMimeType($file_path),
			);
		}
		else
		{
			$json_response = new \phpbb\json_response();
			$json_response->send(array(
				'jsonrpc' => '2.0',
				'id' => 'id',
				'result' => null,
			));
		}
	}

	/**
	* Fill in the plupload configuration options in the template
	*
	* @param \phpbb\cache\service		$cache
	* @param \phpbb\template\template	$template
	* @param string						$s_action The URL to submit the POST data to
	* @param int						$forum_id The ID of the forum
	*
	* @return null
	*/
	public function configure(\phpbb\cache\service $cache, \phpbb\template\template $template, $s_action, $forum_id)
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
	* Checks whether the page request was sent by plupload or not
	*
	* @return bool
	*/
	public function is_active()
	{
		return $this->request->header('X-PHPBB-USING-PLUPLOAD', false);
	}

	/**
	* Returns whether the current HTTP request is a multipart request.
	*
	* @return bool
	*/
	public function is_multipart()
	{
		$content_type = $this->request->server('CONTENT_TYPE');

		return strpos($content_type, 'multipart') === 0;
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
		$json_response = new \phpbb\json_response();
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
	* @param \phpbb\cache\service $cache
	* @param string $forum_id The ID of the forum
	*
	* @return string
	*/
	public function generate_filter_string(\phpbb\cache\service $cache, $forum_id)
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
	public function generate_resize_string()
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
	public function get_chunk_size()
	{
		$max = min(
			$this->php_ini->get_bytes('upload_max_filesize'),
			$this->php_ini->get_bytes('post_max_size'),
			max(1, $this->php_ini->get_bytes('memory_limit')),
			$this->config['max_filesize']
		);

		// Use half of the maximum possible to leave plenty of room for other
		// POST data.
		return floor($max / 2);
	}

	protected function temporary_filepath($file_name)
	{
		// Must preserve the extension for plupload to work.
		return sprintf(
			'%s/%s_%s%s',
			$this->temporary_directory,
			$this->config['plupload_salt'],
			md5($file_name),
			\filespec::get_extension($file_name)
		);
	}

	/**
	* Checks whether the chunk we are about to deal with was actually uploaded
	* by PHP and actually exists, if not, it generates an error
	*
	* @param string $form_name The name of the file in the form data
	*
	* @return null
	*/
	protected function integrate_uploaded_file($form_name, $chunk, $file_path)
	{
		$is_multipart = $this->is_multipart();
		$upload = $this->request->file($form_name);
		if ($is_multipart && (!isset($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])))
		{
			$this->emit_error(103, 'PLUPLOAD_ERR_MOVE_UPLOADED');
		}

		$tmp_file = $this->temporary_filepath($upload['tmp_name']);

		if (!move_uploaded_file($upload['tmp_name'], $tmp_file))
		{
			$this->emit_error(103, 'PLUPLOAD_ERR_MOVE_UPLOADED');
		}

		$out = fopen("{$file_path}.part", $chunk == 0 ? 'wb' : 'ab');
		if (!$out)
		{
			$this->emit_error(102, 'PLUPLOAD_ERR_OUTPUT');
		}

		$in = fopen(($is_multipart) ? $tmp_file : 'php://input', 'rb');
		if (!$in)
		{
			$this->emit_error(101, 'PLUPLOAD_ERR_INPUT');
		}

		while ($buf = fread($in, 4096))
		{
			fwrite($out, $buf);
		}

		fclose($in);
		fclose($out);

		if ($is_multipart)
		{
			unlink($tmp_file);
		}
	}

	/**
	* Creates the temporary directory if it does not already exist.
	*
	* @return null
	*/
	protected function prepare_temporary_directory()
	{
		if (!file_exists($this->temporary_directory))
		{
			mkdir($this->temporary_directory);

			copy(
				$this->upload_directory . '/index.htm',
				$this->temporary_directory . '/index.htm'
			);
		}
	}
}
