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

namespace phpbb\install\module\install_data\task;

use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Statement;
use phpbb\install\database_task;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\sequential_task;
use phpbb\language\language;

class add_bots extends database_task
{
	use sequential_task;

	/**
	 * A list of the web-crawlers/bots we recognise by default
	 *
	 * Candidates but not included:
	 * 'Accoona [Bot]'				'Accoona-AI-Agent/'
	 * 'ASPseek [Crawler]'			'ASPseek/'
	 * 'Boitho [Crawler]'			'boitho.com-dc/'
	 * 'Bunnybot [Bot]'				'powered by www.buncat.de'
	 * 'Cosmix [Bot]'				'cfetch/'
	 * 'Crawler Search [Crawler]'	'.Crawler-Search.de'
	 * 'Findexa [Crawler]'			'Findexa Crawler ('
	 * 'GBSpider [Spider]'			'GBSpider v'
	 * 'genie [Bot]'				'genieBot ('
	 * 'Hogsearch [Bot]'			'oegp v. 1.3.0'
	 * 'Insuranco [Bot]'			'InsurancoBot'
	 * 'IRLbot [Bot]'				'http://irl.cs.tamu.edu/crawler'
	 * 'ISC Systems [Bot]'			'ISC Systems iRc Search'
	 * 'Jyxobot [Bot]'				'Jyxobot/'
	 * 'Kraehe [Metasuche]'			'-DIE-KRAEHE- META-SEARCH-ENGINE/'
	 * 'LinkWalker'					'LinkWalker'
	 * 'MMSBot [Bot]'				'http://www.mmsweb.at/bot.html'
	 * 'Naver [Bot]'				'nhnbot@naver.com)'
	 * 'NetResearchServer'			'NetResearchServer/'
	 * 'Nimble [Crawler]'			'NimbleCrawler'
	 * 'Ocelli [Bot]'				'Ocelli/'
	 * 'Onsearch [Bot]'				'onCHECK-Robot'
	 * 'Orange [Spider]'			'OrangeSpider'
	 * 'Sproose [Bot]'				'http://www.sproose.com/bot'
	 * 'Susie [Sync]'				'!Susie (http://www.sync2it.com/susie)'
	 * 'Tbot [Bot]'					'Tbot/'
	 * 'Thumbshots [Capture]'		'thumbshots-de-Bot'
	 * 'Vagabondo [Crawler]'		'http://webagent.wise-guys.nl/'
	 * 'Walhello [Bot]'				'appie 1.1 (www.walhello.com)'
	 * 'WissenOnline [Bot]'			'WissenOnline-Bot'
	 * 'WWWeasel [Bot]'				'WWWeasel Robot v'
	 * 'Xaldon [Spider]'			'Xaldon WebSpider'
	 *
	 * @var array
	 */
	protected $bot_list = array(
		'AdsBot [Google]'			=> array('AdsBot-Google', ''),
		'Ahrefs [Bot]'				=> array('AhrefsBot/', ''),
		'Alexa [Bot]'				=> array('ia_archiver', ''),
		'Alta Vista [Bot]'			=> array('Scooter/', ''),
		'Amazon [Bot]'				=> array('Amazonbot/', ''),
		'Ask Jeeves [Bot]'			=> array('Ask Jeeves', ''),
		'Baidu [Spider]'			=> array('Baiduspider', ''),
		'Bing [Bot]'				=> array('bingbot/', ''),
		'DuckDuckGo [Bot]'			=> array('DuckDuckBot/', ''),
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
		'ichiro [Crawler]'			=> array('ichiro/', ''),
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
		'Semrush [Bot]'				=> array('SemrushBot/', ''),
		'Sensis [Crawler]'			=> array('Sensis Web Crawler', ''),
		'SEO Crawler'				=> array('SEO search Crawler/', ''),
		'Seoma [Crawler]'			=> array('Seoma [SEO Crawler]', ''),
		'SEOSearch [Crawler]'		=> array('SEOsearch/', ''),
		'Snappy [Bot]'				=> array('Snappy/1.1 ( http://www.urltrends.com/ )', ''),
		'Steeler [Crawler]'			=> array('http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', ''),
		'Synoo [Bot]'				=> array('SynooBot/', ''),
		'Telekom [Bot]'				=> array('crawleradmin.t-info@telekom.de', ''),
		'TurnitinBot [Bot]'			=> array('TurnitinBot/', ''),
		'Voyager [Bot]'				=> array('voyager/', ''),
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

	/**
	 * @var config
	 */
	protected $install_config;

	/**
	 * @var iohandler_interface
	 */
	protected $io_handler;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var string
	 */
	protected $groups_table;

	/**
	 * @var string
	 */
	protected $bots_table;

	/**
	 * @var DriverStatement|Statement
	 */
	protected $stmt;

	/**
	 * @var int
	 */
	protected $group_id;

	/**
	 * Constructor
	 *
	 * @param config				$install_config		Installer's config
	 * @param database				$db_helper			Database helper.
	 * @param iohandler_interface	$iohandler			Input-output handler for the installer
	 * @param container_factory		$container			Installer's DI container
	 * @param language				$language			Language provider
	 * @param string				$phpbb_root_path	Relative path to phpBB root
	 * @param string				$php_ext			PHP extension
	 */
	public function __construct(config $install_config,
								database $db_helper,
								iohandler_interface $iohandler,
								container_factory $container,
								language $language,
								string $phpbb_root_path,
								string $php_ext)
	{
		$this->install_config	= $install_config;
		$this->io_handler		= $iohandler;
		$this->language			= $language;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;

		$this->bots_table	= $container->get_parameter('tables.bots');
		$this->groups_table	= $container->get_parameter('tables.groups');

		parent::__construct(
			self::get_doctrine_connection($db_helper, $install_config),
			$this->io_handler,
			true
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->group_id = $this->install_config->get('bots_group_id');
		if ($this->group_id === false)
		{
			$sql = 'SELECT group_id FROM ' . $this->groups_table . " WHERE group_name = 'BOTS'";
			$result = $this->query($sql);

			try
			{
				$this->group_id = (int) $result->fetchOne();
				$result->free();
			}
			catch (Exception $e)
			{
				$this->group_id = 0;
			}

			$this->install_config->set('bots_group_id', $this->group_id);
		}

		if (!$this->group_id)
		{
			// If we reach this point then something has gone very wrong
			$this->io_handler->add_error_message('NO_GROUP');
		}

		$sql = 'INSERT INTO ' . $this->bots_table . ' '
			. '(bot_active, bot_name, user_id, bot_agent, bot_ip) VALUES '
			. '(:bot_active, :bot_name, :user_id, :bot_agent, :bot_ip)';
		$this->stmt = $this->create_prepared_stmt($sql);
		$this->execute($this->install_config, $this->bot_list);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute_step($key, $value) : void
	{
		if (!function_exists('user_add'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$user_id = user_add([
			'user_type'				=> USER_IGNORE,
			'group_id'				=> $this->group_id,
			'username'				=> $key,
			'user_regdate'			=> time(),
			'user_password'			=> '',
			'user_colour'			=> '9E8DA7',
			'user_email'			=> '',
			'user_lang'				=> $this->install_config->get('default_lang'),
			'user_style'			=> 1,
			'user_timezone'			=> 'UTC',
			'user_dateformat'		=> $this->language->lang('default_dateformat'),
			'user_allow_massemail'	=> 0,
			'user_allow_pm'			=> 0,
		]);

		if (!$user_id)
		{
			// If we can't insert this user then continue to the next one to avoid inconsistent data
			$this->io_handler->add_error_message('CONV_ERROR_INSERT_BOT');
			return;
		}

		$this->exec_prepared_stmt($this->stmt, [
			'bot_active'	=> 1,
			'bot_name'		=> (string) $key,
			'user_id'		=> (int) $user_id,
			'bot_agent'		=> (string) $value[0],
			'bot_ip'		=> (string) $value[1],
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count() : int
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name() : string
	{
		return 'TASK_ADD_BOTS';
	}
}
