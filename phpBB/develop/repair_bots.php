<?php
/**
* Rebuild BOTS
*
* You should make a backup from your whole database. Things can and will go wrong. 
* This will only work if no BOTs were added.
*
*/
die("Please read the first lines of this script for instructions on how to enable it");

set_time_limit(0);

define('IN_PHPBB', true);
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . '/includes/functions_user.'.$phpEx);


// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$bots = array(
	'AdsBot [Google]'			=> array('AdsBot-Google', ''),
	'Alexa [Bot]'				=> array('ia_archiver', ''),
	'Alta Vista [Bot]'			=> array('Scooter/', ''),
	'Ask Jeeves [Bot]'			=> array('Ask Jeeves', ''),
	'Baidu [Spider]'			=> array('Baiduspider+(', ''),
	'Exabot [Bot]'				=> array('Exabot/', ''),
	'FAST Enterprise [Crawler]'	=> array('FAST Enterprise Crawler', ''),
	'FAST WebCrawler [Crawler]'	=> array('FAST-WebCrawler/', ''),
	'Francis [Bot]'				=> array('http://www.neomo.de/', ''),
	'Gigabot [Bot]'				=> array('Gigabot/', ''),
	'Google Adsense [Bot]'		=> array('Mediapartners-Google', ''),
	'Google Desktop'			=> array('Google Desktop', ''),
	'Google Feedfetcher'		=> array('Feedfetcher-Google', ''),
	'Google [Bot]'				=> array('Googlebot', ''),
	'Heise IT-Markt [Crawler]'	=> array('heise-IT-Markt-Crawler', ''),
	'Heritrix [Crawler]'		=> array('heritrix/1.', ''),
	'IBM Research [Bot]'		=> array('ibm.com/cs/crawler', ''),
	'ICCrawler - ICjobs'		=> array('ICCrawler - ICjobs', ''),
	'ichiro [Crawler]'			=> array('ichiro/2', ''),
	'Majestic-12 [Bot]'			=> array('MJ12bot/', ''),
	'Metager [Bot]'				=> array('MetagerBot/', ''),
	'MSN NewsBlogs'				=> array('msnbot-NewsBlogs/', ''),
	'MSN [Bot]'					=> array('msnbot/', ''),
	'MSNbot Media'				=> array('msnbot-media/', ''),
	'NG-Search [Bot]'			=> array('NG-Search/', ''),
	'Nutch [Bot]'				=> array('http://lucene.apache.org/nutch/', ''),
	'Nutch/CVS [Bot]'			=> array('NutchCVS/', ''),
	'OmniExplorer [Bot]'		=> array('OmniExplorer_Bot/', ''),
	'Online link [Validator]'	=> array('online link validator', ''),
	'psbot [Picsearch]'			=> array('psbot/0', ''),
	'Seekport [Bot]'			=> array('Seekbot/', ''),
	'Sensis [Crawler]'			=> array('Sensis Web Crawler', ''),
	'SEO Crawler'				=> array('SEO search Crawler/', ''),
	'Seoma [Crawler]'			=> array('Seoma [SEO Crawler]', ''),
	'SEOSearch [Crawler]'		=> array('SEOsearch/', ''),
	'Snappy [Bot]'				=> array('Snappy/1.1 ( http://www.urltrends.com/ )', ''),
	'Steeler [Crawler]'			=> array('http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', ''),
	'Synoo [Bot]'				=> array('SynooBot/', ''),
	'Telekom [Bot]'				=> array('crawleradmin.t-info@telekom.de', ''),
	'TurnitinBot [Bot]'			=> array('TurnitinBot/', ''),
	'Voyager [Bot]'				=> array('voyager/1.0', ''),
	'W3 [Sitesearch]'			=> array('W3 SiteSearch Crawler', ''),
	'W3C [Linkcheck]'			=> array('W3C-checklink/', ''),
	'W3C [Validator]'			=> array('W3C_*Validator', ''),
	'WiseNut [Bot]'				=> array('http://www.WISEnutbot.com', ''),
	'YaCy [Bot]'				=> array('yacybot', ''),
	'Yahoo MMCrawler [Bot]'		=> array('Yahoo-MMCrawler/', ''),
	'Yahoo Slurp [Bot]'			=> array('Yahoo! DE Slurp', ''),
	'Yahoo [Bot]'				=> array('Yahoo! Slurp', ''),
	'YahooSeeker [Bot]'			=> array('YahooSeeker/', ''),
);
	
$bot_ids = array();
user_get_id_name($bot_ids, array_keys($bots), USER_IGNORE);
foreach($bot_ids as $bot)
{
	user_delete('remove', $bot);
}
// Done
add_bots($bots);
echo 'done';


/**
* Add the search bots into the database
* This code should be used in execute_last if the source database did not have bots
* If you are converting bots this function should not be called
* @todo We might want to look at sharing the bot list between the install code and this code for consistency
*/
function add_bots($bots)
{
	global $db, $config;

	$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = 'BOTS'";
	$result = $db->sql_query($sql);
	$group_id = (int) $db->sql_fetchfield('group_id', false, $result);
	$db->sql_freeresult($result);
	$db->sql_query('TRUNCATE TABLE ' . BOTS_TABLE);

	if (!$group_id)
	{
		add_default_groups();

		$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = 'BOTS'";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id', false, $result);
		$db->sql_freeresult($result);

	}




	foreach ($bots as $bot_name => $bot_ary)
	{
		$user_row = array(
			'user_type'				=> USER_IGNORE,
			'group_id'				=> $group_id,
			'username'				=> $bot_name,
			'user_regdate'			=> time(),
			'user_password'			=> '',
			'user_colour'			=> '9E8DA7',
			'user_email'			=> '',
			'user_lang'				=> $config['default_lang'],
			'user_style'			=> 1,
			'user_timezone'			=> 'UTC',
			'user_allow_massemail'	=> 0,
		);

		$user_id = user_add($user_row);

		if ($user_id)
		{
			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> $bot_name,
				'user_id'		=> $user_id,
				'bot_agent'		=> $bot_ary[0],
				'bot_ip'		=> $bot_ary[1])
			);
			$db->sql_query($sql);
		}
	}
}
