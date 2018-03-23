/**
* Set a URL or IMG tag's attributes
*
* @param {!Tag}    tag      URL or IMG tag
* @param {string} linkInfo Link's info: an URL optionally followed by spaces and a title
* @param {string} attrName Name of the URL attribute
*/
function setLinkAttributes(tag, linkInfo, attrName)
{
	var url   = linkInfo.replace(/^\s*/, '').replace(/\s*$/, ''),
		title = '',
		pos   = url.indexOf(' ')
	if (pos !== -1)
	{
		title = url.substr(pos).replace(/^\s*\S/, '').replace(/\S\s*$/, '');
		url   = url.substr(0, pos);
	}

	tag.setAttribute(attrName, decode(url));
	if (title > '')
	{
		tag.setAttribute('title', decode(title));
	}
}