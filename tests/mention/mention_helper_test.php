<?php

use Symfony\Component\DependencyInjection\ContainerInterface;
use phpbb\notification\manager;

class phpbb_mention_helper_test extends phpbb_test_case
{
    protected $matches;
    protected $helper;
    protected $user_list;
    protected $sample_text;

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/users/user_details.xml');
    }

    public function test_get_mentioned_users()
    {
        global $request;
        $notification_manager = $this->prophesize(manager::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('notification_manager')->willReturn($notification_manager);
        $container = $container->reveal();
        $sample_text = 'cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][mention]admin[/mention][mention]admin[/mention] dwjpfw [mention]admin[/mention]';
        $request = new phpbb_mock_request();
        $db = $this->getMock('\phpbb\db\driver\driver_interface');
        $helper_container = new \phpbb\mention\helper\mention_helper($db, $notification_manager);
        $post_parsing_text = $helper_container->get_mentioned_users($sample_text);
    }
}
