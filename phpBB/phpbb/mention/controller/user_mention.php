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

namespace phpbb\mention\controller;

use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\JsonResponse;
use phpbb\mention\helper\mention_helper;

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
	* @var mention_helper
	*/
	private $helper;

	/**
	* User Mention Controller Constructor
	*
	* @param $db \phpbb\db\driver\driver_interface
	* @param $request   \phpbb\request\request_interface
	*  @param $helper   \phpbb\mention\helper\mention_helper
	*
	* @return \phpbb\mention\controller\user_mention
	*/
	public function __construct(driver_interface $db, request_interface $request, mention_helper $helper)
	{
		$this->db = $db;
		$this->request = $request;
		$this->helper = $helper;
	}
	/**
	*
	* Get a list of users matching on a username.
	*
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function handle()
	{
		$user_search_keyword = utf8_clean_string($this->request->variable('q', '', true));
		if (strlen($user_search_keyword) < 3)
		{
			return new JsonResponse(['usernames' => []]);
		}
		$return_usernames_userid = $this->helper->get_allusers($user_search_keyword);
		return new JsonResponse($return_usernames_userid);
	}
}
