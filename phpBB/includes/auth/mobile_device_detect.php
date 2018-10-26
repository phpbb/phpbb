<?php
/**
* @package phpBB Extension - Mobile Device
* @copyright (c) 2015 Sniper_E - http://www.sniper-e.com
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @copyright (c) 2015 martin - http://www.martins-phpbb.com
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace sniper\mobiledevice\core;

class functions
{
	/** @var \phpbb\request\request */
	protected $request;
	
	/** @var \phpbb\user */
	protected $user;
	
	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/**
	* Constructor
	*
	* @param \phpbb\request\request   	$request
	* @param \phpbb\user              	$user
	* @param \phpbb\config\config     	$config
	* @param \phpbb\template\template 	$template
	* @param \phpbb\extension\manager 	$extension_manager
	*
	*/
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\user $user,
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\extension\manager $extension_manager
	)
	{
		$this->request           = $request;
		$this->user              = $user;
		$this->config            = $config;
		$this->template          = $template;
		$this->extension_manager = $extension_manager;
	}
	
	public function mobile_device_detect($iphone = true, $ipod = true, $ipad = true, $android = true, $opera = true, $blackberry = true, $palm = true, $windows = true, $lg = true)
	{
		$mobile_browser = false;
		$user_agent     = $this->request->server('HTTP_USER_AGENT');
		
		switch (true)
		{		
			case (preg_match('/x86_64|WOW64|Win64|Iceweasel/i',$user_agent) && $this->config['mobile_test_enable']);
				$status = $this->user->lang['DESKTOP'];
				$mobile_browser = true;
			break;
			case (preg_match('/Bot|CFNetwork|libwww|Java|Jigsaw|SpreadTrum|httpget/i',$user_agent)) || $this->user->data['is_bot'];
				$mobile_browser = false;
			break;
			case (preg_match('/ipad/i',$user_agent));
				$status = $this->user->lang['IPAD'];
				$mobile_browser = $ipad;
			break;
			case (preg_match('/ipod/i',$user_agent));
				$status = $this->user->lang['IPOD'];
				$mobile_browser = $ipod;
			break;
			case (preg_match('/iphone/i',$user_agent));
				$status = $this->user->lang['IPHONE'];
				$mobile_browser = $iphone;
			break;
			case (preg_match('/android/i',$user_agent));
				if (preg_match('/SM-G870A/i',$user_agent))
				{
					$status = $this->user->lang['SGS5A'];
				}
				else if (preg_match('/SM-G900A|SM-G900F|SM-G900H|SM-G900M|SM-G900P|SM-G900R4|SM-G900T|SM-G900V|SM-G900W8|SM-G800F/i',$user_agent))
				{
					$status = $this->user->lang['SGS5'];
				}
				else if (preg_match('/SM-G920F/i',$user_agent))
				{
					$status = $this->user->lang['SGS6'];
				}
				else if (preg_match('/SGH-I497/i',$user_agent))
				{
					$status = $this->user->lang['SG2T'];
				}
				else if (preg_match('/GT-P5210|SM-T110|SM-T310/i',$user_agent))
				{
					$status = $this->user->lang['SGT3'];
				}
				else if (preg_match('/SM-T210/i',$user_agent))
				{
					$status = $this->user->lang['SGT3W'];
				}
				else if (preg_match('/SM-T335|SM-T530/i',$user_agent))
				{
					$status = $this->user->lang['SGT4'];
				}
				else if (preg_match('/SM-T520/i',$user_agent))
				{
					$status = $this->user->lang['SGTP'];
				}
				else if (preg_match('/SGH-I537/i',$user_agent))
				{
					$status = $this->user->lang['SGS4A'];
				}
				else if (preg_match('/GT-I9505|GT-I9500|SPH-L720T/i',$user_agent))
				{
					$status = $this->user->lang['SGS4'];
				}
				else if (preg_match('/GT-I9100P/i',$user_agent))
				{
					$status = $this->user->lang['SGS2'];
				}
				else if (preg_match('/SM-N9005|SM-P600/i',$user_agent))
				{
					$status = $this->user->lang['SGN3'];
				}
				else if (preg_match('/SM-N7505/i',$user_agent))
				{
					$status = $this->user->lang['SGN3N'];
				}
				else if (preg_match('/SM-N910C|SM-N910F/i',$user_agent))
				{
					$status = $this->user->lang['SGN4'];
				}
				else if (preg_match('/SM-N920P/i',$user_agent))
				{
					$status = $this->user->lang['SGN5'];
				}
				else if (preg_match('/SM-G357FZ/i',$user_agent))
				{
					$status = $this->user->lang['SGA4'];
				}
				else if (preg_match('/SM-G925P/i',$user_agent))
				{
					$status = $this->user->lang['SGS6E'];
				}
				else if (preg_match('/SM-G935F/i',$user_agent))
				{
					$status = $this->user->lang['SGS7E'];
				}
				else if (preg_match('/SM-G950F|SM-G955F/i',$user_agent))
				{
					$status = $this->user->lang['SGS8'];
				}
				else if (preg_match('/GT-S7582/i',$user_agent))
				{
					$status = $this->user->lang['SGSD2'];
				}
				else if (preg_match('/GT-I9100P/i',$user_agent))
				{
					$status = $this->user->lang['SGS2'];
				}
				else if (preg_match('/HONORPLK-L01/i',$user_agent))
				{
					$status = $this->user->lang['HPL01'];
				}
				else if (preg_match('/EVA-L09/i',$user_agent))
				{
					$status = $this->user->lang['HPL09'];
				}
				else if (preg_match('/VNS-L23/i',$user_agent))
				{
					$status = $this->user->lang['HPL23'];
				}
				else if (preg_match('/IMM76B/i',$user_agent))
				{
					$status = $this->user->lang['SGN'];
				}
				else if (preg_match('/TF101/i',$user_agent))
				{
					$status = $this->user->lang['ATT'];
				}
				else if (preg_match('/Archos 40b/i',$user_agent))
				{
					$status = $this->user->lang['A4TS'];
				}
				else if (preg_match('/A0001/i',$user_agent))
				{
					$status = $this->user->lang['OPO'];
				}
				else if (preg_match('/Orange Nura/i',$user_agent))
				{
					$status = $this->user->lang['ORN'];
				}
				else if (preg_match('/XT1030/i',$user_agent))
				{
					$status = $this->user->lang['MDM'];
				}
				else if (preg_match('/TIANYU-KTOUCH/i',$user_agent))
				{
					$status = $this->user->lang['TKT'];
				}
				else if (preg_match('/D2005|D2105/i',$user_agent))
				{
					$status = $this->user->lang['SXED'];
				}
				else if (preg_match('/C2005|D2303/i',$user_agent))
				{
					$status = $this->user->lang['SXM2'];
				}
				else if (preg_match('/C6906/i',$user_agent))
				{
					$status = $this->user->lang['SXZ1'];
				}
				else if (preg_match('/D5803/i',$user_agent))
				{
					$status = $this->user->lang['SXZ3'];
				}
				else if (preg_match('/P710/i',$user_agent))
				{
					$status = $this->user->lang['LGOL7IT'];
				}
				else if (preg_match('/LG-H850/i',$user_agent))
				{
					$status = $this->user->lang['LGH850'];
				}
				else if (preg_match('/LG-V500/i',$user_agent))
				{
					$status = $this->user->lang['LGV500'];
				}
				else if (preg_match('/lg/i',$user_agent))
				{
					$status = $this->user->lang['LG'];
				}
				else if (preg_match('/ASUS_T00J/i',$user_agent))
				{
					$status = $this->user->lang['ATOOJ'];
				}
				else if (preg_match('/Aquaris E5/i',$user_agent))
				{
					$status = $this->user->lang['AE5HD'];
				}
				else if (preg_match('/HTC Desire|626s/i',$user_agent))
				{
					$status = $this->user->lang['HTCD'];
				}
				else if (preg_match('/Nexus One/i',$user_agent))
				{
					$status = $this->user->lang['N1'];
				}
				else if (preg_match('/Nexus 4|LRX22C|LVY48F|LMY47V/i',$user_agent))
				{
					$status = $this->user->lang['N4'];
				}
				else if (preg_match('/Nexus 5|LMY48S/i',$user_agent))
				{
					$status = $this->user->lang['N5'];
				}
				else if (preg_match('/Nexus 7|KTU84P/i',$user_agent))
				{
					$status = $this->user->lang['N7'];
				}
				else if (preg_match('/Nexus 9|LMY47X/i',$user_agent))
				{
					$status = $this->user->lang['N9'];
				}
				else if (preg_match('/Lenovo_K50_T5/i',$user_agent))
				{
					$status = $this->user->lang['LK50T5'];
				}
				else
				{
					$status = $this->user->lang['ANDROID'];
				}
				$mobile_browser = $android;
			break;
			case (preg_match('/opera mini/i',$user_agent));
				$status = $this->user->lang['MOBILE_DEVICE'];
				$mobile_browser = $opera;
			break;
			case (preg_match('/blackberry/i',$user_agent));
				if (preg_match('/BlackBerry9900|BlackBerry9930|BlackBerry9790|BlackBerry9780|BlackBerry9700|BlackBerry9650|BlackBerry9000|/i',$user_agent))
				{
					$status = 'BlackBerry Bold';
				}
				else if (preg_match('/BlackBerry9380|BlackBerry9370|BlackBerry9360|BlackBerry9350|BlackBerry9330|BlackBerry9320|BlackBerry9300|BlackBerry9220|BlackBerry8980|BlackBerry8900|BlackBerry8530|BlackBerry8520|BlackBerry8330|BlackBerry8320|BlackBerry8310|BlackBerry8300/i',$user_agent))
				{
					$status = $this->user->lang['BBCURVE'];
				}
				else if (preg_match('/BlackBerry9860|BlackBerry9850|BlackBerry9810|BlackBerry9800/i',$user_agent))
				{
					$status = $this->user->lang['BBTORCH'];
				}
				else if (preg_match('/BlackBerry9900/i',$user_agent))
				{
					$status = $this->user->lang['BBTOUCH'];
				}
				else if (preg_match('/BlackBerry9105/i',$user_agent))
				{
					$status = $this->user->lang['BBPEARL'];
				}
				else if (preg_match('/BlackBerry8220/i',$user_agent))
				{
					$status = $this->user->lang['BBPEARLF'];
				}
				else if (preg_match('/BlackBerry Storm|BlackBerry Storm2/i',$user_agent))
				{
					$status = $this->user->lang['BBSTORM'];
				}
				else if (preg_match('/BlackBerry Passport/i',$user_agent))
				{
					$status = $this->user->lang['BBPP'];
				}
				else if (preg_match('/BlackBerry Porsche/i',$user_agent))
				{
					$status = $this->user->lang['BBP'];
				}
				else if (preg_match('/BlackBerry PlayBook/i',$user_agent))
				{
					$status = $this->user->lang['BBPB'];
				}
				else
				{
					$status = $this->user->lang['BLACKBERRY'];
				}
				$mobile_browser = $blackberry;
			break;
			case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent));
				$status = $this->user->lang['PALM'];
				$mobile_browser = $palm;
			break;
			case (preg_match('/(iris|3g_t|windows ce|windows Phone|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent));
				if (preg_match('/Lumia 640 XL/i',$user_agent))
				{
					$status = $this->user->lang['L640XL'];
				}
				else
				{
					$status = $this->user->lang['WSP'];
				}
				$mobile_browser = $windows;
			break;
			case (preg_match('/lge vx10000/i',$user_agent));
				$status = $this->user->lang['VOYAGER'];
				$mobile_browser = $windows;
			break;
			case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent));
				$status = $this->user->lang['MOBILE_DEVICE'];
				$mobile_browser = true;
			break;
			case (isset($post['HTTP_X_WAP_PROFILE'])||isset($post['HTTP_PROFILE']));
				$status = $this->user->lang['MOBILE_DEVICE'];
				$mobile_browser = true;
			break;
			case (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',)));
				$status = $this->user->lang['MOBILE_DEVICE'];
				$mobile_browser = true;
			break;
			default;
				$status = $this->user->lang['DESKTOP'];
				$mobile_browser = false;
			break;
		}
		header('Cache-Control: no-transform');
		header('Vary: User-Agent');
		
		if ($mobile_browser=='')
		{
			return $mobile_browser;
		}
		else
		{
			return array($mobile_browser,$status);
		}
	}

	public function assign_authors()
	{
		$md_manager = $this->extension_manager->create_extension_metadata_manager('sniper/mobiledevice', $this->template);
		$meta = $md_manager->get_metadata();
		$author_names = array();
		$author_homepages = array();

		foreach (array_slice($meta['authors'], 0, 3) as $author)
		{
			$author_names[] = $author['name'];
			$author_homepages[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $author['homepage'], $author['name']);
		}

		$this->template->assign_vars(array(
			'MOBILEDEVICE_DISPLAY_NAME'     => $meta['extra']['display-name'],
			'MOBILEDEVICE_AUTHOR_NAMES'     => implode(' &amp; ', $author_names),
			'MOBILEDEVICE_AUTHOR_HOMEPAGES' => implode(' &amp; ', $author_homepages),
		));

		return;
	}
	
	public function page_name()
	{
		$this_page = explode(".", $this->user->page['page']);
		$this_page_name = $this_page[0];
		
		return($this_page_name);
	}
}
