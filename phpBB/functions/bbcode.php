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
   *                                                           
   * 
   ***************************************************************************/ 

  //include('phptimer.php');


define("BBCODE_UID_LEN", 10);


function bbencode_second_pass(&$text)
{
	
	$uid_tag_length = strpos($text, ']') + 1;
	$uid = substr($text, 5, BBCODE_UID_LEN);
	$max_code_nesting = substr($text, BBCODE_UID_LEN + 6, ($uid_tag_length - BBCODE_UID_LEN - 7));
	$text = substr($text, $uid_tag_length);
	
	
	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;
	
	// First: If there isn't a "[" and a "]" in the message, don't bother.
	if (! (strpos($text, "[") && strpos($text, "]")) )
	{
		// Remove padding, return.
		$text = substr($text, 1);
		return TRUE;	
	}

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	bbencode_second_pass_code($text, $uid, $max_code_nesting);
	
	// [list] and [list=x] for (un)ordered lists.
	// unordered lists
	$text = str_replace("[list:$uid]", '<UL>', $text);
	// li tags
	$text = str_replace("[*:$uid]", '<LI>', $text);
	// ending tags
	$text = str_replace("[/list:u:$uid]", '</UL>', $text);
	$text = str_replace("[/list:o:$uid]", '</OL>', $text);
	// Ordered lists
	$text = preg_replace("/\[list=([a1]):$uid\]/si", '<OL TYPE="\1">', $text);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.	
	$text = str_replace("[quote:$uid]", '<TABLE BORDER="0" ALIGN="CENTER" WIDTH="85%"><TR><TD><font size="-1">Quote:</font><HR></TD></TR><TR><TD><FONT SIZE="-1"><BLOCKQUOTE>', $text);
	$text = str_replace("[/quote:$uid]", '</BLOCKQUOTE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE>', $text);
	
	// [b] and [/b] for bolding text.
	$text = str_replace("[b:$uid]", '<B>', $text);
	$text = str_replace("[/b:$uid]", '</B>', $text);
	
	// [i] and [/i] for italicizing text.
	$text = str_replace("[i:$uid]", '<I>', $text);
	$text = str_replace("[/i:$uid]", '</I>', $text);
	
	// [img]image_url_here[/img] code..
	$text = str_replace("[img:$uid]", '<IMG SRC="', $text);
	$text = str_replace("[/img:$uid]", '" BORDER="0"></IMG>', $text);

	// Patterns and replacements for URL and email tags..
	$patterns = array();
	$replacements = array();
	
	// [url]xxxx://www.phpbb.com[/url] code..
	$patterns[0] = "#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si";
	$replacements[0] = '<A HREF="\1\2" TARGET="_blank">\1\2</A>';
	
	// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
	$patterns[1] = "#\[url\](.*?)\[/url\]#si";
	$replacements[1] = '<A HREF="http://\1" TARGET="_blank">\1</A>';
	
	// [url=xxxx://www.phpbb.com]phpBB[/url] code.. 
	$patterns[2] = "#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si";
	$replacements[2] = '<A HREF="\1\2" TARGET="_blank">\3</A>';
	
	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[3] = "#\[url=(.*?)\](.*?)\[/url\]#si";
	$replacements[3] = '<A HREF="http://\1" TARGET="_blank">\2</A>';
	
	// [email]user@domain.tld[/email] code..
	$patterns[4] = "#\[email\](.*?)\[/email\]#si";
	$replacements[4] = '<A HREF="mailto:\1">\1</A>';
						
	$text = preg_replace($patterns, $replacements, $text);

	// Remove our padding from the string..
	$text = substr($text, 1);

	return TRUE;
}



