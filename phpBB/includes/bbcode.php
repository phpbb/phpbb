<?php
/***************************************************************************
 *                              bbcode.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define("BBCODE_UID_LEN", 10);

// global that holds loaded-and-prepared bbcode templates, so we only have to do 
// that stuff once.

$bbcode_tpl = null;

/**
 * Loads bbcode templates from the bbcode.tpl file of the current template set.
 * Creates an array, keys are bbcode names like "b_open" or "url", values
 * are the associated template.
 * Probably pukes all over the place if there's something really screwed
 * with the bbcode.tpl file.
 *
 * Nathan Codding, Sept 26 2001.
 */
function load_bbcode_template()
{
	global $template;
	$tpl_filename = $template->make_filename('bbcode.tpl');
	$tpl = fread(fopen($tpl_filename, 'r'), filesize($tpl_filename));
	
	// replace \ with \\ and then ' with \'.
	$tpl = str_replace('\\', '\\\\', $tpl);
	$tpl  = str_replace('\'', '\\\'', $tpl);
	
	// strip newlines.
	$tpl  = str_replace("\n", '', $tpl);
	
	// Turn template blocks into PHP assignment statements for the values of $bbcode_tpls..
	$tpl = preg_replace('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#', "\n" . '$bbcode_tpls[\'\\1\'] = \'\\2\';', $tpl);
	
	$bbcode_tpls = array();

	eval($tpl);
	
	return $bbcode_tpls;
}


/**
 * Prepares the loaded bbcode templates for insertion into preg_replace()
 * or str_replace() calls in the bbencode_second_pass functions. This
 * means replacing template placeholders with the appropriate preg backrefs
 * or with language vars. NOTE: If you change how the regexps work in 
 * bbencode_second_pass(), you MUST change this function.
 *
 * Nathan Codding, Sept 26 2001
 *
 */
