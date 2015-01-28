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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* A simplified function to deliver avatars
* The argument needs to be checked before calling this function.
*/
function send_avatar_to_browser($file, $browser)
{
	global $config, $phpbb_root_path;

	$prefix = $config['avatar_salt'] . '_';
	$image_dir = $config['avatar_path'];

	// Adjust image_dir path (no trailing slash)
	if (substr($image_dir, -1, 1) == '/' || substr($image_dir, -1, 1) == '\\')
	{
		$image_dir = substr($image_dir, 0, -1) . '/';
	}
	$image_dir = str_replace(array('../', '..\\', './', '.\\'), '', $image_dir);

	if ($image_dir && ($image_dir[0] == '/' || $image_dir[0] == '\\'))
	{
		$image_dir = '';
	}
	$file_path = $phpbb_root_path . $image_dir . '/' . $prefix . $file;

	if ((@file_exists($file_path) && @is_readable($file_path)) && !headers_sent())
	{
		header('Cache-Control: public');

		$image_data = @getimagesize($file_path);
		header('Content-Type: ' . image_type_to_mime_type($image_data[2]));

		if ((strpos(strtolower($browser), 'msie') !== false) && !phpbb_is_greater_ie_version($browser, 7))
		{
			header('Content-Disposition: attachment; ' . header_filename($file));

			if (strpos(strtolower($browser), 'msie 6.0') !== false)
			{
				header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
			}
			else
			{
				header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
			}
		}
		else
		{
			header('Content-Disposition: inline; ' . header_filename($file));
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
		}

		$size = @filesize($file_path);
		if ($size)
		{
			header("Content-Length: $size");
		}

		if (@readfile($file_path) == false)
		{
			$fp = @fopen($file_path, 'rb');

			if ($fp !== false)
			{
				while (!feof($fp))
				{
					echo fread($fp, 8192);
				}
				fclose($fp);
			}
		}

		flush();
	}
	else
	{
		header('HTTP/1.0 404 Not Found');
	}
}

/**
* Wraps an url into a simple html page. Used to display attachments in IE.
* this is a workaround for now; might be moved to template system later
* direct any complaints to 1 Microsoft Way, Redmond
*/
function wrap_img_in_html($src, $title)
{
	echo '<!DOCTYPE html>';
	echo '<html>';
	echo '<head>';
	echo '<meta charset="utf-8">';
	echo '<title>' . $title . '</title>';
	echo '</head>';
	echo '<body>';
	echo '<div>';
	echo '<img src="' . $src . '" alt="' . $title . '" />';
	echo '</div>';
	echo '</body>';
	echo '</html>';
}

