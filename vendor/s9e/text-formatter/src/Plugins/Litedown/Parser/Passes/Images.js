function parse()
{
	var pos = text.indexOf('![');
	if (pos === -1)
	{
		return;
	}
	if (text.indexOf('](', pos) > 0)
	{
		parseInlineImages();
	}
	if (hasReferences)
	{
		parseReferenceImages();
	}
}

/**
* Add an image tag for given text span
*
* @param {number} startPos Start tag position
* @param {number} endPos   End tag position
* @param {number} endLen   End tag length
* @param {string} linkInfo URL optionally followed by space and a title
* @param {string} alt      Value for the alt attribute
*/
function addImageTag(startPos, endPos, endLen, linkInfo, alt)
{
	var tag = addTagPair('IMG', startPos, 2, endPos, endLen);
	setLinkAttributes(tag, linkInfo, 'src');
	tag.setAttribute('alt', decode(alt));

	// Overwrite the markup
	overwrite(startPos, endPos + endLen - startPos);
}

/**
* Parse inline images markup
*/
function parseInlineImages()
{
	var m, regexp = /!\[(?:[^\x17[\]]|\[[^\x17[\]]*\])*\]\(( *(?:[^\x17\s()]|\([^\x17\s()]*\))*(?=[ )]) *(?:"[^\x17]*?"|'[^\x17]*?'|\([^\x17)]*\))? *)\)/g;
	while (m = regexp.exec(text))
	{
		var linkInfo = m[1],
			startPos = m['index'],
			endLen   = 3 + linkInfo.length,
			endPos   = startPos + m[0].length - endLen,
			alt      = m[0].substr(2, m[0].length - endLen - 2);

		addImageTag(startPos, endPos, endLen, linkInfo, alt);
	}
}

/**
* Parse reference images markup
*/
function parseReferenceImages()
{
	var m, regexp = /!\[((?:[^\x17[\]]|\[[^\x17[\]]*\])*)\](?: ?\[([^\x17[\]]+)\])?/g;
	while (m = regexp.exec(text))
	{
		var startPos = +m['index'],
			endPos   = startPos + 2 + m[1].length,
			endLen   = 1,
			alt      = m[1],
			id       = alt;

		if (m[2] > '' && linkReferences[m[2]])
		{
			endLen = m[0].length - alt.length - 2;
			id     = m[2];
		}
		else if (!linkReferences[id])
		{
			continue;
		}

		addImageTag(startPos, endPos, endLen, linkReferences[id], alt);
	}
}