<?
/**
 *
 * phpBB mentions. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, paul999, https://www.phpbbextensions.io
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\JsonResponse;

getUsernameFromDb();

function getUsernameFromDb(driver_interface $db, request_interface $request) {

	$keyword = utf8_clean_string($request->variable('query', '', true));
	$sql_query_to_retrive_users = 'SELECT user_id, username
						FROM ' . USERS_TABLE . '
						WHERE user_id <> ' . ANONYMOUS . '
						AND ' . $db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]) .  '
						AND username_clean ' . $db->sql_like_expression($keyword . $db->get_any_char());
	$result = $db->sql_query($sql_query_to_retrive_users);

	while ($row = $db->sql_fetchrow($result))
	{
		$userdetail_array[] = [
			'name'  => $row['username'],
			'id'    => $row['user_id'],
		];
	}
	$db->sql_freeresult($result);
	return json_encode($userdetail_array);
}