/**
* Send file to browser
*/
function send_file_to_browser($attachment, $upload_dir, $category)
{
	global $user, $db, $config, $phpbb_root_path;

	$filename = $phpbb_root_path . $upload_dir . '/' . $attachment['physical_filename'];

	if (!@file_exists($filename))
	{
		send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
	}

	// Correct the mime type - we force application/octetstream for all files, except images
	// Please do not change this, it is a security precaution
	if ($category != ATTACHMENT_CATEGORY_IMAGE || strpos($attachment['mimetype'], 'image') !== 0)
	{
		$attachment['mimetype'] = (strpos(strtolower($user->browser), 'msie') !== false || strpos(strtolower($user->browser), 'opera') !== false) ? 'application/octetstream' : 'application/octet-stream';
	}

	if (@ob_get_length())
	{
		@ob_end_clean();
	}

	// Now send the File Contents to the Browser
	$size = @filesize($filename);

	// To correctly display further errors we need to make sure we are using the correct headers for both (unsetting content-length may not work)

	// Check if headers already sent or not able to get the file contents.
	if (headers_sent() || !@file_exists($filename) || !@is_readable($filename))
	{
		// PHP track_errors setting On?
		if (!empty($php_errormsg))
		{
			send_status_line(500, 'Internal Server Error');
			trigger_error($user->lang['UNABLE_TO_DELIVER_FILE'] . '<br />' . sprintf($user->lang['TRACKED_PHP_ERROR'], $php_errormsg));
		}

		send_status_line(500, 'Internal Server Error');
		trigger_error('UNABLE_TO_DELIVER_FILE');
	}

	// Make sure the database record for the filesize is correct
	if ($size > 0 && $size != $attachment['filesize'])
	{
		// Update database record
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET filesize = ' . (int) $size . '
			WHERE attach_id = ' . (int) $attachment['attach_id'];
		$db->sql_query($sql);
	}

	// Now the tricky part... let's dance
	header('Cache-Control: public');

	// Send out the Headers. Do not set Content-Disposition to inline please, it is a security measure for users using the Internet Explorer.
	header('Content-Type: ' . $attachment['mimetype']);

	if (phpbb_is_greater_ie_version($user->browser, 7))
	{
		header('X-Content-Type-Options: nosniff');
	}

	if ($category == ATTACHMENT_CATEGORY_FLASH && request_var('view', 0) === 1)
	{
		// We use content-disposition: inline for flash files and view=1 to let it correctly play with flash player 10 - any other disposition will fail to play inline
		header('Content-Disposition: inline');
	}
	else
	{
		if (empty($user->browser) || ((strpos(strtolower($user->browser), 'msie') !== false) && !phpbb_is_greater_ie_version($user->browser, 7)))
		{
			header('Content-Disposition: attachment; ' . header_filename(htmlspecialchars_decode($attachment['real_filename'])));
			if (empty($user->browser) || (strpos(strtolower($user->browser), 'msie 6.0') !== false))
			{
				header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
			}
		}
		else
		{
			header('Content-Disposition: ' . ((strpos($attachment['mimetype'], 'image') === 0) ? 'inline' : 'attachment') . '; ' . header_filename(htmlspecialchars_decode($attachment['real_filename'])));
			if (phpbb_is_greater_ie_version($user->browser, 7) && (strpos($attachment['mimetype'], 'image') !== 0))
			{
				header('X-Download-Options: noopen');
			}
		}
	}

	// Close the db connection before sending the file etc.
	file_gc(false);

	if (!set_modified_headers($attachment['filetime'], $user->browser))
	{
		// We make sure those have to be enabled manually by defining a constant
		// because of the potential disclosure of full attachment path
		// in case support for features is absent in the webserver software.
		if (defined('PHPBB_ENABLE_X_ACCEL_REDIRECT') && PHPBB_ENABLE_X_ACCEL_REDIRECT)
		{
			// X-Accel-Redirect - http://wiki.nginx.org/XSendfile
			header('X-Accel-Redirect: ' . $user->page['root_script_path'] . $upload_dir . '/' . $attachment['physical_filename']);
			exit;
		}
		else if (defined('PHPBB_ENABLE_X_SENDFILE') && PHPBB_ENABLE_X_SENDFILE && !phpbb_http_byte_range($size))
		{
			// X-Sendfile - http://blog.lighttpd.net/articles/2006/07/02/x-sendfile
			// Lighttpd's X-Sendfile does not support range requests as of 1.4.26
			// and always requires an absolute path.
			header('X-Sendfile: ' . dirname(__FILE__) . "/../$upload_dir/{$attachment['physical_filename']}");
			exit;
		}

		if ($size)
		{
			header("Content-Length: $size");
		}

		// Try to deliver in chunks
		@set_time_limit(0);

		$fp = @fopen($filename, 'rb');

		if ($fp !== false)
		{
			// Deliver file partially if requested
			if ($range = phpbb_http_byte_range($size))
			{
				fseek($fp, $range['byte_pos_start']);

				send_status_line(206, 'Partial Content');
				header('Content-Range: bytes ' . $range['byte_pos_start'] . '-' . $range['byte_pos_end'] . '/' . $range['bytes_total']);
				header('Content-Length: ' . $range['bytes_requested']);
			}

			while (!feof($fp))
			{
				echo fread($fp, 8192);
			}
			fclose($fp);
		}
		else
		{
			@readfile($filename);
		}

		flush();
	}

	exit;
}

/**
* Get a browser friendly UTF-8 encoded filename
*/
function header_filename($file)
{
	global $request;

	$user_agent = $request->header('User-Agent');

	// There be dragons here.
	// Not many follows the RFC...
	if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Safari') !== false || strpos($user_agent, 'Konqueror') !== false)
	{
		return "filename=" . rawurlencode($file);
	}

	// follow the RFC for extended filename for the rest
	return "filename*=UTF-8''" . rawurlencode($file);
}

