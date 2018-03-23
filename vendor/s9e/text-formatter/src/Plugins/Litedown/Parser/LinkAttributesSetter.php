<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser;
use s9e\TextFormatter\Parser\Tag;
trait LinkAttributesSetter
{
	protected function setLinkAttributes(Tag $tag, $linkInfo, $attrName)
	{
		$url   = \trim($linkInfo);
		$title = '';
		$pos   = \strpos($url, ' ');
		if ($pos !== \false)
		{
			$title = \substr(\trim(\substr($url, $pos)), 1, -1);
			$url   = \substr($url, 0, $pos);
		}
		$tag->setAttribute($attrName, $this->text->decode($url));
		if ($title > '')
			$tag->setAttribute('title', $this->text->decode($title));
	}
}