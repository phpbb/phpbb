<?php
/**
 *
 * phpBB mentions. A controller class for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, phpBB, https://www.phpbbextensions.io
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace phpbb\mention\controller;

use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\JsonResponse;
use phpbb\controller\helper;
/**
 * phpBB mentions main controller.
 */

class user_mention
{

	/**
	* @var driver_interface
	*/
	private $db;

	/**
	* @var request_interface
	*/
	private $request;

	/**
 	* @var helper
 	*/
	protected $helper;

	public function __construct(driver_interface $db, request_interface $request)
	{
		$this->db = $db;
		$this->request = $request;
		$this->helper = $helper;
	}

	/**
	 * get a list of users matching on a username.
	 *
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle()
	{
		$keyword = utf8_clean_string($this->request->variable('q', '', true));
		if (strlen($keyword) < 2)
		{
			return new JsonResponse(['usernames' => []]);
		}
		$sql_query = 'SELECT user_id, username FROM ' . USERS_TABLE . ' WHERE user_id <> ' . ANONYMOUS . ' AND ' . $this->db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]) .  ' AND username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char());
		$result = $this->db->sql_query($sql_query);
		$return_usernames_userid = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$return_usernames_userid[] = [
				'name'  => $row['username'],
				'id'    => $row['user_id'],
			];
		}
		$this->db->sql_freeresult($result);
		return new JsonResponse($return_usernames_userid);
	}
}