function prepare_bbcode_template($bbcode_tpl)
{
	global $lang;
	
	$bbcode_tpl['olist_open'] = str_replace('{LIST_TYPE}', '\\1', $bbcode_tpl['olist_open']);
	
	$bbcode_tpl['color_open'] = str_replace('{COLOR}', '\\1', $bbcode_tpl['color_open']);
	
	$bbcode_tpl['size_open'] = str_replace('{SIZE}', '\\1', $bbcode_tpl['size_open']);
	
	$bbcode_tpl['quote_open'] = str_replace('{L_QUOTE}', $lang['Quote'], $bbcode_tpl['quote_open']);
	
	$bbcode_tpl['quote_username_open'] = str_replace('{L_QUOTE}', $lang['Quote'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{L_WROTE}', $lang['wrote'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{USERNAME}', '\\1', $bbcode_tpl['quote_username_open']);
	
	$bbcode_tpl['code_open'] = str_replace('{L_CODE}', $lang['Code'], $bbcode_tpl['code_open']);

	$bbcode_tpl['img'] = str_replace('{URL}', '\\1', $bbcode_tpl['img']);
	
	// We do URLs in several different ways..
	$bbcode_tpl['url1'] = str_replace('{URL}', '\\1\\2', $bbcode_tpl['url']);
	$bbcode_tpl['url1'] = str_replace('{DESCRIPTION}', '\\1\\2', $bbcode_tpl['url1']);
	
	$bbcode_tpl['url2'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url2'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url2']);
	
	$bbcode_tpl['url3'] = str_replace('{URL}', '\\1\\2', $bbcode_tpl['url']);
	$bbcode_tpl['url3'] = str_replace('{DESCRIPTION}', '\\3', $bbcode_tpl['url3']);
	
	$bbcode_tpl['url4'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url4'] = str_replace('{DESCRIPTION}', '\\2', $bbcode_tpl['url4']);

	$bbcode_tpl['email'] = str_replace('{EMAIL}', '\\1', $bbcode_tpl['email']);
	
	define("BBCODE_TPL_READY", true);
	
	return $bbcode_tpl;
}


/**
 * Does second-pass bbencoding. This should be used before displaying the message in
 * a thread. Assumes the message is already first-pass encoded, and we are given the 
 * correct UID as used in first-pass encoding.
 */
function bbencode_second_pass($text, $uid)
{
	global $lang, $bbcode_tpl;

	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// First: If there isn't a "[" and a "]" in the message, don't bother.
	if (! (strpos($text, "[") && strpos($text, "]")) )
	{
		// Remove padding, return.
		$text = substr($text, 1);
		return $text;
	}
	
	// Only load the templates ONCE..
	if (!defined("BBCODE_TPL_READY"))
	{
		// load templates from file into array.
		$bbcode_tpl = load_bbcode_template();
		
		// prepare array for use in regexps.
		$bbcode_tpl = prepare_bbcode_template($bbcode_tpl);
	}

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_second_pass_code($text, $uid, $bbcode_tpl);

	// [list] and [list=x] for (un)ordered lists.
	// unordered lists
	$text = str_replace("[list:$uid]", $bbcode_tpl['ulist_open'], $text);
	// li tags
	$text = str_replace("[*:$uid]", $bbcode_tpl['listitem'], $text);
	// ending tags
	$text = str_replace("[/list:u:$uid]", $bbcode_tpl['ulist_close'], $text);
	$text = str_replace("[/list:o:$uid]", $bbcode_tpl['olist_close'], $text);
	// Ordered lists
	$text = preg_replace("/\[list=([a1]):$uid\]/si", $bbcode_tpl['olist_open'], $text);

	// colours
	$text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", $bbcode_tpl['color_open'], $text);
	$text = str_replace("[/color:$uid]", $bbcode_tpl['color_close'], $text);

	// size
	$text = preg_replace("/\[size=([\-\+]?[1-2]?[0-9]):$uid\]/si", $bbcode_tpl['size_open'], $text);
	$text = str_replace("[/size:$uid]", $bbcode_tpl['size_close'], $text);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = str_replace("[quote:$uid]", $bbcode_tpl['quote_open'], $text);
	$text = str_replace("[/quote:$uid]", $bbcode_tpl['quote_close'], $text);
	
	$text = preg_replace("/\[quote:$uid=\"?(.*?)\"?\]/si", $bbcode_tpl['quote_username_open'], $text);

	// [b] and [/b] for bolding text.
	$text = str_replace("[b:$uid]", $bbcode_tpl['b_open'], $text);
	$text = str_replace("[/b:$uid]", $bbcode_tpl['b_close'], $text);

	// [u] and [/u] for underlining text.
	$text = str_replace("[u:$uid]", $bbcode_tpl['u_open'], $text);
	$text = str_replace("[/u:$uid]", $bbcode_tpl['u_close'], $text);

	// [i] and [/i] for italicizing text.
	$text = str_replace("[i:$uid]", $bbcode_tpl['i_open'], $text);
	$text = str_replace("[/i:$uid]", $bbcode_tpl['i_close'], $text);

	// Patterns and replacements for URL and email tags..
	$patterns = array();
	$replacements = array();

	// [img]image_url_here[/img] code..
	// This one gets first-passed..
	$patterns[0] = "#\[img:$uid\](.*?)\[/img:$uid\]#si";
	$replacements[0] = $bbcode_tpl['img'];
	
	// [url]xxxx://www.phpbb.com[/url] code..
	$patterns[1] = "#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si";
	$replacements[1] = $bbcode_tpl['url1'];

	// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
	$patterns[2] = "#\[url\](.*?)\[/url\]#si";
	$replacements[2] = $bbcode_tpl['url2'];

	// [url=xxxx://www.phpbb.com]phpBB[/url] code..
	$patterns[3] = "#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si";
	$replacements[3] = $bbcode_tpl['url3'];

	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[4] = "#\[url=(.*?)\](.*?)\[/url\]#si";
	$replacements[4] = $bbcode_tpl['url4'];

	// [email]user@domain.tld[/email] code..
	$patterns[5] = "#\[email\](.*?)\[/email\]#si";
	$replacements[5] = $bbcode_tpl['email'];

	$text = preg_replace($patterns, $replacements, $text);

	// Remove our padding from the string..
	$text = substr($text, 1);

	return $text;

} // bbencode_second_pass()

function make_bbcode_uid()
{
	// Unique ID for this message..
	$uid = md5(uniqid(rand()));
	$uid = substr($uid, 0, BBCODE_UID_LEN);

	return $uid;
}

function bbencode_first_pass($text, $uid)
{
	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_first_pass_pda($text, $uid, '[code]', '[/code]', '', true, '');

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = bbencode_first_pass_pda($text, $uid, '[quote]', '[/quote]', '', false, '');
	
	$text = bbencode_first_pass_pda($text, $uid, '/\[quote=(\\\\".*?\\\\")\]/is', '[/quote]', '', false, '', "[quote:$uid=\\1]");

	// [list] and [list=x] for (un)ordered lists.
	$open_tag = array();
	$open_tag[0] = "[list]";

	// unordered..
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:u]", false, 'replace_listitems');

	$open_tag[0] = "[list=1]";
	$open_tag[1] = "[list=a]";

	// ordered.
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:o]",  false, 'replace_listitems');

	// [color] and [/color] for setting text color
	$text = preg_replace("#\[color=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]#si", "[color=\\1:$uid]\\2[/color:$uid]", $text);

	// [size] and [/size] for setting text size
	$text = preg_replace("#\[size=([\-\+]?[1-2]?[0-9])\](.*?)\[/size\]#si", "[size=\\1:$uid]\\2[/size:$uid]", $text);

	// [b] and [/b] for bolding text.
	$text = preg_replace("#\[b\](.*?)\[/b\]#si", "[b:$uid]\\1[/b:$uid]", $text);

	// [u] and [/u] for underlining text.
	$text = preg_replace("#\[u\](.*?)\[/u\]#si", "[u:$uid]\\1[/u:$uid]", $text);

	// [i] and [/i] for italicizing text.
	$text = preg_replace("#\[i\](.*?)\[/i\]#si", "[i:$uid]\\1[/i:$uid]", $text);

	// [img]image_url_here[/img] code..
	$text = preg_replace("#\[img\](([a-z]+?)://([^, \n\r]+))\[/img\]#si", "[img:$uid]\\1[/img:$uid]", $text);

	// Remove our padding from the string..
	$text = substr($text, 1);


	return $text;

} // bbencode_first_pass()

