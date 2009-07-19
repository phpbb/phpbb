<?php
/**
*
* recaptcha [English]
*
* @package language
* @version $Id: recaptcha.php 9709 2009-06-30 14:23:16Z Kellanved $
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'CAPTCHA_QA'				=> 'Q&amp;A CAPTCHA',
	'CONFIRM_QUESTION_EXPLAIN'	=> 'Please answer this question to avoid automated registrations.',
	'CONFIRM_QUESTION_WRONG'	=> 'The answer to the confirmation question was not recognized.',

	'QUESTION_ANSWERS'			=> 'Answers',
	'ANSWERS_EXPLAIN'			=> 'The Answers. Please write one answer per line.',
	'CONFIRM_QUESTION'			=> 'Question',

	'ANSWER'					=> 'Answer',
	'QUESTIONS'					=> 'Questions',
	'QUESTIONS_EXPLAIN'			=> 'Here you can add enter and edit questions to be asked on registration to ward against automatted installs.',
	'QUESTION_DELETED'			=> 'Question deleted',
	'QUESTION_LANG'				=> 'Language',
	'QUESTION_LANG_EXPLAIN'		=> 'The language this question and its answers is written in.',
	'QUESTION_STRICT'			=> 'Strict check',
	'QUESTION_STRICT_EXPLAIN'	=> 'If enabled, the check also recognizes capitalization and whitespaces.',

	'QUESTION_TEXT'				=> 'Question',
	'QUESTION_TEXT_EXPLAIN'		=> 'The question that will be asked on registration.',

	'QA_ERROR_MSG'				=> 'Please fill out all fields and enter at least one answer.',


));

?>