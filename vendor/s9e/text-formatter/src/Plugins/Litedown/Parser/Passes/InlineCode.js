function parse()
{
	var markers = getInlineCodeMarkers(),
		i       = -1,
		cnt     = markers.length;
	while (++i < (cnt - 1))
	{
		var pos = markers[i].next,
			j   = i;
		if (text[markers[i].pos] !== '`')
		{
			// Adjust the left marker if its first backtick was escaped
			++markers[i].pos;
			--markers[i].len;
		}
		while (++j < cnt && markers[j].pos === pos)
		{
			if (markers[j].len === markers[i].len)
			{
				addInlineCodeTags(markers[i], markers[j]);
				i = j;
				break;
			}
			pos = markers[j].next;
		}
	}
}

/**
* Add the tag pair for an inline code span
*
* @param {!Object} left  Left marker
* @param {!Object} right Right marker
*/
function addInlineCodeTags(left, right)
{
	var startPos = left.pos,
		startLen = left.len + left.trimAfter,
		endPos   = right.pos - right.trimBefore,
		endLen   = right.len + right.trimBefore;
	addTagPair('C', startPos, startLen, endPos, endLen);
	overwrite(startPos, endPos + endLen - startPos);
}


/**
* Capture and return inline code markers
*
* @return {!Array<!Object>}
*/
function getInlineCodeMarkers()
{
	var pos = text.indexOf('`');
	if (pos < 0)
	{
		return [];
	}

	var regexp   = /(`+)(\s*)[^\x17`]*/g,
		trimNext = 0,
		markers  = [],
		_text    = text.replace(/\x1BB/g, '\\`'),
		m;
	regexp.lastIndex = pos;
	while (m = regexp.exec(_text))
	{
		markers.push({
			pos        : m['index'],
			len        : m[1].length,
			trimBefore : trimNext,
			trimAfter  : m[2].length,
			next       : m['index'] + m[0].length
		});
		trimNext = m[0].length - m[0].replace(/\s+$/, '').length;
	}

	return markers;
}