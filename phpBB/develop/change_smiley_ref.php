<?php
/**
*
* @package phpBB3
* @copyright (c) 2003 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

@set_time_limit(2400);

// This script adds missing permissions
$db = $dbhost = $dbuser = $dbpasswd = $dbport = $dbname = '';

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path='./../';
include($phpbb_root_path . 'config.'.$phpEx);
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.'.$phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);

$cache		= new acm();
$db			= new $sql_db();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

$sql = "SELECT post_id, post_text FROM {$table_prefix}posts WHERE post_text LIKE '%{SMILE_PATH}%'";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$db->sql_query("UPDATE {$table_prefix}posts SET post_text = '" . $db->sql_escape(str_replace('{SMILE_PATH}', '{SMILIES_PATH}', $row['post_text'])) . "' WHERE post_id = " . $row['post_id']);
}
$db->sql_freeresult($result);

echo "<p><b>Done</b></p>\n";
 
?>