/**
 * $text - The text to operate on.
 * $uid - The UID to add to matching tags.
 * $open_tag - The opening tag to match. Can be an array of opening tags.
 * $close_tag - The closing tag to match.
 * $close_tag_new - The closing tag to replace with.
 * $mark_lowest_level - boolean - should we specially mark the tags that occur
 * 					at the lowest level of nesting? (useful for [code], because
 *						we need to match these tags first and transform HTML tags
 *						in their contents..
 * $func - This variable should contain a string that is the name of a function.
 *				That function will be called when a match is found, and passed 2
 *				parameters: ($text, $uid). The function should return a string.
 *				This is used when some transformation needs to be applied to the
 *				text INSIDE a pair of matching tags. If this variable is FALSE or the
 *				empty string, it will not be executed.
 * If open_tag is an array, then the pda will try to match pairs consisting of
 * any element of open_tag followed by close_tag. This allows us to match things
 * like [list=A]...[/list] and [list=1]...[/list] in one pass of the PDA.
 *
 * NOTES:	- this function assumes the first character of $text is a space.
 *				- every opening tag and closing tag must be of the [...] format.
 */
function bbencode_first_pass_pda($text, $uid, $open_tag, $close_tag, $close_tag_new, $mark_lowest_level, $func, $open_regexp_replace = false)
{
	$open_tag_count = 0;

	if (!$close_tag_new || ($close_tag_new == ''))
	{
		$close_tag_new = $close_tag;
	}

	$close_tag_length = strlen($close_tag);
	$close_tag_new_length = strlen($close_tag_new);
	$uid_length = strlen($uid);

	$use_function_pointer = ($func && ($func != ''));

	$stack = array();

	if (is_array($open_tag))
	{
		if (0 == count($open_tag))
		{
			// No opening tags to match, so return.
			return $text;
		}
		$open_tag_count = count($open_tag);
	}
	else
	{
		// only one opening tag. make it into a 1-element array.
		$open_tag_temp = $open_tag;
		$open_tag = array();
		$open_tag[0] = $open_tag_temp;
		$open_tag_count = 1;
	}
	
	$open_is_regexp = false;
	
	if ($open_regexp_replace)
	{
		$open_is_regexp = true;
		if (!is_array($open_regexp_replace))
		{
			$open_regexp_temp = $open_regexp_replace;
			$open_regexp_replace = array();
			$open_regexp_replace[0] = $open_regexp_temp;
		}
	}
	
	if ($mark_lowest_level && $open_is_regexp)
	{
		message_die(GENERAL_ERROR, "Unsupported operation for bbcode_first_pass_pda().");
	}
	

	// Start at the 2nd char of the string, looking for opening tags.
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($text)))
	{
		$curr_pos = strpos($text, "[", $curr_pos);

		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending tag.
			$found_start = false;
			$which_start_tag = "";
			$start_tag_index = -1;
			for ($i = 0; $i < $open_tag_count; $i++)
			{
				// Grab everything until the first "]"...
				$possible_start = substr($text, $curr_pos, strpos($text, "]", $curr_pos + 1) - $curr_pos + 1);

				// Now compare, either using regexp or not.
				if ($open_is_regexp)
				{
					$match_result = array();
					// PREG regexp comparison.
					if (preg_match($open_tag[$i], $possible_start, $match_result))
					{
						$found_start = true;
						$which_start_tag = $match_result[0];
						$start_tag_index = $i;
						break;
					}
				}
				else
				{
					// straightforward string comparison.
					if (0 == strcasecmp($open_tag[$i], $possible_start))
					{
						$found_start = true;
						$which_start_tag = $open_tag[$i];
						$start_tag_index = $i;
						break;
					}
				}
			}

			if ($found_start)
			{
				// We have an opening tag.
				// Push its position, the text we matched, and its index in the open_tag array on to the stack, and then keep going to the right.
				$match = array("pos" => $curr_pos, "tag" => $which_start_tag, "index" => $start_tag_index);
				bbcode_array_push($stack, $match);
				++$curr_pos;
			}
			else
			{
				// check for a closing tag..
				$possible_end = substr($text, $curr_pos, $close_tag_length);
				if (0 == strcasecmp($close_tag, $possible_end))
				{
					// We have an ending tag.
					// Check if we've already found a matching starting tag.
					if (sizeof($stack) > 0)
					{
						// There exists a starting tag.
						$curr_nesting_depth = sizeof($stack);
						// We need to do 2 replacements now.
						$match = bbcode_array_pop($stack);
						$start_index = $match['pos'];
						$start_tag = $match['tag'];
						$start_length = strlen($start_tag);
						$start_tag_index = $match['index'];

						if ($open_is_regexp)
						{
							$start_tag = preg_replace($open_tag[$start_tag_index], $open_regexp_replace[$start_tag_index], $start_tag);
						}

						// everything before the opening tag.
						$before_start_tag = substr($text, 0, $start_index);

						// everything after the opening tag, but before the closing tag.
						$between_tags = substr($text, $start_index + $start_length, $curr_pos - $start_index - $start_length);

						// Run the given function on the text between the tags..
						if ($use_function_pointer)
						{
							$between_tags = $func($between_tags, $uid);
						}

						// everything after the closing tag.
						$after_end_tag = substr($text, $curr_pos + $close_tag_length);

						// Mark the lowest nesting level if needed.
						if ($mark_lowest_level && ($curr_nesting_depth == 1))
						{
							$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$curr_nesting_depth:$uid]";
							$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$curr_nesting_depth:$uid]";
						}
						else
						{
							if ($open_is_regexp)
							{
								$text = $before_start_tag . $start_tag;
							}
							else
							{
								$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$uid]";
							}
							$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$uid]";
						}

						$text .= $after_end_tag;

						// Now.. we've screwed up the indices by changing the length of the string.
						// So, if there's anything in the stack, we want to resume searching just after it.
						// otherwise, we go back to the start.
						if (sizeof($stack) > 0)
						{
							$match = bbcode_array_pop($stack);
							$curr_pos = $match['pos'];
							bbcode_array_push($stack, $match);
							++$curr_pos;
						}
						else
						{
							$curr_pos = 1;
						}
					}
					else
					{
						// No matching start tag found. Increment pos, keep going.
						++$curr_pos;
					}
				}
				else
				{
					// No starting tag or ending tag.. Increment pos, keep looping.,
					++$curr_pos;
				}
			}
		}
	} // while

	return $text;

} // bbencode_first_pass_pda()