/**
* Check if downloading item is allowed
*/
function download_allowed()
{
	global $config, $user, $db, $request;

	if (!$config['secure_downloads'])
	{
		return true;
	}

	$url = htmlspecialchars_decode($request->header('Referer'));

	if (!$url)
	{
		return ($config['secure_allow_empty_referer']) ? true : false;
	}

	// Split URL into domain and script part
	$url = @parse_url($url);

	if ($url === false)
	{
		return ($config['secure_allow_empty_referer']) ? true : false;
	}

	$hostname = $url['host'];
	unset($url);

	$allowed = ($config['secure_allow_deny']) ? false : true;
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
	$server_name = $user->host;

	// Forcing server vars is the only way to specify/override the protocol
	if ($config['force_server_vars'] || !$server_name)
	{
		$server_name = $config['server_name'];
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
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
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
							$allowed = ($config['secure_allow_deny']) ? false : true;
							break 2;
						}
						else
						{
							$allowed = ($config['secure_allow_deny']) ? true : false;
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
						$allowed = ($config['secure_allow_deny']) ? false : true;
						break;
					}
					else
					{
						$allowed = ($config['secure_allow_deny']) ? true : false;
					}
				}
			}
		}
		$db->sql_freeresult($result);
	}

	return $allowed;
}

/**
* Check if the browser has the file already and set the appropriate headers-
* @returns false if a resend is in order.
*/
function set_modified_headers($stamp, $browser)
{
	global $request;

	// let's see if we have to send the file at all
	$last_load 	=  $request->header('Modified-Since') ? strtotime(trim($request->header('Modified-Since'))) : false;

	if (strpos(strtolower($browser), 'msie 6.0') === false && !phpbb_is_greater_ie_version($browser, 7))
	{
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
	}
	return false;
}

/**
* Garbage Collection
*
* @param bool $exit		Whether to die or not.
*
* @return null
*/
function file_gc($exit = true)
{
	global $cache, $db;

	if (!empty($cache))
	{
		$cache->unload();
	}

	$db->sql_close();

	if ($exit)
	{
		exit;
	}
}

/**
* HTTP range support (RFC 2616 Section 14.35)
*
* Allows browsers to request partial file content
* in case a download has been interrupted.
*
* @param int $filesize		the size of the file in bytes we are about to deliver
*
* @return mixed		false if the whole file has to be delivered
*					associative array on success
*/
function phpbb_http_byte_range($filesize)
{
	// Only call find_range_request() once.
	static $request_array;

	if (!$filesize)
	{
		return false;
	}

	if (!isset($request_array))
	{
		$request_array = phpbb_find_range_request();
	}

	return (empty($request_array)) ? false : phpbb_parse_range_request($request_array, $filesize);
}

/**
* Searches for HTTP range request in request headers.
*
* @return mixed		false if no request found
*					array of strings containing the requested ranges otherwise
*					e.g. array(0 => '0-0', 1 => '123-125')
*/
function phpbb_find_range_request()
{
	global $request;

	$value = $request->header('Range');

	// Make sure range request starts with "bytes="
	if (strpos($value, 'bytes=') === 0)
	{
		// Strip leading 'bytes='
		// Multiple ranges can be separated by a comma
		return explode(',', substr($value, 6));
	}

	return false;
}

