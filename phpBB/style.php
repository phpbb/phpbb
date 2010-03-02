<?php 
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'config.' . $phpEx);

if (version_compare(PHP_VERSION, '6.0.0-dev', '<'))
{
	set_magic_quotes_runtime(0);
}

// Load Extensions
if (!empty($load_extensions))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}


$sid = (isset($_GET['sid'])) ? htmlspecialchars($_GET['sid']) : '';
$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

if (strspn($sid, 'abcdefABCDEF0123456789') !== strlen($sid))
{
	$sid = '';
}

// This is a simple script to grab and output the requested CSS data stored in the DB
// We include a session_id check to try and limit 3rd party linking ... unless they
// happen to have a current session it will output nothing. We will also cache the
// resulting CSS data for five minutes ... anything to reduce the load on the SQL
// server a little
if ($id && $sid)
{
	if (empty($acm_type) || empty($dbms))
	{
		die('Hacking attempt');
	}

	// Include files
	require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
	require($phpbb_root_path . 'includes/cache.' . $phpEx);
	require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
	require($phpbb_root_path . 'includes/constants.' . $phpEx);

	$db = new $sql_db();
	$cache = new cache();

	// Connect to DB
	if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false))
	{
		exit;
	}
	unset($dbpasswd);

	$config = $cache->obtain_config();

	$sql = 'SELECT u.user_id, u.user_lang
		FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
		WHERE s.session_id = '" . $db->sql_escape($sid) . "'
			AND s.session_user_id = u.user_id";
	$result = $db->sql_query($sql);
	$user = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($user)
	{
		$sql = 'SELECT s.style_id, c.theme_data, c.theme_path, c.theme_name, c.theme_mtime, i.*, t.template_path
			FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c, ' . STYLES_IMAGESET_TABLE . ' i
			WHERE s.style_id = ' . $id . '
				AND t.template_id = s.template_id
				AND c.theme_id = s.theme_id
				AND i.imageset_id = s.imageset_id';
		$result = $db->sql_query($sql, 300);
		$theme = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($user['user_id'] == ANONYMOUS)
		{
			$user['user_lang'] = $config['default_lang'];
		}

		$user_image_lang = (file_exists($phpbb_root_path . 'styles/' . $theme['imageset_path'] . '/imageset/' . $user['user_lang'])) ? $user['user_lang'] : $config['default_lang'];

		$sql = 'SELECT *
			FROM ' . STYLES_IMAGESET_DATA_TABLE . '
			WHERE imageset_id = ' . $theme['imageset_id'] . "
			AND image_lang IN('" . $db->sql_escape($user_image_lang) . "', '')";
		$result = $db->sql_query($sql, 3600);

		$img_array = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$img_array[$row['image_name']] = $row;
		}

		if (!$theme)
		{
			exit;
		}

		// Re-cache stylesheet data if necessary
		if ($config['load_tplcompile'] || empty($theme['theme_data']))
		{
			$recache = (empty($theme['theme_data'])) ? true : false;
			$update_time = time();

			// We test for stylesheet.css because it is faster and most likely the only file changed on common themes
			if (!$recache && $theme['theme_mtime'] < @filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css'))
			{
				$recache = true;
				$update_time = @filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css');
			}
			else if (!$recache)
			{
				$last_change = $theme['theme_mtime'];

				foreach (glob("{$phpbb_root_path}styles/{$theme['theme_path']}/theme/*.css", GLOB_NOSORT) as $file)
				{
					if ($last_change < @filemtime($file))
					{
						$recache = true;
						break;
					}
				}
			}

			if ($recache)
			{
				include_once($phpbb_root_path . 'includes/acp/acp_styles.' . $phpEx);

				$theme['theme_data'] = acp_styles::db_theme_data($theme);
				$theme['theme_mtime'] = $update_time;

				// Save CSS contents
				$sql_ary = array(
					'theme_mtime'	=> $theme['theme_mtime'],
					'theme_data'	=> $theme['theme_data']
				);

				$sql = 'UPDATE ' . STYLES_THEME_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE theme_id = $id";
				$db->sql_query($sql);

				$cache->destroy('sql', STYLES_THEME_TABLE);

				header('Expires: 0');
			}
		}
		else
		{
			header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
		}

		header('Content-type: text/css');

		// Parse Theme Data
		$replace = array(
			'{T_THEME_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme',
			'{T_TEMPLATE_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['template_path'] . '/template',
			'{T_IMAGESET_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['imageset_path'] . '/imageset',
			'{T_IMAGESET_LANG_PATH}'	=> "{$phpbb_root_path}styles/" . $theme['imageset_path'] . '/imageset/' . $user_image_lang,
			'{T_STYLESHEET_NAME}'		=> $theme['theme_name'],
			'{S_USER_LANG}'				=> $user['user_lang']
		);

		$theme['theme_data'] = str_replace(array_keys($replace), array_values($replace), $theme['theme_data']);

		$matches = array();
		preg_match_all('#\{IMG_([A-Za-z0-9_]*?)_(WIDTH|HEIGHT|SRC)\}#', $theme['theme_data'], $matches);

		$imgs = $find = $replace = array();
		if (isset($matches[0]) && sizeof($matches[0]))
		{
			foreach ($matches[1] as $i => $img)
			{
				$img = strtolower($img);
				$find[] = $matches[0][$i];

				if (!isset($img_array[$img]))
				{
					$replace[] = '';
					continue;
				}

				if (!isset($imgs[$img]))
				{
					$img_data = &$img_array[$img];
					$imgsrc = ($img_data['image_lang'] ? $img_data['image_lang'] . '/' : '') . $img_data['image_filename'];
					$imgs[$img] = array(
						'src'		=> $phpbb_root_path . 'styles/' . $theme['imageset_path'] . '/imageset/' . $imgsrc,
						'width'		=> $img_data['image_width'],
						'height'	=> $img_data['image_height'],
					);
				}

				switch ($matches[2][$i])
				{
					case 'SRC':
						$replace[] = $imgs[$img]['src'];
					break;
					
					case 'WIDTH':
						$replace[] = $imgs[$img]['width'];
					break;
		
					case 'HEIGHT':
						$replace[] = $imgs[$img]['height'];
					break;

					default:
						continue;
				}
			}

			if (sizeof($find))
			{
				$theme['theme_data'] = str_replace($find, $replace, $theme['theme_data']);
			}
		}

		echo $theme['theme_data'];
	}

	if (!empty($cache))
	{
		$cache->unload();
	}
	$db->sql_close();
}

?>