function bbencode_first_pass($text)
{
	// Unique ID for this message..
	$uid = md5(uniqid(rand()));
	$uid = substr($uid, 0, BBCODE_UID_LEN);
	
	echo "UID LENGTH: " . strlen($uid) . "<br>";
	
	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$max_code_nesting = bbencode_first_pass_code($text, $uid);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.	
	bbencode_first_pass_quote($text, $uid);

	// [list] and [list=x] for (un)ordered lists.
	bbencode_first_pass_list($text, $uid);
	
	// [b] and [/b] for bolding text.
	$text = preg_replace("#\[b\](.*?)\[/b\]#si", "[b:$uid]\\1[/b:$uid]", $text);
	
	// [i] and [/i] for italicizing text.
	$text = preg_replace("#\[i\](.*?)\[/i\]#si", "[i:$uid]\\1[/i:$uid]", $text);
	
	// [img]image_url_here[/img] code..
	$text = preg_replace("#\[img\](.*?)\[/img\]#si", "[img:$uid]\\1[/img:$uid]", $text);
	
	// Remove our padding from the string..
	$text = substr($text, 1);

	// Add the uid tag to the start of the string..
	$text = '[uid=' . $uid . ':' . $max_code_nesting . ']' . $text;
	
	return $text;
	
} // bbencode_first_pass()


/**
 * Nathan Codding - Feb. 14, 2001.
 * Performs [quote][/quote] bbencoding on the given string.
 * Any unmatched "[quote]" or "[/quote]" token will just be left alone. 
 * This works fine with both having more than one quote in a message, and with nested quotes.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $text is a space, which is added by 
 * bbencode().
 */