/**
* Analyses a range request array.
*
* A range request can contain multiple ranges,
* we however only handle the first request and
* only support requests from a given byte to the end of the file.
*
* @param array	$request_array	array of strings containing the requested ranges
* @param int	$filesize		the full size of the file in bytes that has been requested
*
* @return mixed		false if the whole file has to be delivered
*					associative array on success
*						byte_pos_start		the first byte position, can be passed to fseek()
*						byte_pos_end		the last byte position
*						bytes_requested		the number of bytes requested
*						bytes_total			the full size of the file
*/
function phpbb_parse_range_request($request_array, $filesize)
{
	// Go through all ranges
	foreach ($request_array as $range_string)
	{
		$range = explode('-', trim($range_string));

		// "-" is invalid, "0-0" however is valid and means the very first byte.
		if (sizeof($range) != 2 || $range[0] === '' && $range[1] === '')
		{
			continue;
		}

		if ($range[0] === '')
		{
			// Return last $range[1] bytes.

			if (!$range[1])
			{
				continue;
			}

			if ($range[1] >= $filesize)
			{
				return false;
			}

			$first_byte_pos	= $filesize - (int) $range[1];
			$last_byte_pos	= $filesize - 1;
		}
		else
		{
			// Return bytes from $range[0] to $range[1]

			$first_byte_pos	= (int) $range[0];
			$last_byte_pos	= (int) $range[1];

			if ($last_byte_pos && $last_byte_pos < $first_byte_pos)
			{
				// The requested range contains 0 bytes.
				continue;
			}

			if ($first_byte_pos >= $filesize)
			{
				// Requested range not satisfiable
				return false;
			}

			// Adjust last-byte-pos if it is absent or greater than the content.
			if ($range[1] === '' || $last_byte_pos >= $filesize)
			{
				$last_byte_pos = $filesize - 1;
			}
		}

		// We currently do not support range requests that end before the end of the file
		if ($last_byte_pos != $filesize - 1)
		{
			continue;
		}

		return array(
			'byte_pos_start'	=> $first_byte_pos,
			'byte_pos_end'		=> $last_byte_pos,
			'bytes_requested'	=> $last_byte_pos - $first_byte_pos + 1,
			'bytes_total'		=> $filesize,
		);
	}
}

/**
* Increments the download count of all provided attachments
*
* @param \phpbb\db\driver\driver_interface $db The database object
* @param array|int $ids The attach_id of each attachment
*
* @return null
*/
function phpbb_increment_downloads($db, $ids)
{
	if (!is_array($ids))
	{
		$ids = array($ids);
	}

	$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
		SET download_count = download_count + 1
		WHERE ' . $db->sql_in_set('attach_id', $ids);
	$db->sql_query($sql);
}

/**
* Handles authentication when downloading attachments from a post or topic
*
* @param \phpbb\db\driver\driver_interface $db The database object
* @param \phpbb\auth\auth $auth The authentication object
* @param int $topic_id The id of the topic that we are downloading from
*
* @return null
*/
function phpbb_download_handle_forum_auth($db, $auth, $topic_id)
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

	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row && $row['topic_visibility'] != ITEM_APPROVED && !$auth->acl_get('m_approve', $row['forum_id']))
	{
		send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
	}
	else if ($row && $auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']))
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
* @param \phpbb\db\driver\driver_interface $db The database object
* @param \phpbb\auth\auth $auth The authentication object
* @param int $user_id The user id
* @param int $msg_id The id of the PM that we are downloading from
*
* @return null
*/
function phpbb_download_handle_pm_auth($db, $auth, $user_id, $msg_id)
{
	if (!$auth->acl_get('u_pm_download'))
	{
		send_status_line(403, 'Forbidden');
		trigger_error('SORRY_AUTH_VIEW_ATTACH');
	}

	$allowed = phpbb_download_check_pm_auth($db, $user_id, $msg_id);

	if (!$allowed)
	{
		send_status_line(403, 'Forbidden');
		trigger_error('ERROR_NO_ATTACHMENT');
	}
}

/**
* Checks whether a user can download from a particular PM
*
* @param \phpbb\db\driver\driver_interface $db The database object
* @param int $user_id The user id
* @param int $msg_id The id of the PM that we are downloading from
*
* @return bool Whether the user is allowed to download from that PM or not
*/
function phpbb_download_check_pm_auth($db, $user_id, $msg_id)
{
	// Check if the attachment is within the users scope...
	$sql = 'SELECT msg_id
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE msg_id = ' . (int) $msg_id . '
			AND (
				user_id = ' . (int) $user_id . '
				OR author_id = ' . (int) $user_id . '
			)';
	$result = $db->sql_query_limit($sql, 1);
	$allowed = (bool) $db->sql_fetchfield('msg_id');
	$db->sql_freeresult($result);

	return $allowed;
}

/**
* Check if the browser is internet explorer version 7+
*
* @param string $user_agent	User agent HTTP header
* @param int $version IE version to check against
*
* @return bool true if internet explorer version is greater than $version
*/
function phpbb_is_greater_ie_version($user_agent, $version)
{
	if (preg_match('/msie (\d+)/', strtolower($user_agent), $matches))
	{
		$ie_version = (int) $matches[1];
		return ($ie_version > $version);
	}
	else
	{
		return false;
	}
}
