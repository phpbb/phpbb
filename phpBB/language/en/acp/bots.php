<?php
/** 
*
* acp_bots [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

// Bot settings
$lang += array(
	'BOTS'				=> 'Manage Bots',
	'BOTS_EXPLAIN'		=> 'Bots or crawlers are automated agents most commonly used by search engines to update their databases. Since they rarely make proper use of sessions they can distort visitor counts, increase load and sometimes fail to index sites correctly. Here you can define a special type of user to overcome these problems.',
	'BOT_ACTIVATE'		=> 'Activate',
	'BOT_ACTIVE'		=> 'Bot active',
	'BOT_ADD'			=> 'Add bot',
	'BOT_ADDED'			=> 'New bot successfully added',
	'BOT_AGENT'			=> 'Agent match',
	'BOT_AGENT_EXPLAIN'	=> 'A string matching the bots browser agent, partial matches are allowed.',
	'BOT_DEACTIVATE'	=> 'Deactivate',
	'BOT_DELETED'		=> 'Bot deleted successfully',
	'BOT_EDIT'			=> 'Edit bots',
	'BOT_EDIT_EXPLAIN'	=> 'Here you can add or edit an existing bot entry. You may define an agent string and/or one or more IP addresses (or range of addresses) to match. Be careful when defining matching agent strings or addresses. You may also specify a style and language that the bot will view the board using. This may allow you to reduce bandwidth use by setting a simple style for bots. Remember to set appropriate permissions for the special Bot usergroup.',
	'BOT_LANG'			=> 'Bot language',
	'BOT_LANG_EXPLAIN'	=> 'The language presented to the bot as it browses.',
	'BOT_LAST_VISIT'	=> 'Last visit',
	'BOT_IP'			=> 'Bot IP address',
	'BOT_IP_EXPLAIN'	=> 'Partial matches are allowed, seperate addresses with an apostrophe. A single hostname may be entered instead of an IP.',
	'BOT_NAME'			=> 'Bot name',
	'BOT_NAME_EXPLAIN'	=> 'Used only for your own information.',
	'BOT_NEVER'			=> 'Never',
	'BOT_STYLE'			=> 'Bot style',
	'BOT_STYLE_EXPLAIN'	=> 'The style used for the board by the bot.',
	'BOT_UPDATED'		=> 'Existing bot updated successfully',
	'BOT_VIS'			=> 'Bot visible',
	'BOT_VIS_EXPLAIN'	=> 'Allow bot to be seen by all users in online lists.',

	'ERR_BOT_NO_IP'			=> 'The IP addresses you supplied were invalid or the hostname could not be resolved.',
	'ERR_BOT_NO_MATCHES'	=> 'You must supply at least one of an agent or IP for this bot match.',

	'NO_BOT'	=> 'Found no bot with the specified ID',
);

?>