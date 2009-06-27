<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

include($phpbb_root_path . 'includes/questionnaire/questionnaire.' . $phpEx);

class questionnaire_phpbb_data_provider
{
	var $config;
	var $unique_id;

	/**
	* Constructor.
	*
	* @param	array	$config
	* @param	string	$oldversion
	*/
	function __construct($config)
	{
		// generate a unique id if necessary
		if (!isset($config['questionnaire_unique_id']))
		{
			$this->unique_id = unique_id();
			set_config('questionnaire_unique_id', $this->unique_id);
		}
		else
		{
			$this->unique_id = $config['questionnaire_unique_id'];
		}

		$this->config = $config;
	}

	/**
	* Returns a string identifier for this data provider
	*
	* @return	string	"phpBB"
	*/
	function getIdentifier()
	{
		return 'phpBB';
	}


	/**
	* Get data about this phpBB installation.
	*
	* @return	array	Relevant anonymous config options
	*/
	function getData()
	{
		
		// Exclude certain config vars
		$exclude_config_vars = array(
			'avatar_gallery_path' => true,
			'avatar_path' => true,
			'avatar_salt' => true,
			'board_contact' => true,
			'board_disable_msg' => true,
			'board_email' => true,
			'board_email_sig' => true,
			'cookie_name' => true,
			'icons_path' => true,
			'icons_path' => true,
			'jab_host' => true,
			'jab_password' => true,
			'jab_port' => true,
			'jab_username' => true,
			'ldap_base_dn' => true,
			'ldap_email' => true,
			'ldap_password' => true,
			'ldap_port' => true,
			'ldap_server' => true,
			'ldap_uid' => true,
			'ldap_user' => true,
			'ldap_user_filter' => true,
			'ranks_path' => true,
			'script_path' => true,
			'server_name' => true,
			'server_port' => true,
			'server_protocol' => true,
			'site_desc' => true,
			'sitename' => true,
			'smilies_path' => true,
			'smtp_host' => true,
			'smtp_password' => true,
			'smtp_port' => true,
			'smtp_username' => true,
			'upload_icons_path' => true,
			'upload_path' => true,
			'newest_user_colour' => true,
			'newest_user_id' => true,
			'newest_username' => true,
			'rand_seed' => true,
		);

		$result = array();
		foreach ($this->config as $name => $value)
		{
			if (!isset($exclude_config_vars[$name]))
			{
				$result['config.' . $name] = $value;
			}
		}

		return $result;
	}
}