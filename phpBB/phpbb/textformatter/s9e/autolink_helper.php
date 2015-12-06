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

namespace phpbb\textformatter\s9e;

class autolink_helper
{
	/**
	* Clean up and invalidate an AUTOLINK_TEXT tag if applicable
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag    AUTOLINK_TEXT tag
	* @param  \s9e\TextFormatter\Parser     $parser Parser
	* @return bool                                  Whether the tag is valid
	*/
	public function cleanup_tag(\s9e\TextFormatter\Parser\Tag $tag, \s9e\TextFormatter\Parser $parser)
	{
		// Remove the url attribute because it's not needed.
		$tag->removeAttribute('url');

		// Invalidate if the content of the tag matches the text attribute
		$text = substr($parser->getText(), $tag->getPos(), $tag->getLen());

		return ($text !== $tag->getAttribute('text'));
	}

	/**
	* Create an AUTOLINK_TEXT tag inside of a link created by the Autolink plugin
	*
	* Will only apply to URL tags that do not use any markup (e.g. not "[url]")
	* on the assumption that those tags were created by the Autolink plugin to
	* linkify URLs found in plain text
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag    URL tag (start tag)
	* @param  \s9e\TextFormatter\Parser     $parser Parser
	* @return bool                                  Always true to indicate that the tag is valid
	*/
	public function generate_autolink_text_tag(\s9e\TextFormatter\Parser\Tag $tag, \s9e\TextFormatter\Parser $parser)
	{
		// If the tag consumes any text then we ignore it because it's not a
		// linkified URL. Same if it's not paired with an end tag that doesn't
		// consume any text either
		if ($tag->getLen() > 0 || !$tag->getEndTag())
		{
			return true;
		}

		// Capture the text between the start tag and its end tag
		$start  = $tag->getPos();
		$end    = $tag->getEndTag()->getPos();
		$length = $end - $start;
		$text   = substr($parser->getText(), $start, $length);

		// Create a tag that consumes the link's text
		$parser->addSelfClosingTag('AUTOLINK_TEXT', $start, $length)->setAttribute('text', $text);

		return true;
	}

	/**
	* Remove the board's root URL from a the start of a string
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag       AUTOLINK_TEXT tag
	* @param  string                        $board_url Forum's root URL (with trailing slash)
	* @return bool                                     Always true to indicate that the tag is valid
	*/
	public function truncate_local_url(\s9e\TextFormatter\Parser\Tag $tag, $board_url)
	{
		$text = $tag->getAttribute('text');
		if (stripos($text, $board_url) === 0 && strlen($text) > strlen($board_url))
		{
			$tag->setAttribute('text', substr($text, strlen($board_url)));
		}

		return true;
	}

	/**
	* Truncate the replacement text set in an AUTOLINK_TEXT tag
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag AUTOLINK_TEXT tag
	* @return bool                               Always true to indicate that the tag is valid
	*/
	public function truncate_text(\s9e\TextFormatter\Parser\Tag $tag)
	{
		$text = $tag->getAttribute('text');
		if (utf8_strlen($text) > 55)
		{
			$text = utf8_substr($text, 0, 39) . ' ... ' . utf8_substr($text, -10);
		}

		$tag->setAttribute('text', $text);

		return true;
	}
}
