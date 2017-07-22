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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    * @var container_interface
    */
    private $container;

    /**
    * User Mention Controller Constructor
    *
    * @param $db       Database Driver Interface Object.
    * @param $request  Request Interface Object.
    *
    * @return \phpbb\mention\controller\user_mention
    */
    public function __construct(driver_interface $db, request_interface $request, ContainerInterface $container)
    {
        $this->db = $db;
        $this->request = $request;
        $this->container = $container;
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
        $helper_container = $this->container->get('phpbb_mention_helper');
        $return_usernames_userid = $helper_container->get_allusers($user_search_keyword);
        return new JsonResponse($return_usernames_userid);
    }
}
