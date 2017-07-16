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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use phpbb\controller\helper;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        $keyword = utf8_clean_string($this->request->variable('q', '', true));
        if (strlen($keyword) < 2)
        {
            return new JsonResponse(['usernames' => []]);
        }
        $helper_container = $this->container->get('phpbb_mention_helper');
        $return_usernames_userid = $helper_container->get_allusers($keyword);
        return new JsonResponse($return_usernames_userid);
    }
}
