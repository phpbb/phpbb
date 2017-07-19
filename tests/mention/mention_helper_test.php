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
    protected $matches;
    protected $helper;
    protected $user_list;
    protected $sample_text;
    protected $notification_manager;

    public function setUp()
    {
        // $this->notification_manager = $this->getMockBuilder('\phpbb\notification\manager')->disableOriginalConstructor()->setMethods(['get_item_type_class', 'load_object'])->getMock();
    }

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/users/user_details.xml');
    }

    public function test_get_mentioned_users()
    {
        global $request;
        $sample_text = 'cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][mention]admin[/mention][mention]admin[/mention] dwjpfw [mention]admin[/mention]';
        $db = $this->getMock('\phpbb\db\driver\driver_interface');
        echo get_class($this->notification_manager);
        $helper_container = new \phpbb\mention\helper\mention_helper($db, $this->notification_manager);
        $post_parsing_text = $helper_container->get_mentioned_users($sample_text);
    }
}
