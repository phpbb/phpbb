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
        $return_usernames_userid = array();
        while ($row = $db->sql_fetchrow($result))
        {
            $temp_username_userid = array();
            $temp_username_userid['id'] = $row['user_id'];
            $temp_username_userid['name'] = $row['username'];
            array_push($return_usernames_userid, $temp_username_userid);
        }
        $db->sql_freeresult($result);
        $this->assertEquals($return_usernames_userid[0]['name'], 'Anonymous');
    }
}