function bbencode_first_pass_quote(&$text, $uid)
{
	// First things first: If there aren't any "[quote]" strings in the message, we don't
	// need to process it at all.
	
	if (!strpos(strtolower($text), "[quote]"))
	{
		return TRUE;	
	}
	
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($text)))
	{	
		$curr_pos = strpos($text, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending quote tag.
			$possible_start = substr($text, $curr_pos, 7);
			$possible_end = substr($text, $curr_pos, 8);
			if (strcasecmp("[quote]", $possible_start) == 0)
			{
				// We have a starting quote tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, $curr_pos);
				++$curr_pos;
			}
			else if (strcasecmp("[/quote]", $possible_end) == 0)
			{
				// We have an ending quote tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					// We need to do 2 replacements now.
					$start_index = bbcode_array_pop($stack);

					// everything before the [quote] tag.
					$before_start_tag = substr($text, 0, $start_index);

					// everything after the [quote] tag, but before the [/quote] tag.
					$between_tags = substr($text, $start_index + 7, $curr_pos - $start_index - 7);

					// everything after the [/quote] tag.
					$after_end_tag = substr($text, $curr_pos + 8);

					$text = $before_start_tag . "[quote:$uid]";
					$text .= $between_tags . "[/quote:$uid]";
					$text .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$curr_pos = array_pop($stack);
						bbcode_array_push($stack, $curr_pos);
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
	} // while
	
	return TRUE;
	
} // bbencode_first_pass_quote()


/**
 * Nathan Codding - Feb. 14, 2001.
 * Performs [code][/code] bbencoding on the given string.
 * Any unmatched "[code]" or "[/code]" token will just be left alone. 
 * This works fine with both having more than one code block in a message, and with nested code blocks.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_first_pass_code(&$text, $uid)
{
	// First things first: If there aren't any "[code]" strings in the message, we don't
	// need to process it at all.
	if (!strpos(strtolower($text), "[code]"))
	{
		return 0;
	}
	
	// Second things second: we have to watch out for stuff like [1code] or [/code1] in the 
	// input.. So escape them to [#1code] or [/code#1] for now:
	$temp_uid = md5(uniqid(rand()));
	
	$text = preg_replace("#\[([0-9]+?)code\]#si", "[$temp_uid:\\1code]", $text);
	$text = preg_replace("#\[/code([0-9]+?)\]#si", "[/code$temp_uid:\\1]", $text);
	
	$stack = Array();
	$curr_pos = 1;
	$max_nesting_depth = 0;
	while ($curr_pos && ($curr_pos < strlen($text)))
	{	
		$curr_pos = strpos($text, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending code tag.
			$possible_start = substr($text, $curr_pos, 6);
			$possible_end = substr($text, $curr_pos, 7);
			if (strcasecmp("[code]", $possible_start) == 0)
			{
				// We have a starting code tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, $curr_pos);
				++$curr_pos;
			}
			else if (strcasecmp("[/code]", $possible_end) == 0)
			{
				// We have an ending code tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					$curr_nesting_depth = sizeof($stack);
					$max_nesting_depth = ($curr_nesting_depth > $max_nesting_depth) ? $curr_nesting_depth : $max_nesting_depth;
					
					// We need to do 2 replacements now.
					$start_index = bbcode_array_pop($stack);

					// everything before the [code] tag.
					$before_start_tag = substr($text, 0, $start_index);

					// everything after the [code] tag, but before the [/code] tag.
					$between_tags = substr($text, $start_index + 6, $curr_pos - $start_index - 6);

					// everything after the [/code] tag.
					$after_end_tag = substr($text, $curr_pos + 7);

					$text = $before_start_tag . '[code:' . $curr_nesting_depth . ':' . $uid . ']';
					$text .= $between_tags . '[/code:' . $curr_nesting_depth . ':' . $uid . ']';
					$text .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$curr_pos = bbcode_array_pop($stack);
						bbcode_array_push($stack, $curr_pos);
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
	} // while
	
	// Undo our escaping from "second things second" above..
	$text = preg_replace("#\[$temp_uid:([0-9]+?)code\]#si", "[\\1code]", $text);
	$text = preg_replace("#\[/code$temp_uid:([0-9]+?)\]#si", "[/code\\1]", $text);
	
	return $max_nesting_depth;
	
} // bbencode_first_pass_code()



function bbencode_second_pass_code(&$text, $uid, $max_nesting_depth)
{
	for ($i = 1; $i <= $max_nesting_depth; ++$i)
	{
		$match_count = preg_match_all("#\[code:$i:$uid\](.*?)\[/code:$i:$uid\]#si", $text, $matches);

		for ($j = 0; $j < $match_count; $j++)
		{
			$before_replace = $matches[1][$j];
			$after_replace = $matches[1][$j];
			
			if ($i < 2)
			{
				// don't escape special chars when we're nested, 'cause it was already done
				// at the lower level..
				$after_replace = htmlspecialchars($after_replace);	
			}
			
			$str_to_match = "[code:$i:$uid]" . $before_replace . "[/code:$i:$uid]";
			
			$replacement = '<TABLE BORDER="0" ALIGN="CENTER" WIDTH="85%"><TR><TD><font size="-1">Code:</font><HR></TD></TR><TR><TD><FONT SIZE="-1"><PRE>';
			$replacement .= $after_replace;
			$replacement .= '</PRE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE>';
			
			$text = str_replace($str_to_match, $replacement, $text);
			
		}
	}
	
} // bbencode_second_pass_code()



/**
 * Nathan Codding - Jan. 12, 2001.
 * Performs [list][/list] and [list=?][/list] bbencoding on the given string, and returns the results.
 * Any unmatched "[list]" or "[/list]" token will just be left alone. 
 * This works fine with both having more than one list in a message, and with nested lists.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_first_pass_list(&$text, $uid)
{		
	$start_length = Array();
	$start_length['ordered'] = 8;
	$start_length['unordered'] = 6;
	
	// First things first: If there aren't any "[list" strings in the message, we don't
	// need to process it at all.
	
	if (!strpos(strtolower($text), "[list"))
	{
		return TRUE;	
	}
	
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($text)))
	{	
		$curr_pos = strpos($text, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending list tag.
			$possible_ordered_start = substr($text, $curr_pos, $start_length[ordered]);
			$possible_unordered_start = substr($text, $curr_pos, $start_length[unordered]);
			$possible_end = substr($text, $curr_pos, 7);
			if (strcasecmp("[list]", $possible_unordered_start) == 0)
			{
				// We have a starting unordered list tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, array($curr_pos, ""));
				++$curr_pos;
			}
			else if (preg_match("/\[list=([a1])\]/si", $possible_ordered_start, $matches))
			{
				// We have a starting ordered list tag.
				// Push its position on to the stack, and the starting char onto the start
				// char stack, the keep going to the right.
				bbcode_array_push($stack, array($curr_pos, $matches[1]));
				++$curr_pos;
			}
			else if (strcasecmp("[/list]", $possible_end) == 0)
			{
				// We have an ending list tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					// We need to do 2 replacements now.
					$start = bbcode_array_pop($stack);
					$start_index = $start[0];
					$start_char = $start[1];
					$is_ordered = ($start_char != "");
					$start_tag_length = ($is_ordered) ? $start_length[ordered] : $start_length[unordered];
					
					// everything before the [list] tag.
					$before_start_tag = substr($text, 0, $start_index);

					// everything after the [list] tag, but before the [/list] tag.
					$between_tags = substr($text, $start_index + $start_tag_length, $curr_pos - $start_index - $start_tag_length);
					// Need to replace [*] with <LI> inside the list.
					$between_tags = str_replace('[*]', "[*:$uid]", $between_tags);
					
					// everything after the [/list] tag.
					$after_end_tag = substr($text, $curr_pos + 7);

					if ($is_ordered)
					{
						$text = $before_start_tag . '[list=' . $start_char . ':' . $uid . ']';
						$text .= $between_tags . '[/list:o:' . $uid . ']';
					}
					else
					{
						$text = $before_start_tag . '[list:' . $uid . ']';
						$text .= $between_tags . '[/list:u:' . $uid . ']';
					}
					
					$text .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$a = bbcode_array_pop($stack);
						$curr_pos = $a[0];
						bbcode_array_push($stack, $a);
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
	} // while
	
	return TRUE;
	
} // bbencode_first_pass_list()




// END new 2-pass functions.




/**
 * bbdecode/bbencode functions:
 * Rewritten - Nathan Codding - Aug 24, 2000
 * quote, code, and list rewritten again in Jan. 2001.
 * All BBCode tags now implemented. Nesting and multiple occurances should be 
 * handled fine for all of them. Using str_replace() instead of regexps often
 * for efficiency. quote, list, and code are not regular, so they are 
 * implemented as PDAs - probably not all that efficient, but that's the way it is. 
 *
 * Note: all BBCode tags are case-insensitive.
 */
function bbdecode($message) {

		// Undo [code]
		$code_start_html = "<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Code:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><PRE>";
		$code_end_html = "</PRE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->";
		$message = str_replace($code_start_html, "[code]", $message);
		$message = str_replace($code_end_html, "[/code]", $message);

		// Undo [quote]
		$quote_start_html = "<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Quote:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><BLOCKQUOTE>";
		$quote_end_html = "</BLOCKQUOTE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->";
		$message = str_replace($quote_start_html, "[quote]", $message);
		$message = str_replace($quote_end_html, "[/quote]", $message);
		
		// Undo [b] and [i]
		$message = preg_replace("#<!-- BBCode Start --><B>(.*?)</B><!-- BBCode End -->#s", "[b]\\1[/b]", $message);
		$message = preg_replace("#<!-- BBCode Start --><I>(.*?)</I><!-- BBCode End -->#s", "[i]\\1[/i]", $message);
		
		// Undo [url] (both forms)
		$message = preg_replace("#<!-- BBCode Start --><A HREF=\"([a-z]+?://)(.*?)\" TARGET=\"_blank\">(.*?)</A><!-- BBCode End -->#s", "[url=\\1\\2]\\3[/url]", $message);
		
		// Undo [email]
		$message = preg_replace("#<!-- BBCode Start --><A HREF=\"mailto:(.*?)\">(.*?)</A><!-- BBCode End -->#s", "[email]\\1[/email]", $message);
		
		// Undo [img]
		$message = preg_replace("#<!-- BBCode Start --><IMG SRC=\"(.*?)\" BORDER=\"0\"><!-- BBCode End -->#s", "[img]\\1[/img]", $message);
		
		// Undo lists (unordered/ordered)
	
		// <li> tags:
		$message = str_replace("<!-- BBCode --><LI>", "[*]", $message);
		
		// [list] tags:
		$message = str_replace("<!-- BBCode ulist Start --><UL>", "[list]", $message);
		
		// [list=x] tags:
		$message = preg_replace("#<!-- BBCode olist Start --><OL TYPE=([A1])>#si", "[list=\\1]", $message);
		
		// [/list] tags:
		$message = str_replace("</UL><!-- BBCode ulist End -->", "[/list]", $message);
		$message = str_replace("</OL><!-- BBCode olist End -->", "[/list]", $message);

		return($message);
}

/**
 * Nathan Codding - Jan. 12, 2001.
 * Performs [quote][/quote] bbencoding on the given string, and returns the results.
 * Any unmatched "[quote]" or "[/quote]" token will just be left alone. 
 * This works fine with both having more than one quote in a message, and with nested quotes.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_quote($message)
{
	// First things first: If there aren't any "[quote]" strings in the message, we don't
	// need to process it at all.
	
	if (!strpos(strtolower($message), "[quote]"))
	{
		return $message;	
	}
	
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($message)))
	{	
		$curr_pos = strpos($message, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending quote tag.
			$possible_start = substr($message, $curr_pos, 7);
			$possible_end = substr($message, $curr_pos, 8);
			if (strcasecmp("[quote]", $possible_start) == 0)
			{
				// We have a starting quote tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, $curr_pos);
				++$curr_pos;
			}
			else if (strcasecmp("[/quote]", $possible_end) == 0)
			{
				// We have an ending quote tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					// We need to do 2 replacements now.
					$start_index = bbcode_array_pop($stack);

					// everything before the [quote] tag.
					$before_start_tag = substr($message, 0, $start_index);

					// everything after the [quote] tag, but before the [/quote] tag.
					$between_tags = substr($message, $start_index + 7, $curr_pos - $start_index - 7);

					// everything after the [/quote] tag.
					$after_end_tag = substr($message, $curr_pos + 8);

					$message = $before_start_tag . "<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Quote:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><BLOCKQUOTE>";
					$message .= $between_tags . "</BLOCKQUOTE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->";
					$message .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$curr_pos = array_pop($stack);
						bbcode_array_push($stack, $curr_pos);
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
	} // while
	
	return $message;
	
} // bbencode_quote()


/**
 * Nathan Codding - Jan. 12, 2001.
 * Performs [code][/code] bbencoding on the given string, and returns the results.
 * Any unmatched "[code]" or "[/code]" token will just be left alone. 
 * This works fine with both having more than one code block in a message, and with nested code blocks.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_code($message)
{
	// First things first: If there aren't any "[code]" strings in the message, we don't
	// need to process it at all.
	if (!strpos(strtolower($message), "[code]"))
	{
		return $message;	
	}
	
	// Second things second: we have to watch out for stuff like [1code] or [/code1] in the 
	// input.. So escape them to [#1code] or [/code#1] for now:
	$message = preg_replace("/\[([0-9]+?)code\]/si", "[#\\1code]", $message);
	$message = preg_replace("/\[\/code([0-9]+?)\]/si", "[/code#\\1]", $message);
	
	$stack = Array();
	$curr_pos = 1;
	$max_nesting_depth = 0;
	while ($curr_pos && ($curr_pos < strlen($message)))
	{	
		$curr_pos = strpos($message, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending code tag.
			$possible_start = substr($message, $curr_pos, 6);
			$possible_end = substr($message, $curr_pos, 7);
			if (strcasecmp("[code]", $possible_start) == 0)
			{
				// We have a starting code tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, $curr_pos);
				++$curr_pos;
			}
			else if (strcasecmp("[/code]", $possible_end) == 0)
			{
				// We have an ending code tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					$curr_nesting_depth = sizeof($stack);
					$max_nesting_depth = ($curr_nesting_depth > $max_nesting_depth) ? $curr_nesting_depth : $max_nesting_depth;
					
					// We need to do 2 replacements now.
					$start_index = bbcode_array_pop($stack);

					// everything before the [code] tag.
					$before_start_tag = substr($message, 0, $start_index);

					// everything after the [code] tag, but before the [/code] tag.
					$between_tags = substr($message, $start_index + 6, $curr_pos - $start_index - 6);

					// everything after the [/code] tag.
					$after_end_tag = substr($message, $curr_pos + 7);

					$message = $before_start_tag . "[" . $curr_nesting_depth . "code]";
					$message .= $between_tags . "[/code" . $curr_nesting_depth . "]";
					$message .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$curr_pos = bbcode_array_pop($stack);
						bbcode_array_push($stack, $curr_pos);
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
	} // while
	
	if ($max_nesting_depth > 0)
	{
		for ($i = 1; $i <= $max_nesting_depth; ++$i)
		{
			$start_tag = escape_slashes(preg_quote("[" . $i . "code]"));
			$end_tag = escape_slashes(preg_quote("[/code" . $i . "]"));
			
			$match_count = preg_match_all("/$start_tag(.*?)$end_tag/si", $message, $matches);
	
			for ($j = 0; $j < $match_count; $j++)
			{
				$before_replace = escape_slashes(preg_quote($matches[1][$j]));
				$after_replace = $matches[1][$j];
				
				if ($i < 2)
				{
					// don't escape special chars when we're nested, 'cause it was already done
					// at the lower level..
					$after_replace = htmlspecialchars($after_replace);	
				}
				
				$str_to_match = $start_tag . $before_replace . $end_tag;
				
				$message = preg_replace("/$str_to_match/si", "<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Code:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><PRE>$after_replace</PRE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->", $message);
			}
		}
	}
	
	// Undo our escaping from "second things second" above..
	$message = preg_replace("/\[#([0-9]+?)code\]/si", "[\\1code]", $message);
	$message = preg_replace("/\[\/code#([0-9]+?)\]/si", "[/code\\1]", $message);
	
	return $message;
	
} // bbencode_code()


/**
 * Nathan Codding - Jan. 12, 2001.
 * Performs [list][/list] and [list=?][/list] bbencoding on the given string, and returns the results.
 * Any unmatched "[list]" or "[/list]" token will just be left alone. 
 * This works fine with both having more than one list in a message, and with nested lists.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_list($message)
{		
	$start_length = Array();
	$start_length[ordered] = 8;
	$start_length[unordered] = 6;
	
	// First things first: If there aren't any "[list" strings in the message, we don't
	// need to process it at all.
	
	if (!strpos(strtolower($message), "[list"))
	{
		return $message;	
	}
	
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($message)))
	{	
		$curr_pos = strpos($message, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending list tag.
			$possible_ordered_start = substr($message, $curr_pos, $start_length[ordered]);
			$possible_unordered_start = substr($message, $curr_pos, $start_length[unordered]);
			$possible_end = substr($message, $curr_pos, 7);
			if (strcasecmp("[list]", $possible_unordered_start) == 0)
			{
				// We have a starting unordered list tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, array($curr_pos, ""));
				++$curr_pos;
			}
			else if (preg_match("/\[list=([a1])\]/si", $possible_ordered_start, $matches))
			{
				// We have a starting ordered list tag.
				// Push its position on to the stack, and the starting char onto the start
				// char stack, the keep going to the right.
				bbcode_array_push($stack, array($curr_pos, $matches[1]));
				++$curr_pos;
			}
			else if (strcasecmp("[/list]", $possible_end) == 0)
			{
				// We have an ending list tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					// We need to do 2 replacements now.
					$start = bbcode_array_pop($stack);
					$start_index = $start[0];
					$start_char = $start[1];
					$is_ordered = ($start_char != "");
					$start_tag_length = ($is_ordered) ? $start_length[ordered] : $start_length[unordered];
					
					// everything before the [list] tag.
					$before_start_tag = substr($message, 0, $start_index);

					// everything after the [list] tag, but before the [/list] tag.
					$between_tags = substr($message, $start_index + $start_tag_length, $curr_pos - $start_index - $start_tag_length);
					// Need to replace [*] with <LI> inside the list.
					$between_tags = str_replace("[*]", "<!-- BBCode --><LI>", $between_tags);
					
					// everything after the [/list] tag.
					$after_end_tag = substr($message, $curr_pos + 7);

					if ($is_ordered)
					{
						$message = $before_start_tag . "<!-- BBCode olist Start --><OL TYPE=" . $start_char . ">";
						$message .= $between_tags . "</OL><!-- BBCode olist End -->";
					}
					else
					{
						$message = $before_start_tag . "<!-- BBCode ulist Start --><UL>";
						$message .= $between_tags . "</UL><!-- BBCode ulist End -->";
					}
					
					$message .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$a = bbcode_array_pop($stack);
						$curr_pos = $a[0];
						bbcode_array_push($stack, $a);
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
	} // while
	
	return $message;
	
} // bbencode_list()


function bbencode($message) {

	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$message = " " . $message;
	
	// First: If there isn't a "[" and a "]" in the message, don't bother.
	if (! (strpos($message, "[") && strpos($message, "]")) )
	{
		// Remove padding, return.
		$message = substr($message, 1);
		return $message;	
	}

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$message = bbencode_code($message);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.	
	$message = bbencode_quote($message);

	// [list] and [list=x] for (un)ordered lists.
	$message = bbencode_list($message);
	
	// [b] and [/b] for bolding text.
	$message = preg_replace("/\[b\](.*?)\[\/b\]/si", "<!-- BBCode Start --><B>\\1</B><!-- BBCode End -->", $message);
	
	// [i] and [/i] for italicizing text.
	$message = preg_replace("/\[i\](.*?)\[\/i\]/si", "<!-- BBCode Start --><I>\\1</I><!-- BBCode End -->", $message);
	
	// [url]www.phpbb.com[/url] code..
	$message = preg_replace("/\[url\](http:\/\/)?(.*?)\[\/url\]/si", "<!-- BBCode Start --><A HREF=\"http://\\2\" TARGET=\"_blank\">\\2</A><!-- BBCode End -->", $message);
	
	// [url=xxxx://www.phpbb.com]phpBB[/url] code.. 
	$message = preg_replace("#\[url=([a-z]+?://)?(.*?)\](.*?)\[/url\]#si", "<!-- BBCode Start --><A HREF=\"\\1\\2\" TARGET=\"_blank\">\\3</A><!-- BBCode End -->", $message);
	
	// [email]user@domain.tld[/email] code..
	$message = preg_replace("/\[email\](.*?)\[\/email\]/si", "<!-- BBCode Start --><A HREF=\"mailto:\\1\">\\1</A><!-- BBCode End -->", $message);
	
	// [img]image_url_here[/img] code..
	$message = preg_replace("/\[img\](.*?)\[\/img\]/si", "<!-- BBCode Start --><IMG SRC=\"\\1\" BORDER=\"0\"><!-- BBCode End -->", $message);
	
	// Remove our padding from the string..
	$message = substr($message, 1);
	return $message;
	
} // bbencode()


/**
 * Nathan Codding - Oct. 30, 2000
 *
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
 * James Atkinson - Feb 5, 2001
 * This function does exactly what the PHP4 function array_push() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with out own 
 * method of doing it.
 */
function bbcode_array_push(&$stack, $value) {
   $stack[] = $value;
   return(sizeof($stack));
}

/**
 * James Atkinson - Feb 5, 2001
 * This function does exactly what the PHP4 function array_pop() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with out own
 * method of doing it.
 */
function bbcode_array_pop(&$stack) {
   $arrSize = count($stack);
   $x = 1;
   while(list($key, $val) = each($stack)) {
      if($x < count($stack)) {
	 $tmpArr[] = $val;
      }
      else {
	 $return_val = $val;
      }
      $x++;
   }
   $stack = $tmpArr;
   return($return_val);
}


?>