/**
 * Does second-pass bbencoding of the [code] tags. This includes
 * running htmlspecialchars() over the text contained between
 * any pair of [code] tags that are at the first level of
 * nesting. Tags at the first level of nesting are indicated
 * by this format: [code:1:$uid] ... [/code:1:$uid]
 * Other tags are in this format: [code:$uid] ... [/code:$uid]
 */
function bbencode_second_pass_code($text, $uid, $bbcode_tpl)
{
	global $lang;

	$html_entities_match = array("#<#", "#>#");
	$html_entities_replace = array("&lt;", "&gt;");

	$code_start_html = $bbcode_tpl['code_open'];
	$code_end_html =  $bbcode_tpl['code_close'];

	// First, do all the 1st-level matches. These need an htmlspecialchars() run,
	// so they have to be handled differently.
	$match_count = preg_match_all("#\[code:1:$uid\](.*?)\[/code:1:$uid\]#si", $text, $matches);

	for ($i = 0; $i < $match_count; $i++)
	{
		$before_replace = $matches[1][$i];
		$after_replace = $matches[1][$i];

		$after_replace = preg_replace($html_entities_match, $html_entities_replace, $after_replace);
		
		// Replace all spaces with non-breaking spaces.
		$after_replace = str_replace(" ", "&nbsp;", $after_replace);
		
		// Replace 3 nbsp's with "&nbsp; &nbsp;" so non-tabbed code indents without making huge long lines.
		$after_replace = str_replace("&nbsp;&nbsp;&nbsp;", "&nbsp; &nbsp;", $after_replace);
		
		// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
		$after_replace = str_replace("\t", "&nbsp; &nbsp;", $after_replace);

		$str_to_match = "[code:1:$uid]" . $before_replace . "[/code:1:$uid]";

		$replacement = $code_start_html;
		$replacement .= $after_replace;
		$replacement .= $code_end_html;

		$text = str_replace($str_to_match, $replacement, $text);
	}

	// Now, do all the non-first-level matches. These are simple.
	$text = str_replace("[code:$uid]", $code_start_html, $text);
	$text = str_replace("[/code:$uid]", $code_end_html, $text);

	return $text;

} // bbencode_second_pass_code()

