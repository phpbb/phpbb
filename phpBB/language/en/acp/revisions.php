<?php
/**
*
* acp_revisions [English]
*
* @package language
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_REVISIONS'					=> 'Post Revision Tracking',
	'ACP_REVISIONS_PURGE'			=> 'Purge Revisions',
	'ACP_REVISION_SETTINGS'			=> 'Revision Settings',

	// Purging
	'PURGE_REVISIONS_TITLE'					=> 'Purge post revisions',
	'PURGE_REVISIONS_EXPLAIN'				=> 'All post revision will be purged from the database. This cannot be undone.',
	'PURGE_REVISIONS_CONFIRM'				=> 'Are you sure you wish to purge all post revisions?',
	'PURGE_REVISIONS_CONFIRM_EXPLAIN'		=> 'To purge post revisions, type <strong>%1$s</strong> into the box and press Submit',
	'PURGE_REVISIONS_CONFIRM_WORD'			=> 'confirm',
	'REVISIONS_PURGED_SUCCESS'				=> 'All post revisions have been purged from the database.',

	// Settings
	'REVISION_SETTINGS'				=> 'Revision settings',
	'REVISION_SETTINGS_EXPLAIN'		=> 'Here you can configure post revision tracking.',
	'REVISION_SETTINGS_UPDATED'		=> 'Revision tracking configuration updated successfully.',

	'REVISION_HISTORY'							=> 'Track post revision history',
	'REVISION_HISTORY_EXPLAIN'					=> 'Each time a post is edited, an a copy of the old post is saved. Authorized users can view post revision history and can change between revisions.',
	'REVISION_MAX_AGE'							=> 'Maximum revision age',
	'REVISION_MAX_AGE_EXPLAIN'					=> 'Set to 0 for unlimited revision age. Revisions older than the specified age will be removed automatically.',
	'REVISION_LIMIT'							=> 'Maximum revisions to save per post',
	'REVISION_LIMIT_EXPLAIN'					=> 'Set to 0 for unlimited revisions per post. Once revision threshold is reached, the oldest revision will be removed to make room for the new one. <strong>Protected revisions do not count toward the total number of allowed revisions.</strong>',
	'REVISION_WIKI_ALLOW'						=> 'Allow wiki posts',
	'REVISION_WIKI_ALLOW_EXPLAIN'				=> 'If enabled, authorized users can make their posts freely editable for other authorized users.',
	'REVISION_WIKI_LIMIT'						=> 'Maximum revisions to save per wiki post',
	'REVISION_WIKI_LIMIT_EXPLAIN'				=> 'Set to 0 for unlimited revisions per wiki post. Once revision threshold is reached, the oldest revision will be removed to make room for the new one.',
	'REVISION_CRON_AGE_FREQUENCY'				=> 'Old revision pruning frequency',
	'REVISION_CRON_AGE_FREQUENCY_EXPLAIN'		=> 'Number of days that cron should wait before running again.',
	'REVISION_CRON_EXCESS_FREQUENCY'			=> 'Excess revision pruning frequency',
	'REVISION_CRON_EXCESS_FREQUENCY_EXPLAIN'	=> 'Number of days that cron should wait before running again.',
));
