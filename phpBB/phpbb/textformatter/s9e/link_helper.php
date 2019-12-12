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

class link_helper
{
	/**
	* Clean up and invalidate a LINK_TEXT tag if applicable
	*
	* Will invalidate the tag if its replacement text is the same as the original
	* text and would have no visible effect
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag    LINK_TEXT tag
	* @param  \s9e\TextFormatter\Parser     $parser Parser
	* @return void
	*/
	public function cleanup_tag(\s9e\TextFormatter\Parser\Tag $tag, \s9e\TextFormatter\Parser $parser)
	{
		// Invalidate if the content of the tag matches the text attribute
		$text = substr($parser->getText(), $tag->getPos(), $tag->getLen());
		if ($text === $tag->getAttribute('text'))
		{
			$tag->invalidate();
		}
	}

	/**
	* Create a LINK_TEXT tag inside of a link
	*
	* Meant to only apply to linkified URLs and [url] BBCodes without a parameter
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag    URL tag (start tag)
	* @param  \s9e\TextFormatter\Parser     $parser Parser
	* @return void
	*/
	public function generate_link_text_tag(\s9e\TextFormatter\Parser\Tag $tag, \s9e\TextFormatter\Parser $parser)
	{
		// Only create a LINK_TEXT tag if the start tag is paired with an end
		// tag, which is the case with tags from the Autolink plugins and with
		// the [url] BBCode when its content is used for the URL
		if (!$tag->getEndTag() || !$this->should_shorten($tag, $parser->getText()))
		{
			return;
		}

		// Capture the text between the start tag and its end tag
		$start  = $tag->getPos() + $tag->getLen();
		$end    = $tag->getEndTag()->getPos();
		$length = $end - $start;
		$text   = substr($parser->getText(), $start, $length);

		// Create a tag that consumes the link's text and make it depends on this tag
		$link_text_tag = $parser->addSelfClosingTag('LINK_TEXT', $start, $length, 10);
		$link_text_tag->setAttribute('text', $text);
		$tag->cascadeInvalidationTo($link_text_tag);
	}

	/**
	* Test whether we should shorten this tag's text
	*
	* Will test whether the tag either does not use any markup or uses a single
	* [url] BBCode
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag  URL tag
	* @param  string                        $text Original text
	* @return bool
	*/
	protected function should_shorten(\s9e\TextFormatter\Parser\Tag $tag, $text)
	{
		return ($tag->getLen() === 0 || strtolower(substr($text, $tag->getPos(), $tag->getLen())) === '[url]');
	}

	/**
	* Remove the board's root URL from a the start of a string
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag       LINK_TEXT tag
	* @param  string                        $board_url Forum's root URL (with trailing slash)
	* @return void
	*/
	public function truncate_local_url(\s9e\TextFormatter\Parser\Tag $tag, $board_url)
	{
		$text = $tag->getAttribute('text');
		if (stripos($text, $board_url) === 0 && strlen($text) > strlen($board_url))
		{
			$tag->setAttribute('text', substr($text, strlen($board_url)));
		}
	}

	/**
	* Truncate the replacement text set in a LINK_TEXT tag
	*
	* @param  \s9e\TextFormatter\Parser\Tag $tag LINK_TEXT tag
	* @return void
	*/
	public function truncate_text(\s9e\TextFormatter\Parser\Tag $tag)
	{
		$text = $tag->getAttribute('text');
		if (utf8_strlen($text) > 55)
		{
			$text = utf8_substr($text, 0, 39) . ' ... ' . utf8_substr($text, -10);
			$tag->setAttribute('text', $text);
		}
	}
}
