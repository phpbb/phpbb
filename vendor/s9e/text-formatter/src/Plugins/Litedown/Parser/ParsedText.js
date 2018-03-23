/**
* @var {boolean} Whether to decode HTML entities when decoding text
*/
var decodeHtmlEntities = config.decodeHtmlEntities;

/**
* @var {bool} Whether text contains escape characters
*/
var hasEscapedChars = false;

/**
* @var {bool} Whether text contains link references
*/
var hasReferences = false;

/**
* @dict
*/
var linkReferences = {};

if (text.indexOf('\\') >= 0)
{
	hasEscapedChars = true;

	// Encode escaped literals that have a special meaning otherwise, so that we don't have
	// to take them into account in regexps
	text = text.replace(
		/\\[!"'()*[\\\]^_`~]/g,
		function (str)
		{
			return {
				'\\!': "\x1B0", '\\"': "\x1B1", "\\'": "\x1B2", '\\(' : "\x1B3",
				'\\)': "\x1B4", '\\*': "\x1B5", '\\[': "\x1B6", '\\\\': "\x1B7",
				'\\]': "\x1B8", '\\^': "\x1B9", '\\_': "\x1BA", '\\`' : "\x1BB",
				'\\~': "\x1BC"
			}[str];
		}
	);
}

// We append a couple of lines and a non-whitespace character at the end of the text in
// order to trigger the closure of all open blocks such as quotes and lists
text += "\n\n\x17";

/**
* Decode a chunk of encoded text to be used as an attribute value
*
* Decodes escaped literals and removes slashes and 0x1A characters
*
* @param  {string} str Encoded text
* @return {string}     Decoded text
*/
function decode(str)
{
	if (HINT.LITEDOWN_DECODE_HTML_ENTITIES && decodeHtmlEntities && str.indexOf('&') > -1)
	{
		str = html_entity_decode(str);
	}
	str = str.replace(/\x1A/g, '');

	if (hasEscapedChars)
	{
		str = str.replace(
			/\x1B./g,
			function (seq)
			{
				return {
					"\x1B0": '!', "\x1B1": '"', "\x1B2": "'", "\x1B3": '(',
					"\x1B4": ')', "\x1B5": '*', "\x1B6": '[', "\x1B7": '\\',
					"\x1B8": ']', "\x1B9": '^', "\x1BA": '_', "\x1BB": '`',
					"\x1BC": '~'
				}[seq];
			}
		);
	}

	return str;
}

/**
* Test whether given position is preceded by whitespace
*
* @param  {number}  pos
* @return {boolean}
*/
function isAfterWhitespace(pos)
{
	return (pos > 0 && isWhitespace(text.charAt(pos - 1)));
}

/**
* Test whether given character is alphanumeric
*
* @param  {string}  chr
* @return {boolean}
*/
function isAlnum(chr)
{
	return (' abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.indexOf(chr) > 0);
}

/**
* Test whether given position is followed by whitespace
*
* @param  {number}  pos
* @return {boolean}
*/
function isBeforeWhitespace(pos)
{
	return isWhitespace(text[pos + 1]);
}

/**
* Test whether a length of text is surrounded by alphanumeric characters
*
* @param  {number}  pos Start of the text
* @param  {number}  len Length of the text
* @return {boolean}
*/
function isSurroundedByAlnum(pos, len)
{
	return (pos > 0 && isAlnum(text[pos - 1]) && isAlnum(text[pos + len]));
}

/**
* Test whether given character is an ASCII whitespace character
*
* NOTE: newlines are normalized to LF before parsing so we don't have to check for CR
*
* @param  {string}  chr
* @return {boolean}
*/
function isWhitespace(chr)
{
	return (" \n\t".indexOf(chr) > -1);
}

/**
* Mark the boundary of a block in the original text
*
* @param {number} pos
*/
function markBoundary(pos)
{
	text = text.substr(0, pos) + "\x17" + text.substr(pos + 1);
}

/**
* Overwrite part of the text with substitution characters ^Z (0x1A)
*
* @param  {number} pos Start of the range
* @param  {number} len Length of text to overwrite
*/
function overwrite(pos, len)
{
	if (len > 0)
	{
		text = text.substr(0, pos) + new Array(1 + len).join("\x1A") + text.substr(pos + len);
	}
}