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

use Symfony\Component\DependencyInjection\ContainerInterface;
use phpbb\notification\manager;
use phpbb\notification\type\mention;

class phpbb_mention_helper_test extends phpbb_test_case
{

    protected $notification_manager;
    protected $mention_type;

    public function setUp()
    {
        $this->notification_manager = $this->getMockBuilder('\phpbb\notification\manager')->disableOriginalConstructor()->setMethods(['get_item_type_class'])->getMock();
        $this->mention_type = $this->getMockBuilder('\phpbb\notification\type\mention')->disableOriginalConstructor()->getMock();
        $this->notification_manager->method('get_item_type_class')
            ->willReturn($this->mention_type);
        $this->mention_type->method('find_users_for_notification')->willReturn(array("admin" => 2));
    }

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/users/user_details.xml');
    }

    public function test_get_mentioned_users()
    {
        global $request;
        global $config;
        global $user;
       $config = new \phpbb\config\config(array(
            'script_path'           => '/phpbb',
            'server_name'           => 'localhost',
            'server_port'           => 80,
            'server_protocol'       => 'http://',
        ));
        $request = new phpbb_mock_request;
        $user = new phpbb_mock_user;
        $sample_text = 'cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][mention]admin[/mention][mention]admin[/mention] dwjpfw [mention]admin[/mention]';
        $db = $this->getMock('\phpbb\db\driver\driver_interface');
        $helper_container = new \phpbb\mention\helper\mention_helper($db, $this->notification_manager);
        $user_list = $helper_container->get_allusers('');
        $post_parsing_text = $helper_container->get_mentioned_users($sample_text);
        $new_post_text = $post_parsing_text['new_post_text'];
        $expected_text = 'cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][url=http://testhost/memberlist.php?mode=viewprofile&u=2]admin[/url][url=http://testhost/memberlist.php?mode=viewprofile&u=2]admin[/url] dwjpfw [url=http://testhost/memberlist.php?mode=viewprofile&u=2]admin[/url]';
        $this->assertEquals($expected_text, $new_post_text);
    }
}
