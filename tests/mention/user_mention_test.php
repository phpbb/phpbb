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
require_once dirname(__FILE__) . '/../../phpBB/phpbb/mention/controller/user_mention.php';

class phpbb_user_mention_test extends phpbb_functional_test_case {

	public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/users/user_details.xml');
    }

	public function test_user_mention()
	{
		$keyword = 'ad';
    	$db = $this->getMock('\phpbb\db\driver\driver_interface');
    	// $db = $this->new_dbal();
    	$sql_query = 'SELECT user_id, username FROM ' . USERS_TABLE . ' WHERE user_id <> ' . ANONYMOUS . ' AND ' . $db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]) .  ' AND username_clean ' . $db->sql_like_expression($keyword . $db->get_any_char());
		$result = $db->sql_query($sql_query);
		$return_usernames_userid = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$return_usernames_userid[] = [
				'name'  => $row['username'],
				'id'    => $row['user_id'],
			];
		}
		$db->sql_freeresult($result);
		$controller_route = "http://localhost/phpbb/phpBB/app.php/usermention?q=".$keyword;
		$client = new GuzzleHttp\Client();
    	$request = $client->request('GET',$controller_route,[]);
    	$data = json_decode($request->getBody(), true);
	}
}