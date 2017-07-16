<?php

class phpbb_mention_helper_test extends phpbb_test_case {

    protected $matches;
    protected $helper;
    protected $user_list;
    protected $sample_text;

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/users/user_details.xml');
    }

    public function test_get_regex_match()
    {
        $sample_text = 'cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][mention]admin[/mention][mention]admin[/mention] dwjpfw [mention]admin[/mention]';
        $regular_expression_match = '#\[mention\](.*?)\[/mention\]#';
        $db = $this->getMock('\phpbb\db\driver\driver_interface');
        $log = new \phpbb\mention\helper\mention_helper($db);
        $matches = false;
        $matches = $log->get_regex_match($regular_expression_match, $sample_text);
        $this->assertEquals($matches[0][0][0], "[mention]admin[/mention]");

    }

    public function test_get_user_list()
    {
        $sample_text = 'cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][mention]admin[/mention][mention]admin[/mention] dwjpfw [mention]admin[/mention]';
        $regular_expression_match = '#\[mention\](.*?)\[/mention\]#';
        $db = $this->getMock('\phpbb\db\driver\driver_interface');
        $log = new \phpbb\mention\helper\mention_helper($db);
        $matches = false;
        $matches = $log->get_regex_match($regular_expression_match, $sample_text);
        $user_list = $log->get_user_list($matches);
        $this->assertEquals($user_list[0], "admin");
    }

    public function test_get_regex_substituted_text()
    {
        global $request;
        $sample_text = 'cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][mention]admin[/mention][mention]admin[/mention] dwjpfw [mention]admin[/mention]';
        $regular_expression_match = '#\[mention\](.*?)\[/mention\]#';
        $db = $this->getMock('\phpbb\db\driver\driver_interface');
        $log = new \phpbb\mention\helper\mention_helper($db);
        $matches = false;
        $start_tag_length = strlen("[mention]");
        $end_tag_length = strlen("[\mention]");
        $userid_list = array("admin" => 2);
        $request = new phpbb_mock_request();
        $matches = $log->get_regex_match($regular_expression_match, $sample_text);
        $new_text = $log->get_regex_substituted_text($matches, $sample_text, $start_tag_length, $end_tag_length, $userid_list);
        $expected_text = "cbwkf kfbwkef wkfwb @robdyrdek vghjgjdsrshc ftfkyugb buykyuf yiglyftydtrf gyg ygiy @RickyBahner vgjdrs jfytg jbhjv ygfjh @HackMurphy kdnkwe [url = #]@andream[/url] [url = http://www.google.com]google[/url]wifhr [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13]LindsayM[/url] wewe [url = http://localhost/phpbb/phpBB/memberlist.php?mode=viewprofile&u=13] @LindsayM [/url][url=http://memberlist.php?mode=viewprofile&u=2]admin[/url][url=http://memberlist.php?mode=viewprofile&u=2]admin[/url] dwjpfw [url=http://memberlist.php?mode=viewprofile&u=2]admin[/url]";
        $this->assertEquals($new_text['post_text'], $expected_text);
    }
}
