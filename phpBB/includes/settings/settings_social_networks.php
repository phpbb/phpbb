<?php
/**
*
* @package Icy Phoenix
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

$settings_details = array();
$settings_details = array(
	'id' => 'social_settings',
	'name' => '27_Social_Networks',
	'sort' => 0,
	'sub_name' => '',
	'sub_sort' => 0,
	'menu_name' => 'Preferences',
	'menu_sort' => 0,
	'clear_cache' => false,
);

$settings_data = array();
$settings_data = array(

	'enable_social_connect' => array(
		'lang_key' => 'Enable_Social_Networks_Login',
		'explain' => 'Enable_Social_Networks_Login_Explain',
		'type' => 'LIST_RADIO',
		'default' => 0,
		'values' => $this->list_yes_no,
	),

	'enable_facebook_login' => array(
		'separator' => 'Facebook_Login_Settings',
		'separator_explain' => 'Facebook_Login_Settings_explain',
		'lang_key' => 'Enable_Facebook_Login',
		'explain' => 'Enable_Facebook_Login_explain',
		'type' => 'LIST_RADIO',
		'default' => 0,
		'values' => $this->list_yes_no,
	),

	'facebook_app_id' => array(
		'lang_key' => 'Facebook_App_ID',
		'type' => 'VARCHAR',
		'default' => '',
	),

	'facebook_app_secret' => array(
		'lang_key' => 'Facebook_App_Secret',
		'type' => 'VARCHAR',
		'default' => '',
	),

);

$this->init_config($settings_details, $settings_data);

?>