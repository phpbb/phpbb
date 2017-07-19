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

use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use phpbb\mention\helper\mention_helper;
use Symfony\Component\HttpFoundation\JsonResponse;

class phpbb_user_mention_test extends phpbb_test_case {

    protected $db;
    protected $request;
    protected $container;
    protected $helper;

    protected function setUp() {
        $this->db = $this->getMock('\phpbb\db\driver\driver_interface');
        $this->helper = $this->getMockBuilder('phpbb\mention\helper\mention_helper')->disableOriginalConstructor()->setMethods(['get_allusers'])->getMock();
        $this->helper->method('get_allusers')
            ->willReturn(array("name" => "admin", "id" => 2));
        $conatiner_functions = ['set', 'get', 'has', 'initialized', 'getParameter', 'setParameter', 'hasParameter'];
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->disableOriginalConstructor()->setMethods($conatiner_functions)->getMock();
        $this->container->method('get')
            ->willReturn($this->helper);
        $request_functions = ['overwrite','variable','raw_variable','server','header','is_set_post','is_set','is_ajax','is_secure','variable_names','get_super_global', 'escape'];
        $this->request = $this->getMockBuilder('phpbb\request\request_interface')->disableOriginalConstructor()->setMethods($request_functions)->getMock();
        $this->request->method('variable')
            ->willReturn('adm');
    }

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/users/user_details.xml');
    }

    public function test_handle()
    {
        $mention_controller_class = new \phpbb\mention\controller\user_mention($this->db, $this->request, $this->container);
        $json_response = $mention_controller_class->handle();
        $user_suggestion = json_decode($json_response->getContent());
        $this->assertEquals(array($user_suggestion->name,$user_suggestion->id), array("admin", 2));
    }
}