/**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */
function make_clickable($text)
{
	// pad it with a space so we can match things at the start of the 1st line.
	$ret = " " . $text;

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, or comma.
	$ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", $ret);

	// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// yyyy contains either alphanum, "-", or "."
	// zzzz is optional.. will contain everything up to the first space, newline, or comma.
	// This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
	// This is to keep it from getting annoying and matching stuff that's not meant to be a link.
	$ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i", "\\1<a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a>", $ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
	// After the @ sign, we accept anything up to the first space, linebreak, or comma.
	$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);

	// Remove our padding..
	$ret = substr($ret, 1);

	return($ret);
}

/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 */
function undo_make_clickable($text)
{
	$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
	$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);

	return $text;

}

/**
 * Nathan Codding - August 24, 2000.
 * Takes a string, and does the reverse of the PHP standard function
 * htmlspecialchars().
 */
function undo_htmlspecialchars($input)
{
	$input = preg_replace("/&gt;/i", ">", $input);
	$input = preg_replace("/&lt;/i", "<", $input);
	$input = preg_replace("/&quot;/i", "\"", $input);
	$input = preg_replace("/&amp;/i", "&", $input);

	return $input;
}

/**
 * This is used to change a [*] tag into a [*:$uid] tag as part
 * of the first-pass bbencoding of [list] tags. It fits the
 * standard required in order to be passed as a variable
 * function into bbencode_first_pass_pda().
 */
function replace_listitems($text, $uid)
{
	$text = str_replace("[*]", "[*:$uid]", $text);

	return $text;
}

/**
 * Escapes the "/" character with "\/". This is useful when you need
 * to stick a runtime string into a PREG regexp that is being delimited
 * with slashes.
 */
function escape_slashes($input)
{
	$output = str_replace('/', '\/', $input);
	return $output;
}

/**
 * This function does exactly what the PHP4 function array_push() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
 * method of doing it.
 */
function bbcode_array_push(&$stack, $value)
{
   $stack[] = $value;
   return(sizeof($stack));
}

/**
 * This function does exactly what the PHP4 function array_pop() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
 * method of doing it.
 */
function bbcode_array_pop(&$stack)
{
   $arrSize = count($stack);
   $x = 1;

   while(list($key, $val) = each($stack))
   {
      if($x < count($stack))
      {
	 		$tmpArr[] = $val;
      }
      else
      {
	 		$return_val = $val;
      }
      $x++;
   }
   $stack = $tmpArr;

   return($return_val);
}

?>