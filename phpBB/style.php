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
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'config.'.$phpEx);

set_magic_quotes_runtime(0);

// Load Extensions
if (!empty($load_extensions))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}

// This is a simple script to grab and output the requested CSS data stored in the DB
// We include a session_id check to try and limit 3rd party linking ... unless they
// happen to have a current session it will output nothing. We will also cache the
// resulting CSS data for five minutes ... anything to reduce the load on the SQL
// server a little
if (!empty($_GET['id']) && !empty($_GET['sid']))
{
	// Include files
	require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.'.$phpEx);
	require($phpbb_root_path . 'includes/acm/acm_main.' . $phpEx);
	require($phpbb_root_path . 'includes/db/' . $dbms . '.'.$phpEx);

	$db = new $sql_db();
	$cache = new cache();
	
	// Connect to DB
	if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false))
	{
		exit;
	}

	$sid = htmlspecialchars($_GET['sid']);
	$id = intval($_GET['id']);

	$sql = "SELECT s.session_id, u.user_lang
		FROM {$table_prefix}sessions s, {$table_prefix}users u
		WHERE s.session_id = '" . ((!get_magic_quotes_gpc()) ? $db->sql_escape($sid) : $sid) . "'
			AND s.session_user_id = u.user_id";
	$result = $db->sql_query($sql);
	
	if ($user = $db->sql_fetchrow($result))
	{
		$sql = "SELECT s.style_id, c.theme_data, c.theme_path, c.theme_name, c.theme_mtime, i.imageset_path, t.template_path
			FROM {$table_prefix}styles s, {$table_prefix}styles_template t, {$table_prefix}styles_theme c, {$table_prefix}styles_imageset i
			WHERE s.style_id = $id
				AND t.template_id = s.template_id
				AND c.theme_id = s.theme_id
				AND i.imageset_id = s.imageset_id";
		$result2 = $db->sql_query($sql, 300);

		if (!($theme = $db->sql_fetchrow($result2)))
		{
			exit;
		}
		$db->sql_freeresult($result2);
		
		$force_load = true;	// Ideally this needs to be based on $config['load_tplcompile']
		
		if ($theme['theme_mtime'] < filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css')  || $force_load)
		{
			$theme['theme_data'] = implode('', file("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css'));
			
			// Match CSS imports
			preg_match_all('/@import url\(\"(.*)\"\);/i', $theme['theme_data'], $matches);
			
			if ($matches)
			{
				foreach ($matches[0] as $idx => $match)
				{
					$theme['theme_data'] = str_replace($match, load_css_file( $matches[1][$idx] ), $theme['theme_data']);
				}
			}
			
			$db->sql_query("UPDATE {$table_prefix}styles_theme SET theme_data = '" . $db->sql_escape($theme['theme_data']) . "', theme_mtime = " . time() . "
				WHERE theme_id = $id");
		}

		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
		header('Content-type: text/css');
		
		
		// Parse Theme Data
		$replace = array(
			'{T_THEME_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme',
			'{T_TEMPLATE_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['template_path'] . '/template',
			'{T_IMAGESET_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['imageset_path'] . '/imageset',
			'{T_IMAGESET_LANG_PATH}'	=> "{$phpbb_root_path}styles/" . $theme['imageset_path'] . '/imageset/' . $user['user_lang'],
			'{T_STYLESHEET_NAME}'		=> $theme['theme_name'],
			'{S_USER_LANG}'				=> $user['user_lang']
		);

		$theme['theme_data'] = str_replace(array_keys($replace), array_values($replace), $theme['theme_data']);

		echo $theme['theme_data'];
	}
	$db->sql_freeresult($result);

	if (!empty($cache))
	{
		$cache->unload();
	}
	$db->sql_close();
}

function load_css_file($filename)
{
	global $phpbb_root_path, $theme;
	
	$handle = "{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/' . $filename;
	
	if ($fp = @fopen($handle, 'r'))
	{
		$content = trim(@fread($fp, filesize($handle)));
		@fclose($fp);
	}
	else
	{
		$content = '';
	}
	
	return $content;
}


?>